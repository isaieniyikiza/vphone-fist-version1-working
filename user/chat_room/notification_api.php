<?php
header("Content-Type: application/json");

$csvFile = __DIR__ . "/notifications.csv";


// Create CSV if not exists
if (!file_exists($csvFile)) {

    file_put_contents(
        $csvFile,
        "id,time,user_id,sender_id,message\n"
    );

}


// GET ALL NOTIFICATIONS
if ($_SERVER["REQUEST_METHOD"] === "GET") {


    $notifications = [];


    if (($handle = fopen($csvFile, "r")) !== false) {


        $header = fgetcsv($handle);


        while (($row = fgetcsv($handle)) !== false) {


            $notifications[] = [

                "id"=>$row[0],
                "time"=>$row[1],
                "user_id"=>$row[2],
                "sender_id"=>$row[3],
                "message"=>$row[4]

            ];

        }


        fclose($handle);

    }


    echo json_encode([
        "success"=>true,
        "notifications"=>$notifications
    ]);


    exit;

}





// SAVE NEW NOTIFICATION

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $data=json_decode(
        file_get_contents("php://input"),
        true
    );


    if(
        !isset($data["user_id"]) ||
        !isset($data["sender_id"]) ||
        !isset($data["message"])
    ){

        echo json_encode([
            "success"=>false,
            "message"=>"Missing data"
        ]);

        exit;

    }



    $id=time();

    $time=date(
        "Y-m-d H:i:s"
    );


    $line=[
        $id,
        $time,
        $data["user_id"],
        $data["sender_id"],
        str_replace(
            ",",
            " ",
            $data["message"]
        )
    ];



    $file=fopen(
        $csvFile,
        "a"
    );


    fputcsv(
        $file,
        $line
    );


    fclose($file);



    echo json_encode([
        "success"=>true
    ]);


    exit;

}





// CLEAR ALL NOTIFICATIONS

if($_SERVER["REQUEST_METHOD"]==="DELETE"){



    file_put_contents(
        $csvFile,
        "id,time,user_id,sender_id,message\n"
    );


    echo json_encode([
        "success"=>true,
        "message"=>"Notifications cleared"
    ]);


    exit;

}

?>