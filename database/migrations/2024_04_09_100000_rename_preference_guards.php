<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Matteoc99\LaravelPreference\Models\Preference;

return new class extends Migration {

    public function up()
    {

        $preferenceTable = (new Preference())->getTable();

        Schema::table($preferenceTable, function (Blueprint $table) {
            $table->renameColumn('guard', 'policy');
        });
    }

    public function down()
    {
        $preferenceTable = (new Preference())->getTable();

        Schema::table($preferenceTable, function (Blueprint $table) {
            $table->renameColumn('policy', 'guard');
        });
    }
};