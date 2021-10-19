<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuildingFileActualChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('building_files', function (Blueprint $table) {
            $table->boolean('is_actual')->default(true)->after('user_filename');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('building_files', function (Blueprint $table) {
            $table->dropColumn('is_actual');
        });
    }
}
