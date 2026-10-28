<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_answers_the_same_for_unknown_emails(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $known = $this->postJson('/api/forgot-password', ['email' => $user->email]);
        $unknown = $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com']);

        $known->assertOk();
        $this->assertSame($known->json(), $unknown->json());
        Notification::assertSentTo($user, ResetPassword::class);
        Notification::assertCount(1);
    }

    public function test_reset_password_changes_password_and_revokes_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('old');
        $token = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertOk();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'new-password-1'])->assertOk();
        $this->assertSame(1, $user->tokens()->count()); // only the login token remains
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/reset-password', [
            'token' => 'bad', 'email' => $user->email,
            'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertUnprocessable();
    }
}
