<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;


class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();
        return response()->json($user);
    }
}
