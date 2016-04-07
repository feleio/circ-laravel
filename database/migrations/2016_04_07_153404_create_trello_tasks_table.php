<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrelloTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_trello')->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',50);
            $table->decimal('actual_hour', 10, 2)->default(0.0);
            $table->decimal('plan_hour', 10, 2)->default(0.0);
            $table->integer('list_id');
            $table->enum('label',['None','Done','Cancel','Delay'])->default('None');
            $table->timestamp('plan_date')->useCurrent();
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
        Schema::connection('mysql_trello')->drop('tasks');
    }
}
