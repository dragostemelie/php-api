<?php
require_once '../config.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//PREFLIGHT RESPONSE
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
    header("Access-Control-Allow-Origin: * ");
    header("HTTP/1.1 200 OK");
    http_response_code(200);
    exit();    
} 

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->username) || 
    !isset($data->password) || 
    !isset($data->first_name) || 
    !isset($data->last_name) || 
    !isset($data->email) ) 
    {
        http_response_code(400);
        echo json_encode(array("error" => "Bad request."));
        exit();
    }

$database = new DatabaseService();
$conn = $database->getConnection();    

//CHECK IF USERNAME EXISTS   
$query = "SELECT username FROM users WHERE username = :username";
$stmt = $conn->prepare($query);
$stmt->bindParam(':username', $data->username);
if($stmt->execute()) {
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(array("error" => "Username already exists."));
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Bad request."));
    exit();
}

//CHECK IF MAIL EXISTS 
$query = "SELECT email FROM profiles WHERE email = :email";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $data->email);

if($stmt->execute()) {
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(array("error" => "Email already exists."));
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Bad request."));
    exit();
} 
  
//ADD USER
$query = 'CALL ADDUSER(:username, :password, :firstname, :lastname, :email)';
$stmt = $conn->prepare($query);

$password_hash = password_hash($data->password, PASSWORD_BCRYPT);

$stmt->bindParam(':username', $data->username);
$stmt->bindParam(':password', $password_hash);
$stmt->bindParam(':firstname', $data->first_name);
$stmt->bindParam(':lastname', $data->last_name);
$stmt->bindParam(':email', $data->email);

if($stmt->execute()) {
    http_response_code(201);
    echo json_encode(array("message" => "New user registered."));
}
else {
    http_response_code(400);
    echo json_encode(array("error" => "Bad request."));
}
?>