<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthSpecTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration
     */
    public function test_register_user_successfully()
    {
        $response = $this->postJson('/api/auth/register', [
            'firstName' => 'Covenant',
            'lastName' => 'Ekundayo',
            'email' => 'covenantekunndayo@gmail.com',
            'password' => 'zaqxswcde1290.',
            'phone' => '09049082096',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'accessToken',
                         'user' => [
                             'userId',
                             'firstName',
                             'lastName',
                             'email',
                             'phone',
                         ]
                     ]
                 ]);
    }

    /**
     * Test registration with missing required fields
     */
    public function test_register_user_validation_errors()
    {
        $response = $this->postJson('/api/auth/register', [
            'firstName' => '',
            'lastName' => '',
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         'firstName',
                         'lastName',
                         'email',
                         'password',
                     ]
                 ]);
    }

    /**
     * Test registration with duplicate email
     */
    public function test_register_user_duplicate_email()
    {
        User::factory()->create([
            'email' => 'covenantekundayo@gmail.com',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'firstName' => 'Covenant',
            'lastName' => 'Ekundayo',
            'email' => 'covenantekundayo@gmail.com',
            'password' => 'zaqxswcde1290.',
            'phone' => '09049082096',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         'email'
                     ]
                 ]);
    }

    /**
     * Test user login successfully
     */
    public function test_login_user_successfully()
    {
        $user = User::factory()->create([
            'email' => 'covenantekundayo@gmail.com',
            'password' => bcrypt('zaqxswcde1290.'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'covenantekundayo@gmail.com',
            'password' => 'zaqxswcde1290.',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'accessToken',
                         'user' => [
                             'userId',
                             'firstName',
                             'lastName',
                             'email',
                             'phone',
                         ]
                     ]
                 ]);
    }

    /**
     * Test user login with invalid credentials
     */
    public function test_login_user_failed_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'covenantekundayo@gmail.com',
            'password' => bcrypt('zaqxswcde1290.'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'covenantekundayo@gmail.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'Bad request',
                     'message' => 'Authentication failed',
                     'statusCode' => 401
                 ]);
    }

    /**
     * Test getting user organisations
     */
    public function test_get_user_organisations()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['userId' => $user->id]);
    
        $user->organisations()->attach($organisation);
    
        $this->actingAs($user, 'api');
    
        $response = $this->getJson('/api/user/organisations');
    
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         [
                             'orgId' => $organisation->orgId,
                             'name' => $organisation->name,
                         ]
                     ]
                 ]);
    }
    
    public function test_create_organisation()
    {
        $user = User::factory()->create();
    
        $this->actingAs($user, 'api');
    
        $response = $this->postJson('/api/organisations', [
            'name' => 'New Organisation',
            'description' => 'A new organisation description',
            'userId' => $user->id,
        ]);
    
        $response->assertStatus(201);
    }

    /**
     * Test add user to organisation
     */
    public function test_add_user_to_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $this->actingAs($user, 'api');

        $response = $this->postJson("/api/organisations/{$organisation->orgId}/users", [
            'userId' => $user->userId,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'User added to organisation successfully',
                 ]);
    }

    /**
     * Test add user to organisation with invalid data
     */
    public function test_add_user_to_organisation_invalid_data()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $this->actingAs($user, 'api');

        // Sending invalid user ID (non-existent)
        $response = $this->postJson("/api/organisations/{$organisation->orgId}/users", [
            'userId' => 999, // Assuming 999 does not exist
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'User not found',
                 ]);
    }
}
