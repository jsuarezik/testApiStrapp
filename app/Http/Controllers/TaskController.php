<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Priority;
use App\Models\User;
use Auth;

class TaskController extends Controller
{
    public function all(Request $request)
    {
        $tasks = Task::all();
        return response()->json($tasks,200);
    }

    public function get(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        return response()->json($task,200);
    }

    public function getCreatorUser(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        return response()->json($task->creator, 200);
    }

    public function getAssignedUser(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        return response()->json($task->user_assigned, 200);
    }

    public function add(Request $request)
    {
        $logged_user = Auth::User();

        $task = new Task();

        $rules = [
            'title' => 'required|alpha_num|min:2',
            'description' => 'required|min:2' ,
            'due_date' => 'required|date',
            'priority_id' => 'required|numeric|min:0',
            'creator_id' => 'sometimes|numeric|min:0',
            'user_assigned_id' => 'required|numeric|min:0'
        ];

        $this->validate($request, $rules);

        $priority = Priority::findOrFail($request->input('priority_id'));
        $creator =  User::findOrFail($request->input('creator_id', $logged_user->id));
        $assigned = User::findOrFail($request->input('user_assigned_id'));

        $fields = $request->all();
        $fields['creator_id'] = $request->input('creator_id', $logged_user->id);

        $task->fill($fields);
        $task->save();

        return response()->json($task, 201);
    }

    public function assignTask(Request $request, $id, $user_id)
    {
        $task = Task::findOrFail($id);
        $user = User::findOrFail($user_id);
        $task->user_assigned()->associate($user);
        $task->save();

        return response()->json($task,200);
    }

    public function patch(Request $request ,$id)
    {
        $logged_user = Auth::user();

        $task = Task::findOrFail($id);

        $rules = [
            'title' => 'sometimes|alpha_num|min:2',
            'description' => 'sometimes|min:2' ,
            'due_date' => 'sometimes|date',
            'priority_id' => 'sometimes|numeric|min:0',
            'creator_id' => 'sometimes|numeric|min:0',
            'user_assigned_id' => 'sometimes|numeric|min:0'
        ];

        $this->validate($request, $rules);

        if ($request->has('priority_id'))
            $priority = Priority::findOrFail($request->input('priority_id'));
        if ($request->has('creator_id'))
            $creator = User::findOrFail($request->input('creator_id', $logged_user->id));
        if ($request->has('user_assigned_id'))
            $assigned = User::findOrFail($request->input('user_assigned_id'));

        $task->fill($request->all());
        $task->save();

        return response()->json([], 204);
    }

    public function delete(Request $request ,$id)
    {
        $logged_user = Auth::user();

        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([],204);
    }
}
