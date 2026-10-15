<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class EnvironmentSetupTest extends TestCase
{
    public function test_existing_mysql_password_and_application_key_are_preserved_even_when_password_is_empty(): void
    {
        $directory = $this->fixture();
        try {
            $key = 'base64:'.base64_encode(str_repeat('a', 32));
            file_put_contents($directory.'/.env', "DB_USERNAME=root\nDB_PASSWORD=\nAPP_KEY={$key}\n");
            $this->configure($directory);
            $content = file_get_contents($directory.'/.env');
            $this->assertMatchesRegularExpression('/^DB_PASSWORD=$/m', $content);
            $this->assertStringContainsString('APP_KEY='.$key, $content);
            $this->configure($directory);
            $this->assertSame($content, file_get_contents($directory.'/.env'));
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_fresh_development_configuration_has_a_key_and_database_password(): void
    {
        $directory = $this->fixture();
        try {
            $this->configure($directory);
            $content = file_get_contents($directory.'/.env');
            $this->assertMatchesRegularExpression('/^APP_KEY=base64:\S+$/m', $content);
            $this->assertMatchesRegularExpression('/^DB_PASSWORD=[a-f0-9]{36}$/m', $content);
            $this->configure($directory);
            $this->assertSame($content, file_get_contents($directory.'/.env'));
        } finally {
            File::deleteDirectory($directory);
        }
    }

    private function fixture(): string
    {
        $directory = sys_get_temp_dir().'/airline-config-'.bin2hex(random_bytes(8));
        mkdir($directory.'/scripts', 0700, true);
        copy(base_path('scripts/configure-env.php'), $directory.'/scripts/configure-env.php');
        copy(base_path('.env.example'), $directory.'/.env.example');

        return $directory;
    }

    private function configure(string $directory): void
    {
        $process = new Process([PHP_BINARY, $directory.'/scripts/configure-env.php']);
        $process->run();
        $this->assertSame(0, $process->getExitCode(), 'A configuração local deve executar sem erros.');
    }
}
