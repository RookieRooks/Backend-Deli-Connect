<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    "type" => 'required',
                    "telephone" => 'required',
                    "motdepasse" => 'required'
                ],
                [
                    'telephone.required' => 'Numero de téléphone requis !',
                    'motdepasse.required' => 'Mot de passe obligatoire !',
                    'type.required' => 'Type d\'utilisateur requis !'
                ]
            );
            //erreur de validation
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => "Erreur de validation",
                    'error' => $validator->errors()
                ], 404);
            }
            //erreur d'identification
            $credentials = ['email' => "{$request->telephone}@{$request->type}.dc", 'password' => $request->motdepasse];
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => "Erreur d'identification",
                    'error' => $validator->errors()
                ], 404);
            }

            $user = Auth::user();
            return response()->json([
                'status' => true,
                'message' => "Utilisateur connecté avec succès",
                "token" => $user->createToken("DC_TOKEN_API")->plainTextToken,
                "data" => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'error_message' => $th->getMessage()
            ], 404);
        }
    }

    public function register(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make(
                $input,
                [
                    "nom" => 'required',
                    "prenom" => 'required',
                    "type" => 'required',
                    "telephone" => 'required|unique:users,telephone',
                    "motdepasse" => 'required'
                ],
                [
                    'nom.required' => 'Votre nom est requis !',
                    'prenom.required' => 'Votre prenom est requis !',
                    'type.required' => "Type d'utilisateur requis !",
                    'telephone.required' => 'Numero de téléphone requis !',
                    'telephone.unique' => 'Ce numero de téléphone a déjà un compte !',
                    'motdepasse.required' => 'Mot de passe obligatoire !'
                ]
            );
            //erreur de validation
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => "Erreur de validation",
                    'error' => $validator->errors()
                ], 404);
            }

            $user = User::create([
                "type" => $request->type,
                "nom" => $request->nom,
                "prenom" => $request->prenom,
                "telephone" => $request->telephone,
                "motdepasse" => $request->motdepasse,
                "email" => "{$request->telephone}@{$request->type}.dc",
                "password" => Hash::make($request->motdepasse),
            ]);

            return response()->json([
                'status' => true,
                'message' => "Utilisateur inscrit avec succès",
                "token" => $user->createToken("DC_TOKEN_API")->plainTextToken,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Erreur",
                'error' => $th->getMessage()
            ], 404);
        }
    }

    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => "Profil Utilisateur",
            "data" => $request->user()
        ], 200);
    }
}
