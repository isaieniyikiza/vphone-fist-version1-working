<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json');

require_once "../../db.php";


if (!isset($pdo)) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);

    exit;
}


// Check if user is logged in
if (!isset($_SESSION["user_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "No user is logged in."
    ]);

    exit;
}


$user_id = $_SESSION["user_id"];


try {


    $stmt = $pdo->prepare("
        SELECT 
            id,
            username,
            profile_image,
            created_at,
            is_blocked
        FROM users
        WHERE id = ?
        LIMIT 1
    ");


    $stmt->execute([$user_id]);


    $user = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!$user) {

        session_destroy();

        echo json_encode([
            "success" => false,
            "message" => "User account not found."
        ]);

        exit;
    }

    if ((int)($user['is_blocked'] ?? 0) === 1) {
        $_SESSION = [];
        session_destroy();
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "logged_in" => false,
            "message" => "This account has been blocked. Contact the administrator."
        ]);
        exit;
    }

    $pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$user_id]);



    // Fix profile image path
    $profile = "";

    if (!empty($user["profile_image"])) {

        if (
            preg_match('#^https?://#', $user["profile_image"])
            ||
            str_starts_with($user["profile_image"], "/")
        ) {

            $profile = $user["profile_image"];

        } else {

            $profile = "/virtual-phone/uploads/profile/" . $user["profile_image"];

        }
    }



    echo json_encode([

        "success" => true,

        "logged_in" => true,

        "user" => [

            "id" => $user["id"],

            "username" => $user["username"],

            "profile_image" => $profile,

            "created_at" => $user["created_at"]

        ]

    ]);



} catch(PDOException $e){


    http_response_code(500);


    echo json_encode([

        "success" => false,

        "message" => "Database error."

    ]);

}