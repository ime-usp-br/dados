<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Log;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(public Request $request)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {            
        Log::create([
            "operacao"=>"LOGIN",
            "status"=>"OK",
            "usuario_id"=>$event->user->id,
            "descricao"=>$this->request->getClientIp()
        ]);
    }
}
