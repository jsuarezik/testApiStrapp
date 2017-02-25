<?php

use App\Models\Product;

use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testListProducts()
    {
        //Test for an empty list
        $this->withoutMiddleware();
        $get = $this->json('GET','/api/v1/products');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(0,$response, 'Test if query count is zero');
        //Test for a non empty list
        $products = factory(Product::class,2)->create();
        $get = $this->json('GET', 'api/v1/products');
        $this->assertResponseOk();
        $response = json_decode($get->response->getContent(), true);
        $this->assertNotNull($response, 'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE, 'Test if the response was ok');
        $this->assertCount(2, $response, 'Test if query count is 2');

        $this->assertEquals($products->toArray(), $response);
    }

    public function testPostProduct()
    {
        $this->withoutMiddleware();
        $post = $this->json('POST', '/api/v1/products', []); //Empty request
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if Unprocessable Entity');
        $post = $this->json('POST', '/api/v1/products', ['name' => '12']); // Minimun Lenght 3
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid name
        $post = $this->json('POST', '/api/v1/products', ['name' => 'foo', 'price' => 'asd']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY, 'Test if HTTP Unprocessable Entity');//Invalid price
        $post = $this->json('POST', '/api/v1/products', ['name' => 'foo', 'price' => '5', 'in_stock' => true]);
        $this->assertResponseStatus(Response::HTTP_CREATED, 'Test if HTTP Created');//Valid data
        $this->seeInDatabase('product',['name' => 'foo', 'price' => '5']);
        $response = json_decode($post->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $product = Product::findOrFail(1);
        $this->assertEquals($product->toArray(), $response);
    }

    public function testGetProduct()
    {

        $this->withoutMiddleware();
        //Product not found
        $query = $this->json('GET', '/api/v1/products/1');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');
        //User found
        $product = factory(Product::class)->create();
        $query = $this->json('GET','/api/v1/products/'.$product->id);
        $this->assertResponseOk();
        $response = json_decode($query->response->getContent(),true);
        $this->assertNotNull($response,'Test if is a valid json');
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE,'Test if the response was ok');
        $this->assertEquals($product->toArray(), $response);
    }

    public function testPatchProduct()
    {

        $this->withoutMiddleware();
        $patch = $this->json('PATCH', '/api/v1/products/1',[]);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test if HTTP Not Found');//Not found
        //Product found but invalid data
        $product = factory(Product::class)->create();

        $patch = $this->json('PATCH', '/api/v1/products/'.$product->id,['name' => '123']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid name
        $patch = $this->json('PATCH', '/api/v1/products/'.$product->id,['name' => 'foo', 'price' => 'foo']);
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY,'Test if HTTP Unprocessable Entity'); //Invalid price
        $patch = $this->json('PATCH', '/api/v1/products/'.$product->id,['name' => 'foo', 'price' => '5.0']);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT, 'Test if HTTP No Content');//Valid Data
        $this->seeInDatabase('product', ['name' => 'foo', 'price' => '5.0']);
    }

    public function testDeleteProduct()
    {
        $this->withoutMiddleware();

        $delete = $this->json('DELETE', 'api/v1/products/1');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND, 'Test HTTP Not Found');
        $product = factory(Product::class)->create();
        $delete = $this->json('DELETE', 'api/v1/products/'.$product->id);
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT, 'Test HTTP No Content');
        $this->missingFromDatabase('product',['id' => $product->id]);
    }

}