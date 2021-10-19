<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandbookWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('works', 'handbook_works');
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('parent_id')->nullable();;
            $table->timestamps();
        });
        Schema::table('contractor_items', function (Blueprint $table) {
            $table->dropForeign(['work_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('works');
        Schema::rename('handbook_works', 'works');
        Schema::table('contractor_items', function (Blueprint $table) {
            $table->foreign('work_id')
                ->references('id')
                ->on('works');
        });
    }
}
