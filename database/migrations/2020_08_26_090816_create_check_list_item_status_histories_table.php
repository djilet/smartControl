<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckListItemStatusHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_list_item_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('check_list_item_id')->index();
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_list_item_status_histories');
    }
}
