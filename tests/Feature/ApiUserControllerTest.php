<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class ApiUserControllerTest extends TestCase {

    use RefreshDatabase;

    public function testRegister() {

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Send a POST request to the register route
        $response = $this->postJson('/api/v1/register', $data);

        // Assert the response status is 201 (Created)
        $response->assertStatus(Response::HTTP_CREATED);

        // Assert the response has the correct message and user data
        $response->assertJsonStructure([
            'message',
            'user' => ['first_name', 'last_name', 'email'],
        ]);

        // Assert that the user is actually in the database
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        // Assert that the password is hashed correctly
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function testRegisterData()
    {
        // Invalid data: email is missing
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Send the POST request with invalid data
        $response = $this->postJson('/api/v1/register', $data);

        // Assert the response status is 400
        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        // Assert the response contains validation errors
        $response->assertJsonStructure(['error' => ['email']]);
    }

    public function testRegisterEmailExistence() {
        // Create an existing user
        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Try to register a new user with the same email
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',  // Already taken
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $data);

        // Assert the response status is 400
        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        // Assert the response contains the error for duplicate email
        $response->assertJsonStructure(['error' => ['email']]);
    }

    public function testLogin() {

        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $data);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'token',
        ]);
    }

    public function testGetAuthenticatedUser() {
        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $data);

        $response2 = $this->getJson('/api/v1/user', ['Authorization' => 'Bearer ' . $response['token']]);
        
        $response2->assertStatus(Response::HTTP_OK);

    }

    public function testLogout() {
        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $loginResponse = $this->postJson('/api/v1/login', $data);

        $logoutResponse = $this->getJson('/api/v1/logout', ['Authorization' => 'Bearer ' . $loginResponse['token']]);

        $logoutResponse->assertStatus(Response::HTTP_OK);

        $logoutResponse->assertJsonStructure(['message']);

        $userResponse = $this->getJson('/api/v1/user', ['Authorization' => 'Bearer ' . $loginResponse['token']]);

        $userResponse->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}