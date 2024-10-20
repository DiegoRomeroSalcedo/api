<?php

// Este sera nuestro punto de entrada para las solicitudes.

require __DIR__ . '/../vendor/autoload.php'; // Autoload de composer.

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
Use Proyecto\Controllers\UsersController;
use Proyecto\Controllers\CategoriesController;
use Proyecto\Controllers\ProductsController;
Use Proyecto\Middleware\AuthMiddleware;

// Creamos instancias de los controladores.

$userController = new UsersController();
$productController = new ProductsController();
$categoriesController = new CategoriesController();

// Creamos el despachador de rutas.
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    // Definimos las rutas y los metodos http que manejan.
    $r->addRoute('POST', '/register', 'register');
    $r->addRoute('POST', '/login', 'login');
    $r->addRoute('GET', '/op', 'apiEndpoints'); // Opciones que maneja mi API REST
    $r->addRoute('POST', '/add-categories', 'addCategories');
    $r->addRoute('GET', '/categories', 'getAllCategories');
    $r->addRoute('GET', '/categories/{id}', 'getCategory');
    $r->addRoute('PUT', '/update-category/{id}', 'updateCategory');
    $r->addRoute('DELETE', '/delete-category/{id}', 'deleteCategory');
    $r->addRoute('POST', '/add-product', 'addProduct');
    $r->addRoute('GET', '/products', 'getAllProducts');
    $r->addRoute('GET', '/product/{id}', 'getProduct');
    $r->addRoute('PUT', '/update-product/{id}', 'updateProduct');
    $r->addRoute('DELETE', '/delete-product/{id}', 'deleteProduct');
    $r->addRoute('POST', '/add-transaction/{id}', 'addTransaction');
    $r->addRoute('GET', '/get-transactions/{id}', 'getTransactions');
});

// Formateamos la URI
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api', '', $path);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Despachamos la ruta.
$routeInfo = $dispatcher->dispatch($requestMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // Manejar la ruta no encontrada.
        http_response_code(404);
        echo json_encode(["Error_message" => "Route not found"]);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // Manejar metodo no permitido.
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo json_encode(["Error_message" => "Method not allowed", "allowed" => $allowedMethods]);
        break;
    case FastRoute\Dispatcher::FOUND:
        // Llamamos al función correspondiente.
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        switch ($handler) {
            case 'register':
                $data = json_decode(file_get_contents('php://input'), true);
                echo $userController->register($data);
                break;
            case 'login':
                $data = json_decode(file_get_contents('php://input'), true);
                echo $userController->login($data);
                break;
            case 'apiEndpoints':
                AuthMiddleware::protegerEndpoint($requestMethod);
                // Devuelve la lista de endpoints
                $response = [
                    'success' => true,
                    'endpoints' => getApiEndpoints(), // Función para obtener los endpoints disponibles de la api
                ];
                header('Content-Type: application/json');
                echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                break;
            case 'addCategories':
                AuthMiddleware::protegerEndpoint($requestMethod);
                $data = json_decode(file_get_contents('php://input'), true);
                echo $categoriesController->addCategory($data);
                break;
            case 'getAllCategories':
                AuthMiddleware::protegerEndpoint($requestMethod);
                echo $categoriesController->getAllCategories();
                break;
            case 'getCategory':
                AuthMiddleware::protegerEndpoint($requestMethod);
                echo $categoriesController->getIdOrName($vars['id']);
                break;
            case 'updateCategory':
                AuthMiddleware::protegerEndpoint($requestMethod);
                $data = json_decode(file_get_contents('php://input'), true);
                echo $categoriesController->updateCategory($vars['id'], $data);
                break;
            case 'deleteCategory':
                AuthMiddleware::protegerEndpoint($requestMethod);
                echo $categoriesController->deleteCategory($vars['id']);
                break;
            case 'addProduct':
                AuthMiddleware::protegerEndpoint($requestMethod);
                $data = json_decode(file_get_contents('php://input'), true);
                echo $productController->addProduct($data);
                break;
            case 'getAllProducts':
                AuthMiddleware::protegerEndpoint($requestMethod);
                echo $productController->getAllProducts();
                break;
            case 'getProduct':
                AuthMiddleware::protegerEndpoint($requestMethod);
                echo $productController->getIdOrName($vars['id']);
                break;
            case 'updateProduct':
                AuthMiddleware::protegerEndpoint($requestMethod);
                $data = json_decode(file_get_contents('php://input'), true);
                echo $productController->updateProduct($vars['id'], $data);
                break;
            case 'deleteProduct':
                AuthMiddleware::protegerEndpoint($requestMethod);
                echo $productController->deleteProduct($vars['id']);
                break;
            // case 'addTransaction':
            //     AuthMiddleware::protegerEndpoint($requestMethod);
            //     $data = json_decode(file_get_contents('php://input'), true);
            //     $productoId = $vars['id'];
            //     echo $productController->registerTransaction($productoId, $data);
            //     break;
            // case 'getTransactions':
            //     AuthMiddleware::protegerEndpoint($requestMethod);
            //     echo $productController->getTransaction($vars['id']);
            //     break;
        }
        break;
}

