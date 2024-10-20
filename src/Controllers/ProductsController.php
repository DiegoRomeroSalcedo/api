<?php

// Controlador de los productos

namespace Proyecto\Controllers;


use Exception;
use Proyecto\Middleware\AuthMiddleware;
use Proyecto\Models\Products;

class ProductsController extends BaseController{
    private $product;

    public function __construct() {
        $this->product = new Products();
    }

    public function addProduct($data) {
        try {
            // Devemos sanitizar los valores que nos llegan del front.
            ['nom_product' => $nomProduct, 'description' => $description, 'precio_prod' => $precioProd, 'id_categori' => $idCategori] = $data;
            $nomProduct = trim($nomProduct);
            $descProduc = trim($description) ?? 'No Reporta';
            $patternName = '/^[a-zA-Z0-9\s]+$/';

            if (!preg_match($patternName, $nomProduct)) {
                $response = [
                    'success' => false,
                    "error_message" => "El formato de nombre puede incluir: Letras (a-zA-Z), números (0-9) y espacios entre el texto"
                ];
                return $this->jsonResponse($response, 400);
            }

            if (strlen($descProduc) > 200) {
                $response = [
                    'success' => false,
                    "error_message" => "La longitud de la descripción del producto debe ser menor o igual a 200 caracteres."
                ];
                return $this->jsonResponse($response, 400);
            }

            // El precio (debe ser un número y tener el formato correcto).
            if (!is_numeric($precioProd) || (float) $precioProd <= 0) {
                $response = [
                    'success' => false,
                    "error_message" => "El precio debe ser un número válido y ser mayor a 0"
                ];
                return $this->jsonResponse($response, 400);
            }

            // Limitamos el número de decimales a 2.
            if (round($precioProd, 2) != $precioProd)  {
                $response = [
                    'success' => false,
                    "error_message" => "El precio solo puede tener hasta 2 decimales"
                ];
                return $this->jsonResponse($response, 400);
            }

            $userLogged = AuthMiddleware::$autheticatedUser;
            $userId = (int) $userLogged->sub;

            $dataArray = [
                'nom_product' => $nomProduct,
                'description' => $descProduc,
                'precio_prod' => $precioProd,
                'id_categori' => $idCategori,
                'usu_inserci' => $userId,
                'usu_actuali' => $userId
            ];

            $product = $this->product->createProduct($dataArray);

            if ($product) {
                $response = [
                    'success' => true,
                    "success_message" => "El producto se inserto con ID: {$product}"
                ];
                return $this->jsonResponse($response, 201);
            }

        } catch(Exception $e) {
            $response = [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
            return $this->jsonResponse($response, 500);
        }
    }
    public function getAllProducts() {
        // Llamamos al modelo para obtener los datos.
        $data = $this->product->getAll();
        if ($data) {
            $response = [
                'success' => true,
                'data' => $data
            ];
            return $this->jsonResponse($response);
        } else {
            $response = [
                'success' => false,
                'message' => "No hay productos."
            ];
            return $this->jsonResponse($response, 404);
        }
    }

    public function getIdOrName($input) {

        // Llamamos al modelo para obtener los datos por id o por nombre
        $formattedInput = preg_replace("/-/", ' ', $input);
        $data = $this->product->getProduct($formattedInput);
        if ($data) {
            $response = [
                'success' => true,
                'data' => $data
            ];
            return $this->jsonResponse($response);
        } else {
            $response = [
                'success' => false,
                "message" => "Producto no encontrado"
            ];
            return $this->jsonResponse($response, 404);
        }
    }

    public function updateProduct($idProduct, $data) {
        try {
            ['nom_product' => $nomProduct, 'description' => $description, 'precio_prod' => $precioProd, 'id_categori' => $idCategori] = $data;
            $nomProduct = trim($nomProduct);
            $descProduc = trim($description) ?? 'No reporta';
            $patternName = '/^[a-zA-Z0-9\s]+$/';

            if (!preg_match($patternName, $nomProduct)) {
                $response = [
                    'success' => false,
                    "error_message" => "El formato de nombre puede incluir: Letras (a-zA-Z), números (0-9) y espacios entre el texto"
                ];
                return $this->jsonResponse($response, 400);
            }

            if (strlen($descProduc) > 200) {
                $response = [
                    'success' => false,
                    "error_message" => "La longitud de la descripción del producto debe ser menor o igual a 200 caracteres."
                ];
                return $this->jsonResponse($response, 400);
            }

            if (!is_numeric($precioProd) && (float) $precioProd <= 0) {
                $response = [
                    'success' => false,
                    "error_message" => "El precio debe ser un número válido y ser mayor a 0"
                ];
                return $this->jsonResponse($response, 400);
            }

            if (round($precioProd, 2) != $precioProd) {
                $response = [
                    'success' => false,
                    "error_message" => "El precio solo puede tener hasta dos decimales"
                ];
                return $this->jsonResponse($response, 400);
            }

            $userLogged = AuthMiddleware::$autheticatedUser;
            $userId = (int) $userLogged->sub;

            $dataArray = [
                'nom_product' => $nomProduct,
                'description' => $descProduc,
                'precio_prod' => $precioProd,
                'id_categori' => $idCategori,
                'usu_actuali' => $userId,
                'id_producto' => $idProduct,
            ];

            $product = $this->product->updateProduct($dataArray);

            if ($product) {
                $response = [
                    'success' => true,
                    "success_message" => "Producto actualizado exitosatamente"
                ];
                return $this->jsonResponse($response, 200);
            } else {
                $response = [
                    'success' => false,
                    "message" => "Producto no encontrado"
                ];
                return $this->jsonResponse($response, 404);
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
            return $this->jsonResponse($response, 500);
        }
    }

    public function deleteProduct($idProduct) {
        try {
            $product = $this->product->deleteProduct($idProduct);

            if ($product) {
                $response = [
                    'success' => true,
                    "success_message" => "El Producto con id {$idProduct} se eliminó de manera exitosa"
                ];
                return $this->jsonResponse($response);
            } else {
                $response = [
                    'success' => false,
                    "message" => "Producto no encontrado."
                ];
                return $this->jsonResponse($response, 404);
            }
        } catch(Exception $e) {
            $response = [
                'success' => false,
                "error_message" => $e->getMessage()
            ];
            return $this->jsonResponse($response, 500);
        }
    }

    public function registerEntry($id, $data) {
        try {
            $typeMovement = $data["tipo_movimiento"];
            $amount = $data["cantidad"];
            $userLogged = AuthMiddleware::$autheticatedUser;
            $userId = (int) $userLogged->sub;
            
            // $typeMovement = trim(strtolower($typeMovement));

            if (!is_int($amount) || (int) $amount <= 0) {
                $response = [
                    'success' => false,
                    "error_message" => "Cantidad insuficiente para realizar operacion, debe ser un número entero positivo."
                ];
                return $this->jsonResponse($response, 400);
            }

            $dataArray = [
                'id_producto' => $id,
                'cantidad' => $amount,
                'usuario_insercion' => $userId
            ];

            $entry = $this->product->addEntry($dataArray);

            if ($entry) {
                $response = [
                    'success' => true,
                    "success_message" => "La entrada se inserto con éxito"
                ];
                return $this->jsonResponse($response, 201);
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                "error_message" => $e->getMessage()
            ];
            return $this->jsonResponse($response, 500);
        }
    }

    public function registerSale($id, $data) {
        try {
            $amount = $data["cantidad"];
            $userLogger = AuthMiddleware::$autheticatedUser;
            $userId = (int) $userLogger->sub;

            if (!is_int($amount) || (int) $amount <= 0) {
                $response = [
                    'success' => false,
                    "error_message" => "Cantidad insuficiente para realizar operacion, debe ser un número entero positivo."
                ];
                return $this->jsonResponse($response, 400);
            }

            $dataArray = [
                'id_producto' => $id,
                'cantidad' => $amount,
                'usuario_insercion' => $userId
            ];

            $sale = $this->product->addSale($dataArray);

            if ($sale) {
                $response = [
                    'success' => true,
                    "success_message" => "La salida se inserto con éxito."
                ];
                return $this->jsonResponse($response, 201);
            }
        } catch(Exception $e) {
            $response = [
                'success' => false,
                "error_message" => $e->getMessage()
            ];
            return $this->jsonResponse($response, 500);
        }
    }

    public function getTransaction($id) {
        $transaction = $this->product->getTransaction($id);
        if ($transaction) {
            $response = [
                'succes' => true,
                'data' => $transaction
            ];
            return $this->jsonResponse($response);
        } else {
            $response = [
                'succes' => false,
                "message" => "No hay transacciones"
            ];
            return $this->jsonResponse($response, 404);
        }
    }

    public function getAllTransactions() {
        $transactions = $this->product->getAllTransactions();
        
        if ($transactions) {
            $response = [
                'succes' => true,
                'data' => $transactions
            ];
            return $this->jsonResponse($response);
        } else {
            $response = [
                'succes' => false,
                "message" => "No hay transacciones"
            ];
            return $this->jsonResponse($response, 404);
        }
    }
}