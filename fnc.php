<?php
function pdfAddress($data, $key) {
	return @$data[$key]["name"]."\r\n".
	@$data[$key]["address"]."\r\n".
	@$data[$key]["address2"]."\r\n".
	@$data[$key]["countryCode"]."-".@$data[$key]["zipCode"]." ".@$data[$key]["city"];
}
function pdf($json_in) {
	$debug = false;
	require('tcpdf/tcpdf.php');
	$data = json_decode($json_in, true);
	if(json_last_error()) {
		var_dump("Json error: ".json_last_error());
		die();
	}
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Frans Rosén');
	$pdf->SetTitle('Posten');
	$pdf->SetSubject('Posten');
	$pdf->SetKeywords('Posten');

	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(109.4, 72.5, 6.5);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setFontSubsetting(true);
	$pdf->AddPage();

	/*
	 * FIRST, CREATE SKELLETON!
	*/

	$pdf->SetFont('freesans', '', 6.5, '', true);
	$pdf->setCellHeightRatio(1.0);
	$css = "<style>
	span.title { font-size: 7.6px; line-height: 11.3px; }
	span.biggertitle { font-size: 10px; line-height: 10px; }
	.col1 { width: 39%; }
	.col2 { width: 36%; }
	.col3 { width: 25%; }
	.bigaddress { font-size: 15px; line-height: 16px; }
	.break { line-height: 11.3px; }
	.service { font-size: 15px; line-height: 15px; font-weight: 300; }
	.smallinfo { line-height: 11.5px; }
	.customer-no { font-size: 12px; font-weight: bold; }
	.additional-services { font-size: 13px; }
	.biggerinfo td { font-size: 13px; line-height: 14px; }
	table {
	</style>";
	$header = $css.'
<table cellspacing="0" cellpadding="0"><tr>
<td class="col1" style="height: 55px;"><span class="title">From:</span></td>
<td class="col2"></td>
<td class="col3" align="right"><span class="title" style="line-height: 10px;">ShipWallet 1.0<br /></span><div><img src="data/PostNord_Logistics.png" width="75" /></div></td>
</tr></table>
<table cellspacing="0" cellpadding="0"><tr>
<td class="col1" style="height: 15px;"><span class="title">Phone:</span>&nbsp;&nbsp;&nbsp;'.@$data['phone'].'</td>
<td class="col2"><span class="title">Contact:</span>&nbsp;&nbsp;&nbsp;'.@$data['contact'].'</td>
<td class="col3"><span class="title">Date:</span>&nbsp;&nbsp;&nbsp;'.@$data['date'].'</td>
</tr></table>';

$header_y = $pdf->GetY();
$pdf->writeHTMLCell(0, 0, '', '', $header, array('B' => array('width' => 0.55, 'color' => array(0,0,0))), 1, 0, true, '', false);

$pdf->SetFont('freesans', '', 7.8, '', true);
$address_title = @$data['servicePointAddress']?'Service Point':'To';
$address_y = $pdf->GetY();
$address = <<<EOD
{$css}
<table cellspacing="0" cellpadding="0"><tr>
<td class="col1" style="height: 88px;"><span class="title">{$address_title}:<br /></span></td>
<td class="col2"></td>
<td class="col3"></td>
</tr></table>
<table cellspacing="0" cellpadding="0"><tr>
<td class="col1"><span class="title">Phone:</span>&nbsp;&nbsp;&nbsp;</td>
<td class="col2"><span class="title">Contact:</span>&nbsp;&nbsp;&nbsp;</td>
<td class="col3"><span class="title"><!--Entry Code:--></span>&nbsp;&nbsp;&nbsp;</td>
</tr></table>
EOD;

$pdf->writeHTMLCell(0, 46, '', $pdf->GetY()+0.5, $address, array('L' => array('width' => 3, 'color' => array(!$debug?255:0,255,255)), 'B' => array('width' => 0.55, 'color' => array(0,0,0))), 1, 0, true, '', false);
$service_start_y = $pdf->GetY();

$routing_table = @$data['routing']?'<td>&nbsp;<br />Routing:</td>':'';
$service = $css.'
<table cellspacing="0" cellpadding="0"><tr>
<td colspan="3" height="33px;"><span class="biggertitle">Service:</span><span class="service"><strong><br />'.@$data['service'].'</strong></span></td>
</tr>
</table>
<table cellspacing="0" cellpadding="0" class="smallinfo"><tr>
<td style="width: 30%;">
	Issuer/Customer No:<br />
	Additional Service(s):
</td>
<td style="width: 45%;">
	<span class="customer-no">'.@$data['issuerCustomerNo'].'</span>
</td>
'.$routing_table.'
</tr></table>';

$pdf->writeHTMLCell(0, 34, '', $pdf->GetY()+0.5, $service, false, 1, 0, true, '', false);
$service_y = $pdf->GetY();

if($debug) $pdf->SetFillColor(255, 235, 235);

$has_cod_amount = @$data['codAmount'];

if($has_cod_amount) {
	$infotable = $css.'
<table cellspacing="0" cellpadding="0" class="smallinfo"><tr>
	<td width="22%">COD Amount:</td>
	<td><strong>'.@$data['codAmount'].'</strong></td>
</tr><tr>
	<td>COD Account:</td>
	<td>'.@$data['codAccount'].'</td>
</tr><tr>
	<td>Reference:</td>
	<td>'.@$data['reference'].'</td>
</tr><tr>
	<td>Shipment-ID:</td>
	<td>'.@$data['shipmentId'].'</td>
</tr><tr>
	<td>Information:</td>
	<td>'.@$data['information'].'</td>
</tr></table>';
} else {
	$infotable = $css.'
<table cellspacing="0" cellpadding="0" class="smallinfo"><tr>
	<td width="22%">Contents:</td>
	<td>'.@$data["content"].'</td>
</tr><tr>
	<td>Reference:</td>
	<td>'.@$data["reference"].'</td>
</tr><tr>
	<td>Shipment-ID:</td>
	<td>'.@$data["shipmentId"].'</td>
</tr><tr>
	<td>Information:</td>
	<td>'.@$data["information"].'</td>
</tr></table>';
}
if($has_cod_amount)
	$pdf->writeHTMLCell(0, 19.3, '', $pdf->GetY()-1, $infotable, false, 1, $debug, true, '', false);
else
	$pdf->writeHTMLCell(0, 16.15, '', $pdf->GetY()+2, $infotable, false, 1, $debug, true, '', false);

$bottom_service = $css.'
<table cellspacing="0" cellpadding="0" class="biggerinfo"><tr>
<td class="col1" style="width: 34%"><span class="biggertitle">Package Code:</span><br />'.@$data['packageCode'].'</td>
<td class="col2" style="width: 30%;"><span class="biggertitle">Items:</span><br />'.@$data['items'].'</td>
<td class="col3" style="width: 36%;"><span class="biggertitle">Weight (kg):</span><br />'.@$data['weight'].'</td>
</tr></table>';
$pdf->writeHTMLCell(0, 10, '', $pdf->GetY()+0.5, $bottom_service, array('B' => array('width' => 0.55, 'color' => array(0,0,0))), 1, 0, true, '', false);
$base_x = $pdf->GetX();
$base_y = $pdf->GetY();

/*
 * NOW BACK UP TO THE SAVED Y-COORDINATES AND FILL WITH MULTICELLS that SCALE OR SKIP WHEN INFO IS TOO BIG.
*/

$pdf->SetFont('freesans', '', 7.8, '', true);
$pdf->setCellHeightRatio(1.1);
$pdf->SetY($header_y+3);
if($debug) $pdf->SetFillColor(255, 235, 235);
$pdf->MultiCell(68, 12.9, pdfAddress($data, 'fromAddress')."\n", 0, '', $debug, 1, '', '', true, 0, false, true, 12.6, 'T', true);

/*
 * LARGE BIG LETTERZ ON THE HEADER SIDE
*/
if(@$data['largeLetter']) {
	$pdf->SetFont('freesans', 'b', 42, '', true);
	if($debug) $pdf->SetFillColor(255, 235, 0);
	$pdf->MultiCell('', 10, $data['largeLetter'], 0, '', $debug, 0, $pdf->GetX()+63, $pdf->GetY()+8, false);
}

/*
 * HERE'S THE BIG ADDRESS!
*/

$to_address = pdfAddress($data, 'address');

$pdf->SetFont('freesans', '', 11, '', true);
$pdf->setCellHeightRatio(1.2);
$address = @$data['servicePointAddress'] ? pdfAddress($data, 'servicePointAddress') : pdfAddress($data, 'address');
$pdf->SetY($address_y+3);
if($debug) $pdf->SetFillColor(255, 235, 235);
$pdf->MultiCell(90, 25, $address."\n\n", 0, '', $debug, 1, $pdf->GetX()+0.5, '', true, 0, false, true, 23, 'T', true);

/*
 * THIS IS THE ADDRESS TO THE CUSTOMER, WITH THE EXCLAMATION MARK?!
 * ONLY IF SERVICE POINT ADDRESS EXISTS!
*/

if(@$data['servicePointAddress']) {
	$pdf->SetY($pdf->GetY()+1);
	$x = $pdf->GetX()+4;
	$pdf->Image('data/Exclamation_Mark.png', $pdf->GetX()+1, '', 2.5, 0);
	$pdf->SetFont('freesans', 'b', 6.5, '', true);
	$pdf->MultiCell('', '', 'TO:', 0, '', false, 1, $x, '');
	$pdf->setCellHeightRatio(1.2);
	$pdf->SetFont('freesans', 'b', 10, '', true);
	//print_r($data);
	//die($address);
	if($debug) $pdf->SetFillColor(255, 235, 235);
	$pdf->MultiCell(86, 17, pdfAddress($data, 'address')."\n\n", '', '', $debug, 1, $x, '', true, 0, false, true, 17, 'T', true);
}


/*
 * HERE'S THE ADDITIONAL SERVICES!
*/
$pdf->SetFont('freesans', '', 10, '', true);
$services = @$data['additionalServices'];
if(is_array($services)) $services = implode("\n", $services);
if($debug) $pdf->SetFillColor(255, 235, 235);
$pdf->MultiCell(86, 20, $services."\n\n", '', '', $debug, 1, '', $service_y-17.8, true, 0, false, true, 20, 'T', true);

$after_additional_x = $pdf->GetX();
$after_additional_y = $pdf->GetY();
/*
 * BOX FOR NUMBER ON SERVICE!
*/

if(@$data['serviceCode']) {
	$pdf->SetFillColor(255, 255, 255);
	$pdf->SetFont('freesans', 'b', 14, '', true);
	$pdf->setCellHeightRatio(1.3);
	$pdf->MultiCell(16, 6, @$data['serviceCode'], array('TRBL' => array('width' => 0.5, 'color' => array(0, 0, 0))), 'C', true, 0, $pdf->GetX()+78, $service_start_y+2);
}

/*
 * LARGE BIG ROUTING ON THE SERVICE SIDE
*/

if(@$data['routing']) {
	$pdf->SetFont('freesans', 'b', 32, '', true);
	if($debug) $pdf->SetFillColor(255, 235, 0);
	$pdf->MultiCell('', 10, $data['routing'], 0, '', $debug, 0, $after_additional_x+69, $after_additional_y-22, false);
}

/*
 * IMAGE FOR COD AND STUFF!
*/
if($has_cod_amount) {
	$pdf->Image('data/COD.png', $after_additional_x+65, $after_additional_y-5, 30, 0);
}

/*
 * IMAGE FOR WEIGHT! DIFFERENT TYPES DEPENDING ON SIZE
*/
$pdf->Image('data/0-15kg.png', $after_additional_x+80, $after_additional_y+13, 11.5, 0);


$style = array(
		'position' => '',
		'align' => 'C',
		'stretch' => true,
		#'fitwidth' => true,
		'cellfitalign' => '',
		#'border' => array('B' => array('width' => 1, 'color' => array(0,0,0))),
		#'hpadding' => 'auto',
		'vpadding' => 'auto',
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => false,
		'font' => 'helvetica',
		'fontsize' => 8,
);

$pdf->SetX($base_x);
$pdf->SetY($base_y);
$pdf->Ln(3);
$pdf->write1DBarcode(@$data['firstBarcode'], 'C128', '', '', '', 13.5, 0.4, $style, 'N');
$pdf->Ln(2);
$pdf->SetFont('freesans', '', 1, '', true);
$pdf->MultiCell('', 0.5, ' ', array('B' => array('width' => 0.5, 'color' => array(0, 0, 0))), 'C', true, 1, '', '');
$pdf->Ln(2.8);
$pdf->write1DBarcode(str_replace(" ", "", @$data['shipmentItemId']), 'C128', $pdf->GetX()+13, '', 68, 32, 0.4, $style, 'N');
$pdf->SetFont('freesans', '', 7.8, '', true);
$pdf->Ln(1);
$pdf->MultiCell(26, 5, 'Shipment Item-ID:', 0, 'L', $debug, 0, $base_x+18, '', false);
$pdf->SetFont('freesans', 'b', 10, '', true);
$pdf->MultiCell(55, 5, @$data['shipmentItemId'], 0, 'L', 0, 0, $pdf->GetX(), $pdf->GetY()-0.5, false);
$pdf->Ln();

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('label.pdf', 'I');

die;
}