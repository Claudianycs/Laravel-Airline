<?php

namespace Tests\MySql;

use App\Models\Aircraft;
use App\Models\Customer;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    protected function beforeRefreshingDatabase(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Execute esta suíte no MySQL, usando um banco exclusivo de testes.');
        }
        $this->assertStringEndsWith('_test', DB::connection()->getDatabaseName(), 'Use um banco de testes com sufixo _test.');
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('Este teste de concorrência requer a extensão pcntl (Linux).');
        }
    }

    public function test_two_simultaneous_sales_cannot_sell_the_last_seat_twice(): void
    {
        $agent = User::factory()->agent()->create();
        $aircraft = Aircraft::factory()->create(['capacity' => 1]);
        $flight = Flight::factory()->create(['aircraft_id' => $aircraft->id, 'airline_id' => $aircraft->airline_id]);
        $customers = Customer::factory()->count(2)->create();
        $dir = sys_get_temp_dir().'/airline-race-'.bin2hex(random_bytes(8));
        mkdir($dir, 0700);
        $children = [];
        DB::disconnect();
        try {
            foreach ($customers as $index => $customer) {
                $pid = pcntl_fork();
                $this->assertNotSame(-1, $pid);
                if ($pid === 0) {
                    try {
                        DB::purge();
                        $deadline = microtime(true) + 10;
                        while (! file_exists($dir.'/go') && microtime(true) < $deadline) {
                            usleep(1000);
                        }
                        if (! file_exists($dir.'/go')) {
                            throw new \RuntimeException('Timeout de sincronização.');
                        }
                        app(TicketService::class)->issue(User::findOrFail($agent->id), ['flight_id' => $flight->id, 'customer_id' => $customer->id, 'status' => 'paid', 'payment_method' => 'pix']);
                        file_put_contents($dir.'/'.$index, 'sold');
                        exit(0);
                    } catch (ValidationException $e) {
                        file_put_contents($dir.'/'.$index, isset($e->errors()['flight_id']) ? 'full' : 'unexpected-validation');
                        exit(0);
                    } catch (\Throwable $e) {
                        file_put_contents($dir.'/'.$index, 'error:'.get_class($e));
                        exit(1);
                    }
                }
                $children[] = $pid;
            }
            touch($dir.'/go');
            foreach ($children as $pid) {
                pcntl_waitpid($pid, $status);
                $this->assertSame(0, pcntl_wexitstatus($status));
            }
            DB::purge();
            $results = [file_get_contents($dir.'/0'), file_get_contents($dir.'/1')];
            sort($results);
            $this->assertSame(['full', 'sold'], $results);
            $this->assertSame(1, Ticket::where('flight_id', $flight->id)->count());
            $this->assertSame(0, $flight->fresh()->available_seats);
        } finally {
            foreach (glob($dir.'/*') as $file) {
                unlink($file);
            } rmdir($dir);
        }
    }
}
