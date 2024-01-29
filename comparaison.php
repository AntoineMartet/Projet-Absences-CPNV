<!--
-----------------------------------------------------------------------------------
Nom du fichier : form_validation.php
Auteur(s) : Antoine Martet, Ayami Ogay
Date creation : 27.04.2023
Dernière modification : 08.06.2023 par Antoine Martet
Description : Cette page présente toutes les demandes de congé et justificatifs d'absence reçu par le maître de classe.
              Pour chacune d'entre elles, il peut choisir de les valider ou de les refuser.
              Il peut aussi trier et filtrer les formulaires reçus par type / classe / élève / date.
Remarque(s) : (21.05.2023) Il faut décider de quoi faire quand le prof change le statut d'un formulaire avec les radio buttons.
-----------------------------------------------------------------------------------
-->

<?php
// Commence une session. Permet d'utiliser des variables globales utilisables sur différentes pages.
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des formulaires</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body id="form-validation">

<?php
// Verifier si il y a $_POST["mail"] depuis login_page
if (isset($_SESSION['mail'])){
    $mail = $_SESSION['mail'];
    unset($_SESSION['mail']);
    $fullName = str_replace('@cpnv.ch', '', $mail);
    $fullNameArray = explode('.',$fullName,2);
    $firstName = $fullNameArray[0];
    $lastName = $fullNameArray[1];
    // Variable de session stockant le nom de l'utilisateur inscrit
    $_SESSION["fullName"] = $lastName.' '.$firstName;
}

$fullName = $_SESSION["fullName"];

// Connexion à la base de données
include 'connection_mysql.php';

// Prendre les tableau entiers quand rôle est 3 = Doyen
if($_SESSION['role'] == 3) {
    // Exécution de la requête SQL pour les justifications
    $justifications = $conn->query("SELECT * FROM justifications");
    // Exécution de la requête SQL pour les demandes
    $demands = $conn->query("SELECT * FROM requests");

} else {
    // Exécution de la requête SQL pour les justifications
    $justifications = $conn->query("SELECT * FROM justifications WHERE classTeacher ='" . $fullName . "'");
    // Exécution de la requête SQL pour les demandes
    $demands = $conn->query("SELECT * FROM requests WHERE classTeacher ='" . $fullName . "'");
}



//array pour justifications
$justificationsStudentsData = [];
while ($justification = $justifications->fetch())
{
    $idStudent = $justification['idPerson-FK'];
    //echo $idStudent;
    $studentLastNameSql = $conn->query("SELECT personLastName FROM person WHERE idPerson ='" . $idStudent . "'");
    $studentLastNameData = $studentLastNameSql->fetch();

    $studentFirstNameSql = $conn->query("SELECT personFirstName FROM person WHERE idPerson ='" . $idStudent . "'");
    $studentFirstNameData = $studentFirstNameSql->fetch();

    //Nom d'école (La même pour tous)
    $schoolName = "EMSC";
    //array pour json
    $studentDataArray = [
        "id" => $justification['idJustification'],
        "school" => $schoolName,
        "submissionDateTime" => $justification['submissionDateTime'],
        "studentLastName" => $studentLastNameData[0],
        "studentFirstName" => $studentFirstNameData[0],
        "class" =>  $justification['className'],
        "classTeacher" => $justification['classTeacher'],
        "company" => $justification['company'],
        "startDateTime" => $justification['startDateTime'],
        "endDateTime" => $justification['endDateTime'],
        "missedTest" => $justification['missedTest'],
        "acceptanceStatus" =>  $justification['acceptanceStatus'],
        "reason" => $justification['reason'],
        "annex" => $justification['annex'],
        "validatedPerson" => $justification['personValidated']
    ];
    array_push($justificationsStudentsData, $studentDataArray);
    //echo "<tr><td>".$donnees['idPerson']."</td><td>".$donnees['personLastName']."</td><td>".$donnees['personFirstName']."</td></tr>";
}

