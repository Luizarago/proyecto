<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class homeController extends Controller
{
    /**
     * Muestra la vista de inicio o el panel de usuario seg�n el estado de autenticaci�n.
     */
    public function index(){
        if(!Auth::check()){
            return view('welcome');
        }
        return view('panel.index');
    }

}
