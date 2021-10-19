<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_work_id');
            $table->string('filename')->index();
            $table->string('user_filename');
            $table->timestamps();

            $table->foreign('building_work_id')
                ->references('id')
                ->on('building_works')
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
        Schema::dropIfExists('building_files');
    }
}
