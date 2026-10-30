<?php
namespace App\Http\Requests\Api;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => "L'adresse email est requise.",
            'email.email' => "L'adresse email n'est pas valide.",
            'password.required' => "Le mot de passe est obligatoire.",
            'password.min' => "Le mot de passe doit comporter au moins 8 caractères.",
        ];
    }
}
?>