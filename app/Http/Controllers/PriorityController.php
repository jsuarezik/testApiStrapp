<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Priority;
use Auth;

class PriorityController extends Controller
{
    public function all(Request $request)
    {
        $priorities = Priority::all();

        return response()->json($priorities, 200);
    }

    public function get(Request $request, $id)
    {
        $priority = Priority::findOrFail($id);
        return response()->json($priority, 200);
    }

    public function add(Request $request)
    {
        $priority = new Priority();

        $rules = [
            'name' => 'required|alpha|min:2|unique:priority,name'
        ];

        $this->validate($request, $rules);
        $priority->fill($request->all());
        $priority->save();

        return response()->json($priority, 201);
    }

    public function patch(Request $request ,$id)
    {
        $priority = Priority::findOrFail($id);
        $rules = [
            'name' => 'alpha|min:2'
        ];

        $this->validate($request, $rules);
        $priority->fill($request->all());
        $priority->save();

        return response()->json([], 204);
    }

    public function delete(Request $request ,$id)
    {
        $priority = Priority::findOrFail($id);
        $priority->delete();

        return response()->json([],204);
    }
}
