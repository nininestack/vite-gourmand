<?php

require_once "database.php";
require_once "mongodb.php";


try {

    // REQUETE MariaDB
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



    // NETTOYAGE COLLECTION MongoDB
    $collection->deleteMany([]);



    // ENVOI DANS MongoDB
    foreach ($statistics as $stat) {

        $collection->insertOne([
            "menu_id" => (int)$stat["menu_id"],
            "menu_name" => $stat["menu_name"],
            "total_orders" => (int)$stat["total_orders"],
            "total_revenue" => (float)$stat["total_revenue"]
        ]);

    }


    echo "Synchronisation MongoDB réussie";


} catch(Exception $e) {

    echo "Erreur : " . $e->getMessage();

}