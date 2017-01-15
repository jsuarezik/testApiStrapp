<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function all(Request $request)
    {
        $users = User::all();
        return response()->json($users);
    }

    public function get(Request $request, $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function add(Request $request)
    {
        $user = new User();

        $rules = ['email' => 'required|email|unique:users', 'name' => 'required|min:2', 'password' => 'required|confirmed:password_confirmation', 'password_confirmation' => 'required'];

        $this->validate($request, $rules);
        $user->fill($request->all());
        $user->password = $request->input('password');
        $user->save();

        return response()->json($user);
    }

    public function patch(Request $request ,$id)
    {
        $user = User::findOrFail($id);
        $rules = ['email' => 'required' , 'name' => 'required'];
        $this->validate($request, $rules);
        $user->fill($request->all());
        $user->save();

        return response()->json($user);
    }

    public function delete(Request $request ,$id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response('no content',204);
    }
}
