<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoTaskDeadlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_task_deadlines', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->integer("entity_id");
            $table->integer("account_id")->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("created_at");
            $table->timestamp("new_task_deadline_at");
            $table->timestamp("old_task_deadline_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_task_deadlines');
    }
}
