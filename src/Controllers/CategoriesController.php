<?php

namespace Proyecto\Controllers;

use Exception;
use Proyecto\Middleware\AuthMiddleware;
use Proyecto\Models\Categories;

class CategoriesController extends BaseController {

    private $categories;

    public function __construct() {
        $this->categories = new Categories(); // Recuperamos la instancia del modelo categorias.
    }
    // Implementación del controlador para las categorías.
    public function addCategory($data) {

        try {
            // Traemos el id del usuario para la inserción.
            $user = AuthMiddleware::$autheticatedUser;
            $userId = (int) $user->sub;
            ['nom_categori' => $nom_categori] = $data;
            $nomCategory = trim($nom_categori);
            $patternName = '/^[a-zA-Z0-9\s]+$/'; // El formato de nombre puede incluir: Letras (a-zA-Z), números (0-9) y espacios entre el texto
            if (!preg_match($patternName, $nomCategory)) {
                $response = [
                    'success' => false,
                    'error_message' => "El formato de nombre puede incluir: Letras (a-zA-Z), números (0-9) y espacios entre el texto"
                ];
                return $this->jsonResponse($response, 400);
            }

            $category = $this->categories->createCategory($nomCategory, $userId);

            if ($category) {
                $response = [
                    'success' => true,
                    "success_message" => "Se inserto con éxito la categoría con ID {$category}"
                ];
                return $this->jsonResponse($response, 201);
            }
        } catch(Exception $e) {
            // En caso de error devolvemos el error.
            $response = [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
            return $this->jsonResponse($response, 500);
        }
    }

    // Método para obtener las categorias de la DB.
    public function getAllCategories() {

        // Obtenemos los datos mediante el modelo.
        $data = $this->categories->getAll();
        if ($data) {
            $response = [
                'success' => true,
                'data' => $data
            ];

            return $this->jsonResponse($response);
        } else {
            $response = [
                'success' => false,
                'message' => "No hay categorías."
            ];

            return $this->jsonResponse($response, 404);
        }
    }

    // Método para obtener una categoría en especifico.
    public function getIdOrName($input) {

        $formattedInput = preg_replace("/-/", ' ', $input);
        $data = $this->categories->getCategory($formattedInput);
        if ($data) {
            $response = [
                'success' => true,
                'data' => $data
            ];
            return $this->jsonResponse($response);
        } else {
            $response = [
                'success' => false,
                'message' => "Categoría no encontrada."
            ];
            return $this->jsonResponse($response, 404);
        }
    }

    public function updateCategory($idCategory, $data) {
        try {
            $user = AuthMiddleware::$autheticatedUser;
            $userId = (int) $user->sub;
            ['nom_categori' => $nomCategory] = $data;
            $nomCategory = trim($nomCategory);
            $patternName = "/^[a-zA-Z0-9\s]+$/";

            if (!preg_match($patternName, $nomCategory)) {
                $response = [
                    'success' => false,
                    "error_message" => "El formato de nombre puede incluir: Letras (a-zA-Z), números (0-9) y espacios entre el texto"
                ];
                return $this->jsonResponse($response, 400);
            }

            $category = $this->categories->updateCategory($idCategory, $nomCategory, $userId);

            if ($category) {
                $response = [
                    'success' => true,
                    "success_message" => "Categoría actualizada exitosamente"
                ];
                return $this->jsonResponse($response, 200);
            } else {
                $response = [
                    'success' => false,
                    "message" => "Categoría no encontrada"
                ];
                return $this->jsonResponse($response, 404);
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                "error_message" => $e->getMessage()
            ];
            return $this->jsonResponse($response, 500);
        }
    }

    public function deleteCategory($idCategory) {
        try {
            $category = $this->categories->deleteCategory($idCategory);

            if ($category) {
                $response = [
                    'success' => true,
                    "success_message" => "La categoría con ID: {$idCategory} se eliminó exitosamente"
                ];
                return $this->jsonResponse($response);
            } else {
                $response = [
                    'success' => false,
                    "message" => "Categoría no encontrada"
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
}