<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->put('/user/profile-information', [
            'name' => 'Test Nome',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test Nome', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }
}
