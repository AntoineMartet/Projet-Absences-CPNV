let arrayOfRows = document.getElementsByClassName("form-rows");
let validationPageBody = document.getElementById("form-validation");

for(let i=0; i<arrayOfRows.length; i++){
    // Evénement qui change le pointeur et la couleur d'une ligne quand celle-ci est survolée
    arrayOfRows[i].addEventListener("mouseover",(event) => {
        arrayOfRows[i].style.backgroundColor = "#8ADF62";
        validationPageBody.style.cursor = "pointer";
    });

    // Evénement qui remet le pointeur par défaut et la couleur par défaut d'une ligne quand celle-ci arrête d'être survolée
    arrayOfRows[i].addEventListener("mouseout",(event) => {
        arrayOfRows[i].style.backgroundColor = "white";
        validationPageBody.style.cursor = "default";
    });

    // Evénement qui rafraîchit la page avec une nouvelle URL permettant d'utiliser la méthode GET pour avoir l'ID et la
    // classe de la ligne cliquée (et donc l'ID et le type du formulaire) depuis PHP
    arrayOfRows[i].addEventListener("click",(event) => {
        // Récupère l'ID de la ligne cliquée
        var selectedRowID = event.target.parentElement.id;
        // Récupère la 2e classe de la ligne cliquée. Peut valoir "justification-rows" ou "request-rows".
        var selectedRowTypeClass = event.target.parentElement.classList[1];
        window.location.href="form_validation.php?selectedRowID="+selectedRowID+"&selectedRowTypeClass="+selectedRowTypeClass;
    });
}
