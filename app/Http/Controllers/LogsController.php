<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;

class LogsController extends Controller
{
    public function index()
    {
        if(!Auth::check()){
            return redirect(route("login"));
        }elseif(!Auth::user()->hasPermissionTo("admin", "senhaunica")){
            return abort(403);
        }

        $logs = Log::all();

        return view("logs.index", compact("logs"));
    }
}
