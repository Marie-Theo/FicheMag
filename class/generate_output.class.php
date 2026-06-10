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
    $barcode = dol_textishtml($object->barcode) ? $object->barcode : dol_nl2br($object->barcode, 1, true);
    $descriptionTemp = dol_textishtml($object->description) ? $object->description : dol_nl2br($object->description, 1, true);
    $descriptionTemp = preg_split('<br>', $descriptionTemp);
    $descriptionTemp = str_replace('/>', '', $descriptionTemp);
    $descriptionTemp = str_replace('<', '', $descriptionTemp);
    
}

$dir = $conf->fichemag->dir_output;
$file = $dir . "/produit_" . $id . ".pdf";

## description
$description=array();

foreach($descriptionTemp as $caracteristique){
    if (!isset($description["Label"])){
        $description["Label"] = $caracteristique;
        continue;
    } else if (str_contains($caracteristique, '---')) {
        if (!$description["Label"]){
            $description["Label"] = GETPOST('label');
        }
        continue;
    } else if (str_contains($caracteristique, ':')){
        $temp = explode(':', $caracteristique);
        $description["$temp[0]"] = $temp[1];
        continue;
    } if (str_contains(strtolower($caracteristique), 'clavier')){
        $description["Accessoire"] = isset($description["Accessoire"]) ? $description["Accessoire"] . '/ Clavier ' : 'Clavier ';
    } if (str_contains(strtolower($caracteristique), 'souris')){
        $description["Accessoire"] = isset($description["Accessoire"]) ? $description["Accessoire"] . '/ Souris ' : 'Souris ';
    } if (str_contains(strtolower($caracteristique), 'sacoche')){
        $description["Accessoire"] = isset($description["Accessoire"]) ? $description["Accessoire"] . '/ Sacoche ' : 'Sacoche ';
    }
}

// print_r($description);

#### obligatoire
echo '<center>';
echo "<table>";
print('<tr><td rowspan=2><img src="/viewimage.php?cache=1&modulepart=mycompany&file=logos%2Fthumbs%2Flogo+2_small.jpg"></td>');
print('<td><center>' . $description[' Marque '] . '</center></td></tr>');
print("<tr><td>ATTENTION, COMPTE TENU DES PERTURBATIONS ACTUELLES DU MARCHÉ INFORMATIQUE, LE PRIX INDIQUÉ SUR CETTE FICHE N'EST PAS FIXE POUR UNE DURÉE INDÉTERMINÉE. LE PRIX QUI S'APPLIQUE EST CELUI AFFICHÉ EN MAGASIN LE JOUR DE LA VENTE.</td></tr>");
echo "</table>";

echo "<u>" . $description["Label"] . "</u>";

echo "<table border=1>";
foreach($description as $titre => $caracteristique){
    if ($titre !== ' Marque ' && $titre !== 'Label'){
        echo "<tr>";
        echo"<td>" . $titre. "</td>";
        echo "<td>" . $caracteristique . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

#### obligatoire
echo '<table border=1><tr><td>';
print('<center><u>CONTACT</u> - <u>HORAIRES</u><br>');
print('02.33.60.89.01 contact@misinformatique.com<br>');
print('Du Lundi au Vendredi : 9 h 30 - 12 h 30 et 13 h 30 - 18 h 30<br>');
print("Le samedi (fermé l'après-midi) : 9 h 30 - 12 h 30<br>");
print("Fermé le Lundi matin, le dimanche et les jours fériés");
echo "</center></td></tr></table>";

echo '<table border=1>';
echo "<tr><td><img src=/viewimage.php?modulepart=barcode&generator=tcpdfbarcode&code=" . $barcode . "&encoding=EAN13></td><td>Qr code</td><td>Prix</td></tr>";
echo "</table>";


print('</center>');
// $labelPrint = new LabelPrint($file);
// $labelPrint->printLabel();

// header('Location: ' . $_SERVER['HTTP_HOST']  . '/../showPDF.class.php?id=' . $objectid);

?>