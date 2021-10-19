<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->string("address");
            $table->float("floors");
            $table->text('floors_ext')->nullable();
            $table->string("responsible");
            $table->string("area");
            $table->boolean("closed");
            $table->unsignedBigInteger("user_created");
            $table->timestamps();

            $table->index(['closed', 'created_at']);
            $table->foreign('user_created')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buildings');
    }
}
