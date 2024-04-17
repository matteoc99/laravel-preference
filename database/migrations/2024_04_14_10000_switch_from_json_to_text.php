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
            $table->text('policy')->nullable()->change();
            $table->text('cast')->nullable()->change();
            $table->text('rule')->nullable()->change();
        });
    }

    public function down()
    {
        $preferenceTable = (new Preference())->getTable();

        Schema::table($preferenceTable, function (Blueprint $table) {
            $table->json('policy')->nullable()->change();
            $table->json('cast')->nullable()->change();
            $table->json('rule')->nullable()->change();
        });
    }
};