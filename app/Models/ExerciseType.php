<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseType extends Model
{
    public $table = 'exercise_type';

    protected $fillable = [
        'name'
    ]; 
}
