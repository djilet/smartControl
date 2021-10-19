<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckListRenouncementItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_list_renouncement_items', function (Blueprint $table) {
            $table->unsignedBigInteger('renouncement_id');
            $table->unsignedBigInteger('check_list_id');
            $table->primary(['renouncement_id', 'check_list_id'], 'renouncement_check_list_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_list_renouncement_items');
    }
}
