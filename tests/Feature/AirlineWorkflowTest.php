<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Customer;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AirlineWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function passenger(): User
    {
        $user = User::factory()->create();
        Customer::factory()->create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email]);

        return $user->fresh();
    }

    private function purchase(Flight $flight, array $extra = []): array
    {
        return ['flight_id' => $flight->id, 'status' => 'paid', 'payment_method' => 'pix'] + $extra;
    }

    public function test_registration_always_creates_a_customer_and_ignores_admin_role(): void
    {
        $this->post('/register', ['name' => 'Ana Cliente', 'email' => 'ana@example.test', 'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123', 'role' => 'admin'])->assertRedirect(route('profile.edit'));
        $user = User::where('email', 'ana@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->customer);
        $this->assertTrue(Hash::check('SecurePass123', $user->password));
    }

    public function test_login_logout_and_invalid_credentials(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'TestPassword123!'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_recovery_uses_the_laravel_broker(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk()->assertSee('Nova senha');
        $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'NewSecure123', 'password_confirmation' => 'NewSecure123'])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewSecure123', $user->fresh()->password));
    }

    public function test_guest_redirects_and_public_search_filters(): void
    {
        $flight = Flight::factory()->create();
        Flight::factory()->create(['status' => 'cancelled']);
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get(route('market.index', ['origin_id' => $flight->origin_id, 'date' => $flight->departure_at->toDateString()]))->assertOk()->assertSee($flight->code);
        $this->get(route('market.index', ['origin_id' => 99999]))->assertSessionHasErrors('origin_id');
    }

    public static function resources(): array
    {
        return array_map(fn ($v) => [$v], ['airlines', 'airports', 'aircraft', 'flights', 'customers', 'users']);
    }

    private function payload(string $resource): array
    {
        return match ($resource) {
            'airlines' => ['name' => 'Companhia Teste', 'iata' => 'ZZ', 'country' => 'Brasil', 'email' => 'company@example.test', 'active' => 1],
            'airports' => ['name' => 'Aeroporto Teste', 'iata' => 'ZZZ', 'city' => 'São Paulo', 'country' => 'Brasil'],
            'aircraft' => ['airline_id' => Airline::factory()->create()->id, 'registration' => 'PR-XYZ', 'model' => 'A320', 'capacity' => 150],
            'flights' => $this->flightPayload(Flight::factory()->make(['code' => 'AU9000'])),
            'customers' => ['name' => 'Passageiro Teste', 'email' => 'pass@example.test', 'document' => 'DOC-999', 'phone' => '11912345678', 'birth_date' => '1990-01-01'],
            'users' => ['name' => 'Usuário Teste', 'email' => 'user@example.test', 'role' => 'agent', 'password' => 'SafePass123', 'password_confirmation' => 'SafePass123'],
        };
    }

    private function flightPayload(Flight $flight): array
    {
        // make() materializes factory relationships; persist only through the controller.
        return ['airline_id' => $flight->airline_id, 'code' => $flight->code, 'origin_id' => $flight->origin_id, 'destination_id' => $flight->destination_id, 'aircraft_id' => $flight->aircraft_id, 'departure_at' => $flight->departure_at->format('Y-m-d H:i:s'), 'arrival_at' => $flight->arrival_at->format('Y-m-d H:i:s'), 'price' => '329.99', 'status' => 'scheduled'];
    }

    #[DataProvider('resources')]
    public function test_admin_can_complete_each_entity_crud(string $resource): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $this->get(route($resource.'.index'))->assertOk();
        $this->get(route($resource.'.create'))->assertOk();
        $payload = $this->payload($resource);
        $response = $this->post(route($resource.'.store'), $payload);
        $response->assertSessionHasNoErrors()->assertRedirect();
        $url = $response->headers->get('Location');
        $this->get($url)->assertOk();
        $this->get($url.'/edit')->assertOk();
        $payload[$resource === 'flights' ? 'code' : ($resource === 'aircraft' ? 'model' : 'name')] = 'Cadastro atualizado';
        if ($resource === 'flights') {
            $payload['code'] = 'AU9001';
        }
        if ($resource === 'users') {
            $payload['password'] = '';
            unset($payload['password_confirmation']);
        }
        $this->put($url, $payload)->assertSessionHasNoErrors()->assertRedirect($url);
        $this->get($url)->assertSee($payload[$resource === 'flights' ? 'code' : ($resource === 'aircraft' ? 'model' : 'name')]);
        $this->delete($url)->assertRedirect(route($resource.'.index'));
        $this->get($url)->assertNotFound();
    }

    #[DataProvider('resources')]
    public function test_customer_cannot_manage_operational_entities(string $resource): void
    {
        $this->actingAs($this->passenger())->get(route($resource.'.index'))->assertForbidden();
        $this->get(route($resource.'.create'))->assertForbidden();
        $this->post(route($resource.'.store'), [])->assertForbidden();
    }

    public function test_dashboard_menus_and_gates_follow_roles(): void
    {
        $customer = $this->passenger();
        $this->actingAs($customer)->get('/dashboard')->assertOk()->assertSee('Minhas passagens')->assertDontSee('Usuários e permissões');
        $this->assertFalse(Gate::forUser($customer)->allows('manage-airline'));
        $agent = User::factory()->agent()->create();
        $this->actingAs($agent)->get('/dashboard')->assertOk()->assertSee('Companhias aéreas')->assertDontSee('Usuários e permissões');
        $this->get('/users')->assertForbidden();
        $this->get('/vendas')->assertOk();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/dashboard')->assertSee('Usuários e permissões');
    }

    public function test_customer_can_purchase_only_for_self_at_server_price(): void
    {
        $user = $this->passenger();
        $flight = Flight::factory()->create(['price_cents' => 45999]);
        $this->actingAs($user)->get(route('market.show', $flight))->assertOk()->assertSee('Sua próxima viagem');
        $response = $this->post(route('tickets.store'), $this->purchase($flight, ['price_cents' => 1]));
        $response->assertSessionHasNoErrors()->assertRedirect();
        $ticket = Ticket::firstOrFail();
        $this->assertSame($user->customer->id, $ticket->customer_id);
        $this->assertSame(45999, $ticket->price_cents);
        $this->assertNull($ticket->sold_by);
        $this->assertSame('paid', $ticket->status);
        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee($ticket->reference);
        $this->get(route('tickets.index'))->assertOk()->assertSee($ticket->reference);
        $this->get('/vendas')->assertForbidden();
    }

    public function test_customer_cannot_select_another_passenger_or_use_cash(): void
    {
        $user = $this->passenger();
        $other = Customer::factory()->create();
        $flight = Flight::factory()->create();
        $this->actingAs($user)->post(route('tickets.store'), $this->purchase($flight, ['customer_id' => $other->id]))->assertSessionHasErrors('customer_id');
        $this->post(route('tickets.store'), ['flight_id' => $flight->id, 'status' => 'paid', 'payment_method' => 'cash'])->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_no_document_blocks_purchase_and_profile_can_be_completed(): void
    {
        $user = $this->passenger();
        $user->customer->update(['document' => null]);
        $flight = Flight::factory()->create();
        $this->actingAs($user)->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasErrors('customer_id');
        $this->put(route('profile.update'), ['name' => $user->name, 'email' => $user->email, 'document' => 'DOC-PROFILE', 'user_id' => 999])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($user->id, $user->customer->fresh()->user_id);
        $this->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasNoErrors();
    }

    public function test_capacity_duplicate_booking_cancellation_and_resale(): void
    {
        $flight = Flight::factory()->create(['aircraft_id' => Aircraft::factory()->create(['capacity' => 1])->id]);
        // Keep the airline consistent with the selected aircraft.
        $flight->update(['airline_id' => $flight->aircraft->airline_id]);
        $first = $this->passenger();
        $second = $this->passenger();
        $this->actingAs($first)->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasNoErrors();
        $ticket = Ticket::firstOrFail();
        $this->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasErrors('flight_id');
        $this->actingAs($second)->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasErrors('flight_id');
        $this->actingAs($first)->put(route('tickets.update', $ticket), ['status' => 'cancelled'])->assertSessionHasNoErrors();
        $this->assertSame(1, $flight->fresh()->available_seats);
        $this->actingAs($second)->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('tickets', 2);
        $this->actingAs($first)->put(route('tickets.update', $ticket), ['status' => 'paid', 'payment_method' => 'pix'])->assertSessionHasErrors('status');
    }

    public function test_other_customer_cannot_view_edit_update_or_delete_a_ticket(): void
    {
        $owner = $this->passenger();
        $intruder = $this->passenger();
        $ticket = Ticket::factory()->create(['customer_id' => $owner->customer->id]);
        $this->actingAs($intruder)->get(route('tickets.show', $ticket))->assertForbidden();
        $this->get(route('tickets.edit', $ticket))->assertForbidden();
        $this->put(route('tickets.update', $ticket), ['status' => 'cancelled'])->assertForbidden();
        $this->delete(route('tickets.destroy', $ticket))->assertForbidden();
        $this->get(route('tickets.index'))->assertDontSee($ticket->reference);
        $this->put(route('customers.update', $owner->customer), ['name' => 'Intruso'])->assertForbidden();
    }

    public function test_staff_ticket_crud_and_payment_transitions(): void
    {
        $agent = User::factory()->agent()->create();
        $flight = Flight::factory()->create();
        $customer = Customer::factory()->create();
        $this->actingAs($agent)->get(route('tickets.create'))->assertOk();
        $this->post(route('tickets.store'), ['flight_id' => $flight->id, 'customer_id' => $customer->id, 'status' => 'reserved'])->assertSessionHasNoErrors();
        $ticket = Ticket::firstOrFail();
        $this->assertSame($agent->id, $ticket->sold_by);
        $this->get(route('tickets.edit', $ticket))->assertOk();
        $this->put(route('tickets.update', $ticket), ['status' => 'paid'])->assertSessionHasErrors('payment_method');
        $this->put(route('tickets.update', $ticket), ['status' => 'paid', 'payment_method' => 'cash'])->assertSessionHasNoErrors();
        $this->assertSame('paid', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->paid_at);
        $this->get('/vendas')->assertSee($ticket->reference);
        $this->delete(route('tickets.destroy', $ticket))->assertForbidden();
        $this->put(route('tickets.update', $ticket), ['status' => 'cancelled'])->assertSessionHasNoErrors();
        $this->get('/vendas')->assertDontSee($ticket->reference);
        $this->delete(route('tickets.destroy', $ticket))->assertRedirect(route('tickets.index'));
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_cancelled_past_and_inactive_company_flights_cannot_be_sold(): void
    {
        $user = $this->passenger();
        $this->actingAs($user);
        foreach ([['status' => 'cancelled'], ['departure_at' => now()->subDay(), 'arrival_at' => now()->subHours(22)]] as $attrs) {
            $flight = Flight::factory()->create($attrs);
            $this->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasErrors('flight_id');
        }
        $flight = Flight::factory()->create();
        $flight->airline->update(['active' => false]);
        $this->post(route('tickets.store'), $this->purchase($flight))->assertSessionHasErrors('flight_id');
        $this->get(route('market.index'))->assertDontSee($flight->code);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_flight_validation_prevents_same_airport_and_wrong_company_aircraft(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $flight = Flight::factory()->make();
        $data = $this->flightPayload($flight);
        $data['destination_id'] = $data['origin_id'];
        $this->post('/flights', $data)->assertSessionHasErrors('destination_id');
        $data = $this->flightPayload($flight);
        $data['airline_id'] = Airline::factory()->create()->id;
        $this->post('/flights', $data)->assertSessionHasErrors('aircraft_id');
    }

    public function test_flight_cancellation_cancels_all_active_tickets_and_preserves_paid_history(): void
    {
        $admin = User::factory()->admin()->create();
        $flight = Flight::factory()->create();
        $ticket = app(TicketService::class)->issue($admin, ['flight_id' => $flight->id, 'customer_id' => Customer::factory()->create()->id, 'status' => 'paid', 'payment_method' => 'pix']);
        $data = $this->flightPayload($flight);
        $data['departure_at'] = $flight->departure_at->copy()->addHour()->format('Y-m-d H:i:s');
        $this->actingAs($admin)->put(route('flights.update', $flight), $data)->assertSessionHasErrors('departure_at');
        $data = $this->flightPayload($flight);
        $data['status'] = 'cancelled';
        $this->put(route('flights.update', $flight), $data)->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->paid_at);
        $data['status'] = 'scheduled';
        $this->put(route('flights.update', $flight), $data)->assertSessionHasErrors('status');
    }

    public function test_foreign_key_deletions_and_aircraft_capacity_are_protected(): void
    {
        $admin = User::factory()->admin()->create();
        $flight = Flight::factory()->create();
        Ticket::factory()->count(2)->create(['flight_id' => $flight->id]);
        $this->actingAs($admin)->delete(route('airports.destroy', $flight->origin))->assertSessionHasErrors('record');
        $this->delete(route('airlines.destroy', $flight->airline))->assertSessionHasErrors('record');
        $this->put(route('aircraft.update', $flight->aircraft), ['airline_id' => $flight->airline_id, 'registration' => $flight->aircraft->registration, 'model' => $flight->aircraft->model, 'capacity' => 1])->assertSessionHasErrors('capacity');
    }

    public function test_admin_cannot_demote_self_or_delete_self(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $this->put(route('users.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'role' => 'customer'])->assertSessionHasErrors('role');
        $this->delete(route('users.destroy', $admin))->assertForbidden();
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_seeders_are_repeatable_and_cover_all_entities(): void
    {
        config(['airline.demo_passwords' => ['admin' => 'DemoAdmin123', 'agent' => 'DemoAgent123', 'customer' => 'DemoCustomer123']]);
        $this->seed();
        $counts = [User::count(), Airline::count(), Airport::count(), Aircraft::count(), Customer::count(), Flight::count(), Ticket::count()];
        $this->seed();
        $this->assertSame($counts, [User::count(), Airline::count(), Airport::count(), Aircraft::count(), Customer::count(), Flight::count(), Ticket::count()]);
        $this->assertSame([3, 1, 4, 3, 4, 6, 1], $counts);
    }
}
