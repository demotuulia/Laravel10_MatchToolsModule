<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;


class AuthorizationTest extends LoginBase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function testAuthorization(): void
    {
        $name = 'testRegister';
        $email = 'testRegister2@test.nx';
        $password = '123';

        // register
        $response = $this->postJson(
            '/api/register',
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'c_password' => $password,
                'role' => 'company',
            ]
        );
        $response->assertStatus(200);

        /** @var User $user */
        $user = User::where('name', $name)->first();


        // login
        $response = $this->postJson(
            '/api/login',
            [
                'email' => $email,
                'password' => $password,
            ],
        );

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals('User login successfully.', $response->json()["message"]);

        // check we get OK response with correct token
        $response = $this->getJson(
            '/api/matches/forms?form_id=1',
        );

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_FORBIDDEN)
            ->where('meta.message', 'Acces denied')
        );

        $response = $this->getJson(
            '/api/matches/matches/1',
        );
        $response->assertStatus(Response::HTTP_OK);
    }
}
