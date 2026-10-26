<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExerciseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercise_type_id' => ['sometimes', 'integer', 'exists:exercise_types,id'],
            'weight' => ['sometimes', 'numeric', 'min:0'],
            'series' => ['sometimes', 'integer', 'min:1'],
            'reps' => ['sometimes', 'integer', 'min:1'],
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
