<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_lists', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date', 0);
            $table->string('type');
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('contractor_id')->nullable();
            $table->unsignedBigInteger('work_id')->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->string('floor');
            $table->string('floors_ext')->nullable();
            $table->string('contractor_representative')->default('');
            $table->string('number_ks')->nullable();
            $table->string('sum_ks')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->enum('status', ['with_comments', 'without_comments', 'open', 'accepted', 'accepted_part', 'canceled'])->default('open');
            $table->timestamps();

            $table->foreign('contractor_id')
                ->references('id')
                ->on('contractors')
                ->cascadeOnDelete();

            $table->foreign('work_id')
                ->references('id')
                ->on('works')
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
        Schema::dropIfExists('check_lists');
    }
}
