<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonApiCollection;
use App\Mail\UserCreatedMail;
use App\Mail\UserPasswordResetMail;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request) {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $validatedData['deleted'] = false;
        if (!Auth::attempt($validatedData)) {
            return response()->json([
                "errors" => [
                    "email" => "Invalid email or password",
                ]
            ], 401);
        }

        // delete old tokens on login if not Administrator
        if(Auth::user()->role_id != 1){
            Auth::user()->tokens->each(function($token, $key) {
                $token->delete();
            });
        }
        $token = Auth::user()->createToken('authToken');
        return response()->json([
           "user" => Auth::user(),
           "access_token" => $token->accessToken,
           "expires_at" => $token->token->expires_at,
        ]);
    }

    public function all(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $users = User::where('deleted', false)->with('contractor')->paginate($perPage);

        return new JsonApiCollection($users);
    }

    public function roles()
    {
        return Role::all();
    }

    public function userInfo(int $id)
    {
        return User::with('contractor')->findOrFail($id);
    }

    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'position' => 'required|string',
            'company' => 'required|string',
            'role_id' => 'required|int|exists:roles,id',
            'contractor_id' => 'nullable|int|exists:contractors,id',
        ]);

        $user = new User($validatedData);
        $password = Str::random(13);
        $user->password = Hash::make($password);
        $user->save();

        Mail::to($user->email)
            ->queue(new UserCreatedMail($user->id, $password));

        return $user;
    }

    public function edit(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'email' => 'email|unique:users,email,'.$id,
            'phone' => 'nullable|string',
            'first_name' => 'string',
            'last_name' => 'string',
            'middle_name' => 'string',
            'position' => 'string',
            'company' => 'string',
            'role_id' => 'int|exists:roles,id',
            'contractor_id' => 'nullable|int|exists:contractors,id',
        ]);

        $user = User::findOrFail($id);
        $user->update($validatedData);

        return $user->fresh(['role', 'contractor']);
    }

    public function passwordReset(int $id)
    {
        $user = User::findOrFail($id);
        $password = Str::random(13);
        $user->password = Hash::make($password);
        $user->save();

        Mail::to($user->email)
            ->queue(new UserPasswordResetMail($user->id, $password));

        return $user;
    }

    public function remove(int $id)
    {
        $user = User::findOrFail($id);
        $user->deleted = true;
        $user->deleted_at = Carbon::now();
        $user->save();
        
        //remove all user tokens
        $user->tokens->each(function($token, $key) {
            $token->delete();
        });

        return response()->json([
            'status' => 'success',
        ]);
    }

}
