<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// use DB;
use Hash;
// use Storage;

use App\User;

class APIController extends Controller {

  public function getLogin(Request $request) {

    // TODO: Meter Validation

    $res = [];

    $email = $request->email ?? '';
    $pass = $request->pass ?? 0;    

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

            $status = 200;
            $res['user'] = $user;

          } else { $status = 401; $res['error'] = 'No existe / Borrado / Credenciales inválidas'; }
        } else { $status = 400; $res['error'] = 'Credenciales inválidas'; }
      } else { $status = 400; $res['error'] = 'Falta el pass'; }
    } else { $status = 400; $res['error'] = 'Falta el email/usuario'; }

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
