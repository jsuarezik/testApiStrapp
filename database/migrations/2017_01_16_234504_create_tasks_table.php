<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('description');
            $table->date('due_date');
            $table->integer('priority_id')->unsigned();
            $table->integer('creator_id')->unsigned();
            $table->integer('user_assigned_id')->unsigned();

            $table->foreign('priority_id')->references('id')->on('priority')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('creator_id')->references('id')->on('user')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_assigned_id')->references('id')->on('user')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');
    }
}
