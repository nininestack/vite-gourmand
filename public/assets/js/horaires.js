// GENERATION HORAIRES

const dateInput = document.getElementById('date_livraison');
const heureSelect = document.getElementById('heure_livraison');

if(dateInput && heureSelect){

dateInput.addEventListener('change', function(){

    // RESET SI DATE VIDE

    if(!this.value){

     heureSelect.innerHTML =
     '<option value="">Choisir une heure</option>';

    return;
}
      const date = new Date(this.value);

      // CONVERSION JS VERS PHP
      // JS=DIMANCHE=0
      // PHP=DIMANCHE=7

      const jour = date.getDay() === 0 ? 7 : date.getDay();


    // RESET HORAIRES

    heureSelect.innerHTML =
    '<option value="">Choisir une heure</option>';


    // VERIFICATION FERMETURE

    if(joursFermes.includes(jour)){

        alert("Nous sommes fermés ce jour.");

        this.value = "";

        return;
    }

        // SECURITE

        if(!horaires[jour]){

            return;

        }

        // AJOUT HORAIRES

        horaires[jour].forEach(function(heure){


            const option =
            document.createElement("option");
            option.value = heure;
            option.textContent = heure;
            heureSelect.appendChild(option);

        });

    });

}