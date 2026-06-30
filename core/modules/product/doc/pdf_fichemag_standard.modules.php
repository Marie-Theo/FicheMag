<?php
/* Copyright (C) 2017 	Laurent Destailleur		<eldy@products.sourceforge.net>
 * Copyright (C) 2023 	Anthony Berton			<anthony.berton@bb2a.fr>
 * Copyright (C) 2024-2025	MDW						<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024   Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2024	Nick Fragoulis
 * Copyright (C) 2026	Théo Marie
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/product/doc/pdf_standard.modules.php
 *	\ingroup    societe
 *	\brief      File of class to build PDF documents for products/services
 */

// error_reporting(E_ALL);
// ini_set('display_errors',1);

require_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';


/**
 *	Class to build documents using ODF templates generator
 */
class pdf_fichemag_standard extends ModelePDFProduct
{
	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var string model name
	 */
	public $name;

	/**
	 * @var string model description (short text)
	 */
	public $description;

	/**
	 * @var string document type
	 */
	public $type;

	/**
	 * Dolibarr version of the loaded document
	 * @var string Version, possible values are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'''|'development'|'dolibarr'|'experimental'
	 */
	public $version = 'dolibarr';


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $langs, $mysoc;

		// Load traductions files required by page
		$langs->loadLangs(array("main", "companies"));

		$this->db = $db;
		$this->name = "fiche Magasin";
		$this->description = $langs->trans("Modèle de PDF fiche pouvant etre exposé en magasin");

