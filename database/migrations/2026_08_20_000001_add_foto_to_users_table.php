<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFotoToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'foto')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('foto')->nullable()->after('level');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'foto')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('foto');
            });
        }
    }
}
