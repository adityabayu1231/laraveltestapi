<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test untuk method index sudah ada di contoh sebelumnya

    public function testShowRetrievesUserById()
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function testShowFailsForInvalidUserId()
    {
        $response = $this->getJson('/api/users/123'); // ID user tidak valid

        $response->assertStatus(404)
            ->assertJson(['message' => 'User not found']);
    }

    public function testUpdateValidatesAndUpdatesUser()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'John Doe Updated',
            'email' => 'johndoeupdated@example.com',
        ];

        $response = $this->putJson('/api/users/' . $user->id, $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    public function testUpdateFailsForInvalidUserId()
    {
        $data = [
            'name' => 'John Doe Updated',
            'email' => 'johndoeupdated@example.com',
        ];

        $response = $this->putJson('/api/users/123', $data); // ID user tidak valid

        $response->assertStatus(404)
            ->assertJson(['message' => 'User not found']);
    }

    public function testUpdateFailsForMissingName()
    {
        $user = User::factory()->create();

        $data = [
            'email' => 'johndoeupdated@example.com',
        ];

        $response = $this->putJson('/api/users/' . $user->id, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name' => 'The name field is required.']);
    }

    public function testUpdateFailsForInvalidEmail()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'John Doe Updated',
            'email' => 'invalid_email',
        ];

        $response = $this->putJson('/api/users/' . $user->id, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email must be a valid email address.']);
    }

    public function testUpdateFailsForExistingEmail()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $data = [
            'name' => 'John Doe Updated',
            'email' => $user2->email, // Gunakan email user lain
        ];

        $response = $this->putJson('/api/users/' . $user1->id, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email has already been taken.']);
    }
}
