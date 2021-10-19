<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingHandbookWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_handbook_works', function (Blueprint $table) {
            $table->unsignedBigInteger('handbook_id');
            $table->unsignedBigInteger('work_id');

            $table->foreign('handbook_id')
                ->references('id')
                ->on('building_handbooks');

            $table->foreign('work_id')
                ->references('id')
                ->on('works');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_handbook_works');
    }
}
