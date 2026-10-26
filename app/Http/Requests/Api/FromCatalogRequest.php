<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FromCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Catalog id has the form "muscle/slug", e.g. "biceps/barbell-curl".
            'catalog_id' => ['required', 'string', 'regex:#^[a-z0-9-]+/[a-z0-9-]+$#'],
            'lang' => ['sometimes', 'string', 'in:es,en'],
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
