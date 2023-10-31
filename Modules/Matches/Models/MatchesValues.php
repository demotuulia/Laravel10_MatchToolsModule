<?php

namespace Modules\Matches\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $matches_id
 * @property string $value
 * @property  string $valueLabel
 * @property ?int $order
 * @property int $matches_profile_id
 */
class MatchesValues extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'matches_values';

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

    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            MatchesProfile::class,
            'matches_profile_id',
            'id'
        );
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(
            Matches::class,
            'matches_id',
            'id'
        );
    }
}
