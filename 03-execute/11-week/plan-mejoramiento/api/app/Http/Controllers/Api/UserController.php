<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse { return response()->json(['success'=>true,'message'=>'Usuarios consultados.','data'=>User::orderBy('name')->get(['id','name','email','role','is_active','created_at']),'errors'=>[]]); }
    public function show(User $user): JsonResponse { return response()->json(['success'=>true,'message'=>'Usuario consultado.','data'=>$this->payload($user),'errors'=>[]]); }
    public function store(Request $request): JsonResponse { $data=$this->validateUser($request); $data['password']=Hash::make($data['password']); $user=User::create($data); return response()->json(['success'=>true,'message'=>'Usuario creado.','data'=>$this->payload($user),'errors'=>[]],201); }
    public function update(Request $request, User $user): JsonResponse { $data=$this->validateUser($request,$user); if (isset($data['password'])) $data['password']=Hash::make($data['password']); $user->update($data); return response()->json(['success'=>true,'message'=>'Usuario actualizado.','data'=>$this->payload($user->fresh()),'errors'=>[]]); }
    public function destroy(User $user): JsonResponse { $user->update(['is_active'=>false]); $user->tokens()->delete(); return response()->json(['success'=>true,'message'=>'Usuario desactivado.','data'=>null,'errors'=>[]]); }
    private function validateUser(Request $request, ?User $user=null): array { return $request->validate(['name'=>[$user?'sometimes':'required','string','max:255'],'email'=>[$user?'sometimes':'required','email',Rule::unique('users','email')->ignore($user?->id)],'password'=>[$user?'nullable':'required','string','min:8'],'role'=>[$user?'sometimes':'required',Rule::in(['administrator','advisor'])],'is_active'=>['sometimes','boolean']]); }
    private function payload(User $user): array { return $user->only(['id','name','email','role','is_active','created_at']); }
}
