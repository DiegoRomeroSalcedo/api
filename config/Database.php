<?php

namespace Config;
use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $dsn;
    public $connection;

    public function __construct() {
        $this->dbHost = 'localhost';
        $this->dbName = 'inventario';
        $this->dbUser = 'root';
        $this->dbPass = '';
        $this->dsn = "mysql:host={$this->dbHost};dbName={$this->dbName}";
    }

    // Usaremos el patrón singletón para instanciar la clase, mediante una propiedad de verificacion $instance.
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Database(); // Self hace referencia a la clase para llamar a propiedades o métodos estáticos.
        }

        return self::$instance; // Retornamos la instancia de la clase Database.
    }

    public function getConnection() {
        $this->connection = null;
        // Configuramos las opciones de PDO.
        $options = [
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT            => true,
            PDO::ATTR_CASE                  => PDO::CASE_NATURAL,
        ];

        try {
            $this->connection = new PDO($this->dsn, $this->dbUser, $this->dbPass, $options);
            // echo "Conexión a la base de datos exitosa.";
        } catch(PDOException $e) {
            echo "Error en la conexión a la base de datos: " . $e->getMessage();
        }
        return $this->connection; // Retornamos la conexón a la base de datos.
    }
}