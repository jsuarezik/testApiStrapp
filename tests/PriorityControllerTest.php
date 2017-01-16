<?php

use App\Models\User;
use App\Models\Priority;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

class PriorityControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testListPriorities()
    {
        //Test for an empty list
        $this->withoutMiddleware();
        $get = $this->json('GET','/api/v1/priorities');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(0,$response, 'Test if query count is zero');
        //Test for a non empty list
        $priorities = factory(Priority::class,2)->create();
        $get = $this->json('GET', 'api/v1/priorities');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(2, $response, 'Test if query count is 3');

        foreach ($priorities as $key => $priority){
            $this->assertObjectEqualsExclude($priority, $response[$key]);
        }
    }

    public function testPostPriority()
    {
        //Add Priority
        $this->be(factory(User::class)->create());
        $this->withoutMiddleware();
        //Invalid Data
        $post = $this->json('POST', '/api/v1/priorities', []); //Empty request
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if Http Unprocessable Entity');
        $post = $this->json('POST', '/api/v1/priorities', ['name' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid name
        //Valid Data
        $post = $this->json('POST', '/api/vi/priorities', ['name' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_CREATED, 'Test if HTTP Created');//Valid data
        $this->seeInDatabase('priority',['name' => 'foo']);
        $response = json_decode($post->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $priority = Priority::findOrFail(1);
        $this->assertObjectEqualsExclude($priority, $response);
    }

    public function testGetPriority()
    {
        $this->withoutMiddleware();
        //Priority not found
        $query = $this->json('GET', '/api/v1/priorities/1');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');
        //User found
        $priority = factory(Priority::class)->create();
        $query = $this->json('GET','/api/v1/priorities/'.$priority->id);
        $this->assertResponseOk();
        $response = json_decode($query->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $this->assertObjectEqualsExclude($priority, $response);
    }

    public function testPatchPriority()
    {
        //Priority not found
        $this->be(factory(User::class)->create());
        $patch = $this->json('PATCH', '/api/v1/priority/1',[]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');//Not found
        //Priority found but invalid data
        $priority = factory(Priority::class)->create();
        $patch = $this->json('PATCH', '/api/v1/priorities/'.$priority->id,['name' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid name
        //Admin user, user found valid data
        $patch = $this->json('PATCH', '/api/v1/priorities/'.$priority->id,['name' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT, 'Test if HTTP No Content');//Valid Data
    }

    public function testDeletePriority()
    {
        //Priority not found
        $this->be(factory(User::class)->create());
        $delete = $this->json('DELETE', 'api/v1/priorities/1');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test HTTP Not Found');
        //Priority found
        $priority = factory(Priority::class)->create();
        $delete = $this->json('DELETE', 'api/v1/priorities/'.$priority->id);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT, 'Test HTTP No Content');
        $this->missingFromDatabase('priority',['id' => $priority->id]);
    }

}