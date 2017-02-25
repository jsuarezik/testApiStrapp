<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $users = factory(App\Models\User::class,5)->create();
        $products = factory(App\Models\Product::class,10)->create();

        $users->each(function ($user) use ($products, $faker){
            $user_products = $products->random($faker->numberBetween(2,10));

            $user->products()->sync($user_products);
        });
    }
}
