<?php

namespace Modules\Matches\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Modules\Matches\Models\Traits\TMatchesFormat;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property string $tags
 * @property  int $is_professional
 * @property Collection<Matches> $matches
 * @property int $score // dynamically defined variable in  matches search results
 */
class MatchesProfile extends Model
{
    use HasFactory;
    use TMatchesFormat;

    public static $professional = 1;
    public static $company = 0;

    /**
     * @var string
     */
    protected $table = 'matches_profile';

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

    public function matches(): Collection
    {
        $formId = $this->formId();
        $profileId = $this->id;
        $matches = MatchesForm::where('id', $formId)
            ->first()
            ->matches()->with(
                [
                    'values' => function ($q) use ($profileId) {
                        $q->where('matches_profile_id', '=', $profileId);
                    },
                    'optionValues' => function ($q) use ($profileId) {
                        $q->where('matches_profile_id', '=', $profileId);
                    },
                    'options'
                ]
            )
            ->where('matches_form_id', $formId)
            ->get();

        /** @var Matches $match */
        foreach ($matches as $match) {
            $this->formatOutput($match);

        }
        return $matches;
    }

    public function setMatches(): self
    {
        $this->matches = $this->matches();
        return $this;
    }

    public
    function values(): HasMany
    {
        return $this->hasMany(MatchesValues::class);
    }

    public
    function optionValues(): HasMany
    {
        return $this->hasMany(MatchesOptionValues::class);
    }

    public
    function options(): HasMany
    {
        return $this->hasMany(MatchesOptions::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public
    function formId(): int
    {
        return $this->values()->first()->match()->first()->form()->first()->id;
    }
}
