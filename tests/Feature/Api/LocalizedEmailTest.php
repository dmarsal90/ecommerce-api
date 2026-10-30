<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class LocalizedEmailTest extends TestCase
{
    use RefreshDatabase;

    private function lastEmail(): Email
    {
        return $this->app['mailer']->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    }

    public function test_every_locale_translates_the_same_keys(): void
    {
        $keys = array_keys(require lang_path('en/mail.php'));

        foreach (config('app.supported_locales') as $locale) {
            $this->assertSame($keys, array_keys(require lang_path("$locale/mail.php")), "mail.php for $locale");
        }
    }

    public function test_the_verification_email_uses_the_language_chosen_at_registration(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana', 'email' => 'ana@example.com', 'locale' => 'es',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated();

        $email = $this->lastEmail();
        $this->assertSame('Verifica tu correo electrónico', $email->getSubject());
        $this->assertStringContainsString('Hola, Ana,', $email->getHtmlBody());
        $this->assertStringContainsString('Sennitwo', $email->getHtmlBody());
        $this->assertStringContainsString('Todos los derechos reservados.', $email->getHtmlBody());
    }

    public function test_users_without_a_language_get_english(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana', 'email' => 'ana@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated();

        $this->assertSame('Verify your email address', $this->lastEmail()->getSubject());
    }

    public function test_an_unsupported_language_is_rejected(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana', 'email' => 'ana@example.com', 'locale' => 'xx',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertUnprocessable()->assertJsonValidationErrors('locale');
    }

    public function test_the_reset_email_is_translated_and_links_to_the_storefront(): void
    {
        User::factory()->create(['email' => 'luc@example.com', 'locale' => 'fr']);

        $this->postJson('/api/forgot-password', ['email' => 'luc@example.com'])->assertOk();

        $email = $this->lastEmail();
        $this->assertSame('Réinitialisez votre mot de passe', $email->getSubject());
        $this->assertStringContainsString(config('app.frontend_url').'/reset-password?token=', $email->getHtmlBody());
    }
}
