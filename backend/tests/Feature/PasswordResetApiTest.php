<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PasswordResetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_can_request_and_use_a_password_reset_link(): void
    {
        Notification::fake();
        $this->seed();

        $user = User::query()->where('username', '22.222.222-2')->firstOrFail();
        $user->createToken('frontend');

        $this->postJson('/api/password/forgot', [
            'rut' => $user->username,
        ])->assertOk();

        $token = null;
        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            }
        );

        $this->postJson('/api/password/reset', [
            'rut' => $user->username,
            'token' => $token,
            'password' => 'NuevaClaveSegura2026',
            'password_confirmation' => 'NuevaClaveSegura2026',
        ])->assertOk();

        $user->refresh();
        $this->assertTrue(Hash::check('NuevaClaveSegura2026', $user->password));
        $this->assertFalse($user->must_change_password);
        $this->assertCount(0, $user->tokens);
    }

    public function test_password_request_does_not_reveal_unknown_ruts(): void
    {
        Notification::fake();

        $this->postJson('/api/password/forgot', [
            'rut' => '99.999.999-9',
        ])
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Si el RUT está registrado y tiene un correo asociado, enviaremos un enlace de recuperación.'
            );
    }

    public function test_administrator_can_generate_a_shareable_one_time_link(): void
    {
        $this->seed();

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $volunteer = User::query()->where('username', '22.222.222-2')->firstOrFail();
        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/user/{$volunteer->id}/password-reset-link/generate")
            ->assertOk()
            ->assertJsonPath('expires_in_minutes', 30);

        parse_str((string) parse_url($response->json('url'), PHP_URL_QUERY), $query);

        $this->assertSame($volunteer->username, $query['rut'] ?? null);
        $this->assertTrue(Password::broker()->tokenExists($volunteer, $query['token'] ?? ''));
    }

    public function test_administrator_can_send_the_link_to_the_registered_email(): void
    {
        Notification::fake();
        $this->seed();

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $volunteer = User::query()->where('username', '22.222.222-2')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson("/api/user/{$volunteer->id}/password-reset-link")
            ->assertOk();

        Notification::assertSentTo($volunteer, ResetPasswordNotification::class);
    }
}