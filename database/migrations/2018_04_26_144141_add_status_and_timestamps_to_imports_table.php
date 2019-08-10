<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusAndTimestampsToImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->enum('status', array('new','queued','in progress','completed','error','deleted','killed'))->default('new');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });

        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
}
