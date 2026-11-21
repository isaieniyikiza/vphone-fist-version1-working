<?php
header("Content-Type: application/json");
require_once "../../db.php";


if (!isset($_GET["id"])) {
    echo json_encode(["success"=>false,"message"=>"Missing ID"]);
    exit;
}

$id = $_GET["id"];

$stmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    echo json_encode(["success"=>false]);
    exit;
}

echo json_encode([
    "success"=>true,
    "user"=>$user
]);