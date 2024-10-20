<?php 

namespace Proyecto\Models;

use PDO;
use PDOException;
use Config\Database;
use Proyecto\Models\BaseModel;

class Products extends BaseModel{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection(); // Recuperamos la conexión a la base de datos.
    }

    public function createProduct($data) {
        try {
            $query = "INSERT INTO inventario.productos(
                            nom_product,
                            description,
                            precio_prod,
                            id_categori,
                            usu_inserci,
                            usu_actuali
                        )
                        VALUES(
                            :nom_product,
                            :description,
                            :precio_prod,
                            :id_categori,
                            :usu_inserci,
                            :usu_actuali
                        )";
            $mysql = $this->db->prepare($query);          
            foreach ($data as $key => $value) {
                $mysql->bindValue(":{$key}", $value); // En lugar de bindParam, ya que este ultimo vincula variables por referencia.
            }
            $mysql->execute();
            $result = $this->db->lastInsertId(); // Devuelve el id del último producto insertado.
            return $result;
        } catch (PDOException $e) {
            return $this->jsonResponse(["Error_message" => $e->getMessage()], 400);
        }
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM inventario.productos";
            $mysql = $this->db->prepare($query);
            $mysql->execute();
            $result = $mysql->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch(PDOException $e) {
            return $this->jsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    public function getProduct($input) {
        try {
            $query = "SELECT * FROM inventario.productos ";

            if (is_numeric($input)) {
                $query .= "WHERE id_producto = :id_producto";
                $mysql = $this->db->prepare($query);
                $mysql->bindParam(":id_producto", $input, PDO::PARAM_INT);
            } else {
                $query .= "WHERE nom_product LIKE :nom_product";
                $mysql = $this->db->prepare($query);
                $likeInput = "%{$input}%";
                $mysql->bindParam(":nom_product", $likeInput, PDO::PARAM_STR);
            }
            $mysql->execute();
            $result = $mysql->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch(PDOException $e) {
            return $this->jsonResponse(["messsage" => $e->getMessage()], 500);
        }
    }

    public function updateProduct($data) {
        try {
            $query = "  UPDATE inventario.productos
                        SET nom_product = :nom_product,
                            description = :description,
                            precio_prod = :precio_prod,
                            id_categori = :id_categori,
                            usu_actuali = :usu_actuali
                        WHERE
                            id_producto = :id_producto";
            $mysql = $this->db->prepare($query);
            foreach ($data as $key => $value) {
                $mysql->bindValue(":{$key}", $value);
            }

            $mysql->execute();
            return $mysql->rowCount(); // Devuelve el número de filas afectadas.
        } catch(PDOException $e) {
            return $this->jsonResponse(["Error_message", $e->getMessage()], 500);
        }
    }

    public function deleteProduct($id) {
        try {
            $query = "DELETE FROM inventario.productos WHERE id_producto = :id_producto";
            $mysql = $this->db->prepare($query);
            $mysql->bindParam(":id_producto", $id, PDO::PARAM_INT);
            $mysql->execute();
            return $mysql->rowCount(); // Devuelve el número de filas afectadas.
        } catch(PDOException $e) {
            return $this->jsonResponse(["Error_message" => $e->getMessage()]);
        }
    }

    public function addEntry($data) {
        try {
            $query = "CALL inventario.registrarEntrada(:id_producto, :cantidad, :usuario_insercion)";
            $mysql = $this->db->prepare($query);
            foreach ($data as $key => $value) {
                $mysql->bindValue(":{$key}", $value);
            }
            $result = $mysql->execute();
            return $result;
        } catch(PDOException $e) {
            return $this-> jsonResponse(["Error_message" => $e->getMessage()], 500);
        }
    }

    public function addSale($data) {
        try {
            $query = "CALL inventario.registrarSalida(:id_producto, :cantidad, :usuario_insercion)";
            $mysql = $this->db->prepare($query);
            foreach ($data as $key => $value) {
                $mysql->bindValue(":{$key}", $value);
            }
            $result = $mysql->execute();
            return $result;
        } catch(PDOException $e) {
            $result = $e->getMessage();
            if (preg_match('/^SQLSTATE\[45000\]/', $result)) {
                return $this->jsonResponse(["Error_message" => "Stock insuficiente para realizar la operación."], 409); // Conflicto, pues el recurso no posee el stock suficiente.
            } else {
                return $this->jsonResponse(["Error_message" => $e->getMessage()], 500);
            }
        }
    }

    public function getTransaction($id) {
        try {
            $query = "SELECT * FROM inventario.inventario_productos WHERE id_inventario = :id_inventario";
            $mysql = $this->db->prepare($query);
            $mysql->bindParam(":id_inventario", $id, PDO::PARAM_INT);
            $mysql->execute();
            $result = $mysql->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch(PDOException $e) {
            return $this->jsonResponse(["Error_message" => $e->getMessage()], 500);
        }
    }

    public function getAllTransactions() {
        try {
            $query = "SELECT * FROM inventario.inventario_productos";
            $mysql = $this->db->prepare($query);
            $mysql->execute();
            $result = $mysql->fetchAll();
            return $result;
        } catch(PDOException $e) {
            return $this->jsonResponse(["Error_message" => $e->getMessage()], 500);
        }
    }
}