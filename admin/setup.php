<?php
/* Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2026		Théo Marie
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    fichemag/admin/setup.php
 * \ingroup fichemag
 * \brief   FicheMag setup page.
 */

error_reporting(E_ALL);
ini_set('display_errors',1);

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/fichemag.lib.php';
//require_once "../class/myclass.class.php";
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Translations
$langs->loadLangs(array("admin", "fichemag@fichemag"));

// Initialize a technical object to manage hooks of page. Note that conf->hooks_modules contains an array of hook context
/** @var HookManager $hookmanager */
$hookmanager->initHooks(array('fichemagsetup', 'globalsetup'));

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'product';

$error = 0;
$setupnotempty = 0;

// Access control
if (!$user->admin) {
	accessforbidden();
}


// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
}
$formSetup = new FormSetup($db);

// Access control
if (!$user->admin) {
	accessforbidden();
}

/*
 Enter here all parameters in your setup page
 
 // Setup conf for selection of an URL
 $item = $formSetup->newItem('FICHEMAG_MYPARAM1');
 $item->fieldParams['isMandatory'] = 1;
 $item->fieldAttr['placeholder'] = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
 $item->cssClass = 'minwidth500';
 
 // Setup conf for selection of a simple string input
 $item = $formSetup->newItem('FICHEMAG_MYPARAM2');
 $item->defaultFieldValue = 'default value';
 $item->fieldAttr['placeholder'] = 'A placeholder here';
 $item->helpText = 'Tooltip text';
 
 // Setup conf for selection of a simple textarea input but we replace the text of field title
 $item = $formSetup->newItem('FICHEMAG_MYPARAM3');
 $item->nameText = $item->getNameText().' more html text ';
 
 // Setup conf for a selection of a Thirdparty
 $item = $formSetup->newItem('FICHEMAG_MYPARAM4');
 $item->setAsThirdpartyType();
 
 // Setup conf for a selection of a boolean
 $formSetup->newItem('FICHEMAG_MYPARAM5')->setAsYesNo();
 
 // Setup conf for a selection of an Email template of type thirdparty
 $formSetup->newItem('FICHEMAG_MYPARAM6')->setAsEmailTemplate('thirdparty');
 
 // Setup conf for a selection of a secured key
 //$formSetup->newItem('FICHEMAG_MYPARAM7')->setAsSecureKey();
 
 // Setup conf for a selection of a Product
 $formSetup->newItem('FICHEMAG_MYPARAM8')->setAsProduct();
 
 // Add a title for a new section
 $formSetup->newItem('NewSection')->setAsTitle();
 
 $TField = array(
 	'test01' => $langs->trans('test01'),
 	'test02' => $langs->trans('test02'),
 	'test03' => $langs->trans('test03'),
 	'test04' => $langs->trans('test04'),
 	'test05' => $langs->trans('test05'),
 	'test06' => $langs->trans('test06'),
 );
 
 // Setup conf for a simple combo list
 $formSetup->newItem('FICHEMAG_MYPARAM9')->setAsSelect($TField);
 
 // Setup conf for a multiselect combo list
 $item = $formSetup->newItem('FICHEMAG_MYPARAM10');
 $item->setAsMultiSelect($TField);
 $item->helpText = $langs->transnoentities('FICHEMAG_MYPARAM10');
 
 // Setup conf for a category selection
 $formSetup->newItem('FICHEMAG_CATEGORY_ID_XXX')->setAsCategory('product');
 
 // Setup conf FICHEMAG_MYPARAM10
 $item = $formSetup->newItem('FICHEMAG_MYPARAM10');
 $item->setAsColor();
 $item->defaultFieldValue = '#FF0000';
 //$item->fieldValue = '';
 //$item->fieldAttr = array() ; // fields attribute only for compatible fields like input text
 //$item->fieldOverride = false; // set this var to override field output will override $fieldInputOverride and $fieldOutputOverride too
 //$item->fieldInputOverride = false; // set this var to override field input
 //$item->fieldOutputOverride = false; // set this var to override field output
 
 $item = $formSetup->newItem('FICHEMAG_MYPARAM11')->setAsHtml();
 $item->nameText = $item->getNameText().' more html text ';
 $item->fieldInputOverride = '';
 $item->helpText = $langs->transnoentities('HelpMessage');
 $item->cssClass = 'minwidth500';
 
 $item = $formSetup->newItem('FICHEMAG_MYPARAM12');
 $item->fieldOverride = "Value forced, can't be modified";
 $item->cssClass = 'minwidth500';
 */
