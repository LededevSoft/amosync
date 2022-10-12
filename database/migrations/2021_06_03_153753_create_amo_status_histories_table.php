<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoStatusHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_status_histories', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->integer("entity_id");
            $table->integer("account_id")->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("created_at");
            $table->string("new_status_id");
            $table->string("new_pipeline_id");
            $table->string("old_status_id");
            $table->string("old_pipeline_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_status_histories');
    }
}