//array pour demandes
$requestsStudentsData = [];
while ($request = $demands->fetch())
{
    $idStudent = $request['idPerson-FK'];
    //echo $idStudent;
    $studentLastNameSql = $conn->query("SELECT personLastName FROM person WHERE idPerson ='" . $idStudent . "'");
    $studentLastNameData = $studentLastNameSql->fetch();

    $studentFirstNameSql = $conn->query("SELECT personFirstName FROM person WHERE idPerson ='" . $idStudent . "'");
    $studentFirstNameData = $studentFirstNameSql->fetch();

    //Nom d'école (La même pout tous)
    $schoolName = "EMSC";
    //array pour json
    $studentDataArray = [
        "id" => $request['idRequest'],
        "school" => $schoolName,
        "submissionDateTime" => $request['submissionDateTime'],
        "studentLastName" => $studentLastNameData[0],
        "studentFirstName" => $studentFirstNameData[0],
        "class" =>  $request['className'],
        "classTeacher" => $request['classTeacher'],
        "company" => $request['company'],
        "startDateTime" => $request['startDateTime'],
        "endDateTime" => $request['endDateTime'],
        "acceptanceStatus" =>  $request['acceptanceStatus'],
        "reason" => $request['reason'],
        "annex" => $request['annex'],
        "validatedPerson" => $request['personValidated']
    ];
    array_push($requestsStudentsData, $studentDataArray);
    //echo "<tr><td>".$donnees['idPerson']."</td><td>".$donnees['personLastName']."</td><td>".$donnees['personFirstName']."</td></tr>";
}

// Crée une ligne (les balises <tr>...</tr>) pour chaque objet du JSON rentré en argument
// $type = 0 si justification, $type = 1 si request
function createFormsRows($formsArray, $type){
    foreach ($formsArray as $item) {
        $id = $item['id'];
        if($type == 0){
            $typeClass = "justification-rows";
        }
        else{
            $typeClass = "request-rows";
        }
        $studentLastName = $item['studentLastName'];
        $class = $item['class'];
        $studentFirstName = $item['studentFirstName'];
        $submissionDateTime = strtotime($item['submissionDateTime']);
        $startDateTime = strtotime($item['startDateTime']);
        $endDateTime = strtotime($item['endDateTime']);
        $tempAcceptanceStatus = $item['acceptanceStatus'];
        if($tempAcceptanceStatus == 0){
            $acceptanceStatus = "En attente";
        }
        elseif($tempAcceptanceStatus == 1){
            $acceptanceStatus = "Accepté";
        }
        elseif($tempAcceptanceStatus == 2){
            $acceptanceStatus = "Refusé";
        }?>
        <!-- Chaque ligne a comme ID l'ID du formulaire correspondant dans la base de données -->
        <!-- Chaque ligne a comme classe 1) "form-rows" et 2) "justification-rows" ou "request-rows" -->
    <tr id="<?=$id?>" class="form-rows <?=$typeClass?>">
        <td class="align-left"><?= $studentLastName . " " . $studentFirstName ?></td>
        <td><?= $class ?></td>
        <td><?= date("d.m.Y à H", $submissionDateTime) . "h" . date("i", $submissionDateTime); ?></td>
        <td><?= date("d.m.Y à H", $startDateTime) . "h" . date("i", $startDateTime); ?></td>
        <td><?= date("d.m.Y à H", $endDateTime) . "h" . date("i", $endDateTime); ?></td>
        <td><?= $acceptanceStatus ?></td>
        </tr><?php
    }
}

