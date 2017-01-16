<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class UserController extends Controller
{
    public function all(Request $request)
    {
        $users = User::all();
        return response()->json($users,200);
    }

    public function get(Request $request, $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user,200);
    }

    public function add(Request $request)
    {
        $logged_user = Auth::User();

        if (!$logged_user->isAdmin()){
            abort(403, 'Forbidden, admin access only');
        }

        $user = new User();

        $rules = [
            'email' => 'required|email|unique:user',
            'first_name' => 'alpha|required|min:2' ,
            'last_name' => 'alpha|required|min:2',
            'password' => 'required|confirmed:password_confirmation',
            'password_confirmation' => 'required',
            'is_admin' => 'boolean'
        ];

        $this->validate($request, $rules);
        $user->fill($request->all());
        $user->password = $request->input('password');
        $user->save();

        return response()->json($user,201);
    }

    public function patch(Request $request ,$id)
    {
        $logged_user = Auth::user();

        if (!$logged_user->isAdmin()){
            abort(403, 'Forbidden, admin access only');
        }

        $user = User::findOrFail($id);

        $rules = [
            'email' => 'sometimes|email' ,
            'first_name' => 'alpha|min:2|sometimes',
            'last_name' => 'alpha|min:2|sometimes',
            'password' => 'confirmed:password_confirmation|sometimes',
            'password_confirmation' => 'required_with:password',
            'is_admin' => 'boolean'
        ];

        $this->validate($request, $rules);
        $user->fill($request->all());
        $user->save();

        return response()->json([], 204);
    }

    public function delete(Request $request ,$id)
    {
        $logged_user = Auth::user();

        if (!$logged_user->isAdmin()){
            abort(403, 'Forbidden, admin access only');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([],204);
    }
}
