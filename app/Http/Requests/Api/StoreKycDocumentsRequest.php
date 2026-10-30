<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreKycDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'min:1'],
            'documents.*.type_piece' => ['required', 'string', 'in:CARTE_ETUDIANT,CIP,IFU,RCCM'],
            'documents.*.numero_piece' => ['required', 'string', 'max:255'],
            'documents.*.fichier' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = $this->user();
                $types = collect($this->input('documents', []))->pluck('type_piece')->toArray();

                if ($user->role === 'etudiant') {
                    if (!in_array('CARTE_ETUDIANT', $types) && !in_array('CIP', $types)) {
                        $validator->errors()->add('documents', 'En tant qu\'étudiant, vous devez fournir une CARTE_ETUDIANT ou une CIP.');
                    }
                } elseif ($user->role === 'demarcheur') {
                    if (!in_array('IFU', $types)) {
                        $validator->errors()->add('documents', 'En tant que démarcheur, vous devez fournir un IFU.');
                    }
                } elseif ($user->role === 'boutique') {
                    if (!in_array('IFU', $types) || !in_array('RCCM', $types)) {
                        $validator->errors()->add('documents', 'En tant que boutique, vous devez fournir un IFU et un RCCM.');
                    }
                }
            }
        ];
    }
}
