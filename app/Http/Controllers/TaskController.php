<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    //
    public function index()
    {
        // Fetch all tasks from database
        $tasks = Task::all();

        // Send data to a Blade view
        return view('tasks.index', compact('tasks'));
    }
}
