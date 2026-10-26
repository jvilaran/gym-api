<?php

namespace App\Models;

use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return ExerciseFactory::new();
    }

    protected $fillable = [
        'user_id',
        'exercise_type_id',
        'weight',
        'series',
        'reps',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'series' => 'integer',
            'reps' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ExerciseType, $this>
     */
    public function exerciseType(): BelongsTo
    {
        return $this->belongsTo(ExerciseType::class);
    }

    /**
     * Volumen de la serie: series x reps x peso.
     */
    public function volume(): float
    {
        return (float) $this->weight * $this->series * $this->reps;
    }
}
