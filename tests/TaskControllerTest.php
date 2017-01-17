<?php

use App\Models\User;
use App\Models\Priority;
use App\Models\Task;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testListTasks()
    {
        //Test for an empty list
        $this->withoutMiddleware();
        $get = $this->json('GET','/api/v1/tasks');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(0,$response, 'Test if query count is zero');
        //Test for a non empty list
        $tasks = factory(Task::class,2)->create();
        $get = $this->json('GET', 'api/v1/tasks');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(2, $response, 'Test if query count is 2');

        $tasks->each(function($item, $key) use ($response){
            $this->assertObjectEqualsExclude($item,$response[$key]);
        });
    }

    public function testPostTask()
    {
        //Add Task
        $this->be(factory(User::class)->create());
        $this->withoutMiddleware();
        //Invalid Data
        $post = $this->json('POST', '/api/v1/tasks', []);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if Http Unprocessable Entity');//Empty Request
        $post = $this->json('POST', '/api/v1/tasks', ['title' => '1']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid title
        $post = $this->json('POST', '/api/v1/tasks', ['title' => 'foo' , 'description' => '']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Empty description
        $post = $this->json('POST', '/api/v1/tasks', ['title' => 'foo' , 'description' => 'foo bar baz', 'due_date' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid Date
        $post = $this->json('POST', '/api/v1/tasks', ['title' => 'foo' , 'description' => 'foo bar baz', 'due_date' => '2017-01-17', 'user_assigned_id' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid user assigned id
        $post = $this->json('POST', '/api/v1/tasks', ['title' => 'foo' , 'description' => 'foo bar baz', 'due_date' => '2017-01-17', 'user_assigned_id' => '1' , 'priority_id' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid Priority
        $post = $this->json('POST', '/api/v1/tasks', ['title' => 'foo' , 'description' => 'foo bar baz', 'due_date' => '2017-01-17', 'user_assigned_id' => '1' , 'priority_id' => '1']);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');//Non existent priority
        //Valid Data
        $priority = factory(Priority::class)->create();
        $post = $this->json('POST', '/api/v1/tasks', ['title' => 'foo' , 'description' => 'foo bar baz', 'due_date' => '2017-01-17', 'user_assigned_id' => '1', 'priority_id' => $priority->id]);
        $this->assertResponseStatus(Response::HTTP_CREATED, 'Test if HTTP Created');//Valid data
        $this->seeInDatabase('task',['title' => 'foo' , 'description' => 'foo bar baz' , 'due_date' => '2017-01-17', 'user_assigned_id' => '1']);
        $response = json_decode($post->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $task = Task::findOrFail(1);
        $this->assertObjectEqualsExclude($task, $response);
    }

    public function testAssignTask()
    {
        $this->withoutMiddleware();
        //Task and User not found
        $post = $this->json('POST', '/api/v1/tasks/1/user/1',[]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if Http Not Found');
        //Task found, user not found
        $task = factory(Task::class)->create();
        $post = $this->json('POST', '/api/v1/tasks/'.$task->id.'/user/3', []);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if Http Not Found');
        //Task found, user found
        $user = factory(User::class)->create();
        $post = $this->json('POST', '/api/v1/tasks/'.$task->id.'/user/'.$user->id, []);
        $this->assertResponseStatus(Response::HTTP_OK, 'Test if Http OK');
        $this->seeInDatabase('task', ['id' => $task->id , 'user_assigned_id' => $user->id]);
        $response = json_decode($post->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $task = Task::findOrFail(1);
        $this->assertObjectEqualsExclude($task,$response);
    }

    public function testGetTask()
    {
        $this->withoutMiddleware();
        //Task not found
        $query = $this->json('GET', '/api/v1/tasks/1');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');
        //Task found
        $task = factory(Task::class)->create();
        $query = $this->json('GET','/api/v1/tasks/'.$task->id);
        $this->assertResponseOk();
        $response = json_decode($query->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $this->assertObjectEqualsExclude($task, $response);
    }

    public function testGetCreatorUserByTask()
    {
        $this->withoutMiddleware();
        //Task not found
        $get = $this->json('GET', '/api/v1/tasks/1/creator_user');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');
        //Task found
        $task = factory(Task::class)->create();
        $get = $this->json('GET', 'api/v1/tasks/'.$task->id.'/creator_user');
        $this->assertResponseOk(); //Valid data
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid JSON');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertObjectEqualsExclude($task->creator, $response);
    }

    public function testGetAssignedUserByTask()
    {

        $this->withoutMiddleware();
        //Task not found
        $get = $this->json('GET', '/api/v1/tasks/1/assigned_user');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');
        //Task found
        $task = factory(Task::class)->create();
        $get = $this->json('GET', 'api/v1/tasks/'.$task->id.'/assigned_user');
        $this->assertResponseOk(); //Valid data
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid JSON');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertObjectEqualsExclude($task->user_assigned , $response);
    }

    public function testPatchTask()
    {
        $this->withoutMiddleware();
        //Task not found
        $this->be(factory(User::class)->create());
        $patch = $this->json('PATCH', '/api/v1/tasks/1',[]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');//Not found
        //Task found
        $task = factory(Task::class)->create();
        $patch = $this->json('PATCH', '/api/v1/tasks/'.$task->id,[]);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT,'Test if HTTP Not Content'); //Empty Request
        $patch = $this->json('PATCH', '/api/v1/tasks/'.$task->id,['title' => '1']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Valid Data
        $patch = $this->json('PATCH', '/api/v1/tasks/'.$task->id,['title' => 'foo' , 'description' => 'x']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid description
        $patch = $this->json('PATCH', '/api/v1/tasks/'.$task->id,['title' => 'foo' , 'description' => 'foo bar baz' , 'due_date' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid Date
        $patch = $this->json('PATCH', '/api/v1/tasks/'.$task->id,['title' => 'foo' , 'description' => 'foo bar baz' , 'due_date' => 'foo', 'user_assigned_id' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid user_assigned_id
        $patch = $this->json('PATCH', '/api/v1/tasks/'.$task->id,['title' => 'foo' , 'description' => 'foo bar baz' , 'due_date' => '2017-01-17', 'user_assigned_id' => '1']);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT,'Test if HTTP No Content'); //Valid data
    }

    public function testDeleteTask()
    {
        $this->withoutMiddleware();
        //Priority not found
        $this->be(factory(User::class)->create());
        $delete = $this->json('DELETE', 'api/v1/tasks/1');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test HTTP Not Found');
        //Priority found
        $task = factory(Task::class)->create();
        $delete = $this->json('DELETE', 'api/v1/tasks/'.$task->id);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT, 'Test HTTP No Content');
        $this->missingFromDatabase('task',['id' => $task->id]);
    }


}