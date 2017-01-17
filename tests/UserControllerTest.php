<?php

use App\Models\User;
use App\Models\Task;

use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testListUser()
    {
        //Test for an empty list
        $this->withoutMiddleware();
        $get = $this->json('GET','/api/v1/users');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(0,$response, 'Test if query count is one');
        //Test for a non empty list
        $users = factory(User::class,2)->create();
        $get = $this->json('GET', 'api/v1/users');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(2, $response, 'Test if query count is 2');

        $users->each(function ($item, $key) use ($response){
            $this->assertObjectEqualsExclude($item, $response[$key], ['password']);
        });
    }

    public function testListCreatedTasksByUser(){
        //User not found
        $this->withoutMiddleware();
        $get = $this->json('GET', '/api/v1/users/1/created_tasks');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if Http Not Found');
        //User found with no tasks
        $user = factory(User::class)->create();
        $get = $this->json('GET', '/api/v1/users/'.$user->id.'/created_tasks');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertCount(0, $response, 'Test if query count is 0');
        //User found with tasks
        $tasks = factory(Task::class, 2)->create(['creator_id' => $user->id]);
        $get = $this->json('GET', '/api/v1/users/'.$user->id.'/created_tasks');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(2, $response, 'Test if query count is 2');

        $tasks->each(function ($item, $key) use ($response){
            $this->assertObjectEqualsExclude($item, $response[$key]);
        });
    }

    public function testListAssignedTasksByUser(){
        //User not found
        $this->withoutMiddleware();
        $get = $this->json('GET', '/api/v1/users/1/assigned_tasks');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if Http Not Found');
        //User found with no tasks
        $user = factory(User::class)->create();
        $get = $this->json('GET', '/api/v1/users/'.$user->id.'/assigned_tasks');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertCount(0, $response, 'Test if query count is 0');
        //User found with tasks
        $tasks = factory(Task::class, 2)->create(['user_assigned_id' => $user->id]);
        $get = $this->json('GET', '/api/v1/users/'.$user->id.'/assigned_tasks');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(2, $response, 'Test if query count is 2');

        $tasks->each(function ($item, $key) use ($response){
            $this->assertObjectEqualsExclude($item, $response[$key]);
        });
    }

    public function testPostUser()
    {
        //Add user without admin role
        $this->be(factory(User::class)->create());
        $this->withoutMiddleware();
        $post = $this->json('POST', '/api/v1/users', []); //Empty request
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN,'Test if Http Forbidden');
        //Add user with admin role
        $this->be(factory(User::class,'admin')->create());
        $post = $this->json('POST', '/api/v1/users', ['first_name' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid first_name
        $post = $this->json('POST', '/api/v1/users', ['first_name' => 'foo' , 'last_name' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid last_name
        $post = $this->json('POST', '/api/v1/users', ['first_name' => 'foo', 'last_name' => 'bar', 'email' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid email
        $post = $this->json('POST', '/api/v1/users', ['first_name' => 'foo', 'last_name' => 'bar', 'email' => 'foo@bar.com', 'password' => '123456']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Missing confirmation
        $post = $this->json('POST', '/api/v1/users', ['first_name' => 'foo', 'last_name' => 'bar', 'email' => 'foo@bar.com', 'password' => '123456', 'password_confirmation' => '1234567']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Not matching confirmation
        //Valid Data
        $post = $this->json('POST', '/api/v1/users', ['first_name' => 'foo', 'last_name' => 'bar', 'email' => 'foo@bar.com', 'password' => '123456', 'password_confirmation' => '123456', 'is_admin' => false]);
        $this->assertResponseStatus(Response::HTTP_CREATED, 'Test if HTTP Created');//Valid data
        $this->seeInDatabase('user',['first_name' => 'foo', 'last_name' => 'bar', 'email' => 'foo@bar.com']);
        $response = json_decode($post->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $user = User::findOrFail(3);
        $this->assertObjectEqualsExclude($user, $response,['password']);
    }

    public function testGetUser()
    {

        $this->withoutMiddleware();
        //User not found
        $query = $this->json('GET', '/api/v1/users/1');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');
        //User found
        $user = factory(User::class)->create();
        $query = $this->json('GET','/api/v1/users/'.$user->id);
        $this->assertResponseOk();
        $response = json_decode($query->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $this->assertObjectEqualsExclude($user, $response,['password']);
    }

    public function testPatchUser()
    {
        //Not admin user
        $this->be(factory(User::class)->create());
        $this->withoutMiddleware();
        $patch = $this->json('PATCH', 'api/v1/users/1',[]);
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN, 'Test if HTTP Forbidden');
        //Admin user, user not found
        $this->be(factory(User::class,'admin')->create());
        $patch = $this->json('PATCH', '/api/v1/users/3',[]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');//Not found
        //Admin user, user found but invalid data
        $user = factory(User::class)->create();
        $patch = $this->json('PATCH', '/api/v1/users/'.$user->id,['first_name' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid first_name
        $patch = $this->json('PATCH', '/api/v1/users/'.$user->id,['first_name' => 'foo', 'last_name' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid last_name
        $patch = $this->json('PATCH', '/api/v1/users/'.$user->id,['first_name' => 'foo', 'last_name' => 'bar' , 'email' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid email
        //Admin user, user found valid data
        $patch = $this->json('PATCH', '/api/v1/users/'.$user->id,['first_name' => 'foo', 'last_name' => 'bar' , 'email' => 'foo@bar.com']);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT, 'Test if HTTP No Content');//Valid Data
    }

    public function testDeleteUser()
    {
        //Not admin user
        $this->be(factory(User::class)->create());
        $this->withoutMiddleware();
        $delete = $this->json('DELETE' , 'api/v1/users/1');
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN, 'Test HTTP Forbidden');
        //Admin user, not found user
        $this->be(factory(User::class, 'admin')->create());
        $delete = $this->json('DELETE', 'api/v1/users/10');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test HTTP Not Found');
        //Admin user, found user
        $user = factory(User::class)->create();
        $delete = $this->json('DELETE', 'api/v1/users/'.$user->id);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT, 'Test HTTP No Content');
        $this->missingFromDatabase('user',['id' => $user->id]);
    }

}