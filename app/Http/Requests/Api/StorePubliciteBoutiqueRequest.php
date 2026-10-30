<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePubliciteBoutiqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre_promo' => ['required', 'string', 'max:255'],
            'lien_action' => ['required', 'string', 'max:1000'],
            'quartier_cible' => ['nullable', 'string', 'max:120', Rule::exists('quartiers', 'nom')],
            'banniere' => ['required', 'image', 'mimes:jpg,jpeg,png,webp'],
        ];
    }
}
