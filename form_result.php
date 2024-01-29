<!--
-----------------------------------------------------------------------------------
Nom du fichier : form_result.php
Auteur(s) : Antoine Martet, Maikol Correia Da Silva
Date creation : 27.04.2023
Dernière modification : 08.06.2023 par Antoine Martet
Description : Envoie dans la base de données les informations du formulaire rempli par l'élève et affiche le PDF
              du formulaire rempli et prêt à être téléchargé et/ou imprimé.
-----------------------------------------------------------------------------------
-->
<?php
session_start();
include 'connection_mysql.php';

// Si $_POST n'est pas vide, insertion des données du formulaire dans la BDD
if (isset($_POST["Date-debut_textbox"])) {
    // Passage au bon fuseau horaire + passage au bon format pour la BDD
    $tz = "Europe/Brussels";
    $timestamp = time();
    $dt = new Datetime("now", new DateTimeZone($tz));
    $dt->setTimestamp($timestamp);
    $actualDate = $dt->format("Y-m-d H:i:s");

    // Si le motif coché est "Autres", création d'une chaîne contenant le texte entré par l'élève dans le champ apparu
    if ($_POST["Groupe-Motifs_radio"] == "Autres"){
        $reason = "Autres : ".$_POST["Autres-texte_textbox"];
        $reason = str_replace("'", "\'", $reason);
    }else{
        $reason = $_POST["Groupe-Motifs_radio"];
    }

    // Insertion des données du formulaire dans la BDD
    if ($_SESSION["selectedForm"] == "leave-request") {
        $sql = "INSERT INTO requests (`submissionDateTime`, `company`, `startDateTime`, `endDateTime`, `reason`, `annex`, `idPerson-FK`, `className`, `classTeacher`)
                                VALUES ('" . $actualDate . "', '" . $_POST['Entreprise-formatrice_textbox'] . "', '" . $_POST["Date-debut_textbox"] . " " . $_POST["Heure-debut_textbox"] . ":00', '" . $_POST["Date-fin_textbox"] . " " . $_POST["Heure-fin_textbox"] . ":00',
                                '" . $reason . "', '" . @$_POST["Groupe-Annexes_radio"] . "', '" . $_SESSION["idPerson"] . "', '" . $_SESSION["class"] . "', '" . $_SESSION["teacherName"] . "')";
    } else {
        $sql = "INSERT INTO justifications (`submissionDateTime`, `company`, `startDateTime`, `endDateTime`, `missedTest`, `reason`, `annex`, `idPerson-FK`, `className`, `classTeacher`)
                                VALUES ('" . $actualDate . "', '" . $_POST['Entreprise-formatrice_textbox'] . "', '" . $_POST["Date-debut_textbox"] . " " . $_POST["Heure-debut_textbox"] . ":00', '" . $_POST["Date-fin_textbox"] . " " . $_POST["Heure-fin_textbox"] . ":00',
                                 '" . @$_POST["Test-manque_textbox"] . "','" . $reason . "', '" . @$_POST["Groupe-Annexes_radio"] . "', '" . $_SESSION["idPerson"] . "', '" . $_SESSION["class"] . "', '" . $_SESSION["teacherName"] . "')";
    }
    $conn->exec($sql);
}

// Modification du format des dates pour l'affichage dans le PDF
$_POST['Date-debut_textbox'] = date("d.m.Y", strtotime($_POST['Date-debut_textbox']));
$_POST['Heure-debut_textbox'] = date("H", strtotime($_POST['Heure-debut_textbox'])) . "h" . date("i", strtotime($_POST['Heure-debut_textbox']));
$_POST['Date-fin_textbox'] = date("d.m.Y", strtotime($_POST['Date-fin_textbox']));
$_POST['Heure-fin_textbox'] = date("H", strtotime($_POST['Heure-fin_textbox'])) . "h" . date("i", strtotime($_POST['Heure-fin_textbox']));

/*
// Debugging information, can be deleted
echo "<h1>POST Data</h1>";
echo "<div style='border-style: solid;'><pre>";
print_r($_POST);
echo "</pre></div>";
*/

// Configuration
// Set location for FDF and PDF files
$outputLocation = "output/";
// Location of original PDF form
if($_POST['selected-form'] == "proof-absence"){
    $pdfTemplateLocation = "CPNV-Justificatif-dabsence-mod-radio.pdf";
}else{
    $pdfTemplateLocation = "CPNV-Demande-de-congé-mod-radio.pdf";
}

