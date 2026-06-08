<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

include_once $_SERVER['DOCUMENT_ROOT'] . '/custom/fichemag/class/labelPrinter.class.php';

$objectid = $_GET['id'];
$dir = $conf->fichemag->dir_output;
$file = $dir . "/produit_" . $objectid . ".pdf";

$labelPrint = new LabelPrint($file);
$labelPrint->printLabel();

header('Location: ' . $_SERVER['HTTP_HOST']  . '/../showPDF.class.php?id=' . $objectid);

?>