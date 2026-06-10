<?php

require $_SERVER['DOCUMENT_ROOT'] . '/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

class LabelPrint
{
    #### format / meta data
    private Array $labelFormat;
    private string $labelUnit;
    private string $file;

    private $font_family = "helvetica";
    private $font_style = "B";
    private $font_size=20;

    #### content:
    private string $title;
    // private string $ContentHTML;
    
    public function __construct(string $file)
    {
        $this->labelFormat = array(595.276,  841.890);
        $this->labelUnit = 'mm';
        $this->file = $file;
    }
    
    public function printLabel(){

        $pdf = pdf_getInstance($this->labelFormat, $this->labelUnit);
        $pdf->AddPage();
        $pdf->SetFont($this->font_family, $this->font_style , $this->font_size);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetXY(0, 0);
        $pdf->addHTMLCell($this->ContentHTML, true, false, true, false, '');
        $pdf->Output($this->file, 'F');

    }

    public function setContentHTML(string $html){
        $this->ContentHTML = $html;
    }
    public function setTitle(string $Title){
        $this->title = $Title;
    }
}
?>