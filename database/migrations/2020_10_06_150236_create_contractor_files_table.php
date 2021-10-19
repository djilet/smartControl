<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractorFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_item_id');
            $table->string('filename')->index();
            $table->string('user_filename');
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
        Schema::dropIfExists('contractor_files');
    }
}
