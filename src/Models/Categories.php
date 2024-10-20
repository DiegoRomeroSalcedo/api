<?php

namespace Proyecto\Models;

use PDO;
use PDOException;
use Config\Database;

class Categories extends BaseModel{
    // Modelo para las distintas interacciones con la tabla de categories.
    private $connection;
    
    public function __construct() {
        $this->connection = Database::getInstance()->getConnection(); // Recuperamos la conexión a la base de datos.
    }

    // Método para crear una categoría para los productos del inventario.
    public function createCategory($nomCategory, $userId) {
        try {
            $query = "INSERT INTO inventario.categorias
                        (nom_categori, usu_insercio, usu_actualiz)
                        VALUES (:nom_categori, :usu_insercio, :usu_actualiz)";
            $mysql = $this->connection->prepare($query);
            $mysql->bindParam(':nom_categori', $nomCategory, PDO::PARAM_STR);
            $mysql->bindParam(':usu_insercio', $userId, PDO::PARAM_INT);
            $mysql->bindParam(':usu_actualiz', $userId, PDO::PARAM_INT);
            $mysql->execute();
            $result = $this->connection->lastInsertId(); // Devuelve el id del 
            return $result;
        } catch(PDOException $e) {
            return $this->jsonResponse(['Error_message' => $e->getMessage()], 500);
        }
    }

    // Método para obtener todas las categorías del inventario.
    public function getAll() {
        try {
            $query = "SELECT * FROM inventario.categorias";
            $mysql = $this->connection->prepare($query);
            $mysql->execute();
            $result = $mysql->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch(PDOException $e) {
            return $this->jsonResponse(['Error_message' => $e->getMessage()], 500);
        }
    }

    // Método para obtener una categoría en especifico.
    public function getCategory($input) {
        try {
            $query = "SELECT * FROM inventario.categorias ";
            if (is_numeric($input)) {
                $query .= "WHERE id_categoria = :id_categoria";
                $mysql = $this->connection->prepare($query);
                $mysql->bindParam(':id_categoria', $input, PDO::PARAM_INT);
            } else {
                $query .= "WHERE nom_categori LIKE :nom_categori";
                $mysql = $this->connection->prepare($query);
                $likeInput = "%{$input}%";
                $mysql->bindParam(':nom_categori', $likeInput, PDO::PARAM_STR);
            }

            $mysql->execute();
            $result = $mysql->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch(PDOException $e) {
            return $this->jsonResponse(['Error_message' => $e->getMessage()], 500);
        }
    }

    public function updateCategory($idCategory, $nameCategory, $user) {
        try {
            $query = "  UPDATE inventario.categorias
                        SET nom_categori = :nom_categori,
                            usu_actualiz = :usu_actualiz
                        WHERE id_categoria = :id_categoria
                        AND EXISTS (
                            SELECT 1
                            FROM inventario.categorias
                            WHERE id_categoria = :id_categoria
                        )";
            $mysql = $this->connection->prepare($query);
            $mysql->bindParam(':nom_categori', $nameCategory, PDO::PARAM_STR);
            $mysql->bindParam(':usu_actualiz', $user, PDO::PARAM_INT);
            $mysql->bindParam(':id_categoria', $idCategory, PDO::PARAM_INT);
            $mysql->execute();
            return $mysql->rowCount(); // Devuelve el número de filas afectadas.
        } catch(PDOException $e) {
            return $this->jsonResponse(["Error_message" => $e->getMessage()], 500);
        }
    }

    public function deleteCategory($id) {
        try {
            $query = "DELETE FROM inventario.categorias WHERE id_categoria = :id_categoria";
            $mysql = $this->connection->prepare($query);
            $mysql->bindParam(':id_categoria', $id, PDO::PARAM_INT);
            $mysql->execute();
            return $mysql->rowCount();
        } catch(PDOException $e) {
            return $this->jsonResponse(["Error_message" => $e->getMessage()], 500);
        }
    }
}