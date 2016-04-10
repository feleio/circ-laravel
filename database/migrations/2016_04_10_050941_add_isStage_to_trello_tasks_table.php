<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsStageToTrelloTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_trello')->table('tasks', function (Blueprint $table) {
            $table->boolean('isStage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_trello')->table('tasks', function (Blueprint $table) {
            $table->dropColumn('isStage');
        });
    }
}
