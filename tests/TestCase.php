<?php
use Illuminate\Support\Facades\Artisan;

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function tearDown()
    {
        Artisan::call('migrate:reset');
        parent::tearDown();
    }

    protected function assertObjectEqualsExclude($model, $array, $keys = [])
    {
        $attributes = $model->toArray();
        foreach ($keys as $key)
        {
            unset($attributes[$key]);
        }

        foreach ($attributes as $key => $attribute){
            $this->assertEquals($model->$key, $array[$key]);
        }
    }


}
