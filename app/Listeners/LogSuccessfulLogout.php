<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Log;

class LogSuccessfulLogout
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
    public function handle(Logout $event): void
    {  
        Log::create([
            "operacao"=>"LOGOUT",
            "status"=>"OK",
            "usuario"=>$event->user->id,
            "descricao"=>$this->request->getClientIp()
        ]);
    }
}
