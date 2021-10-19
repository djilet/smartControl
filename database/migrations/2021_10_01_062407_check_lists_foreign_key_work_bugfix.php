<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CheckListsForeignKeyWorkBugfix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('check_lists', function (Blueprint $table) {
            $table->dropForeign(['work_id']);
            $table->foreign('work_id')
                ->references('id')
                ->on('works');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // TODO: Добавить для отката
    }
}
