<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;

return new class extends Migration {

    public function up()
    {

        $preferenceTable     = (new Preference())->getTable();
        $userPreferenceTable = (new UserPreference())->getTable();

        Schema::create($preferenceTable, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group')->default('general'); // find and organize preferences // group -> Collection<Preferences>
            $table->string('name');  // group.name ->  Preference or
            $table->string('description')->nullable();
            $table->json('cast');
            $table->json('rule')->nullable(); // Rule Class for validation | default, validate the cast
            $table->json('default_value')->nullable();
            $table->timestamps();

            $table->unique(['group', 'name']);
        });

        Schema::create($userPreferenceTable, function (Blueprint $table) use ($preferenceTable) {
            $table->bigIncrements('id');
            $table->morphs('preferenceable');
            $table->unsignedInteger('preference_id');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->foreign('preference_id')
                ->references('id')
                ->on($preferenceTable)
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        $preferenceTable     = (new Preference())->getTable();
        $userPreferenceTable = (new UserPreference())->getTable();

        Schema::dropIfExists($userPreferenceTable);
        Schema::dropIfExists($preferenceTable);
    }
};