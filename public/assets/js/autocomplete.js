document.addEventListener("DOMContentLoaded", function () {
  const input = document.getElementById("adresse_livraison");

  if (!input) {
    return;
  }

  const autocomplete = new google.maps.places.Autocomplete(input, {
    componentRestrictions: {
      country: "fr",
    },
    fields: ["formatted_address", "address_components"],
  });

  autocomplete.addListener("place_changed", function () {
    const place = autocomplete.getPlace();

    if (!place.formatted_address) {
      return;
    }

    input.value = place.formatted_address;

    // APPEL PHP POUR CALCULER LIVRAISON

    fetch("calcul_livraison.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },

      body: "adresse=" + encodeURIComponent(place.formatted_address),
    })
      .then((response) => response.json())

      .then((data) => {
        if (data.success) {
          document.getElementById("distance_livraison").textContent =
            data.distance + " km";

          document.getElementById("frais_livraison").textContent =
            data.frais.toFixed(2) + " €";

          const total = parseFloat(
            document.getElementById("total_base").dataset.total,
          );

          const remise = parseFloat(
            document.getElementById("total_remise").dataset.remise,
          );

          document.getElementById("total_final").textContent =
            (total - remise + data.frais).toFixed(2) + " €";
        } else {
          console.error("Impossible de calculer la livraison");
        }
      })

      .catch((error) => {
        console.error("Erreur API livraison :", error);
      });
  });
});
