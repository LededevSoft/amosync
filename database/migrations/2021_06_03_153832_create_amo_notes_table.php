<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_notes', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->integer("entity_id");
			$table->string("entity_type")->nullable();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamp("created_at")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("responsible_user_id")->nullable();
            $table->string("group_id")->nullable();
            $table->string("note_type");
            $table->integer("account_id");
            $table->longText("text")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_notes');
    }
}
