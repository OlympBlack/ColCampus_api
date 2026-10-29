<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Support\ApiData;
use App\Support\ProfileDataManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite']);

        return response()->json([
            'data' => array_merge(ApiData::user($user), [
                'profile_completion_percentage' => ProfileDataManager::completionPercentage($user),
            ]),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'prenoms' => $validated['prenoms'],
            // 'email' => $validated['email'],
            'telephone' => $validated['telephone'],
        ]);

        ProfileDataManager::sync($user, $validated, $request);
        $user->save();
        $user = $user->fresh(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite']);

        return response()->json([
            'message' => 'Profil mis a jour avec succes.',
            'user' => array_merge(ApiData::user($user), [
                'profile_completion_percentage' => ProfileDataManager::completionPercentage($user),
            ]),
        ]);
    }

    public function completeProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->statut_verification, ['quarantaine', 'rejete'], true)) {
            return response()->json(['message' => 'Le profil est deja en cours de validation ou deja valide.'], 400);
        }

        $rules = [];
        if ($user->role === 'etudiant') {
            $rules = [
                'carte_etudiant' => 'required_without:cip|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'cip' => 'required_without:carte_etudiant|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];
        } elseif ($user->role === 'demarcheur') {
            $rules = [
                'ifu_doc' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'piece_identite' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];
        } elseif ($user->role === 'boutique') {
            $rules = [
                'ifu_doc' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'rccm_doc' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];
        }

        $request->validate($rules);

        // Upload and save logic
        if ($user->role === 'etudiant') {
            if ($request->hasFile('carte_etudiant')) {
                $path = $request->file('carte_etudiant')->store('documents/etudiant', 'public');
                $user->profilEtudiant()->update(['carte_etudiant_path' => $path]);
            }
            if ($request->hasFile('cip')) {
                $path = $request->file('cip')->store('documents/etudiant', 'public');
                $user->profilEtudiant()->update(['cip_path' => $path]);
            }
        } elseif ($user->role === 'demarcheur') {
            if ($request->hasFile('ifu_doc')) {
                $path = $request->file('ifu_doc')->store('documents/demarcheur', 'public');
                $user->profilDemarcheur()->update(['ifu_doc_path' => $path]);
            }
            if ($request->hasFile('piece_identite')) {
                $path = $request->file('piece_identite')->store('documents/demarcheur', 'public');
                $user->profilDemarcheur()->update(['piece_identite_path' => $path]);
            }
        } elseif ($user->role === 'boutique') {
            if ($request->hasFile('ifu_doc')) {
                $path = $request->file('ifu_doc')->store('documents/boutique', 'public');
                $user->profilBoutique()->update(['ifu_doc_path' => $path]);
            }
            if ($request->hasFile('rccm_doc')) {
                $path = $request->file('rccm_doc')->store('documents/boutique', 'public');
                $user->profilBoutique()->update(['rccm_doc_path' => $path]);
            }
        }

        $user->update(['statut_verification' => 'pending_validation']);

        return response()->json([
            'message' => 'Documents soumis avec succes. En attente de validation.',
            'user' => array_merge(ApiData::user($user), [
                'profile_completion_percentage' => ProfileDataManager::completionPercentage($user),
            ]),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Mot de passe modifie avec succes.']);
    }
}
