<?php

namespace Tests\Feature;

use App\Models\User;
use Modules\Matches\Database\Seeders\RoleAndPermissionSeeder;
use Modules\Matches\Database\Seeders\Tests\TestDataSeeder;
use Modules\Matches\Models\MatchesProfile;
use Tests\TestCase;


class LoginBase extends TestCase
{
    private ?string $accessToken = null;

    public function setUp(): void
    {
        parent::setUp();
        $users = User::all();
        if ($users->count() == 0) {
            $this->seed(RoleAndPermissionSeeder::class);
            $this->createUsers();
        }
        $profiles = MatchesProfile::all();
        if ($profiles->count() == 0) {
            $this->seed(TestDataSeeder::class);
        }
        $this->login();
    }

    private function getCredentials(): array
    {
        return [
            'testAdmin' => [
                'name' => 'testAdmin',
                'email' => 'testAdmin@test.nx',
                'password' => '123',
            ],
            'testCompany' => [
                'name' => 'testCompany',
                'email' => 'tesCompanyt@test.nx',
                'password' => '123',
            ],
            'testCandidate' => [
                'name' => 'testCandidate',
                'email' => 'tesCandidatet@test.nx',
                'password' => '123',
            ],
        ];
    }

    protected function createUsers(): void
    {
        foreach ($this->getCredentials() as $user) {
            $request = $this->postJson(
                '/api/register',
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'c_password' => $user['password'],
                    'role' => 'company',
                ]
            );
        }

        /** @var User $adminUser */
        $adminUser = User::where('name', 'testAdmin')->first();
        $adminUser->assignRole('admin');

    }

    public function login(string $email = 'testAdmin@test.nx', string $password = '123'): string
    {
        $response = $this->postJson(
            '/api/login',
            [
                'email' => $email,
                'password' => $password,
            ],
        );

        $this->accessToken = ($response->json()['data']['token']);
        return $this->accessToken;
    }

    /**
     * @return \Illuminate\Testing\TestResponse
     */
    public function getJson($uri, array $headers = [], $options = 0)
    {
        if (!isset($headers['Accept'])) {
            $headers['Accept'] = 'application/json';
        }
        if (!isset($headers['Authorization'])) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        return $this->json('GET', $uri, [], $headers, $options);
    }

    /**
     * @return \Illuminate\Testing\TestResponse
     */
    public function postJson($uri, array $data = [], array $headers = [], $options = 0)
    {
        if (!isset($headers['Accept'])) {
            $headers['Accept'] = 'application/json';
        }
        if (!isset($headers['Authorization'])) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        return $this->json('POST', $uri, $data, $headers, $options);
    }
}
