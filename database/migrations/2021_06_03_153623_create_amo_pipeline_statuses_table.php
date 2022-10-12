<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoPipelineStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_pipeline_statuses', function (Blueprint $table) {
            $table->id();
            $table->integer("status_id");
            $table->mediumText("name");
            $table->integer("pipeline_id");
            $table->integer("sort");
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
        Schema::dropIfExists('amo_pipeline_statuses');
    }
}
