<?php
require_once '../config.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
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

//VALIDATE TOKEN
$token = readHeaderToken();
if ($token === null) {
    http_response_code(401);
    echo json_encode(array("error" => "Unauthorised."));
    exit();
} else {
    if (!validateToken($token)) {
        http_response_code(401);
        echo json_encode(array("error"=> "Invalid token"));
        exit();
    }
}

$database = new DatabaseService();
$conn = $database->getConnection();

//GET USERS LIST   
$query = 'SELECT U.username, P.firstname, P.lastname, P.avatar FROM users U LEFT JOIN profiles P ON P.userID = U.userID';

$stmt = $conn->prepare($query);

if($stmt->execute()) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode($results);
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Bad request."));
    exit();
}
?>