// use with getDolGlobalString()

// Setup conf for a selection of a boolean
$item = $formSetup->newItem('PRODUCT_ALLOW_EXTERNAL_DOWNLOAD')->setAsYesNo();
$item->nameText = "Autorisé le téléchargement de PDF des produit via un QR-code";
$item->helpText = "Permettra la création d'un Qr code sur le PDF renvoyant permettant le téléchargement du PDF.<br>Le Qr code ne sera fonctionnel que si votre Dolibarr a un nom de dommaine pour y accéder depuis le web.";

// Add a title for a new section
$item = $formSetup->newItem('NewSection')->setAsTitle();
$item->nameText = 'Contenue du PDF (optionnel)';

// Setup conf for selection of a simple string input
$item = $formSetup->newItem('FICHEMAG_HOURLY_WEEK');
$item->nameText = 'Horaire de semaine';
$item->defaultFieldValue = 'Du Lundi au Vendredi : 9 h 30 - 12 h 30 et 13 h 30 - 18 h 30';
$item->fieldAttr['placeholder'] = 'Du Lundi au Vendredi : x h x - x h x et x h x - x h x';
$item->helpText = 'Horaire de semaine écrit dans le pied de page des PDF';

// Setup conf for selection of a simple string input
$item = $formSetup->newItem('FICHEMAG_HOURLY_WEEK_END');
$item->nameText = "Horaire de fin de semaine";
$item->defaultFieldValue = "Le samedi (fermé l'après-midi) : 9 h 30 - 12 h 30";
$item->fieldAttr['placeholder'] = "Le samedi (fermé l'après-midi) : x h x - x h x";
$item->helpText = 'Horaire de fin de semaine écrit dans le pied de page des PDF';

// Setup conf for selection of a simple string input
$item = $formSetup->newItem('FICHEMAG_ADDITIONAL_INFO');
$item->nameText = "Information supplémentaire";
$item->defaultFieldValue = "Fermé le Lundi matin, le dimanche et les jours fériés";
$item->fieldAttr['placeholder'] = "Fermé le Lundi matin, le dimanche et les jours fériés";
$item->helpText = 'Information supplémentaire écrit dans le pied de page des PDF';

// Setup conf for a simple combo list
$TField = array(
'Style 1' => 'sous le prix',
'Style 2' => 'sous le code barre',
);

$item = $formSetup->newItem('FICHEMAG_CODE_BARR_STYLE')->setAsSelect($TField);
$item->nameText = "Style du Code Barre";
$item->helpText = "Style d'affichage du Code Barre quand tous les éléments sont présent dans le pied de page (QR-code + Code barre)";

//$item = $formSetup->newItem('FICHEMAG_MYPARAM13')->setAsDate();	// Not yet implemented

// End of definition of parameters

$setupnotempty += count($formSetup->items);


$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$moduledir = 'fichemag';
$myTmpObjects = array();
// TODO Scan list of objects to fill this array
$myTmpObjects['fichemag'] = array('label' => 'MyObject', 'includerefgeneration' => 0, 'includedocgeneration' => 1, 'class' => 'MyObject');

/*
 * Actions
 */

