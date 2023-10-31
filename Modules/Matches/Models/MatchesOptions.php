<?php

namespace Modules\Matches\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $matchesProfileId
 * @property int $matches_id
 * @property string $code
 * @property string $value
 * @property int $order
 * // Property to indicate if current option is selected for the current user
 * // (not a db column)
 * @property bool $selected
 */
class MatchesOptions extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'matches_options';

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
