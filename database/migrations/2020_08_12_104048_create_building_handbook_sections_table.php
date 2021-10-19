<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingHandbookSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_handbook_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pid')->nullable()->index();
            $table->unsignedBigInteger('handbook_id')->nullable()->index();
            $table->unsignedBigInteger('work_id')->nullable()->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
        });

        Schema::table('building_handbook_sections', function ($table) {
            $table->dropColumn('body');
        });

        Schema::table('building_handbook_sections', function ($table) {
            $table->text('body')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_handbook_sections');
    }
}
