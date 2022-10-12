<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_leads', function (Blueprint $table) {
            $table->integer("id")->primary();
            $table->string("name")->nullable()->comment("Название сделки");
            $table->bigInteger("price")->nullable()->comment("Бюджет сделки");
            $table->integer("responsible_user_id")->nullable()->comment("ID пользователя, ответственного за сделку");
            $table->integer("group_id")->nullable()->comment("ID группы, в которой состоит ответственны пользователь за сделку");
            $table->integer("status_id")->nullable()->comment("ID статус");
            $table->integer("pipeline_id")->nullable()->comment("ID воронки");
            $table->integer("loss_reason_id")->nullable()->comment("ID причины потери");
            $table->integer("created_by")->nullable()->comment("ID пользователя, создавшего сделку");
            $table->integer("updated_by")->nullable()->comment("ID пользователя, изменившего сделку");
            $table->timestamp("created_at")->nullable()->comment("Дата создания сделки");
            $table->timestamp("updated_at")->nullable()->comment("Дата изменения сделки");
            $table->timestamp("closed_at")->nullable()->comment("Дата закрытия сделки");
            $table->timestamp("closest_task_at")->nullable()->comment("Дата ближайшей задачи к выполнению");
            $table->boolean("is_deleted")->nullable()->comment("Удален ли элемент");
            $table->integer("score")->nullable(); //->comment("ID причины потери");
            $table->integer("account_id")->nullable()->comment("ID аккаунта");
            $table->mediumText("loss_reason")->nullable()->comment("Причины потери");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_leads');
    }
}
