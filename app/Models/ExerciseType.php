<?php

namespace App\Models;

use Database\Factories\ExerciseTypeFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseType extends Model
{
    /** @use HasFactory<ExerciseTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'catalog_id',
        'slug',
        'muscle',
        'equipment',
        'gif_url',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return ExerciseTypeFactory::new();
    }

    /**
     * @return HasMany<Exercise, $this>
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }
}
