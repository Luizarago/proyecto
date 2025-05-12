<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class homeController extends Controller
{
    /**
     * Muestra la vista de inicio o el panel de usuario según el estado de autenticación.
     */
    public function index(){
        if(!Auth::check()){
            return view('welcome');
        }
        return view('panel.index');
    }

}