		// Page size for A4 format
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur, $this->page_hauteur);
		$this->marge_gauche = getDolGlobalInt('MAIN_PDF_MARGIN_LEFT', 10);
		$this->marge_droite = getDolGlobalInt('MAIN_PDF_MARGIN_RIGHT', 10);
		$this->marge_haute = getDolGlobalInt('MAIN_PDF_MARGIN_TOP', 10);
		// $this->marge_basse = getDolGlobalInt('MAIN_PDF_MARGIN_BOTTOM', 10);
		$this->marge_basse = 15;
		$this->corner_radius = getDolGlobalInt('MAIN_PDF_FRAME_CORNER_RADIUS', 0);
		$this->option_logo = 1; // Display logo
		$this->option_multilang = 1; // Available in several languages
		$this->option_freetext = 0; // Support add of a personalised text

		// Define position of columns
		$this->posxdesc = $this->marge_gauche + 1; // For module retrocompatibility support during PDF transition: TODO remove this at the end

		if ($mysoc === null) {
			dol_syslog(get_class($this).'::__construct() Global $mysoc should not be null.'. getCallerInfoString(), LOG_ERR);
			return;
		}

		// Get source company
		$this->emetteur = $mysoc;
		if (!$this->emetteur->country_code) {
			$this->emetteur->country_code = substr($langs->defaultlang, -2); // By default if not defined
		}
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Function to build pdf onto disk
	 *
	 *	@param		Product		$object				Object source to build document
	 *	@param		Translate	$outputlangs		Lang output object
	 *	@param		string		$srctemplatepath	Full path of source filename for generator using a template file
	 *	@param		int<0,1>	$hidedetails		Do not show line details
	 *	@param		int<0,1>	$hidedesc			Do not show desc
	 *	@param		int<0,1>	$hideref			Do not show ref
	 *	@return		int<-1,1>						1 if OK, <=0 if KO
	 */
	public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		// phpcs:enable
		global $user, $langs, $conf, $mysoc, $db, $hookmanager;

		if (!is_object($outputlangs)) {
			$outputlangs = $langs;
		}
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (getDolGlobalString('MAIN_USE_FPDF')) {
			$outputlangs->charset_output = 'ISO-8859-1';
		}

		// Load traductions files required by page
		$outputlangs->loadLangs(array("main", "dict", "companies", "bills", "products", "orders", "deliveries"));

		if (is_array($object->lines)) {
			$nblines = count($object->lines);
		} else {
			$nblines = 0;
		}

		if ($conf->product->dir_output) {
			// Definition of $dir and $file
			if ($object->specimen) {
				$dir = $conf->product->dir_output;
				$file = $dir."/SPECIMEN.pdf";
			} else {
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->product->dir_output."/".$objectref;
				$file = $dir."/".$objectref.".pdf";
			}

			if (!file_exists($dir)) {
				if (dol_mkdir($dir) < 0) {
					$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
					return -1;
				}
			}

			if (file_exists($dir)) {
				// Add pdfgeneration hook
				if (!is_object($hookmanager)) {
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager = new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file' => $file, 'object' => $object, 'outputlangs' => $outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('beforePDFCreation', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

				// Create pdf instance
				$pdf = pdf_getInstance($this->format);
				$default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
				$pdf->setAutoPageBreak(false, 0);

				if (class_exists('TCPDF')) {
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
				if (getDolGlobalString('MAIN_ADD_PDF_BACKGROUND')) {
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/' . getDolGlobalString('MAIN_ADD_PDF_BACKGROUND'));
					$tplidx = $pdf->importPage(1);
				}

				$pdf->Open();
				$pagenb = 0;
				$pdf->SetDrawColor(128, 128, 128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Product"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Product"));
				if (getDolGlobalString('MAIN_DISABLE_PDF_COMPRESSION')) {
					$pdf->SetCompression(false);
				}

				// @phan-suppress-next-line PhanPluginSuspiciousParamOrder
				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right
				
				// récupération des données

				$extrafields = new ExtraFields($db);
				$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);

				$description = array();
				foreach($extralabels as $key => $value){
					if (str_contains($key, 'fichemag_') && $key != 'fichemag_marque' && isset($object->array_options['options_' . $key])){
						$description[$value] = $object->array_options['options_' . $key];
					}
				}
				$object->marque = $object->array_options["options_fichemag_marque"];
				$price_ttc = dol_textishtml($object->price_ttc) ? $object->price_ttc : dol_nl2br($object->price_ttc, 1, true);
				$price_ttc = price($price_ttc);
				$barcode = (int) $object->barcode ? (dol_textishtml($object->barcode) ? $object->barcode : dol_nl2br($object->barcode, 1, true)) : false;

				if ($barcode){
					// création du code bar
					$code = $barcode;
					$generator = 'tcpdfbarcode';  // Can be 'tcpdfbarcode', 'phpbarcode', a value provided by an external module, ...
					$encoding = 'EAN13';  // Can be 'QRCODE', 'EAN13', ...
					
					$dirbarcode = array_merge(array("/core/modules/barcode/doc/"), $conf->modules_parts['barcode']);

					$result = 0;
					foreach ($dirbarcode as $reldir) {
						$dir = dol_buildpath($reldir, 0);
						$newdir = dol_osencode($dir);

						// Check if directory exists (we do not use dol_is_dir to avoid loading files.lib.php)
						if (!is_dir($newdir)) {
							continue;
						}

						$result = @include_once $newdir.$generator.'.modules.php';
						if ($result) {
							break;
						}
					}
					// Load barcode class
					$classname = "mod".ucfirst($generator);
					$module = new $classname($db);
					if ($module->encodingIsSupported($encoding)) {
						$result = $module->writeBarCode($code, $encoding, $readable);
					}
				}

				if (getDolGlobalString('PRODUCT_ALLOW_EXTERNAL_DOWNLOAD')) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
					require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';

					$sharekey = getRandomPassword(true);
					$downloadlink = DOL_MAIN_URL_ROOT.'/document.php?hashp='.$sharekey;

					// Paramètres du QR Code
					$width = 35;           // Largeur
					$height = 35;           // Hauteur
					$style = array(
						'border' => 0,
						'vpadding' => 'auto',
						'hpadding' => 'auto',
						'fgcolor' => array(0,0,0),     // Couleur noire
						'bgcolor' => array(255,255,255), // Couleur de fond blanche
						'module_width' => 1,           // Épaisseur des points
						'module_height' => 1           // Hauteur des points
					);
				}
				
				// New page
				$pdf->AddPage();
				if (!empty($tplidx)) {
					$pdf->useTemplate($tplidx);
				}
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->MultiCell(0, 3, ''); // Set interline to 3
				$pdf->SetTextColor(0, 0, 0);

				$tab_top = 42;
				$tab_top_newpage = (!getDolGlobalInt('MAIN_PDF_DONOTREPEAT_HEAD') ? 42 : 10);
				$posy = $pdf->GetY();
	
				$pdf->SetFont('', 'B', $default_font_size+10);

				$pdf->setXY($this->marge_gauche, $posy-13);
				$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 0, dol_htmlentitiesbr('<center><u>'.$object->label.'</u></center>'), $border=0, $align='C', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=true);


				$pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
				$pdf->SetTextColor(0,0,0);

				$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

				$posy += 2;
				// créer le Tableau des composant
				$this->_tableau($pdf, $posy, $this->page_hauteur - $tab_top_newpage, 0, $outputlangs, 1, 0, $description);
				
				// définir la disposition du pied de page
				$model_pied_page = '';
				if (is_string($barcode) && getDolGlobalString('PRODUCT_ALLOW_EXTERNAL_DOWNLOAD')){
					// le code barre + le qr code + le prix
					$model_pied_page = 'full';
				} else if (is_string($barcode) && !getDolGlobalString('PRODUCT_ALLOW_EXTERNAL_DOWNLOAD')){
					// le code barre + le prix
					$model_pied_page = 'barcode';
				} else if (getDolGlobalString('PRODUCT_ALLOW_EXTERNAL_DOWNLOAD') == 1){
					// le qr code + le prix
					$model_pied_page = 'Qr code';
				} else {
					// juste le prix
					$model_pied_page = 'price';
				}

				// Code Barre + Prix
				$pdf->setDrawColor(255, 0 ,0);
				$html_price_ttc = "<h1>" . $price_ttc . "€&nbsp;ttc</h1>";
				$padding_pied_page = 10;

				$pdf->setTextColor(255,0,0);
				$pdf->SetFont('', 'B', $default_font_size+20);
				$hauteur_price = pdfGetHeightForHtmlContent($pdf, dol_htmlentitiesbr($html_price_ttc));
				
				// générer le Code Barre + le prix
				if ($model_pied_page == 'barcode'){
					$logo = $conf->barcode->dir_temp . '/barcode_' . $code . '_' . $encoding . '.png';
					if (is_readable($logo)) {
						// récupéré la hauteur et la largeur de l'image
						$height = pdf_getHeightForLogo($logo)-5;
						$width = ($this->page_largeur - $this->marge_gauche - $this->marge_droite) / 2 - $this->marge_gauche*2 - $this->marge_droite*2 ;

						$posy= $this->page_hauteur - $this->marge_basse - $height - $padding_pied_page;
						$t = $this->page_hauteur - $this->marge_basse - $height - $padding_pied_page * 2;
						$b = $this->page_hauteur - $this->marge_basse;
						$l = $this->marge_gauche * 2;
						$r = $this->page_largeur - $this->marge_droite * 2;
						$c = ($this->page_largeur / 2);
						$longeur_pied_page= $r - $l;
						$pdf->line($c, $b,$c, $t); // line takes a position y in 2nd parameter and 4th parameter

						// générer l'image
						$pdf->Image($logo, $longeur_pied_page/4 + $l - $width /2 , $posy, $width, $height); // width=0 (auto)
					} else {
						$pdf->SetTextColor(200, 0, 0);
						$pdf->SetFont('', 'B', $default_font_size - 2);
						$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
						$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
					}
					$pdf->setXY($c, $t + ($b - $t) / 2 - ($hauteur_price/2));
					$pdf->MultiCell($longeur_pied_page / 2 , 0, dol_htmlentitiesbr($html_price_ttc), $border=0, $align='C', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=true);
				// générer le QR code + le prix
				} else if ($model_pied_page == 'Qr code'){
					$padding_pied_page = 5;
					
					$t = $this->page_hauteur - $this->marge_basse - $height - $padding_pied_page * 2;
					$b = $this->page_hauteur - $this->marge_basse;
					$l = $this->marge_gauche * 2;
					$r = $this->page_largeur - $this->marge_droite * 2;
					$c = $this->page_largeur / 2;
					$longeur_pied_page= $r - $l;
					$pos_x = $longeur_pied_page/4 + $l - $width /2;
					$pos_y = $t + ($b - $t) / 2 - $height/2;
					$pdf->line($c, $b,$c, $t); // line takes a position y in 2nd parameter and 4th parameter

					$pdf->setTextColor(0,0,0);
					$pdf->write2DBarcode($downloadlink, 'QRCODE,H', $pos_x, $pos_y, $w, $height, $style, 'N', false);
					$pdf->setTextColor(255,0,0);
					$pdf->setXY($c, $t + ($b - $t) / 2 - ($hauteur_price/2));
					$pdf->MultiCell($longeur_pied_page / 2 , 0, dol_htmlentitiesbr($html_price_ttc), $border=0, $align='C', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=true);
				// générer le QR code + le Code Barre + le prix
				} else if ($model_pied_page == 'full'){
					$padding_pied_page = 5;
					
					$t = $this->page_hauteur - $this->marge_basse - $height - $padding_pied_page * 2;
					$b = $this->page_hauteur - $this->marge_basse;
					$l = $this->marge_gauche * 2;
					$r = $this->page_largeur - $this->marge_droite * 2;
					$c = $this->page_largeur / 2;
					$longeur_pied_page= $r - $l;
					$pos_x = $longeur_pied_page/4 + $l - $width /2;
					$pos_y = $t + ($b - $t) / 2 - $height/2;
					$pdf->line($c, $b,$c, $t); // line takes a position y in 2nd parameter and 4th parameter

					// Placement du code barre
					$code_barre = $conf->barcode->dir_temp . '/barcode_' . $code . '_' . $encoding . '.png';
					if (is_readable($code_barre)) {
						// définir la hauteur et la largeur de l'image
						if (getDolGlobalString('FICHEMAG_CODE_BARR_STYLE') == 'Style 1'){
							$heightBarCode = $padding_pied_page;
							$widthBarCode = $longeur_pied_page/2 - $padding_pied_page * 4 ;
							$posyBarCode = $b - $heightBarCode - $padding_pied_page ;
							$posxBarCode = $c + $padding_pied_page * 2;
						} else if (getDolGlobalString('FICHEMAG_CODE_BARR_STYLE') == 'Style 2') {
							$heightBarCode = $b - $t;
							$widthBarCode = $longeur_pied_page/2 ;
							$posyBarCode= $t ;
							$posxBarCode = $l;
						} else if (getDolGlobalString('FICHEMAG_CODE_BARR_STYLE') == 'Style 3') {
							$heightBarCode = $b - $t - $padding_pied_page * 2 ;
							$widthBarCode = $padding_pied_page * 1.5;
							$posyBarCode= $t + $padding_pied_page;
							$posxBarCode = $l + $padding_pied_page;
							$img = imagecreatefrompng($code_barre);
							$rotated = imagerotate($img, 90, 0);
							imagejpeg($rotated, $code_barre);
						}
						// générer l'image
						$pdf->Image($code_barre, $posxBarCode, $posyBarCode, $widthBarCode, $heightBarCode);
					} else {
						$pdf->SetTextColor(200, 0, 0);
						$pdf->SetFont('', 'B', $default_font_size - 2);
						$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
						$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
					}

					$pdf->setTextColor(0,0,0);
					$pdf->write2DBarcode($downloadlink, 'QRCODE,H', $pos_x, $pos_y, $w, $height, $style, 'N', false);
					$pdf->setTextColor(255,0,0);
					if (getDolGlobalString('FICHEMAG_CODE_BARR_STYLE') == 'Style 1') {
						$pdf->setXY($c, $t + $padding_pied_page);
					} else if (getDolGlobalString('FICHEMAG_CODE_BARR_STYLE') == 'Style 2') {
						$pdf->setXY($c,$t + ($b - $t) / 2 - $hauteur_price/2);
					} else if (getDolGlobalString('FICHEMAG_CODE_BARR_STYLE') == 'Style 3') {
						$pdf->setXY($c,$t + ($b - $t) / 2 - $hauteur_price/2);
					}
					$pdf->MultiCell($longeur_pied_page / 2 , 0, dol_htmlentitiesbr($html_price_ttc), $border=0, $align='C', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=true);
					
				// générer juste le prix
				} else {
					$height = pdfGetHeightForHtmlContent($pdf, dol_htmlentitiesbr($html_price_ttc));
					$t = $this->page_hauteur - $this->marge_basse - $height - $padding_pied_page * 2;
					$b = $this->page_hauteur - $this->marge_basse;
					$l = $this->marge_gauche * 4;
					$r = $this->page_largeur - $this->marge_droite * 4;
					$c = $this->page_largeur / 2;
					$longeur_pied_page= $r - $l;

					$pdf->setXY($l, $t + ($b - $t) / 2 - ($hauteur_price/2));
					$pdf->MultiCell($longeur_pied_page , 0, dol_htmlentitiesbr($html_price_ttc), $border=0, $align='C', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=true);
				}

				$pdf->line($l, $b, $r, $b); // line takes a position y in 2nd parameter and 4th parameter
				$pdf->line($l, $t, $r, $t); // line takes a position y in 2nd parameter and 4th parameter
				$pdf->line($l, $b, $l, $t); // line takes a position y in 2nd parameter and 4th parameter
				$pdf->line($r, $b, $r, $t); // line takes a position y in 2nd parameter and 4th parameter

				// Contact - Horaire
				$pdf->setTextColor(0,0,0);
				$pdf->SetFont('', 'B', $default_font_size+5);
				$pdf->setDrawColor(128,128,128);
				$contact = "";
				if (!empty(getDolGlobalString("MAIN_INFO_SOCIETE_TEL")) || !empty(getDolGlobalString("MAIN_INFO_SOCIETE_MAIL"))){
					$contact .= !empty(getDolGlobalString("MAIN_INFO_SOCIETE_TEL")) ? "</td></tr><tr><td>" . dol_print_phone(getDolGlobalString("MAIN_INFO_SOCIETE_TEL"), $countrycode = '', $cid = 0, $socid = 0, $addlink = '', $separ = '.') : "";
					$contact .= empty(getDolGlobalString("MAIN_INFO_SOCIETE_TEL")) ? "" : "   ";
					$contact .= getDolGlobalString("MAIN_INFO_SOCIETE_MAIL");
				}
				if (!empty(getDolGlobalString("FICHEMAG_HOURLY_WEEK"))){
					$contact .= "</td></tr><tr><td>".getDolGlobalString("FICHEMAG_HOURLY_WEEK");
				}
				if (!empty(getDolGlobalString("FICHEMAG_HOURLY_WEEK_END"))){
					$contact .= "</td></tr><tr><td>".getDolGlobalString("FICHEMAG_HOURLY_WEEK_END");
				}
				if (!empty(getDolGlobalString("FICHEMAG_ADDITIONAL_INFO"))){
					$contact .= "</td></tr><tr><td>".getDolGlobalString("FICHEMAG_ADDITIONAL_INFO");
				}
				if ($contact != "") {
					$contact = "<table border=0 cellspacing='10' cellpadding='10'><tr><td><u>CONTACT</u> - <u>HORAIRES</u>" . $contact . "</td></tr></table>";

					$posy = $t - pdfGetHeightForHtmlContent($pdf, dol_htmlentitiesbr($contact)) - 3;

					$pdf->SetFont('', 'B', $default_font_size+5);
					$pdf->setXY($this->marge_gauche * 2, $posy);
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche * 2 - $this->marge_droite * 2, 0, dol_htmlentitiesbr($contact), $border=1, $align='C', $fill=false, $ln=1, $x=null, $y=null, $reseth=true, $stretch=0, $ishtml=true);
				}

				// Pied de page
				/*
					$this->_pagefoot($pdf, $object, $outputlangs);
				*/

				$pdf->Close();

				$pdf->Output($file, 'F');

				if (getDolGlobalString('PRODUCT_ALLOW_EXTERNAL_DOWNLOAD')) {
					$relativepath = $object->element.'/'.dol_sanitizeFileName($object->ref);
					$filename = basename($file);

					$ecmfile = new EcmFiles($this->db);
					$result = $ecmfile->fetch(0, '', $relativepath.'/'.$filename);

					if ($result > 0) {
						// la ligne existe déjà (régénération du PDF) -> on met juste à jour le share
						$ecmfile->share = $sharekey;
						$ecmfile->update($user);
					} else {
						// nouvelle ligne
						$ecmfile->filepath = $relativepath;
						$ecmfile->filename = $filename;
						$ecmfile->label = md5_file(dol_osencode($file));
						$ecmfile->fullpath_orig = $file;
						$ecmfile->gen_or_uploaded = 'generated';
						$ecmfile->src_object_type = $object->table_element;
						$ecmfile->src_object_id = $object->id;
						$ecmfile->share = $sharekey;
						$ecmfile->entity = $conf->entity;
						$ecmfile->create($user);
					}
				}

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file' => $file, 'object' => $object, 'outputlangs' => $outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
				if ($reshook < 0) {
					$this->error = $hookmanager->error;
					$this->errors = $hookmanager->errors;
				}

				dolChmod($file);

				$this->result = array('fullpath' => $file);

				return 1; // No error
			} else {
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		} else {
			$this->error = $langs->trans("ErrorConstantNotDefined", "PRODUCT_OUTPUTDIR");
			return 0;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *   Show table for lines
	 *
	 *   @param		TCPDF		$pdf     		Object PDF
	 *   @param		float|int	$tab_top		Top position of table
	 *   @param		float|int	$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y (not used)
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @param		array		$description	Description 
	 *   @return	void
	 */
	protected function _tableau(&$pdf, &$tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0, $description = [])
	{
		global $conf;

		$largeurTableau = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
		// Force to disable hidetop and hidebottom
		$hidebottom = 0;
		if ($hidetop) {
			$hidetop = -1;
		}

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetDrawColor(128, 128, 128);
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFillColor(200,200,200);
		$pdf->SetFont('', '', $default_font_size);

		$cpt = 0;
		$tab_bottom = $tab_top + 1;
		$tab_espacement = 14.5;
		foreach($description as $titre => $caracteristique){
			if ($titre != "" && $caracteristique != ""){
				$text = $caracteristique;
				if (strlen($text)>=40){
					if (str_contains($caracteristique, '.')){
						$text = explode(".", $caracteristique);
						$text = $text[0];
					} else if (str_contains($caracteristique, '!')){
						$text = explode('!', $caracteristique);
						$text = $text[0];
					}
				}
				$pdf->SetFont('','',  $default_font_size + 5);
				if (str_contains(strtolower($titre), 'garantie')){
					$pdf->SetFont('','B',  $default_font_size + 6);
					$pdf->setDrawColor(0, 0, 0);
				}
				
				$pdf->SetXY($this->marge_gauche, $tab_bottom);
				$pdf->Cell($largeurTableau/3,$h=2, $txt=$titre, $border=1, $ln=0, $align='L', $fill=(str_contains(strtolower($titre), 'garantie') ? false : !($cpt%2)));
				
				$pdf->SetXY($this->marge_gauche + $largeurTableau/3, $tab_bottom);
				$pdf->Cell($largeurTableau*2/3, 2, $text, $border=1, $ln=0, $align='L', $fill=(str_contains(strtolower($titre), 'garantie') ? false : !($cpt%2)));
				
				$tab_bottom += $tab_espacement;
				$cpt++;
			}
		}
		
		$pdf->SetDrawColor(128, 128, 128);
		$tab_top = $tab_bottom;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show top header of page.
	 *
	 *  @param	TCPDF		$pdf     		Object PDF
	 *  @param  Product		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @param	string		$titlekey		Translation key to show as title of document
	 *  @return	float|int                   Return topshift value
	 */
	protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $titlekey = "")
	{
		global $conf;

		$ltrdirection = 'L';
		if ($outputlangs->trans("DIRECTION") == 'rtl') {
			$ltrdirection = 'R';
		}

		// Load traductions files required by page
		$outputlangs->loadLangs(array("main", "propal", "companies", "bills", "orders"));

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		if ($object->type == 1) {
			$titlekey = 'ServiceSheet';
		} else {
			$titlekey = 'ProductSheet';
		}

		pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);

		// Show Draft Watermark
		if ($object->status == 0 && getDolGlobalString('PRODUCT_DRAFT_WATERMARK')) {
			pdf_watermark($pdf, $outputlangs, $this->page_hauteur, $this->page_largeur, 'mm', getDolGlobalString('COMMANDE_DRAFT_WATERMARK'));
		}

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$w = 100;

		$posy = $this->marge_haute+5;
		$posx = $this->page_largeur - $this->marge_droite - 100;

		$pdf->SetXY($this->marge_gauche, $posy);

		// Logo
		if (!getDolGlobalInt('PDF_DISABLE_MYCOMPANY_LOGO')) {
			if ($this->emetteur->logo) {
				$logodir = $conf->mycompany->dir_output;
				if (!empty($conf->mycompany->multidir_output[$object->entity])) {
					$logodir = $conf->mycompany->multidir_output[$object->entity];
				}
				if (!getDolGlobalInt('MAIN_PDF_USE_LARGE_LOGO')) {
					$logo = $logodir.'/logos/thumbs/'.$this->emetteur->logo_small;
				} else {
					$logo = $logodir.'/logos/'.$this->emetteur->logo;
				}
				if (is_readable($logo)) {
					$height = pdf_getHeightForLogo($logo);
					$pdf->Image($logo, $this->marge_gauche, $posy, ($this->format[0]-10)/3, 0); // width=0 (auto)
				} else {
					$pdf->SetTextColor(200, 0, 0);
					$pdf->SetFont('', 'B', $default_font_size - 2);
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
				}
			} else {
				$text = $this->emetteur->name;
				$pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($text), 0, $ltrdirection);
			}
		}

		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);

		// Marque of product

		if ($object->marque){
		$marge=10;
		$pdf->SetFont('', 'B', $default_font_size + 15);
		$pdf->setCellPadding(2);
		$pdf->SetXY($this->format[0]*7/10-$pdf->GetStringWidth($object->marque)/2-$marge, $posy);
		$pdf->MultiCell($pdf->GetStringWidth($object->marque)+$marge*2, 8, $object->marque, 1, 'C');
		}
		$posy += 18;

		// Prévention prix fluctue
		$Message_Top = "<p style='margin:20px;'>ATTENTION, COMPTE TENU DES PERTURBATIONS ACTUELLES DU MARCHÉ INFORMATIQUE, LE PRIX INDIQUÉ SUR CETTE FICHE <u>N'EST PAS FIXE POUR UNE DURÉE INDÉTERMINÉE</u>. <u style='color:red;'>LE PRIX QUI S'APPLIQUE EST CELUI AFFICHÉ EN MAGASIN LE JOUR DE LA VENTE</u>.</p>";

		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->setCellPadding(4);
		$pdf->writeHTMLCell(0, 0, $this->format[0]*5/12, $posy, dol_htmlentitiesbr($Message_Top), 1, 'L');
		
		$posy += 3;
		$pdf->SetFont('', '', $default_font_size - 1);

		// Show list of linked objects
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);

		$pdf->SetTextColor(0, 0, 0);

		return 0;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show footer of page. Need this->emetteur object
	 *
	 *  @param	TCPDF		$pdf     			PDF
	 *  @param	Product		$object				Object to show
	 *  @param	Translate	$outputlangs		Object lang for output
	 *  @param	int			$hidefreetext		1=Hide free text
	 *  @return	int								Return height of bottom margin including footer text
	 */
	protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		$showdetails = getDolGlobalInt('MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS', 0);
		return pdf_pagefoot($pdf, $outputlangs, 'PRODUCT_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext);
	}
}
