<?php

namespace Proyecto\Controllers;

use Exception;
use Proyecto\Models\Users;
use Firebase\JWT\JWT;

class UsersController extends BaseController {

    protected $user;

    public function __construct() {
        $this->user = new Users(); // Recuperamos la instancia de usuario.
    }
    public function register($request) {
        try {
            // Validamos los datos entrantes (nombre, email, password, rol).

            // Llamamos al modelo para registrar el usuario.
            $user = $this->user;
            $user->registerUser($request['nombre_usuario'], $request['email'],  $request['password'], $request['rol']);

            // Verificamos el resultado de la inserción.
            if ($user) {
                return $this->jsonResponse(['message' => 'Usuario registrado con éxito.']);
            } else {
                return $this->jsonResponse(['error_message' => 'Error en el registro del Usuario.'], 500);
            }
        } catch(Exception $e) {
            // En caso de error devolver el mensaje de errror.
            return $this->jsonResponse(['error_message: ' => $e->getMessage()], 500);
        }
    }

    // Método de login
    public function login($request) {
        // Verificamos las credenciales con el modelo.
        $user = $this->user;
        $user = $user->verifyUser($request['email']);

        if ($user && password_verify($request['password'], $user['pass_usuar'])) {
            // Generamos el JWT
            $payload = [
                'iss' => 'http://localhost', // Identifica quien creo el JWT.
                'iat' => time(), // Cuando fue creado el JWT.
                'exp' => time() + (60 * 120), // Tiempo de expiración del JWT.
                'sub' => $user['id_usuario'],
                'rol' => $user['rol_usuari']
            ];

            $jwt = JWT::encode($payload, 'T#mye!gYjo69&aVsgQ7yx8', 'HS256');

            // Devolver JWT en la respuesta
            return $this->jsonResponse(['token' => $jwt]);
        } else {
            return $this->jsonResponse(['message' => 'Credendiales Incorrectas'], 401);
        }
    }
}
