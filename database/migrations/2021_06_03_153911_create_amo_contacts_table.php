<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_contacts', function (Blueprint $table) {
            $table->integer("id")->primary();
            $table->string("name")->nullable()->comment("Название контакта");
            $table->string("first_name")->nullable()->comment("Имя контакта");
            $table->string("last_name")->nullable()->comment("Фамилия контакта");
            $table->integer("responsible_user_id")->nullable()->comment("ID пользователя, ответственного за контакт");
            $table->integer("group_id")->nullable()->comment("ID группы, в которой состоит ответственны пользователь за контакт");
            $table->integer("created_by")->nullable()->comment("ID пользователя, создавший контакт");
            $table->integer("updated_by")->nullable()->comment("ID пользователя, изменивший контакт");
            $table->timestamp("created_at")->nullable()->comment("Дата создания контакта");
            $table->timestamp("updated_at")->nullable()->comment("Дата изменения контакта");
            $table->timestamp("closest_task_at")->nullable()->nullable()->comment("Дата ближайшей задачи к выполнению");
            $table->boolean("is_deleted")->nullable()->comment("Удален ли элемент");
            $table->mediumText("phone")->nullable()->comment("Телефон");
            $table->mediumText("email")->nullable()->comment("Email");
            $table->string("position")->nullable()->comment("Должность");
            $table->string("im")->nullable()->comment("Мгн. сообщения");
            $table->string("user_agreement")->nullable()->comment("Пользовательское соглашение");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_contacts');
    }
}
