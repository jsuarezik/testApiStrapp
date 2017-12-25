<?php

use Illuminate\Database\Seeder;
use App\Models\Travel;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $travels = factory(App\Models\Travel::class,3)->create();
    }
}
