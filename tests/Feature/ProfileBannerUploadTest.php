<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProfileBannerUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiter_without_profile_edit_permission_can_update_own_banner(): void
    {
        $role = Role::create([
            'name' => 'Recruiter',
            'access_level' => 'recruiter',
            'status' => true,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->post(route('admin.upload.profile'), [
            'cover_image' => UploadedFile::fake()->image('banner.jpg', 1200, 300),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $path = $user->fresh()->cover_picture;
        $this->assertNotNull($path);
        $this->assertFileExists(public_path($path));

        File::delete(public_path($path));
    }
}
