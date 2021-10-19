<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractorItemEliminationDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_item_elimination_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_item_id');
            $table->string('sum');
            $table->timestamp('date');
            $table->timestamps();

            $table->foreign('contractor_item_id')
                ->references('id')
                ->on('contractor_items')
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
        Schema::dropIfExists('contractor_item_elimination_dates');
    }
}
