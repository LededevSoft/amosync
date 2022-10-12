<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoCompanyContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_company_contacts', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("contact_id");
            $table->boolean("is_main");
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
        Schema::dropIfExists('amo_company_contacts');
    }
}
