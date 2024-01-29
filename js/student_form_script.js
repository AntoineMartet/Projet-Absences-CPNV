/*
-----------------------------------------------------------------------------------
Nom du fichier : student_form_script.js
Auteur(s) : Maxime Borgeaud
Date de création : ???
Dernière modification : 09.06.2023 par Antoine Martet (ajout de commentaires)
Description : script lié à la page web student_form.php.
-----------------------------------------------------------------------------------
*/

let inputLeaveRequest = document.querySelector('#leave-request-bt'); // Bouton "Demande de congé"
let inputProofAbsence = document.querySelector('#proof-absence-bt'); // Bouton "Justification d'absence"
let other = document.getElementById('other'); // Bouton radio "Autres"
let convocationM = document.getElementById('convocation-m'); // Message demandant la convocation
let attestationM = document.getElementById('attestation-m'); // Message demandant l'attestation
let certificatM = document.getElementById('certificat-m'); // Message demandant le certificat médical
let startDate = $('#start-date'); // Référence à l'input de type date qui sert à choisir la date de départ
let endDate = $('#end-date'); // Référence à l'input de type date qui sert à choisir la date de fin
let selectedForm = new URLSearchParams(window.location.search).get('selectedForm');

window.onload = (event) => {
    dateVerif();
};
// Evénement clic sur le bouton "Demande de congé".
inputLeaveRequest.addEventListener('click',(event)=>{
    selectedForm = "leave-request";
    detectionFormSelected();
});
// Evénement clic sur le bouton "Justification d'absence"
inputProofAbsence.addEventListener('click', (event)=>{
    selectedForm = "proof-absence";
    detectionFormSelected();
});
// Ajout dans l'URL du type de formulaire selectionné : justification d'absence (proof-absence) ou demande de congé (leave-request)
function detectionFormSelected(){
    window.location.href="student_form.php?selectedForm="+selectedForm;
}
// Evénement déclenché à chaque changement de l'input fixant la date de départ
startDate.on('change', function() {
    dateVerif();
});
// Vérifie que les dates sélectionnables sont cohérentes entre elles et avec le contexte
function dateVerif(){
    // Création d'un format de date spécifique pour modifier les input de type date
    let actDate = Date.now();
    let actualYear = new Date(actDate).getFullYear();
    let actualMonth = (new Date(actDate).getMonth()+1).toString().padStart(2, "0");
    let actualDay = new Date(actDate).getDate().toString().padStart(2, "0");
    let actualDate = `${actualYear}-${actualMonth}-${actualDay}`;

    /* Si aucune date de début sélectionnée, interdiction de sélectionner une date de fin. Si date de début sélectionnée,
     autorisation de sélectionner une date de fin, mais seulement une date ultérieure à la date de début. */
    if(startDate.val() == ""){
        endDate.prop('disabled', true);
    } else {
        endDate.prop('disabled', false);
    }

    // Pour les demandes de congé, interdiction de sélectionner une date de départ dans le passé
    if(selectedForm == "leave-request"){
        startDate.prop('min', actualDate);
        endDate.prop('min', startDate.val());
    }if(selectedForm == null || selectedForm == "proof-absence"){
        startDate.prop('max', actualDate);
        endDate.prop('min', startDate.val());
        endDate.prop('max', actualDate);
    }
}
// Evénement déclenché à chaque changement de motif sélectionné.
$('input[name=Groupe-Motifs_radio]').on('change', function() {
    // Recupère la valeur du bouton radio selectionné parmi les motifs
    var annex = $('input[name=Groupe-Motifs_radio]:checked').val();
    console.log(annex);
    if (selectedForm == 'leave-request') {
        switch (annex) {
            // Si bouton radio "Autres" checked, apparition du champ de type texte
            case "Autres":
                convocationM.style.display = "none";
                attestationM.style.display = "none";
                certificatM.style.display = "none";
                other.style.display = "block";
                break;
            // Si bouton radio "Affaire militaires" checked, apparition du message demandant la convocation
            case ("Affaires-militaires"||"Permis-de-conduire"):
                convocationM.style.display = "block";
                certificatM.style.display = "none";
                attestationM.style.display = "none";
                other.style.display = "none";
                break;
            // Si bouton radio "Convocation officielle" checked, apparition du message demandant l'attestation
            case "Convocation-officielle":
                attestationM.style.display = "block";
                certificatM.style.display = "none";
                convocationM.style.display = "none";
                other.style.display = "none";
                break;
            // Par défaut, aucun message affiché et champ pour "Autres" caché
            default:
                certificatM.style.display = "none";
                attestationM.style.display = "none";
                convocationM.style.display = "none";
                other.style.display = "none";
                break;
        }
    } else if(selectedForm == null || selectedForm == 'proof-absence'){
        switch (annex){
            // Si bouton radio "Autres" checked, apparition du champ de type texte
            case "Autres":
                convocationM.style.display = "none";
                attestationM.style.display = "none";
                certificatM.style.display = "none";
                other.style.display = "block";
                break;
            // Si boutons radio "Affaire militaires" ou "Permis de conduire" checked, apparition du message demandant la convocation
            case ("Affaires-militaires"||"Permis-de-conduire"):
                convocationM.style.display = "block";
                certificatM.style.display = "none";
                attestationM.style.display = "none";
                other.style.display = "none";
                break;
            // Si bouton radio "Convocation officielle" checked, apparition du message demandant la convocation
            case "Convocation-officielle":
                attestationM.style.display = "block";
                certificatM.style.display = "none";
                convocationM.style.display = "none";
                other.style.display = "none";
                break;
            // Si bouton radio "Maladie / accident sans certificat médical" checked, apparition du message demandant le certificat médical
            case "Maladie-accident-avec-certificat":
                certificatM.style.display = "block";
                attestationM.style.display = "none";
                convocationM.style.display = "none";
                other.style.display = "none";
                break;
            // Par défaut, aucun message affiché et champ pour "Autres" caché
            default:
                certificatM.style.display = "none";
                attestationM.style.display = "none";
                convocationM.style.display = "none";
                other.style.display = "none";
                break;
        }
    }
});
