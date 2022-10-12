<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValueToAmoStatusHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('amo_status_histories', function (Blueprint $table) {
            $table->mediumText("new_status")->nullable()->after("new_status_id");
            $table->mediumText("new_pipeline")->nullable()->after("new_pipeline_id");
            $table->mediumText("old_status")->nullable()->after("old_status_id");
            $table->mediumText("old_pipeline")->nullable()->after("old_pipeline_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('amo_status_histories', function (Blueprint $table) {
            //
        });
    }
}
