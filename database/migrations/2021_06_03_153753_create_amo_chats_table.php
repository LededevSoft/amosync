<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_chats', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->integer("entity_id");
			$table->string("type");
			$table->string("entity_type");
            $table->integer("account_id")->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("created_at");
            $table->string("message_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_chats');
    }
}
