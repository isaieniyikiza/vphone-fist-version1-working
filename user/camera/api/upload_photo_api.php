<?php

$message = "";
$user_photo = "";

$base_folder = "../../../storage/uploads/";


if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $user_id = $_POST['user_id'];


    $user_folder = $base_folder . $user_id . "/";


    // UPLOAD PHOTO
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){


        // create folder if it does not exist

        if(!is_dir($user_folder)){

            mkdir($user_folder,0777,true);

        }


        $file_name = $_FILES['photo']['name'];

        $file_tmp = $_FILES['photo']['tmp_name'];


        $destination = $user_folder . $file_name;



        if(move_uploaded_file($file_tmp,$destination)){


            $message = "Photo uploaded successfully";


        }else{


            $message = "Upload failed";


        }


    }



    // RETRIEVE USER PHOTO

    if(is_dir($user_folder)){


        $files = scandir($user_folder);


        foreach($files as $file){


            if($file != "." && $file != ".."){


                $user_photo = $user_folder . $file;

                break;


            }

        }


        if($user_photo == ""){


            $message = "No photo found for this user";


        }


    }else{


        $message = "User folder does not exist";


    }



}



?>


<!DOCTYPE html>
<html>

<head>

<title>User Photo System</title>


<style>

body{

font-family:Arial;
background:#f2f2f2;

}


.container{

width:400px;
margin:50px auto;
background:white;
padding:20px;
border-radius:10px;

}


input{

width:100%;
padding:10px;
margin:10px 0;

}


button{

width:100%;
padding:12px;
background:#007bff;
color:white;
border:none;
cursor:pointer;

}


img{

width:200px;
height:200px;
object-fit:cover;
border-radius:50%;
display:block;
margin:20px auto;

}


.message{

text-align:center;
color:green;

}

</style>


</head>


<body>


<div class="container">


<h2>
User Profile Photo
</h2>


<p class="message">
<?php echo $message; ?>
</p>



<form method="POST" enctype="multipart/form-data">


<label>
User ID
</label>


<input 
type="text"
name="user_id"
placeholder="Example: 1001"
required
>


<label>
Upload Photo
</label>


<input 
type="file"
name="photo"
accept="image/*"
>


<button>
Save / Find Photo
</button>


</form>



<?php if($user_photo != ""): ?>


<h3>
User Photo
</h3>


<img src="<?php echo $user_photo; ?>">


<?php endif; ?>


</div>


</body>

</html>