<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// use DB;
use Hash;
// use Storage;

use App\User;

class APIController extends Controller {

  public function getLogin(Request $request) {

    // Meter Validation

    $res = [];

    $email = isset($request->email) ? $request->email : '';
    $pass = isset($request->pass) ? $request->pass : 0;

    // print_r($pass);
    // exit();

    $status = 200;
    $msj = '';

    if ($email) {
      if ($pass) {

        // OJO! Esta consulta ya ignora los usuarios que tienen rellenado el campo 'deleted_at' si queremos verlos todos hay que añadir ->withTrashed() a la consulta
        if (strpos($email, '@') !== false) {
          $user = User::where('email', $email)->first();
        } else {
          $user = User::where('clave', $email)->first();
        }

        if ($user) {

          // Comprobar que el $pass que nos pasan coincida con los datos del usuario que pretenden loguear
          // if ($user->password == md5($pass)) {
          if (Hash::check($pass, $user->password)) {

            // Si el usuario no tiene api_token se lo generamos
            if (!$user->api_token) {
              $user->generateToken();
            }

            $res['user'] = $user;
          }
          else { $status = 401; $msj = 'No existe / Borrado / Credenciales inválidas'; }
        }
        // El usuario no existe.
        else { $status = 400; $msj = 'Credenciales inválidas'; }
      }
      else { $status = 400; $msj = 'Falta el pass'; }
    }
    else { $status = 400; $msj = 'Falta el email/usuario'; }

    $res['error'] = $msj;

    return response()->json($res, $status);
  }

  public function getUser(Request $request) {

    // OJO! Se ignoran los usuarios que tienen rellenado el campo 'deleted_at'
    // Si esta borrado devuelve "error": "Unauthenticated."
    $res['user'] = $request->user();

    return response()->json($res, 200);
  }

  // TODO: Añadir function datos_comunes()

}
