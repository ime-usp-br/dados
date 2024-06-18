<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class APIAcessoController extends Controller
{
    public function individual(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

        if(!$request->has("codpes")){
            return response()->json([
                "status"=>false,
                "message"=>"Não foi encontrado o codpes."
            ]);
        }

        $codpes = $request->get("codpes");

        return response()->json([
            "status"=>true,
            "message"=>$codpes
        ]);


    }

    public function lote(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

    }

    public function ativo(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != "Bearer ".env("API_ACESSO_TOKEN")){
            return response()->json([
                "status"=>"403",
                "message"=>"Token não confere!"
            ]);
        }

    }
}
