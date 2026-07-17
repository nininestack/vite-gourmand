document.addEventListener("DOMContentLoaded", () => {
  console.log("admin.js connecté !");

  // RECHERCHE COMMANDES

  const searchOrder = document.getElementById("search-order");

  if (searchOrder) {
    searchOrder.addEventListener("input", (e) => {
      let value = e.target.value.toLowerCase();

      document.querySelectorAll("#commandes tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(value)
          ? ""
          : "none";
      });
    });
  }
});

// CONFIRMATION ANNULATION COMMANDE

function confirmStatus(select) {
  if (select.value === "annulee") {
    if (confirm("Voulez-vous vraiment annuler cette commande ?")) {
      select.form.submit();
    } else {
      select.selectedIndex = 0;
    }
  } else {
    select.form.submit();
  }
}
