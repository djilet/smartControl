<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCheckListStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE check_lists CHANGE status status ENUM('with_comments','without_comments','open','accepted','accepted_part','canceled','draft') NOT NULL DEFAULT 'open'");
        DB::statement("ALTER TABLE check_list_items CHANGE status status ENUM('red','green','yellow','grey') NOT NULL");
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
