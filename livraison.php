<?php

require_once __DIR__ . '/config.php';


function calculFraisLivraison($adresseClient)
{

    $url =
        "https://maps.googleapis.com/maps/api/distancematrix/json?"
        .
        http_build_query([
            "origins" => ADRESSE_RESTAURANT,
            "destinations" => $adresseClient,
            "units" => "metric",
            "key" => GOOGLE_API_KEY
        ]);


    $response = file_get_contents($url);


    if (!$response) {
        return null;
    }


    $data = json_decode($response, true);



    if (
        !isset($data["status"])
        ||
        $data["status"] !== "OK"
        ||
        !isset($data["rows"][0]["elements"][0])
        ||
        $data["rows"][0]["elements"][0]["status"] !== "OK"
    ) {

        return null;

    }



    $distanceMetres =
        $data["rows"][0]["elements"][0]["distance"]["value"];


    $distanceKm =
        $distanceMetres / 1000;



    // LIVRAISON OFFERTE BORDEAUX
    if (
        stripos($adresseClient, "Bordeaux") !== false
    ) {

        $frais = 0;

    } else {

        $frais =
            5 + ($distanceKm * 0.59);

    }



    return [

        "distance" => round($distanceKm, 2),

        "frais" => round($frais, 2)

    ];

}

?>