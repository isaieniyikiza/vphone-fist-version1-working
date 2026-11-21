<?php
require_once "../../db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == "" || $password == "") {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } else {

        // Check if username already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);

        if ($check->rowCount() > 0) {

            $message = "Username already exists.";
            $messageType = "error";

        } else {

            $imageName = "";

            if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {

                $uploadDir = "../../uploads/profile/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));

                $allowed = ["jpg","jpeg","png","gif","webp"];

                if (in_array($extension, $allowed)) {

                    $imageName = uniqid() . "." . $extension;

                    move_uploaded_file(
                        $_FILES["profile_image"]["tmp_name"],
                        $uploadDir . $imageName
                    );

                }

            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare("
                INSERT INTO users
                (username,password,profile_image)
                VALUES
                (?,?,?)
            ");

            if ($insert->execute([$username,$hashedPassword,$imageName])) {

                $message = "User account created successfully.";
                $messageType = "success";

            } else {

                $message = "Failed to create account.";
                $messageType = "error";

            }

        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create User</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Poppins,sans-serif;
}

body{
background:#f4f6fb;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.card{

width:430px;
background:#fff;
padding:30px;
border-radius:20px;
box-shadow:0 15px 40px rgba(0,0,0,.15);

}

h2{

text-align:center;
margin-bottom:20px;

}

.photo{

width:120px;
height:120px;
margin:auto;
border-radius:50%;
overflow:hidden;
border:4px solid #2196F3;
display:flex;
justify-content:center;
align-items:center;
cursor:pointer;
background:#eee;

}

.photo img{

width:100%;
height:100%;
object-fit:cover;
display:none;

}

.photo span{

font-size:45px;
color:#888;

}

input{

width:100%;
padding:15px;
margin-top:18px;
border-radius:10px;
border:1px solid #ccc;
font-size:15px;

}

input[type=file]{

display:none;

}

button{

width:100%;
padding:15px;
margin-top:25px;
border:none;
background:#2196F3;
color:white;
font-size:16px;
border-radius:10px;
cursor:pointer;

}

.success{

background:#d4edda;
padding:12px;
color:green;
border-radius:10px;
margin-bottom:15px;

}

.error{

background:#f8d7da;
padding:12px;
color:red;
border-radius:10px;
margin-bottom:15px;

}

</style>

</head>

<body>

<div class="card">

<h2>Create Virtual Phone Account</h2>

<?php
if($message!=""){
    echo "<div class='$messageType'>$message</div>";
}
?>

<form method="POST" enctype="multipart/form-data">

<label for="profile">

<div class="photo">

<img id="preview">

<span id="plus">+</span>

</div>

</label>

<input
type="file"
name="profile_image"
id="profile"
accept="image/*">

<input
type="text"
name="username"
placeholder="Username"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button type="submit">

Create Account

</button>

</form>

</div>

<script>

const profile=document.getElementById("profile");
const preview=document.getElementById("preview");
const plus=document.getElementById("plus");

profile.onchange=function(){

let file=this.files[0];

if(file){

preview.src=URL.createObjectURL(file);
preview.style.display="block";
plus.style.display="none";

}

}

</script>

</body>
</html>