// Pour mise à jour le status
// Verifier si il y a $_POST["selectedStatus"] depuis cette page
//if ((isset($_POST["selectedStatus"]))&&(isset($_POST["formID"]))&&(isset($_POST["formType"]))){
if ((isset($_POST["selectedStatus"]))&&(isset($_POST["selectedID"]))&&(isset($_POST["selectedFormType"]))){
    $updateSelectedFormType = $_POST["selectedFormType"];
    $updateSelectedID = $_POST["selectedID"];
    $updateSelectedStatus = $_POST["selectedStatus"];
    $updateValidPersonName = $_POST["validPersonName"];
    $tableNameForUpdate = "";
    $columnNameForUpdate = "";
    if($updateSelectedFormType == "Justification d'absence") {
        $tableNameForUpdate = "justifications";
        $columnNameForUpdate = "idJustification";
    } else if($updateSelectedFormType == "Demande de congé") {
        $tableNameForUpdate = "requests";
        $columnNameForUpdate = "idRequest";
    }

    $sql = "UPDATE ".$tableNameForUpdate." SET acceptanceStatus=".$updateSelectedStatus.", personValidated='".$updateValidPersonName."' WHERE ".$columnNameForUpdate."=".$updateSelectedID;
    $stmt= $conn->prepare($sql);
    $stmt->execute();

    //Mise à jours les deux tableaux
    // Exécution de la requête SQL pour les justifications
    $justifications = $conn->query("SELECT * FROM justifications WHERE classTeacher ='" . $fullName . "'");
    //array pour justifications
    $justificationsStudentsData = [];
    //Mise à jour $justificationsStudentsData
    while ($justification = $justifications->fetch())
    {
        $idStudent = $justification['idPerson-FK'];
        //echo $idStudent;
        $studentLastNameSql = $conn->query("SELECT personLastName FROM person WHERE idPerson ='" . $idStudent . "'");
        $studentLastNameData = $studentLastNameSql->fetch();

        $studentFirstNameSql = $conn->query("SELECT personFirstName FROM person WHERE idPerson ='" . $idStudent . "'");
        $studentFirstNameData = $studentFirstNameSql->fetch();

        //Nom d'école (La même pour tous)
        $schoolName = "EMSC";
        //array pour json
        $studentDataArray = [
            "id" => $justification['idJustification'],
            "school" => $schoolName,
            "submissionDateTime" => $justification['submissionDateTime'],
            "studentLastName" => $studentLastNameData[0],
            "studentFirstName" => $studentFirstNameData[0],
            "class" =>  $justification['className'],
            "classTeacher" => $justification['classTeacher'],
            "company" => $justification['company'],
            "startDateTime" => $justification['startDateTime'],
            "endDateTime" => $justification['endDateTime'],
            "missedTest" => $justification['missedTest'],
            "acceptanceStatus" =>  $justification['acceptanceStatus'],
            "reason" => $justification['reason'],
            "annex" => $justification['annex'],
            "validatedPerson" => $justification['personValidated']
        ];
        array_push($justificationsStudentsData, $studentDataArray);
        //echo "<tr><td>".$donnees['idPerson']."</td><td>".$donnees['personLastName']."</td><td>".$donnees['personFirstName']."</td></tr>";
    }

    // Exécution de la requête SQL pour les demandes
    $demands = $conn->query("SELECT * FROM requests WHERE classTeacher ='" . $fullName . "'");
    //array pour demandes
    $requestsStudentsData = [];
    //Mise à jour $requestsStudentsData
    while ($request = $demands->fetch())
    {
        $idStudent = $request['idPerson-FK'];
        //echo $idStudent;
        $studentLastNameSql = $conn->query("SELECT personLastName FROM person WHERE idPerson ='" . $idStudent . "'");
        $studentLastNameData = $studentLastNameSql->fetch();

        $studentFirstNameSql = $conn->query("SELECT personFirstName FROM person WHERE idPerson ='" . $idStudent . "'");
        $studentFirstNameData = $studentFirstNameSql->fetch();

        //Nom d'école (La même pout tous)
        $schoolName = "EMSC";
        //array pour json
        $studentDataArray = [
            "id" => $request['idRequest'],
            "school" => $schoolName,
            "submissionDateTime" => $request['submissionDateTime'],
            "studentLastName" => $studentLastNameData[0],
            "studentFirstName" => $studentFirstNameData[0],
            "class" =>  $request['className'],
            "classTeacher" => $request['classTeacher'],
            "company" => $request['company'],
            "startDateTime" => $request['startDateTime'],
            "endDateTime" => $request['endDateTime'],
            "acceptanceStatus" =>  $request['acceptanceStatus'],
            "reason" => $request['reason'],
            "annex" => $request['annex'],
            "validatedPerson" => $request['personValidated']
        ];
        array_push($requestsStudentsData, $studentDataArray);
        //echo "<tr><td>".$donnees['idPerson']."</td><td>".$donnees['personLastName']."</td><td>".$donnees['personFirstName']."</td></tr>";
    }

}

?>

<h1>Gestion des congés et absences</h1>

