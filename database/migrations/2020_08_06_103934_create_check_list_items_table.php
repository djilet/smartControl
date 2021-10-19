<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckListItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('check_list_id');
            $table->string('image');
            $table->string('image_size');
            $table->string('image_crop')->nullable();
            $table->string('coor');
            $table->enum('status', ['red', 'green', 'yellow']);
            $table->text('desc')->nullable();
            $table->timestamp('date_elimination')->nullable();
            $table->float('scale')->default('1');
            $table->timestamps();

            $table->foreign('check_list_id')
                ->references('id')
                ->on('check_lists')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_list_items');
    }
}
