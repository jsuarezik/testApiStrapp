<?php
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends TestCase
{
    use DatabaseMigrations;
    public function testLoginNonExistentEMail()
    {
        $login = $this->post('/api/v1/auth/login', [
            'email' => 'nonexistant@email.tld',
            'password' => 'secret',
        ]);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $login->response->getStatusCode(),
            "Testing non existing email");
    }
    public function testLoginEmptyPassword()
    {
        $login = $this->post('/api/v1/auth/login', [
            'email' => 'nonexistant@email.tld',
        ]);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $login->response->getStatusCode(),
            "Testing empty password");
    }
    public function testLoginEmptyJson()
    {
        $login = $this->post('/api/v1/auth/login', []);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $login->response->getStatusCode(),
            "Testing empty json");
    }
    public function testLoginWrongPassword()
    {
        $user = factory(User::class)->create(['password' => 'secret']);
        $login = $this->post('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'invalid',
        ]);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $login->response->getStatusCode(),
            "Testing wrong password");
    }
    public function testLoginValid()
    {
        $user = factory(User::class)->create(['password' => 'secret']);
        $login = $this->post('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);
        $login->assertResponseOk();
        $response = json_decode($this->response->getContent(), true);
        $this->assertNotNull($response, "Test if login response is valid json");
        $this->assertTrue(json_last_error() === JSON_ERROR_NONE, "Test if response decode is ok");
        $this->assertArrayHasKey('token', $response, "Test if response has token");
        $this->assertNotEmpty($response['token'], "Test if token is not empty");
        $jwt = app('tymon.jwt.auth');
        $loggedUser = $jwt->setToken($response['token'])->authenticate();
        $this->assertNotNull($loggedUser, "Test if token is valid");
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals($user->id, $loggedUser->id, "Test if logged user is the same as the created user");
    }
}