<?php

header('Content-Type: application/json');

require_once 'livraison.php';


if (
    !isset($_POST['adresse'])
    ||
    empty(trim($_POST['adresse']))
) {

    echo json_encode([
        "success" => false
    ]);

    exit();

}


$resultat = calculFraisLivraison(
    $_POST['adresse']
);


if ($resultat === null) {

    echo json_encode([
        "success" => false
    ]);

    exit();

}


echo json_encode([
    "success" => true,
    "distance" => $resultat['distance'],
    "frais" => $resultat['frais']
]);
?>