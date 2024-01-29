<!--
-----------------------------------------------------------------------------------
Nom du fichier : connection_mysql.php
Auteur(s) : Ayami Ogay
Date creation : 27.04.2023
Dernière modification : 08.06.2023 par Antoine Martet
Description : Cette page présente toutes les demandes de congé et justificatifs d'absence reçu par le maître de classe.
              Pour chacune d'entre elles, il peut choisir de les valider ou de les refuser.
              Quand le doyen arrive sur cette page, il voit tous les formulaires de tous les maîtres de classe.
              Quand un prof qui n'est pas maître de classe arrive sur cette page, il voit la structure de la page mais
              ne voit aucun formulaire.
              Les élèves ne sont jamais redirigés vers cette page.
-----------------------------------------------------------------------------------
-->
<?php
try {
    // Connexion à la base de données
    $conn = new PDO('mysql:host=web24.swisscenter.com;dbname=xyz', 'xyz', 'xyz');
}
catch (Exception $error){
    die('Erreur: '.$error->getMessage());
}
?>
