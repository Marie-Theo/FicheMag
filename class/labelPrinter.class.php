<?php

require $_SERVER['DOCUMENT_ROOT'] . '/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
// require_once DOL_DOCUMENT_ROOT.'/includes/tecnickcom/tcpdf/tcpdf.php';

class LabelPrint
{

    private $pdf = null;

    #### format / meta data
    private Array $labelFormat;
    private string $labelUnit;
    private string $file;

    private $font_family = "helvetica";
    private $font_style = "B";
    private $font_size=100;

    #### content:
    // private string $ContentHTML;
    private string $Marque;
    private string $Logo;
    private string $Message_Top;
    private Array $Proprieter;
    private Array $ContactHoraire;
    private string $CodeBar;
    private string $QRCode;
    private string $Prix;



    ####
    public function __construct(string $file)
    {
        $this->labelFormat = array(595.276,  841.890);
        $this->labelUnit = 'mm';
        $this->file = $file;

        $this->pdf = pdf_getInstance($this->labelFormat, $this->labelUnit);;

        // $this->pdf->setCreator('tc-lib-pdf');
        // $this->pdf->setAuthor('Nicola Asuni');
        // $this->pdf->setSubject('tc-lib-pdf example: 031');
        // $this->pdf->setTitle('HTML Features Example');
        // $this->pdf->setKeywords('TCPDF tc-lib-pdf example HTML selectors forms table tags');
        // $this->pdf->setPDFFilename('031_html_features.pdf');
    }

    
    public function printLabel(){

        $this->pdf = pdf_getInstance($this->labelFormat, $this->labelUnit);
        $this->pdf->AddPage();
        $this->pdf->SetFont($this->font_family, $this->font_style , $this->font_size);
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->SetAutoPageBreak(false);
        if (isset($this->Logo)){
            $this->pdf->SetXY(25, 25);
            $this->pdf->Image($this->Logo, $x=20, $y=20, ($this->labelFormat[0]-10)/3);
        }
        if (isset($this->Marque)){
            $marge=12;
            $this->pdf->SetXY($this->labelFormat[0]*2/3-strlen($this->Marque)*$marge-$marge, 35);
            $this->setFontSize(100);
            $this->pdf->MultiCell(strlen($this->Marque)*($marge*2)+$marge*2, 0, $this->Marque, 1, 'C');
        }
        if (isset($this->Message_Top)){
            $this->setFontSize(23);
            $this->setFontStyle('');
            $this->pdf->setCellPadding(4);
    
            $this->pdf->writeHTMLCell($this->labelFormat[0]/2, 0, $this->labelFormat[0]*5/12, 90, $this->Message_Top, 1, 'L');
        }
        // $this->pdf->writeHTMLCell(0, 0, 0, 0, $this->ContentHTML);
        $this->pdf->Output($this->file, 'F');

    }

    // public function setContentHTML(string $html){
    //     $this->ContentHTML = $html;
    // }
    public function setFontFamily(string $font_family){
        $this->font_family = $font_family;
        if (isset($this->pdf)){
            $this->pdf->SetFont($this->font_family, $this->font_style , $this->font_size);
        }
    }
    public function setFontStyle(string $font_style){
        $this->font_style = $font_style;
        if (isset($this->pdf)){
            $this->pdf->SetFont($this->font_family, $this->font_style , $this->font_size);
        }
    }
    public function setFontSize(int $font_size){
        $this->font_size = $font_size;
        if (isset($this->pdf)){
            $this->pdf->SetFont($this->font_family, $this->font_style , $this->font_size);
        }
    }
    
    public function setMarque(string $Marque){
        $this->Marque = $Marque;
    }
    public function setLogo(string $Logo){
        $this->Logo = $Logo;
    }
    public function setMessage_Top(string $Message_Top){
        $this->Message_Top = $Message_Top;
    }
    public function setProprieter(array $Proprieter){
        $this->Proprieter = $Proprieter;
    }
    public function setContactHoraire(array $ContactHoraire){
        $this->ContactHoraire = $ContactHoraire;
    }
    public function setCodeBar(string $CodeBar){
        $this->CodeBar = $CodeBar;
    }
    public function setQRCode(string $QRCode){
        $this->QRCode = $QRCode;
    }
    public function setPrix(string $Prix){
        $this->Prix = $Prix;
    }

}
?>