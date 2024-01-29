<!--
-----------------------------------------------------------------------------------
Nom du fichier : login_page.php
Auteur(s) : Maxime Borgeaud, Ayami Ogay
Date de création : 28.04.2023
Dernière modification : 09.06.2023 par Antoine Martet (ajout de commentaires)
Description : Page web qui sert à savoir quel utilisateur est connecté. Elle redirige vers la bonne page en fonction du type
              d'utilisateur qui se connecte. Elle ne devrait plus exister après l'insertion du projet dans l'intranet car
              il faudra de toute façon être connecté pour accéder aux pages de création de formulaire (pour les élèves,
              student_form.php) ou de validation de formulaire (pour les maîtres de classe / doyens : form_validation.php).
-----------------------------------------------------------------------------------
-->

<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body id="login-page">
    <div id="login_div">
        <img id="logo_cpnv" src="img/logo.png">
        <h2>Rentrez votre adresse mail du CPNV :</h2>
        <div>
            <form method="post" action="login_page.php">
                <input type="text" id="login_text" placeholder="exemple@cpnv.ch" name="mail">
                <input type="submit" value="valider" id="login_bt">
            </form>
        </div>
    </div>
</body>
</html>
<?php
// Connexion à la base de données
include 'connection_mysql.php';

// Condition pour rediriger à la page selon le rôle
if(isset($_POST['mail'])) {
    // Affichage d'un message si la valeur du champ mail est vide
    if($_POST['mail'] == "") {
        echo "Merci de mettre un email dans le formulaire";
        exit;
    }
    $mail = $_POST["mail"];
    // Extraction du nom de l'utilisateur à partir de l'adresse mail
    $fullName = str_replace('@cpnv.ch', '', $mail);
    $fullNameArray = explode('.',$fullName,2);
    $firstName = $fullNameArray[0];
    $lastName = $fullNameArray[1];
    // Transformation de prenom.nom en Prenom.NOM
    $lastName = strtoupper($lastName);
    $firstName = strtolower($firstName);
    $firstName = ucfirst($firstName);
    // Ajout du '@cpnv.ch' pour avoir enfin le bon format pour appeler la BDD
    $mailToSend = $firstName.'.'.$lastName.'@cpnv.ch';
    $_SESSION['mail'] = $mailToSend;

    // SQL extraction de la personne connectée
    $personSql = $conn->query("SELECT * FROM person WHERE personLastName ='" . $lastName . "'AND personFirstName='" . $firstName . "'");

    // 0 = élève, 1 = prof, 2 = maître de classe, 3 = doyen
    while ($person = $personSql->fetch()) {
        if($person['role'] >= 1 && $person['role'] <= 3) {
            // Initialisation d'une variable session pour le role de la personne connectée
            $_SESSION['role'] = $person['role'];
            session_write_close();
            // Redirection vers form_validation.php avec $_POST["mail"]
            header('Location: form_validation.php');
        } elseif($person['role'] == 0) {
            session_write_close();
            // Redirection vers student_form.php avec $_POST["mail"]
            header('Location: student_form.php');
        }
    }
}
?>