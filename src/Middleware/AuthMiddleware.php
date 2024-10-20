<?php

// La clase cumple la función de middleware, para verificar la autenticidad del JWT antes de permitir el acceso a cualquier recurso protegido.

namespace Proyecto\Middleware;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Proyecto\Controllers\BaseController;

class AuthMiddleware extends BaseController{
    // Propiedad estática para almacenar el usuario autenticado.
    public static $autheticatedUser;
    public static function verificarJWT($token) {
        try {
            $decoded = JWT::decode($token, new key('T#mye!gYjo69&aVsgQ7yx8', 'HS256'));

            self::$autheticatedUser = $decoded;
            return $decoded;
        } catch(Exception $e) {
            return false;
        }
    }

    public static function protegerEndpoint($method = null) {
        $baseController = new BaseController(); // Esto con el motivo de no cambiar la encapsulacion del metodo jsonResponse.

        // Si la cabecera no esta definida y es null me niega el acceso.
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            echo json_encode(['message' => 'Acceso denegado']);
            http_response_code(401);
            exit();
        }

        // Extraer el token de la cabecera Authorization.
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $token = str_replace('Bearer ', '', $authHeader);

        $decoded = self::verificarJWT($token);

        if (!$decoded) {
            echo json_encode(['message' => 'Acceso denegado, token invalido o expirado']);
            http_response_code(401);
            exit();
        }

        if ($method) {
            if ($decoded->rol !== 'admin' && in_array($method, ['POST', 'PUT', 'DELETE'])) {
                return $baseController->jsonResponse(['message' => 'Acceso denegado. Rol insuficiente para realizar esta acción.'], 403);
            }
        }

        return $decoded;
    }
}