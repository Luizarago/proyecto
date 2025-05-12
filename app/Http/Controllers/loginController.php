<?php

namespace App\Http\Controllers;

use App\Http\Requests\loginRequest;
use Illuminate\Support\Facades\Auth;

// Controlador encargado de la autenticación de usuarios (login).
class loginController extends Controller
{
    // Muestra el formulario de login. Si el usuario ya está autenticado, lo redirige al panel.
    public function index(){
        if(Auth::check()){
            return redirect()->route('panel');
        }
        return view('auth.login');
    }

    // Procesa el formulario de login.
    public function login(loginRequest $request){
        // Valida las credenciales del usuario.
        if(!Auth::validate($request->only('email','password'))){
            return redirect()->to('login')->withErrors('Credenciales incorrectas');
        }

        // Si son correctas, inicia sesión y redirige al panel con un mensaje de bienvenida.
        $user = Auth::getProvider()->retrieveByCredentials($request->only('email','password')); 
        Auth::login($user);

        return redirect()->route('panel')->with('success', 'Bienvenido '.$user->name);
    }
}