// For retrocompatibility Dolibarr < 15.0
if (versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
	$formSetup->saveConfFromPost();
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

if ($action == 'updateMask') {
	$maskconst = GETPOST('maskconst', 'aZ09');
	$maskvalue = GETPOST('maskvalue', 'alpha');

	if ($maskconst && preg_match('/_MASK$/', $maskconst)) {
		$res = dolibarr_set_const($db, $maskconst, $maskvalue, 'chaine', 0, '', $conf->entity);
		if (!($res > 0)) {
			$error++;
		}
	}

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
} elseif ($action == 'specimen') {
	$modele = GETPOST('module', 'alpha');

	$product = new Product($db);
	$product->initAsSpecimen();

	// Search template files
	$file = '';
	$classname = '';
	$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
	foreach ($dirmodels as $reldir) {
		$file = dol_buildpath($reldir."core/modules/product/doc/pdf_".$modele.".modules.php", 0);
		if (file_exists($file)) {
			$classname = "pdf_".$modele;
			break;
		}
	}

	if ($classname !== '') {
		require_once $file;

		$module = new $classname($db);

		'@phan-var-force ModelePDFProduct $module';

		if ($module->write_file($product, $langs, '') > 0) {
			header("Location: ".DOL_URL_ROOT."/document.php?modulepart=product&file=SPECIMEN.pdf");
			return;
		} else {
			setEventMessages($obj->error, $obj->errors, 'errors');
			dol_syslog($obj->error, LOG_ERR);
		}
	} else {
		setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	}
} elseif ($action == 'setmod') {
	// TODO Check if numbering module chosen can be activated by calling method canBeActivated
	if (!empty($tmpobjectkey)) {
		$constforval = 'FICHEMAG_'.strtoupper($tmpobjectkey)."_ADDON";
		dolibarr_set_const($db, $constforval, $value, 'chaine', 0, '', $conf->entity);
	}
} elseif ($action == 'set') {
	// Activate a model
	$ret = addDocumentModel($value, $type, $label, $scandir);
} elseif ($action == 'del') {
	$ret = delDocumentModel($value, $type);
	if ($ret > 0) {
		if (!empty($tmpobjectkey)) {
			$constforval = 'FICHEMAG_'.strtoupper($tmpobjectkey).'_ADDON_PDF';
			if (getDolGlobalString($constforval) == "$value") {
				dolibarr_del_const($db, $constforval, $conf->entity);
			}
		}
	}
} elseif ($action == 'setdoc') {
	if (dolibarr_set_const($db, "PRODUCT_ADDON_PDF", $value, 'chaine', 0, '', $conf->entity)) {
		// La constante qui a ete lue en avant du nouveau set
		// on passe donc par une variable pour avoir un affichage coherent
		$conf->global->PRODUCT_ADDON_PDF = $value;
	}

	// On active le modele
	$ret = delDocumentModel($value, $type);
	if ($ret > 0) {
		$ret = addDocumentModel($value, $type, $label, $scandir);
	}
} elseif ($action == 'unsetdoc') {
	if (!empty($tmpobjectkey)) {
		$constforval = 'FICHEMAG_'.strtoupper($tmpobjectkey).'_ADDON_PDF';
		dolibarr_del_const($db, $constforval, $conf->entity);
	}
}

$action = 'edit';


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$title = "FicheMagSetup";

llxHeader('', $langs->trans($title), $help_url, '', 0, 0, '', '', '', 'mod-fichemag page-admin');

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

// Configuration header
$head = fichemagAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($title), -1, "fichemag@fichemag");

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("FicheMagSetupPage").'</span><br><br>';


/*if ($action == 'edit') {
 print $formSetup->generateOutput(true);
 print '<br>';
 } elseif (!empty($formSetup->items)) {
 print $formSetup->generateOutput();
 print '<div class="tabsAction">';
 print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&token='.newToken().'">'.$langs->trans("Modify").'</a>';
 print '</div>';
 }
 */
if (!empty($formSetup->items)) {
	print $formSetup->generateOutput(true);
	print '<br>';
}


foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
	if (!empty($myTmpObjectArray['includerefgeneration'])) {
		// Numbering models

		$setupnotempty++;

		print load_fiche_titre($langs->trans("NumberingModules", $myTmpObjectArray['label']), '', '');

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("Name").'</td>';
		print '<td>'.$langs->trans("Description").'</td>';
		print '<td class="nowrap">'.$langs->trans("Example").'</td>';
		print '<td class="center" width="60">'.$langs->trans("Status").'</td>';
		print '<td class="center" width="16">'.$langs->trans("ShortInfo").'</td>';
		print '</tr>'."\n";

		clearstatcache();
		foreach ($dirmodels as $reldir) {
			$dir = dol_buildpath($reldir."core/modules/".$moduledir);
			
			if (is_dir($dir)) {
				$handle = opendir($dir);
				if (is_resource($handle)) {
					while (($file = readdir($handle)) !== false) {
						if (strpos($file, 'mod_'.strtolower($myTmpObjectKey).'_') === 0 && substr($file, dol_strlen($file) - 3, 3) == 'php') {
							$file = substr($file, 0, dol_strlen($file) - 4);

							require_once $dir.'/'.$file.'.php';

							$module = new $file($db);
							'@phan-var-force ModeleNumRefMyObject $module';

							// Show modules according to features level
							if ($module->version == 'development' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 2) {
								continue;
							}
							if ($module->version == 'experimental' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 1) {
								continue;
							}

							if ($module->isEnabled()) {
								dol_include_once('/'.$moduledir.'/class/'.strtolower($myTmpObjectKey).'.class.php');

								print '<tr class="oddeven"><td>'.$module->getName($langs)."</td><td>\n";
								print $module->info($langs);
								print '</td>';

								// Show example of numbering model
								print '<td class="nowrap">';
								$tmp = $module->getExample();
								if (preg_match('/^Error/', $tmp)) {
									$langs->load("errors");
									print '<div class="error">'.$langs->trans($tmp).'</div>';
								} elseif ($tmp == 'NotConfigured') {
									print $langs->trans($tmp);
								} else {
									print $tmp;
								}
								print '</td>'."\n";

								print '<td class="center">';
								$constforvar = 'FICHEMAG_'.strtoupper($myTmpObjectKey).'_ADDON';
								$defaultifnotset = 'thevaluetousebydefault';
								$activenumberingmodel = getDolGlobalString($constforvar, $defaultifnotset);
								if ($activenumberingmodel == $file) {
									print img_picto($langs->trans("Activated"), 'switch_on');
								} else {
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&token='.newToken().'&value='.urlencode($file).'">';
									print img_picto($langs->trans("Disabled"), 'switch_off');
									print '</a>';
								}
								print '</td>';

								$className = $myTmpObjectArray['class'];
								$mytmpinstance = new $className($db);
								'@phan-var-force MyObject $mytmpinstance';
								$mytmpinstance->initAsSpecimen();

								// Info
								$htmltooltip = '';
								$htmltooltip .= ''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';

								$nextval = $module->getNextValue($mytmpinstance);
								if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
									$htmltooltip .= ''.$langs->trans("NextValue").': ';
									if ($nextval) {
										if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured') {
											$nextval = $langs->trans($nextval);
										}
										$htmltooltip .= $nextval.'<br>';
									} else {
										$htmltooltip .= $langs->trans($module->error).'<br>';
									}
								}

								print '<td class="center">';
								print $form->textwithpicto('', $htmltooltip, 1, 'info');
								print '</td>';

								print "</tr>\n";
							}
						}
					}
					closedir($handle);
				}
			}
		}
		print "</table><br>\n";
	}

	if (!empty($myTmpObjectArray['includedocgeneration'])) {
		/*
		 * Document templates generators
		 */
		$setupnotempty++;
		$type = strtolower($myTmpObjectKey);

		print load_fiche_titre($langs->trans("DocumentModules", $myTmpObjectKey), '', '');

		// Module to build doc
		$def = array();
		$sql = "SELECT nom";
		$sql .= " FROM ".MAIN_DB_PREFIX."document_model";
		$sql .= " WHERE type = 'product'";
		$sql .= " AND entity = ".$conf->entity;
		$resql = $db->query($sql);
		if ($resql) {
			$i = 0;
			$num_rows = $db->num_rows($resql);
			while ($i < $num_rows) {
				$array = $db->fetch_array($resql);
				if (is_array($array)) {
					array_push($def, $array[0]);
				}
				$i++;
			}
		} else {
			dol_print_error($db);
		}

		print '<table class="noborder centpercent">'."\n";
		print '<tr class="liste_titre">'."\n";
		print '<td>'.$langs->trans("Name").'</td>';
		print '<td>'.$langs->trans("Description").'</td>';
		print '<td class="center" width="60">'.$langs->trans("Status")."</td>\n";
		print '<td class="center" width="60">'.$langs->trans("Default")."</td>\n";
		print '<td class="center" width="38">'.$langs->trans("ShortInfo").'</td>';
		print '<td class="center" width="38">'.$langs->trans("Preview").'</td>';
		print "</tr>\n";

		clearstatcache();

		$filelist = array();
		foreach ($dirmodels as $reldir) {
			foreach (array('', '/doc') as $valdir) {
		$dir = dol_buildpath($reldir."core/modules/product".$valdir);
		if (is_dir($dir)) {
			$handle = opendir($dir);
			if (is_resource($handle)) {
				while (($file = readdir($handle)) !== false) {
					$filelist[] = $file;
				}
				closedir($handle);
				arsort($filelist);

				foreach ($filelist as $file) {
					if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file)) {
						if (file_exists($dir.'/'.$file)) {
							$name = substr($file, 4, dol_strlen($file) - 16);
							$classname = substr($file, 0, dol_strlen($file) - 12);

							require_once $dir.'/'.$file;
							$module = new $classname($db);
							'@phan-var-force ModelePDFProduct $module';

							$modulequalified = 1;
							if ($module->version == 'development' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 2) {
								$modulequalified = 0;
							}
							if ($module->version == 'experimental' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 1) {
								$modulequalified = 0;
							}

							if ($modulequalified) {
								print '<tr class="oddeven"><td width="100">';
								print(empty($module->name) ? $name : $module->name);
								print "</td><td>\n";
								if (method_exists($module, 'info')) {
									print $module->info($langs); // @phan-suppress-current-line PhanUndeclaredMethod
								} else {
									print $module->description;
								}
								print '</td>';

								// Active
								if (in_array($name, $def)) {
									print '<td class="center">'."\n";
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&token='.newToken().'&value='.urlencode($name).'">';
									print img_picto($langs->trans("Enabled"), 'switch_on');
									print '</a>';
									print '</td>';
								} else {
									print '<td class="center">'."\n";
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&token='.newToken().'&value='.urlencode($name).'&scan_dir='.urlencode($module->scandir).'&label='.urlencode($module->name).'">'.img_picto($langs->trans("Disabled"), 'switch_off').'</a>';
									print "</td>";
								}

								// Default
								print '<td class="center">';
								if (getDolGlobalString('PRODUCT_ADDON_PDF') == $name) {
									print img_picto($langs->trans("Default"), 'on');
								} else {
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&token='.newToken().'&value='.urlencode($name).'&scan_dir='.urlencode($module->scandir).'&label='.urlencode($module->name).'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"), 'off').'</a>';
								}
								print '</td>';

								// Info
								$htmltooltip = ''.$langs->trans("Name").': '.$module->name;
								$htmltooltip .= '<br>'.$langs->trans("Type").': '.($module->type ? $module->type : $langs->trans("Unknown"));
								if ($module->type == 'pdf') {
									$htmltooltip .= '<br>'.$langs->trans("Width").'/'.$langs->trans("Height").': '.$module->page_largeur.'/'.$module->page_hauteur;
								}
								$htmltooltip .= '<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
								$htmltooltip .= '<br>'.$langs->trans("Logo").': '.yn($module->option_logo, 1, 1);
								$htmltooltip .= '<br>'.$langs->trans("MultiLanguage").': '.yn($module->option_multilang, 1, 1);


								print '<td class="center">';
								print $form->textwithpicto('', $htmltooltip, 1, 'info');
								print '</td>';

								// Preview
								print '<td class="center">';
								if ($module->type == 'pdf') {
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'">'.img_object($langs->trans("Preview"), 'contract').'</a>';
								} else {
									print img_object($langs->transnoentitiesnoconv("PreviewNotAvailable"), 'generic');
								}
								print '</td>';

								print "</tr>\n";
							}
						}
					}
				}
			}
		}
			}
		}

		print '</table>';
	}
}

if (empty($setupnotempty)) {
	print '<br>'.$langs->trans("NothingToSetup")."<br>";
	print '<br>'."Vous pouvez choisir le modèle de fichemag à utiliser par defaut dans la configuration du module produit."."<br>";
	print '<br>'."Plus d'information dans :";
	// print "<br>>> <a href=".$_SERVER['HTTP_REFERER'].">".$langs->trans("About")."</a>";
	print ''.dolGetButtonAction('', $langs->trans('About'), 'default', $_SERVER["HTTP_REFERER"]);
}

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
