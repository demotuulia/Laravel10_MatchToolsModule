<?php

namespace Modules\Matches\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $db_code  // code for database
 * @property string $label  // readable name
 * @property string $valueLabel // dynamically created human readable value
 * @property string $match_type
 * @property int $matches_form_id
 * @property int $ordering
 */
class Matches extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'matches';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


    public function form(): BelongsTo
    {
        return $this->belongsTo(
            MatchesForm::class,
            'matches_form_id',
            'id'
        );
    }

    public function values(): HasMany
    {
        return $this->hasMany(MatchesValues::class);
    }

    public function optionValues(): HasMany
    {
        return $this->hasMany(MatchesOptionValues::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(MatchesOptions::class);
    }

}
