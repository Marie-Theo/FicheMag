<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

include_once $_SERVER['DOCUMENT_ROOT'] . '/custom/fichemag/class/labelPrinter.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

# ---------------- récuperer l'objet ----------------

// Get parameters
$id  = GETPOSTINT('id');
if (getDolGlobalString('MAIN_SECURITY_ALLOW_UNSECURED_REF_LABELS')) {
	$ref = (GETPOSTISSET('ref') ? GETPOST('ref', 'nohtml') : null);
} else {
	$ref = (GETPOSTISSET('ref') ? GETPOST('ref', 'alpha') : null);
}

$object = new Product($db);


if ($id > 0 || !empty($ref)) {
	$result = $object->fetch($id, (string) $ref);
    $price_ttc = dol_textishtml($object->price_ttc) ? $object->price_ttc : dol_nl2br($object->price_ttc, 1, true);
    $price_ttc = price($price_ttc);
    $barcode = dol_textishtml($object->barcode) ? $object->barcode : dol_nl2br($object->barcode, 1, true);
    $descriptionTemp = dol_textishtml($object->description) ? $object->description : dol_nl2br($object->description, 1, true);
    $descriptionTemp = preg_split('<br>', $descriptionTemp);
    $descriptionTemp = str_replace('/', '', $descriptionTemp);
    $descriptionTemp = str_replace('<', '', $descriptionTemp);
    $descriptionTemp = str_replace('>', '', $descriptionTemp);
    $descriptionTemp = str_replace(' : ', ':', $descriptionTemp);
    $descriptionTemp = str_replace('  ', ' ', $descriptionTemp);
}

$dir = $conf->fichemag->dir_output;
$file = $dir . "/produit_" . $id . ".pdf";

## description
$description=array();
$accessoire = false;
$Marque='Undefined';
$Message_Top = "<p style='margin:20px;'>ATTENTION, COMPTE TENU DES PERTURBATIONS ACTUELLES DU MARCHÉ INFORMATIQUE, LE PRIX INDIQUÉ SUR CETTE FICHE <u>N'EST PAS FIXE POUR UNE DURÉE INDÉTERMINÉE</u>. <u style='color:red;'>LE PRIX QUI S'APPLIQUE EST CELUI AFFICHÉ EN MAGASIN LE JOUR DE LA VENTE</u>.</p>";
$ContactHoraire = array(
                "<u>CONTACT</u> - <u>HORAIRES</u><br>",
                "02.33.60.89.01 contact@misinformatique.com<br>",
                "Du Lundi au Vendredi : 9 h 30 - 12 h 30 et 13 h 30 - 18 h 30<br>",
                "Le samedi (fermé l'après-midi) : 9 h 30 - 12 h 30<br>",
                "Fermé le Lundi matin, le dimanche et les jours fériés",
                );

foreach($descriptionTemp as $caracteristique){
    if (str_contains($caracteristique, '---')) {
        if (!isset($Label)){
            $Label = GETPOST('label');
        }
        continue;
    } else if (!isset($Label)){
        $Label = $caracteristique;
    } else if (str_contains($caracteristique, ':')){
        $temp = explode(':', $caracteristique);
        if (str_contains(strtolower($caracteristique), 'marque')){
            $Marque=$temp[1];
        } else {
            if (str_contains(strtolower($temp[0]), 'accessoire')){$accessoire=true;}
            $description["$temp[0]"] = $temp[1];
        }
    }
    if (str_contains(strtolower($caracteristique), 'sacoche') && $accessoire==false){
        $description["Accessoire"] = isset($description["Accessoire"]) ? $description["Accessoire"] . '/ Sacoche ' : 'Sacoche ';
    } if (str_contains(strtolower($caracteristique), 'clavier') && $accessoire==false){
        $description["Accessoire"] = isset($description["Accessoire"]) ? $description["Accessoire"] . '/ Clavier ' : 'Clavier ';
    } if (str_contains(strtolower($caracteristique), 'souris') && $accessoire==false){
        $description["Accessoire"] = isset($description["Accessoire"]) ? $description["Accessoire"] . '/ Souris ' : 'Souris ';
    }
}

#### obligatoire

$html = "<center>
    <table style='width:80%'>
        <tr>
            <td rowspan=2 style='width:30%;'>
                <img src='/viewimage.php?cache=1&modulepart=mycompany&file=logos%2Fthumbs%2Flogo+2_small.jpg'>
            </td>
            <td  style='width:70%'>
                <center>
                    <h1>" . $Marque . "</h1>
                </center>
            </td>
        </tr>
        <tr>
            <td style='width:70%;'>
                $Message_Top
            </td>
        </tr>
    </table>
    <h2><u>" . $Label . "</u></h2><br>";

$cpt = 0;
$html .= '<table border=1 cellpadding="5" style="border:1px solid black;width:85%;border-collapse: collapse;">';
foreach($description as $titre => $caracteristique){
    if (str_contains(strtolower($titre), 'garantie')){
        $html .= "<tr>
            <td style='border-left:2px solid;border-top:2px solid;border-bottom:2px solid;'>" . $titre. "</td>
            <td style='border-right:2px solid;border-top:2px solid;border-bottom:2px solid;'>" . $caracteristique . "</td>
        </tr>";
    } else if ($titre !== 'Marque' && $titre !== 'Label'){
        $cpt++;
        $html .= $cpt%2 == 0 ? "<tr>" : "<tr style='background-color:#ccc;'>";
        $html .= "<td>" . $titre. "</td>
            <td>" . $caracteristique . "</td>
        </tr>";
    }
}
$html .= "</table><br>";

#### obligatoire
$html .= "<table border='1' cellspacing='10' cellpadding='10' style='border:1px solid black;border-collapse: collapse;font-size:20;'>
    <tr>
        <td>
            <center>";

foreach($ContactHoraire as $ligne){
    $html .= $ligne;
}

$html .=    "</center>
        </td>
    </tr>
</table><br>

<table cellspacing='0' cellpadding='20' style='border:1px solid red;width:80%;'>

    <tr>
        <td>
            <img src=/viewimage.php?modulepart=barcode&generator=tcpdfbarcode&code=" . $barcode . "&encoding=EAN13>
        </td>
        <td>Qr code</td>
        <td style='border:2px solid red;'>$price_ttc € ttc</td>
    </tr>
</table>

</center>";

echo $html;

$labelPrint = new LabelPrint($file);
$labelPrint->setMarque($Marque);
// $img = file_get_contents('/viewimage.php?cache=1&modulepart=mycompany&file=logos%2Fthumbs%2Flogo+2_small.jpg');
// $labelPrint->setLogo(DOL_DOCUMENT_ROOT . '/viewimage.php?cache=1&modulepart=mycompany&file=logos%2Fthumbs%2Flogo+2_small.jpg');
$labelPrint->setLogo(DOL_DOCUMENT_ROOT . '/../documents/mycompany/logos/logo 2.jpg');
$labelPrint->setMessage_Top($Message_Top);
$labelPrint->setProprieter($description);
$labelPrint->setContactHoraire($ContactHoraire);
$labelPrint->setCodeBar("/viewimage.php?modulepart=barcode&generator=tcpdfbarcode&code=" . $barcode . "&encoding=EAN13");
// $labelPrint->setQRCode();
$labelPrint->setPrix($price_ttc);

$labelPrint->printLabel();

// header('Location: ' . $_SERVER['HTTP_HOST']  . '/../showPDF.class.php?id=' . $objectid);

?>