<div class="area">
    <input type="radio" name="tab_name" id="tab-justifications"
        <?php
        if(!isset($_GET['selectedRowTypeClass'])||
            ($_GET['selectedRowTypeClass'] == "justification-rows") ||
            (isset($_POST["selectedFormType"]) && ($_POST["selectedFormType"]) == "Justification d'absence" )) {
            echo "checked";
        }
        ?>>
    <label class="tab_class" for="tab-justifications">Justifications d'absences</label>
    <div class="content-class">
        <table class="main-table">
            <tr>
                <th id="header-student">Élève</th>
                <th id="header-class">Classe</th>
                <th id="header-submission-date">Date de soumission</th>
                <th id="header-beginning-date">Début</th>
                <th id="header-end-date">Fin</th>
                <th id="header-status">Statut</th>
            </tr>
            <?php createFormsRows($justificationsStudentsData, 0);
            ?>
        </table>
        <?php


        // Récupération de l'ID de la ligne obtenue via un évènement JavaScript
        if (isset($_GET["selectedRowID"]) && isset($_GET["selectedRowTypeClass"])) {
            $selectedRowID = $_GET["selectedRowID"];
            $selectedRowTypeClass = $_GET["selectedRowTypeClass"];

            if ($selectedRowTypeClass == "justification-rows") {
                //Parcours des lignes du tableau des justifications
                foreach ($justificationsStudentsData as $item) {
                    if ($item["id"] == $selectedRowID) {
                        displayFormDetails($item, 0);
                        break;
                    }
                }
            }
        }
        ?>
    </div>
    <input type="radio" name="tab_name" id="tab-requests" <?php
    // Si $_GET['selectedRowTypeClass'] == "request-rows"
    if(((isset($_GET['selectedRowTypeClass'])) && ($_GET['selectedRowTypeClass'] == "request-rows"))||
        ((isset($_POST["selectedFormType"]) && ($_POST["selectedFormType"]) == "Demande de congé"))) {
        echo "checked";
    }
    ?>>
    <label class="tab_class" for="tab-requests">Demandes de congés</label>
    <div class="content-class">
        <table class="main-table">
            <tr>
                <th id="header-student">Élève</th>
                <th id="header-class">Classe</th>
                <th id="header-date">Date de soumission</th>
                <th id="header-beginning-date">Début</th>
                <th id="header-end-date">Fin</th>
                <th id="header-status">Statut</th>
            </tr>
            <?php createFormsRows($requestsStudentsData, 1);
            ?>
        </table>
        <?php


        // Récupération de l'ID de la ligne obtenue via un évènement JavaScript
        if (isset($_GET["selectedRowID"]) && isset($_GET["selectedRowTypeClass"])) {
            $selectedRowID = $_GET["selectedRowID"];
            $selectedRowTypeClass = $_GET["selectedRowTypeClass"];

            if ($selectedRowTypeClass == "request-rows") {
                //Parcours des lignes du tableau des demandes (si la ligne avec le bon ID n'a pas encore été trouvée)
                foreach ($requestsStudentsData as $item) {
                    if ($item["id"] == $selectedRowID) {
                        displayFormDetails($item, 1);
                        break;
                    }
                }
            }
        }
        ?>
    </div>
</div>

