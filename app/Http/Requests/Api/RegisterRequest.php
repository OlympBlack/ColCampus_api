<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->input('role');

        return [
            'name' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'telephone' => ['required', 'string'],
            'role' => ['required', 'string', 'in:etudiant,demarcheur,boutique'],
            'photo_profil' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],

            'etablissement' => [Rule::requiredIf($role === 'etudiant'), 'string', 'max:255'],
            'universite_id' => ['nullable', 'integer', 'exists:universites,id'],
            'universite' => ['nullable', 'string', 'max:20', 'exists:universites,code'],
            'filiere' => [Rule::requiredIf($role === 'etudiant'), 'string', 'max:255'],
            'niveau' => [Rule::requiredIf($role === 'etudiant'), 'in:L1,L2,L3,M1,M2'],
            'sexe' => [Rule::requiredIf($role === 'etudiant'), 'in:M,F'],
            'preferences' => [Rule::requiredIf($role === 'etudiant'), 'array', 'min:1'],
            'preferences.*' => ['string', 'max:255'],

            'ifu' => [Rule::requiredIf($role === 'demarcheur'), 'string', 'max:255', 'unique:profils_demarcheurs,ifu'],
            'nom_agence' => [Rule::requiredIf($role === 'demarcheur'), 'string', 'max:255'],
            'zone_activite' => [Rule::requiredIf($role === 'demarcheur'), 'string', 'max:255'],

            'ifu_rccm' => [Rule::requiredIf($role === 'boutique'), 'string', 'max:255', 'unique:profils_boutiques,ifu_rccm'],
            'nom_boutique' => [Rule::requiredIf($role === 'boutique'), 'string', 'max:255'],
            'categorie' => [Rule::requiredIf($role === 'boutique'), 'string', 'max:255'],
            'adresse_physique' => [Rule::requiredIf($role === 'boutique'), 'string', 'max:1000'],
            'logo_boutique' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('role') === 'etudiant' && !$this->input('universite_id') && !$this->input('universite')) {
                $validator->errors()->add('universite', 'Indiquez votre université.');
            }
        });
    }
}
