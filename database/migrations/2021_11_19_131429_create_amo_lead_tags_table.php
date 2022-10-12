<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoLeadTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_lead_tags', function (Blueprint $table) {
            $table->id();
            $table->integer("lead_id");
            $table->integer("tag_id");
            $table->string("tag_name");
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
        Schema::dropIfExists('amo_lead_tags');
    }
}