<?php
// $type = 0 si justification, $type = 1 si request
function displayFormDetails($form, $type){
    include 'connection_mysql.php';
    $formID = $form['id'];
    if($type == 0){
        $formType = "Justification d'absence";
        $formReasonRequest = $conn->query('SELECT name FROM justifications_reasons where pdfFieldName = "'.$form["reason"].'"');
        $formReasonTemp = $formReasonRequest->fetch();
        $formAnnexRequest = $conn->query('SELECT name FROM justifications_annex where pdfFieldName = "'.$form["annex"].'"');
        $formAnnexTemp = $formAnnexRequest->fetch();
    }
    else{
        $formType = "Demande de congé";
        $formReasonRequest = $conn->query('SELECT name FROM requests_reasons where pdfFieldName = "'.$form["reason"].'"');
        $formReasonTemp = $formReasonRequest->fetch();
        $formAnnexRequest = $conn->query('SELECT name FROM requests_annex where pdfFieldName = "'.$form["annex"].'"');
        $formAnnexTemp = $formAnnexRequest->fetch();
    }
    if ($formReasonTemp){
        $formReason = $formReasonTemp['name'];
    }else{
        $formReason = $form["reason"];
    }

    if ($formAnnexTemp){
        $formAnnex = $formAnnexTemp['name'];
    }else{
        $formAnnex = "Aucune";
    }
    $formSchool = $form["school"];
    $formStudentLastName = $form["studentLastName"];
    $formStudentFirstName = $form["studentFirstName"];
    $formClass = $form["class"];
    $formClassTeacher = $form["classTeacher"];
    $formCompany = $form["company"];
    $formStartDateTime = strtotime($form['startDateTime']);
    $formEndDateTime = strtotime($form['endDateTime']);
    //$formReason = $form["reason"];
    //$formAnnex = $form["annex"];
    $formSubmissionDateTime = strtotime($form['submissionDateTime']);
    $tempAcceptanceStatus = $form['acceptanceStatus'];
    $formPersonValid = $form['validatedPerson'];
    if($tempAcceptanceStatus == 0){
        $formAcceptanceStatus = "En attente";
    }
    elseif($tempAcceptanceStatus == 1){
        $formAcceptanceStatus = "Accepté";
    }
    elseif($tempAcceptanceStatus == 2){
        $formAcceptanceStatus = "Refusé";
    }
    ?>

    <div class="form-details">
    <h2>Formulaire sélectionné</h2>
    <div>
        <table>
            <tr>
                <td class="title">Type</td>
                <td><?= $formType ?></td>
            </tr>
            <tr>
                <td class="title">École</td>
                <td><?= $formSchool ?></td>
            </tr>
            <tr>
                <td class="title">Nom</td>
                <td><?= $formStudentLastName ?></td>
            </tr>
            <tr>
                <td class="title">Prénom</td>
                <td><?= $formStudentFirstName ?></td>
            </tr>
            <tr>
                <td class="title">Classe</td>
                <td><?= $formClass ?></td>
            </tr>
            <tr>
                <td class="title">Maître-sse de classe</td>
                <td><?= $formClassTeacher ?></td>
            </tr>
            <tr>
                <td class="title">Entreprise formative</td>
                <td><?= $formCompany ?></td>
            </tr>
            <tr>
                <td class="title">Date et heure</td>
                <td>Absence du : <br><?= date("d.m.Y à H", $formStartDateTime) . "h" . date("i", $formStartDateTime) . " au <br>" . date("d.m.Y à H", $formEndDateTime) . "h" . date("i", $formEndDateTime) . "<br>"; ?></td>
            </tr>
            <!-- Le "Test manqué" ne doit apparaître que pour les justificatifs d'absence -->
            <!-- "Non" ou "IEL-1" / "IEL-1, ICT-999, MA-00", etc. -->
            <?php if($type == 0){ ?>
                <tr>
                    <td class="title">Test(s) manqué(s)</td>
                    <td><?= $form['missedTest'] ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td class="title">Motif</td>
                <td><?= $formReason ?></td>
            </tr>
            <tr>
                <td class="title">Annexe</td>
                <td><?= $formAnnex ?></td>
            </tr>
        </table>

        <h3>----- Autres informations -----</h3>
        <table>
            <tr>
                <td class="title">ID du formulaire</td>
                <td><?= $formID ?></td>
            </tr>
            <tr>
                <td class="title">Formulaire soumis le</td>
                <td><?= date("d.m.Y à H", $formSubmissionDateTime) . "h" . date("i", $formSubmissionDateTime)?></td>
            </tr>
            <tr>
                <td class="title">Statut</td>
                <td><?= $formAcceptanceStatus ?></td>
            </tr>
            <tr>
                <td class="title">Validé ou refusé par</td>
                <td><?= $formPersonValid ?></td>
            </tr>
        </table>

        <div id="status-choice">
            <form action="form_validation.php?selectedRowID=<?= @$_GET["selectedRowID"] ?>&selectedRowTypeClass=<?= @$_GET["selectedRowTypeClass"] ?>&statutChange=yes" method="post">
                <h3>----- Changer de statut ----- </h3>
                <input type="radio" id="radio-waiting" value="0" name="selectedStatus" <?php if($tempAcceptanceStatus==0){ echo "checked";} ?>>
                <label for="waiting">En attente</label>
                <input type="radio" id="radio-accepted" value="1" name="selectedStatus" <?php if($tempAcceptanceStatus==1){ echo "checked";} ?>>
                <label for="accepted">Accepté</label>
                <input type="radio" id="radio-refused" value="2" name="selectedStatus" <?php if($tempAcceptanceStatus==2){ echo "checked";} ?>>
                <label for="refused">Refusé</label>
                <!--Hidden inputs pour justifier la donnée-->
                <input type="hidden" name="selectedID" value="<?=$formID?>">
                <input type="hidden" name="selectedFormType" value="<?=$formType?>">
                <input type="hidden" name="validPersonName" value="<?=$_SESSION["fullName"]?>">
                <?php
                if (isset($_GET["statutChange"]) && @$_GET["statutChange"] == "yes"){
                    echo "<h3 style='color:green'>Le statut à bien été changé</h3>";
                }
                ?>
                <div class="form-btns">
                    <input type="submit" id="btn-validation" value="Valider le choix">
                    <input type="reset" id="btn-reinitialize" value="Annuler le changement">
                </div>
            </form>
        </div>
    </div>
    </div><?php
}?>
<script src="js/form_validation_script.js"></script>
</body>
</html>