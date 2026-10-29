<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut_verification' => ['required', 'string', 'in:valide,rejete'],
            'commentaire_admin' => [Rule::requiredIf($this->input('statut_verification') === 'rejete'), 'nullable', 'string', 'max:1000'],
        ];
    }
}
