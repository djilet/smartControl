<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractorItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_id');
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('work_id');
            $table->string('cost');
            $table->timestamps();

            $table->foreign('contractor_id')
                ->on('contractors')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreign('building_id')
                ->on('buildings')
                ->references('id');

            $table->foreign('work_id')
                ->on('works')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractor_items');
    }
}
