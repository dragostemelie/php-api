<?php
require_once 'jwt/BeforeValidException.php';
require_once 'jwt/ExpiredException.php';
require_once 'jwt/SignatureInvalidException.php';
require_once 'jwt/JWT.php';

use \Firebase\JWT\JWT;
$secret_key = "MY_SECRET_KEY";

class DatabaseService {
    private $db_host = "localhost";
    private $db_name = "test";
    private $db_user = "root";
    private $db_password = "";
    private $connection;

    public function getConnection(){
        $this->connection = null;
        try{
            $this->connection = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user, $this->db_password);
        }catch(PDOException $exception){
            echo "Connection failed: " . $exception->getMessage();
        }
        return $this->connection;
    }
}

function validateToken($token) {  
    global $secret_key;  
    try {
        $decoded = JWT::decode($token, $secret_key, array('HS256'));
        if ($decoded->exp > time()) {
            return true;
        } else {
            return false;
        }    
    } catch (Exception $e) {
        return false;
    }
}

function readHeaderToken() {
    $headers = apache_request_headers();
    if(isset($headers['Authorization'])){
        $header = explode(" ", $headers['Authorization']);
        if ($header[0] === "Bearer") {
            return $header[1];
        }        
    }
    return null;
}

?>
