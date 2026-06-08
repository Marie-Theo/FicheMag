<?php

require $_SERVER['DOCUMENT_ROOT'] . '/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

$id = GETPOST('id');
print 'id : ' . $id;

$formfile = new FormFile($db);
print $formfile->showdocuments();

?>