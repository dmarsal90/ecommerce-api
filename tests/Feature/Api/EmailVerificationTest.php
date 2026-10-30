<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function link(User $user, ?string $hash = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]);
    }

    public function test_registering_sends_a_verification_email_and_leaves_the_account_unverified(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Ana', 'email' => 'ana@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonPath('user.email_verified_at', null);

        Notification::assertSentTo(User::first(), VerifyEmail::class);
    }

    public function test_the_signed_link_verifies_the_email_and_redirects_to_the_storefront(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get($this->link($user))
            ->assertRedirect(config('app.frontend_url').'/verify-email?status=success');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_a_tampered_or_unsigned_link_does_not_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get($this->link($user, sha1('someone-else@example.com')))
            ->assertRedirect(config('app.frontend_url').'/verify-email?status=invalid');
        $this->get("/api/email/verify/{$user->id}/".sha1($user->email))
            ->assertRedirect(config('app.frontend_url').'/verify-email?status=invalid');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_an_unverified_user_can_resend_the_link_but_a_verified_one_gets_nothing(): void
    {
        Notification::fake();
        $unverified = User::factory()->unverified()->create();
        $verified = User::factory()->create();

        Sanctum::actingAs($unverified);
        $this->postJson('/api/email/resend')->assertOk();
        Notification::assertSentTo($unverified, VerifyEmail::class);

        Sanctum::actingAs($verified);
        $this->postJson('/api/email/resend')->assertOk();
        Notification::assertNotSentTo($verified, VerifyEmail::class);
    }

    public function test_an_unverified_user_cannot_place_an_order_or_start_a_payment(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $this->postJson('/api/orders', [])->assertForbidden();
        $this->postJson('/api/checkout/payment-intent', [])->assertForbidden();
    }

    public function test_a_verified_user_gets_past_the_verification_check(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/orders', [])->assertUnprocessable();
        $this->postJson('/api/checkout/payment-intent', [])->assertUnprocessable();
    }

    public function test_changing_the_email_requires_verifying_it_again_but_keeping_it_does_not(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'password123']);
        Sanctum::actingAs($user);

        $this->putJson('/api/me', ['email' => $user->email, 'current_password' => 'password123'])->assertOk();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $this->putJson('/api/me', ['email' => 'new@example.com', 'current_password' => 'password123'])->assertOk();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($user->fresh(), VerifyEmail::class);
    }
}
