<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_uploading_a_photo_stores_a_256_px_webp_and_returns_its_url(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('me.jpg', 900, 600)])
            ->assertOk();

        $path = $user->fresh()->avatar_url;
        $this->assertMatchesRegularExpression('#^avatars/user-\d+-\w+\.webp$#', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame([256, 256], array_slice(getimagesizefromstring(Storage::disk('public')->get($path)), 0, 2));
        $this->assertStringEndsWith($path, $response->json('user.avatar_url'));
    }

    public function test_replacing_the_photo_deletes_the_previous_file(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('a.png', 300, 300)])->assertOk();
        $first = $user->fresh()->avatar_url;
        $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('b.png', 300, 300)])->assertOk();

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($user->fresh()->avatar_url);
    }

    public function test_it_rejects_files_over_2_mb_non_images_svg_and_huge_dimensions(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('big.jpg', 600, 600)->size(2049)])
            ->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])
            ->assertUnprocessable();
        $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')])
            ->assertUnprocessable();
        $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('wide.png', 4001, 10)])
            ->assertUnprocessable();
    }

    public function test_removing_the_photo_clears_it_but_keeps_files_we_did_not_upload(): void
    {
        $user = User::factory()->create(['avatar_url' => 'custom-avatars/admin-picked.png']);
        Storage::disk('public')->put('custom-avatars/admin-picked.png', 'x');
        Sanctum::actingAs($user);

        $this->deleteJson('/api/me/avatar')->assertOk()->assertJsonPath('user.avatar_url', null);

        $this->assertNull($user->fresh()->avatar_url);
        Storage::disk('public')->assertExists('custom-avatars/admin-picked.png');
    }

    public function test_guests_cannot_upload(): void
    {
        $this->postJson('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('a.jpg')])->assertUnauthorized();
    }
}
