<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmocompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_companies', function (Blueprint $table) {
            $table->integer("id")->primary();
            $table->string("name")->nullable()->comment("Название компании");
            $table->integer("responsible_user_id")->nullable()->comment("ID пользователя, ответственного за контакт");
            $table->integer("group_id")->nullable()->comment("ID группы, в которой состоит ответственны пользователь за контакт");
            $table->integer("created_by")->nullable()->comment("ID пользователя, создавший контакт");
            $table->integer("updated_by")->nullable()->comment("ID пользователя, изменивший контакт");
            $table->timestamp("created_at")->nullable()->comment("Дата создания контакта");
            $table->timestamp("updated_at")->nullable()->comment("Дата изменения контакта");
            $table->timestamp("closest_task_at")->nullable()->nullable()->comment("Дата ближайшей задачи к выполнению");
            $table->boolean("is_deleted")->nullable()->comment("Удален ли элемент");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_companies');
    }
}
