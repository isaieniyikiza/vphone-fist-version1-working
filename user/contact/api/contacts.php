<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Load database connection
require_once __DIR__ . '/../../../db.php';

// Check if database connected
if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection not found."
    ]);
    exit;
}

// Check login
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized. Please log in."
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {

    switch ($method) {

        // ===========================
        // GET CONTACTS
        // ===========================
        case "GET":

            $stmt = $pdo->prepare("
                SELECT id,name,number,photo
                FROM contacts
                WHERE user_id=?
                ORDER BY name ASC
            ");

            $stmt->execute([$user_id]);

            echo json_encode([
                "success" => true,
                "contacts" => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);

        break;

        // ===========================
        // ADD / UPDATE
        // ===========================
        case "POST":

            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $data = $_POST;
            }

            $action = $data["action"] ?? "";

            // ADD CONTACT
            if ($action == "add") {

                $name = trim($data["name"] ?? "");
                $number = trim($data["number"] ?? "");
                $photo = trim($data["photo"] ?? "");

                if ($name == "" || $number == "") {
                    throw new Exception("Name and number are required.");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO contacts(user_id,name,number,photo)
                    VALUES(?,?,?,?)
                ");

                $stmt->execute([
                    $user_id,
                    $name,
                    $number,
                    $photo
                ]);

                echo json_encode([
                    "success" => true,
                    "message" => "Contact added.",
                    "id" => $pdo->lastInsertId()
                ]);

            }

            // UPDATE CONTACT
            elseif ($action == "update") {

                $id = intval($data["id"] ?? 0);

                if ($id <= 0) {
                    throw new Exception("Invalid contact ID.");
                }

                $name = trim($data["name"] ?? "");
                $number = trim($data["number"] ?? "");
                $photo = trim($data["photo"] ?? "");

                $stmt = $pdo->prepare("
                    UPDATE contacts
                    SET name=?,number=?,photo=?
                    WHERE id=? AND user_id=?
                ");

                $stmt->execute([
                    $name,
                    $number,
                    $photo,
                    $id,
                    $user_id
                ]);

                echo json_encode([
                    "success" => true,
                    "message" => "Contact updated."
                ]);

            }

            else {

                throw new Exception("Invalid action.");

            }

        break;

        // ===========================
        // DELETE CONTACT
        // ===========================
        case "DELETE":

            $id = intval($_GET["id"] ?? 0);

            if ($id <= 0) {
                throw new Exception("Invalid contact ID.");
            }

            $stmt = $pdo->prepare("
                DELETE FROM contacts
                WHERE id=? AND user_id=?
            ");

            $stmt->execute([
                $id,
                $user_id
            ]);

            echo json_encode([
                "success" => true,
                "message" => "Contact deleted."
            ]);

        break;

        default:

            http_response_code(405);

            echo json_encode([
                "success" => false,
                "message" => "Method not allowed."
            ]);

    }

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}