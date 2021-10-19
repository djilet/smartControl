<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateContractorItemsAddEditable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractor_items', function (Blueprint $table) {
            $table->boolean('editable')->default(false)->after('cost');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractor_items', function (Blueprint $table) {
            $table->dropColumn('editable');
        });
    }
}
