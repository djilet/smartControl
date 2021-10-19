<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBuildingFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('building_files', function (Blueprint $table) {
            $table->integer('page_count')->default(0)->after('user_filename');
            $table->boolean('is_processing')->default(true)->after('page_count');
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
            $table->dropColumn('page_count');
            $table->dropColumn('is_processing');
        });
    }
}
