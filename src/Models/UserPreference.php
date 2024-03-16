<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Matteoc99\LaravelPreference\Casts\ValueCaster;

/**
 * @property int                $id
 * @property int                $preference_id
 * @property mixed              $value
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 *
 * @property Preference         $preference
 */
class UserPreference extends BaseModel
{

    protected $table = 'users_preferences';

    protected $fillable = ['preference_id', 'value'];

    protected $casts = [
        'value' => ValueCaster::class,
    ];

    public function preference(): BelongsTo
    {
        return $this->belongsTo(Preference::class);
    }

    public function preferenceable(): MorphTo
    {
        return $this->morphTo();
    }
}