// Loop through the $_POST data, creating a new row in the FDF file for each key/value pair
$fdf = "";
foreach($_POST as $key => $value) {
    // If the user filled nothing in the field, like a text field, just skip it.
    // Note that if the PDF you provide already has text in it by default, doing this will leave the text as-is.
    // If you prefer to remove the text, you should remove the lines below so you overwrite the text with nothing.
    if($value == "") {
        continue;
    }

    // Figure out what kind of field it is by its name, which should be in the format name_fieldtype.

    // Textbox
    if(stringEndsWith($key, "_textbox")) {
        $key = str_replace("_textbox", "", $key);
        // Format:
        // << /V (Text) /T (Fieldname) >>

        // Backslashes in the value are encoded as double backslashes
        $value = str_replace("\\", "\\\\", $value);
        // Parenthesis are encoded using \'s in front
        $value = str_replace("(", "\(", $value);
        $value = str_replace(")", "\)", $value);

        $fdf .= "<< /V (" . $value . ")" . " /T (" . $key . ") >>" . "\r\n";
    }

    // Checkbox
    else if(stringEndsWith($key, "_checkbox")) {
        $key = str_replace("_checkbox", "", $key);
        // Format:
        // << /V /On /T (Fieldname) >>

        // If the data was present in $_POST, that's because it was checked, so we can hardcode "/On" here
        $fdf .= "<< /V /On /T (" . $key . ") >>" . "\r\n";
    }

    // Radio Button
    else if(stringEndsWith($key, "_radio")) {
        $key = str_replace("_radio", "", $key);
        // Format:
        // << /V /Test#20Value /T (Fieldname) >>

        // Spaces are encoded as #20
        $value = str_replace(" ", "#20", $value);

        $fdf .= "<< /V /" . $value . " /T (" . $key . ") >>" . "\r\n";
    }

    // Dropdown
    else if(stringEndsWith($key, "_dropdown")) {
        $key = str_replace("_dropdown", "", $key);
        // Format:
        // << /V (Option 2) /T (Dropdown) >>

        $fdf .= "<< /V (" . $value . ") /T (" . $key . ") >>" . "\r\n";
    }

    // Unknown type
    else {
        // echo "ERROR: We don't know what field type " . $key . " is, so we can't put it into the FDF file!";
    }
}

// Include the header and footer, then write the FDF data to a file
$fdf = getFDFHeader() . $fdf . getFDFFooter();

/*
// Debugging information, can be deleted
echo "<h1>FDF Data</h1>";
echo "<div style='border-style: solid;'><pre>";
print_r(htmlspecialchars($fdf));
echo "</pre></div>";
*/

// Dump FDF data to file
$timestamp = time();
$outputFDF = $outputLocation . $timestamp . ".fdf";
$outputPDF = $outputLocation . $timestamp . ".pdf";
file_put_contents($outputFDF, $fdf);

// Generate the PDF
// Format:
// exec("pdftk originalForm.pdf fill_form formData.fdf output filledFormWithData.pdf");
exec("pdftk " . $pdfTemplateLocation . " fill_form " . $outputFDF . " output " . $outputPDF);

/*
echo "<p>Done! Your application will be reviewed shortly.</p>";
echo "<p>It is stored in: " . $outputPDF . "</p>";
echo "<p><br/><a href='/'>Home</a></p>";
*/
echo "<iframe src='" . $outputPDF . "' width='100%' height='100%'></iframe>";


/**
 * Simple "ends with" function, because PHP only included an endsWith() in 8.0
 * From: https://www.tutorialkart.com/php/php-check-if-string-ends-with-substring/
 */
function stringEndsWith($string, $endsWith) {
    if(substr_compare($string, $endsWith, -strlen($endsWith)) === 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get the boilerplate header information for the FDF file
 */
function getFDFHeader() {
    $fdfHeader = "%FDF-1.2" . "\r\n";
    $fdfHeader .= "1 0 obj << /FDF << /Fields [" . "\r\n";
    return $fdfHeader;
}

/**
 * Get the boilerplate footer information for the FDF file
 */
function getFDFFooter() {
    $fdfFooter = "] >> >>" . "\r\n";
    $fdfFooter .= "endobj" . "\r\n";
    $fdfFooter .= "trailer" . "\r\n";
    $fdfFooter .= "<</Root 1 0 R>>" . "\r\n";
    $fdfFooter .= "%%EOF";

    return $fdfFooter;
}

?>