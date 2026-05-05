<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'exercise_type_id' => ['required', 'integer'],
            'weight' => ['required', 'numeric', 'min:0'],
            'series' => ['required', 'integer', 'min:1'],
            'reps' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validacion',
            'errors' => $validator->errors(),
        ], 422));
    }
}