/**
 * Función getApiEndpoints para obtener los endpoints de la API.
 * @return array
 */

function getApiEndpoints() {
    return [
        [
            'name' => 'Registrar usuario',
            'url' => 'http://localhost/api/register',
            'method' => 'POST',
            'description' => 'Endpoint para registrar usuarios.'
        ],
        [
            'name' => 'Login de usuario',
            'url' => 'http://localhost/api/login',
            'method' => 'POST',
            'description' => 'Endpoint para logear usuarios.'
        ],
        [
            'name' => 'Productos',
            'url' => 'http://localhost/api/productos',
            'method' => 'GET',
            'description' => 'Endpoint para obtener la data relacionada con los productos'
        ],
        [
            'name' => 'Categories',
            'url' => 'http://localhost/api/categorias',
            'method' => 'GET',
            'description' => 'Endpoint para obtener la data relacionada con las categorías.'
        ],
        [
            'name' => 'Transactions',
            'url' => 'http://localhost/api/get-transactions',
            'method' => 'GET',
            'description' => 'Endpoint para obtener la data relacionada con las transacciónes de un producto.'
        ],
        [
            'name' => 'Add category',
            'url' => 'http://localhost/api/add-categories',
            'method' => 'POST',
            'description' => 'Endpoint para insertar la data correspondiente a las categorías.'
        ],
        [
            'name' => 'Add product',
            'url' => 'http://localhost/api/add-product',
            'method' => 'POST',
            'description' => 'Endpoint para insertar la data correspondiente a los productos.'
        ],
        [
            'name' => 'Add transaction',
            'url' => 'http://localhost/api/add-transaction/{id}',
            'method' => 'POST',
            'description' => 'Endpoint para insertar la data correspondiente a las transacciones disponibles los productos.'
        ],
        [
            'name' => 'Update category',
            'url' => 'http://localhost/api/update-category/{id}',
            'method' => 'PUT',
            'description' => 'Endpoint para actualizar las categorias.'
        ],
        [
            'name' => 'Update product',
            'url' => 'http://localhost/api/update-product/{id}',
            'method' => 'PUT',
            'description' => 'Endpoint para actualizar los productos.'
        ],
        [
            'name' => 'Delete category',
            'url' => 'http://localhost/api/delete-category/{id}',
            'method' => 'DELETE',
            'description' => 'Endpoint para eliminar una categoría.'
        ],
        [
            'name' => 'Delete product',
            'url' => 'http://localhost/api/delete-product/{id}',
            'method' => 'DELETE',
            'description' => 'Endpoint para eliminar un producto.'
        ],
    ];
}