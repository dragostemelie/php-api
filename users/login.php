<?php
require_once '../config.php';

use \Firebase\JWT\JWT;

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
if (!isset($data->username) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(array("error" => "Bad request."));
    exit();
}

$database = new DatabaseService();
$conn = $database->getConnection();

//CHECK IF USERNAME OR EMAIL EXISTS   
$query = 'SELECT * FROM users U 
    LEFT JOIN profiles P ON P.userID = U.userID 
    WHERE U.username = :username OR P.email = :username';

$stmt = $conn->prepare($query);
$stmt->bindParam(':username', $data->username);

if($stmt->execute()) {
    if ($stmt->rowCount() !== 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($data->password, $row['password'])) {
            //SET TOKEN
            $issuedat = time(); // issued at
            $notbefore_claim = $issuedat + 5; //token valid after 5 secs
            $expire_claim = $issuedat + (7 * 24 * 60 * 60); //NEXT WEEK

            $token = array(
                "iss" => 'THIS_HOST', //required
                "aud" => 'THIS_HOST',
                "iat" => $issuedat,  //required
                "nbf" => $notbefore_claim, //required
                "exp" => $expire_claim,  //required
                "data" => array(
                    "firstname" => $row['firstname'],
                    "lastname" => $row['lastname'],
                    "email" => $row['email']
            ));
            $jwt = JWT::encode($token, $secret_key);

            http_response_code(200);
            echo json_encode(
                array(
                    "message" => "Successful login.",
                    "token" => $jwt,
                    "email" => $row['email'],
                    "expireAt" => date('d-m-Y', $expire_claim)
                ));
            exit();
        }
        else {
            http_response_code(401);
            echo json_encode(array("error" => "Wrong password."));
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(array("error" => "Username not found."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Bad request."));
    exit();
}
?>