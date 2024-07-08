<?php

namespace Tests\Feature;

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

        // Extract token from response and set it for subsequent requests
        $token = $response['data']['accessToken'];
        $this->withHeader('Authorization', 'Bearer ' . $token);
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
        $token = JWTAuth::fromUser($user);

        $organisation = Organisation::factory()->create([
            'name' => 'Covenant\'s Organisation',
        ]);

        $user->organisations()->attach($organisation->orgId);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/organisations');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'organisations' => [
                             [
                                 'orgId',
                                 'name',
                                 'description',
                             ]
                         ]
                     ]
                 ]);
    }

    /**
     * Test creating a new organisation
     */
    public function test_create_organisation()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/organisations', [
            'name' => 'New Organisation',
            'description' => 'A new organisation description',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'orgId',
                         'name',
                         'description'
                     ]
                 ]);
    }

    /**
     * Test creating a new organisation with validation error
     */
    public function test_create_organisation_validation_error()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/organisations', [
            'name' => '',
            'description' => 'A new organisation description',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         'name'
                     ]
                 ]);
    }

    /**
     * Test adding a user to an organisation
     */
    public function test_add_user_to_organisation()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $token = JWTAuth::fromUser($user1);

        $organisation = Organisation::factory()->create([
            'name' => 'Covenant\'s Organisation',
        ]);

        $user1->organisations()->attach($organisation->orgId);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson("/api/organisations/{$organisation->orgId}/users", [
            'userId' => $user2->userId,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'User added to organisation successfully',
                 ]);
    }

    /**
     * Test adding a user to an organisation with validation error
     */
    public function test_add_user_to_organisation_validation_error()
    {
        $user1 = User::factory()->create();
        $token = JWTAuth::fromUser($user1);

        $organisation = Organisation::factory()->create([
            'name' => 'Covenant\'s Organisation',
        ]);

        $user1->organisations()->attach($organisation->orgId);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson("/api/organisations/{$organisation->orgId}/users", [
            'userId' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         'userId'
                     ]
                 ]);
    }
}
