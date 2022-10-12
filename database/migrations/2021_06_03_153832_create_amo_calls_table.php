<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_calls', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->integer("entity_id");
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("created_at")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("responsible_user_id")->nullable();
            $table->string("group_id")->nullable();
            $table->string("note_type");
            $table->integer("account_id");
            $table->string("uniq")->nullable();
            $table->string("duration")->nullable();
            $table->string("source")->nullable();
            $table->mediumText("link")->nullable();
            $table->string("phone")->nullable();
            $table->mediumText("call_result")->nullable();
            $table->string("call_status")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_calls');
    }
}
