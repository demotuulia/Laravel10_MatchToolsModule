<?php

namespace Modules\Matches\Models;

use  Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Matches\Enums\EMatchType;

/**
 * @property int $id
 * @property string $name
 * @property array $matchTypes  // dynamically created variable
 */
class MatchesForm extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'matches_form';

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

    public function matches(): HasMany
    {
        return $this->hasMany(Matches::class);
    }

    public function setMatchTypes(): void
    {
        $this->matchTypes = array_column(EMatchType::cases(), 'name');
    }

    public function profiles(): Collection
    {
        $profiles = DB::table('matches_values')
            ->select('matches_profile_id')
            ->distinct()
            ->join('matches', 'matches.id', '=', 'matches_values.matches_id')
            ->where('matches.matches_form_id', $this->id);
        return $profiles->get();
    }
}
