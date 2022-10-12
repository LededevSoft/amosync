<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_tasks', function (Blueprint $table) {
            $table->integer("id")->primary();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("created_at")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("responsible_user_id")->nullable();
            $table->integer("group_id")->nullable();
            $table->integer("entity_id")->nullable();
            $table->string("entity_type")->nullable();
            $table->integer("duration")->nullable();
            $table->boolean("is_completed")->nullable();
            $table->integer("task_type_id")->nullable();
            $table->mediumText("text")->nullable();
            $table->mediumText("result")->nullable();
            $table->timestamp("complete_till")->nullable();
            $table->integer("account_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_tasks');
    }
}
