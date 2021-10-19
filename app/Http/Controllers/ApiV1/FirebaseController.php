<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Models\FirebaseToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FirebaseController extends Controller
{
    public function saveToken(Request $request)
    {
        $validatedData = $request->validate([
            'token' => 'required|string',
        ]);
        $validatedData['user_id'] = Auth::user()->id;

        $token = FirebaseToken::firstOrNew($validatedData);
        $token->save();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
