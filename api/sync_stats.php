<?php

require_once "../config/database.php";
require_once "../vendor/autoload.php";


try {


    // CONNEXION MongoDB

    $client = new MongoDB\Client("mongodb://localhost:27017");

    $database = $client->vite_gourmand;

    $collection = $database->menu_statistics;



    // RECUPERER STATS MariaDB

    $sql = "
        SELECT 
            menus.id AS menu_id,
            menus.nom AS menu_name,
            COUNT(oders_items.orders_id) AS total_orders,
            SUM(oders_items.prix_unitaire * oders_items.quantite) AS total_revenue

        FROM menus

        LEFT JOIN oders_items 
        ON menus.id = oders_items.menus_id

        GROUP BY menus.id
    ";


    $stmt = $pdo->query($sql);

    $statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);



    // VIDER ANCIENNES STATS

    $collection->deleteMany([]);



    // INSERER NVELLES STATS

    foreach ($statistics as $stat) {


        $collection->insertOne([

            "menu_id" => (int) $stat["menu_id"],

            "menu_name" => $stat["menu_name"],

            "total_orders" => (int) $stat["total_orders"],

            "total_revenue" => (float) $stat["total_revenue"]

        ]);

    }


    echo json_encode([
        "success" => true,
        "message" => "Statistiques mises à jour"
    ]);



} catch (Exception $e) {


    echo json_encode([

        "success" => false,

        "message" => $e->getMessage()

    ]);

}