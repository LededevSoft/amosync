<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_users', function (Blueprint $table) {
            $table->integer("id")->primary();
            $table->mediumText("name");
            $table->mediumText("email");
            $table->integer("group_id")->nullable();
            $table->mediumText("group_name")->nullable();
            $table->boolean("is_sync");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_users');
    }
}
