<?php

require $_SERVER['DOCUMENT_ROOT'] . '/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';
require_once(DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php');

class LabelPrint
{
    public $labelFormat;
    public $labelUnit;
    public $file;
    
    public function __construct($file)
    {
        $this->labelFormat = array(70, 30);
        $this->labelUnit = 'mm';
        $this->file = $file;
    }
    
    public function printLabel(){

        $pdf =  pdf_getInstance($this->labelFormat, $this->labelUnit);
        $pdf->AddPage();
        $pdf->SetFont('', '' , 10);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetXY(0, 0);
        $pdf->Cell(0, 0, 'Hello World');
        $pdf->Output($this->file, 'F');

    }
}
?>