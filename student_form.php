<!--
-----------------------------------------------------------------------------------
Nom du fichier : student_form.php
Auteur(s) : Maxime Borgeaud, Maikol Correia Da Silva
Date de création : 27.04.2023
Dernière modification : 09.06.2023 par Antoine Martet (ajout de commentaires)
Description : Page web permettant à un élève connecté de remplir un formulaire de justification ou un formulaire de
              de demande de congé. Tout les champs pré-remplissables grâce à la BDD et au nom de l'élève connecté sont
              pré-remplis. Valider le formulaire envoie les infos à la BDD et renvoie l'utilisateur vers la page form_result.php.
-----------------------------------------------------------------------------------
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Students page</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
</head>
<body id="student-form">
    <?php
    //phpinfo();
    session_start();
    // Connexion à la base de données
    include 'connection_mysql.php';

    // SQL pour récupération de la tables des personnes
    $personRequest = $conn->query('SELECT * FROM person');

    // Boucle qui parcours chaque personne de la BDD
    while ($data = $personRequest->fetch())
    {
    // Création du mail à partir du nom et prénom de la personne de la BDD
    $mail = $data['personFirstName'].".".$data['personLastName']."@cpnv.ch";

    if (isset($_SESSION["mail"])){
        // Comparaison du mail de la personne connectée avec le mail de la personne de la BDD
        if ($_SESSION["mail"] == $mail){
            $_SESSION["lastName"] = $data['personLastName'];
            $_SESSION["firstName"] = $data['personFirstName'];
            $_SESSION["idPerson"] = $data['idPerson'];

            // Requêtes SQL pour retrouver le maître de classe de la personne connectée
            $belongRequest = $conn->query('SELECT fkFlock FROM belongs where isValid = "1" AND fkPerson = "'.$_SESSION["idPerson"].'"');
            $fkFlock = $belongRequest->fetch();

            $flockRequest = $conn->query('SELECT * FROM flock where idFlock = "'.$fkFlock[0].'"');
            $flock = $flockRequest->fetch();

            $flockTypeRequest = $conn->query('SELECT flockTypeShort FROM flocktype where idFlockType = "'.$flock['fkFlockType'].'"');
            $flockTypeShort = $flockTypeRequest->fetch();

            $teacherRequest = $conn->query('SELECT * FROM person where idPerson = "'.$flock['fkMC'].'"');
            $teacher = $teacherRequest->fetch();

            $_SESSION["teacherName"] = $teacher['personLastName']." ".$teacher['personFirstName'];

            // Récupération de la classe actuelle de la personne
            if((date("m") - 7) < 0){
                $tempYear = 0;
            }
            else{
                $tempYear = 1;
            }
            $schoolYear = date("Y") - $flock['startYear'] + $tempYear;

            $_SESSION["class"] = "SI-".$flockTypeShort[0].strval($schoolYear).$flock['rank'];

            break;
        }
    }
    }
    ?>
    <?php
    // Recupère le type de formulaire selectionné
    if(isset($_GET["selectedForm"])){
        $_SESSION["selectedForm"] = $_GET["selectedForm"];
    }
    else if (!isset($_SESSION["selectedForm"])){ // type par défaut
        $_SESSION["selectedForm"] = "proof-absence";
    }
        // Gestion de l'apparence des onglets de choix de formulaire
        $lrBackGround = ($_SESSION["selectedForm"] == 'proof-absence')? 'lightgray':'green';
        $lrColor = ($_SESSION["selectedForm"] == 'proof-absence')? 'white':'white';
        $paBackGround = ($_SESSION["selectedForm"] == 'proof-absence')? 'green':'lightgray';
        $paColor = ($_SESSION["selectedForm"] == 'proof-absence')? 'white':'white';
        $txtLr = ($_SESSION["selectedForm"] == 'proof-absence')? 'none':'block';
        $txtPa = ($_SESSION["selectedForm"] == 'proof-absence')? 'block':'none';
        echo '<div id="page-choice">
                    <input type="button" value="Justification d\'absence" id="proof-absence-bt" style=" background-color:'.$paBackGround.'; color:'.$paColor.';">
                    <input type="button" value="Demande de congé" id="leave-request-bt" style=" background-color:'.$lrBackGround.'; color:'.$lrColor.';">
             </div>';
    ?>

    <div id="message_admin">
        <?php
        echo '
            <p id="txt-LR" style="display: '.$txtLr.'">Les demandes de congés doivent êtres transmises au minimum deux semaines à l\'avance.</p> <!-- text pour demande de congé s-->
            <p id="txt-PA" style="display: '.$txtPa.'">Les justifications d\'absences doivent parvenir dans la semaine de la reprise des cours.
                <br>Toute absence non justifiée sera passible d\'une sanction.</p>';
        ?>
    </div>

    <div class="form">
        <form method="post" action="form_result.php">
            <h2>Informations personnelles :</h2>
            <!-- Champs de formulaires cachés (nécessaires car certains champs sont pré-remplis) -->
            <div id="hidden-fields">
                <input type="text" name="selected-form" value="<?=$_SESSION["selectedForm"]?>">
                <input type="checkbox" name="EMSC_checkbox" checked>
                <input type="text" name="Nom_textbox" value="<?=$_SESSION["lastName"]?>">
                <input type="text" name="Prenom_textbox" value="<?=$_SESSION["firstName"]?>">
                <input type="text" name="Classe_textbox" value="<?=$_SESSION["class"]?>">
                <input type="text" name="Maitre-de-classe_textbox" value="<?=$_SESSION["teacherName"]?>">
            </div>
            <!-- Champs de formulaires visibles -->
            <div id="personnal-case">
                <div class="col">
                    <p>Nom : <?=$_SESSION["lastName"]?></p>
                    <p>Prénom : <?=$_SESSION["firstName"]?></p>
                    <p>Entreprise formatrice : <input type="text" name="Entreprise-formatrice_textbox"></p>
                </div>
                <div class="col">
                    <p>Établissement : EMSC</p>
                    <p>Classe : <?=$_SESSION["class"]?></p>
                    <p>Maître-sse de classe : <?=$_SESSION["teacherName"]?></p>
                </div>
            </div>
            <h2>Dates :</h2>
            <div class="input-date">
                <p>Absence du <input type="date" id="start-date" name="Date-debut_textbox" required> à <input type="time" id="start-time" name="Heure-debut_textbox" required>
                au <input type="date" id="end-date" name="Date-fin_textbox" required> à <input type="time" name="Heure-fin_textbox" required></p>
            </div>
            <div id="test_absence">
                <?php echo '<div id="test-abs">
                    <!-- Champ caché pour les demandes de congé mais visible pour les justifications d absence -->
                    <label for="check-test-absence" style="display:'.$txtPa. '">J\'ai manqué un test durant cette absence dans les branches :</label>
                    <input type="text" name="Test-manque_textbox" id="text-absence_test" style="display:' .$txtPa.'">
                </div>';?>
            </div>
            <h2>Motif :</h2>
            <?php
            // SQL de récupération des motifs pour justifications d'absence
            $justificationsReasons = $conn->query('SELECT * FROM justifications_reasons');
            // SQL de récupération des motifs pour demandes de congé
            $requestsReasons = $conn->query('SELECT * FROM requests_reasons');
            switch ($_SESSION["selectedForm"]){
                case 'proof-absence':
                    // Récupération des données des motifs pour justifications + affichage HTML des motifs
                    while($reasonData = $justificationsReasons->fetch()){
                        $name = $reasonData["name"];
                        $pdfFieldName = $reasonData['pdfFieldName'];
                        $id = $reasonData["idReasons"];
                        displayReason($name, $pdfFieldName);
                    }
                    break;
                case 'leave-request':
                    // Récupération des données des motifs pour demandes de congés + affichage HTML des motifs
                    while($reasonData = $requestsReasons->fetch()){
                        $name = $reasonData["name"];
                        $pdfFieldName = $reasonData['pdfFieldName'];
                        $id = $reasonData["idReasons"];
                        displayReason($name, $pdfFieldName);
                    }
                    break;
                }

            // Fonction d'affichage HTML des motifs
            function displayReason($nameReas, $pdfNameReas){
                echo '<div>
                            <input type="radio" name="Groupe-Motifs_radio" id="'.$pdfNameReas.'" value="'.$pdfNameReas.'">
                            <label for="'.$pdfNameReas.'">'.$nameReas.'</label>
                      </div>';
            }
            ?>
            <!-- La div other n'apparaît que si le bouton "Autres" est sélectionné (cf. student_form_script.js) -->
            <div id="other">
                <br><p>Précisez votre motif :
                <input type="text" name="Autres-texte_textbox" id="input-other"></p>
            </div>
            <div id="annex-message">
                <p id="convocation-m">Une copie de la convocation est demandée*</p>
                <p id="attestation-m">Une attestation est demandée*</p>
                <p id="certificat-m">Un certificat médical est demandé*</p>
            </div>
            <h2>Annexes :</h2>
            <div id="annexes">
                <?php
                    // SQL de récupération des annexes pour justifications d'absence
                    $justificationsAnnex = $conn->query('SELECT * FROM justifications_annex');
                    // SQL de récupération des annexes pour demandes de congé
                    $requestsAnnex = $conn->query('SELECT * FROM requests_annex');
                    switch ($_SESSION["selectedForm"]) {
                        case 'proof-absence':
                            // Récupération des données des annexes pour justifications + affichage HTML des annexes
                            while ($justifAnnex = $justificationsAnnex->fetch()) {
                                $annexName = $justifAnnex["name"];
                                $annexPdfName = $justifAnnex["pdfFieldName"];
                                $annexId = $justifAnnex["idAnnex"];
                                displayAnnex($annexName, $annexPdfName);
                            }
                            break;
                        case 'leave-request':
                            // Récupération des données des annexes pour demandes de congé + affichage HTML des annexes
                            while ($reqAnnex = $requestsAnnex->fetch()) {
                                $annexName = $reqAnnex['name'];
                                $annexPdfName = $reqAnnex["pdfFieldName"];
                                $annexId = $reqAnnex['idAnnex'];
                                displayAnnex($annexName, $annexPdfName);
                            }
                            break;
                        }

                    // Fonction d'afichage HTML des annexes
                    function displayAnnex($nameAnnex, $annexPdfName)
                    {
                        echo '<input type="radio" name="Groupe-Annexes_radio" id="' . $annexPdfName . '" value="'.$annexPdfName.'">
                            <label for="' . $annexPdfName . '">' . $nameAnnex . '</label>';
                    }
                ?>
            </div>
            <div id="validation">
                <input type="submit" value="Valider et générer le PDF" id="validation-bt">
            </div>
        </form>
    </div>
    <script src="js/student_form_script.js"></script>
</body>
</html>