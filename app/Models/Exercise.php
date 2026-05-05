<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'user_id',
        'exercise_type_id',
        'weight',
        'series',
        'reps',
    ]; 
}
