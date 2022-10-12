<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_custom_fields', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->mediumText("name");
            $table->string("type")->nullable();
            $table->string("code")->nullable();
            $table->string("entity_type");
            $table->string("db_name")->nullable();
            $table->string("db_type")->nullable();
            $table->string("status")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_custom_fields');
    }
}
