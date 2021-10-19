<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChecklistsAddPdfFilename extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_lists', function (Blueprint $table) {
            $table->string('pdf_filename')->nullable()->default(null)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_lists', function (Blueprint $table) {
            $table->dropColumn('pdf_filename');
        });
    }
}
