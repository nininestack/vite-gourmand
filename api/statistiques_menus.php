<?php

require_once "../vendor/autoload.php";

header('Content-Type: application/json');


try {

    $client = new MongoDB\Client("mongodb://localhost:27017");

    $database = $client->vite_gourmand;

    $collection = $database->menu_statistics;


    $stats = $collection->find();


    $data = [];


    foreach ($stats as $stat) {

        $data[] = [
            "menu" => $stat["menu_name"],
            "commandes" => $stat["total_orders"]
        ];

    }


    echo json_encode($data);


} catch(Exception $e) {

    echo json_encode([
        "error" => $e->getMessage()
    ]);

}