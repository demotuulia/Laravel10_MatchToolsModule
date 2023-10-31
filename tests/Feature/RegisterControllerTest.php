<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Models\MatchesProfile;

class RegisterControllerTest extends LoginBase
{
    use DatabaseTruncation;

    public function testRegister(): void
    {
        $name = 'testRegister';
        $email = 'testRegister2@test.nx';
        $password = '123';
        $role = 'company';
        $formId = MatchesForm::where('name' , 'TEST_FORM')->first()->id;

        // register
        $response = $this->postJson(
            '/api/register',
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'c_password' => $password,
                'role' => $role,
                'form_id' => $formId
            ]
        );
        $response->assertStatus(200);

        // login
        $response = $this->postJson(
            '/api/login',
            [
                'email' => $email,
                'password' => $password,
            ],
        );

        $response->assertStatus(200);
        $this->assertEquals('User login successfully.', $response->json()["message"]);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'User login successfully.')
            ->where('data.name', $name)
            ->where('data.role', $role)
        );
        $accessToken = ($response->json()['data']['token']);

        // check we get OK response with correct token
        $response = $this->getJson(
            '/api/matches/matches?form_id=1',
        );
        $response->assertStatus(200);

        // Logout
        $response = $this->getJson(
            '/api/logout',
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ]
        );
        $this->assertEquals('User logged out successfully.', $response->json()["message"]);
        $response->assertStatus(200);


        // register with the same email again
        $response = $this->postJson(
            '/api/register',
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'c_password' => $password,
                'role' => $role,
                'form_id' => $formId
            ]
        );
        $response->assertStatus(200);
        $expectedMessage = 'Validation errors';
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_BAD_REQUEST)
            ->where('success', false)
            ->where('message', $expectedMessage)
            ->where('errors.email.0', "The email has already been taken.")
        );
    }


    public function testRegisterProfessionalHasProfile(): void
    {
        $name = 'testRegisterPro';
        $familyName = 'Family' . uniqid();
        $email = 'testRegisterPro2@test.nx';
        $password = '123';
        $role = 'professional';
        $formId = MatchesForm::where('name' , 'TEST_FORM')->first()->id;

        // register
        $response = $this->postJson(
            '/api/register',
            [
                'name' => $name,
                'familyName' => $familyName,
                'email' => $email,
                'password' => $password,
                'c_password' => $password,
                'role' => $role,
            ]
        );
        $response->assertStatus(200);
        // Here we just check that the profile is created,
        // more detailed tests are in MatcheProfileControllerTest
        $profile = MatchesProfile::where('name', $name . ' ' . $familyName)->get();
        $this->assertEquals(1, $profile->count());


    }
}
