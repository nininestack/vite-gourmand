const adresse = document.getElementById("adresse_livraison");


function calculLivraison() {

    fetch(
        "calcul_livraison.php?adresse="
        +
        encodeURIComponent(adresse.value)
    )

    .then(response => response.json())

    .then(data => {


        if(data.frais !== undefined){


            document.getElementById("prix_livraison")
            .textContent =
            data.frais.toFixed(2) + " €";


            const total =
            totalCommande
            -
            remiseCommande
            +
            data.frais;


            document.getElementById("total_final")
            .textContent =
            total.toFixed(2) + " €";

        }

    });

}


window.addEventListener(
    "load",
    calculLivraison
);


adresse.addEventListener(
    "change",
    calculLivraison
);