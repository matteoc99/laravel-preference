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
            $table->text('policy')->change();
            $table->text('cast')->change();
            $table->text('rule')->change();
        });
    }

    public function down()
    {
        $preferenceTable = (new Preference())->getTable();

        Schema::table($preferenceTable, function (Blueprint $table) {
            $table->json('policy')->change();
            $table->json('cast')->change();
            $table->json('rule')->change();
        });
    }
};