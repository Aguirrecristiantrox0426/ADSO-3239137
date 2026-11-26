<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);
        $email = mb_strtolower(trim($credentials['email']));
        $user = User::where('email', $email)->first();
        if (!$user || !$user->is_active || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['success'=>false,'message'=>'Credenciales inválidas.','data'=>null,'errors'=>['email'=>['Las credenciales no son válidas.']]], 401);
        }
        $token = $user->createToken('rent-tools-api')->plainTextToken;
        return response()->json(['success'=>true,'message'=>'Autenticación exitosa.','data'=>['user'=>$this->payload($user),'token'=>$token],'errors'=>[]]);
    }
    public function logout(Request $request): JsonResponse { $request->user()->currentAccessToken()?->delete(); return response()->json(['success'=>true,'message'=>'Sesión cerrada.','data'=>null,'errors'=>[]]); }
    public function me(Request $request): JsonResponse { return response()->json(['success'=>true,'message'=>'Usuario autenticado.','data'=>$this->payload($request->user()),'errors'=>[]]); }
    private function payload(User $user): array { return ['id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'role'=>$user->role]; }
}
