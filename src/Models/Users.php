<?php

namespace Proyecto\Models;

use Config\Database;
use PDO;
use PDOException;

class Users {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection(); // Recuperamos la conexión a la base de datos.
    }

    public function registerUser($nombre, $email, $password, $rol) {
        try {
            // Hasheamos la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Preparamos la consulta de inserción del usuario en la db.
            $query = "INSERT INTO inventario.usuarios
                        (nom_usuari, email_usua, pass_usuar, rol_usuari)
                        VALUES 
                        (:nom_usuari, :email_usua, :pass_usuar, :rol_usuari)";
            $query = $this->db->prepare($query);
            // Asignamos los valores de las variables.
            $query->bindParam(":nom_usuari", $nombre);
            $query->bindParam(":email_usua", $email);
            $query->bindParam(":pass_usuar", $hashedPassword);
            $query->bindParam(":rol_usuari", $rol);
            $result = $query->execute();
            return $result;
        } catch(PDOException $e) {
            echo 'error: ' . $e->getMessage();
        }
    }

    // Método para validar si el usuario ya esta registrado.
    public function verifyUser($email) {
        $query = $this->db->prepare('SELECT * FROM inventario.usuarios WHERE email_usua = :email_usua');
        $query->bindParam(':email_usua', $email);
        $query->execute();
        return $query->fetch();
    }
}