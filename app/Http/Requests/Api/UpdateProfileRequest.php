<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $role = $user?->role;

        return [
            'name' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'telephone' => ['required', 'string', 'max:255'],
            'photo_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],

            'etablissement' => [Rule::requiredIf($role === 'etudiant'), 'string', 'max:255'],
            'universite_id' => ['nullable', 'integer', 'exists:universites,id'],
            'universite' => ['nullable', 'string', 'max:20', 'exists:universites,code'],
            'filiere' => [Rule::requiredIf($role === 'etudiant'), 'string', 'max:255'],
            'niveau' => [Rule::requiredIf($role === 'etudiant'), 'in:L1,L2,L3,M1,M2'],
            'sexe' => [Rule::requiredIf($role === 'etudiant'), 'in:M,F'],
            'preferences' => ['nullable', 'array'],
            'preferences.*' => ['string', 'max:255'],
            'budget_max' => ['nullable', 'integer', 'min:5000'],

            'ifu' => [
                Rule::requiredIf($role === 'demarcheur'),
                'string',
                'max:255',
                Rule::unique('profils_demarcheurs', 'ifu')->ignore($user?->profilDemarcheur?->id),
            ],
            'nom_agence' => [Rule::requiredIf($role === 'demarcheur'), 'string', 'max:255'],
            'zone_activite' => [Rule::requiredIf($role === 'demarcheur'), 'string', 'max:255'],

            'ifu_rccm' => [
                Rule::requiredIf($role === 'boutique'),
                'string',
                'max:255',
                Rule::unique('profils_boutiques', 'ifu_rccm')->ignore($user?->profilBoutique?->id),
            ],
            'nom_boutique' => [Rule::requiredIf($role === 'boutique'), 'string', 'max:255'],
            'categorie' => [Rule::requiredIf($role === 'boutique'), 'string', 'max:255'],
            'adresse_physique' => [Rule::requiredIf($role === 'boutique'), 'string', 'max:1000'],
            'logo_boutique' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ];
    }
}
