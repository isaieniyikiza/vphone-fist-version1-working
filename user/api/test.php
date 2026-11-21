<?php

require_once "../../db.php";

try {

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();

    echo "<h2 style='color:green;'>✅ PDO Connected Successfully</h2>";
    echo "Users in database: <b>$count</b>";

} catch (PDOException $e) {

    echo "<h2 style='color:red;'>❌ Error</h2>";
    echo $e->getMessage();
}