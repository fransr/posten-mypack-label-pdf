<?php
	require('fnc.php');
	$data = array();
	$data['address']['name'] = 'Frans Rosén';
	$data['address']['address'] = 'Mälarvarvsbacken 8';
	$data['address']['address2'] = '';
	$data['address']['countryCode'] = 'SE';
	$data['address']['zipCode'] = '117 33';
	$data['address']['city'] = 'STOCKHOLM';
	#$data['firstBarcode'] = '421752441399013000123456719';
	$data['firstBarcode'] = '42120862309011012345678919';
	//)421=2086230)90=11012345678919
	$data['shipmentItemId'] = '6919 014 952 5SE';
	$data['date'] = "2013-01-01";
	$data['largeLetter'] = '3';
	$data['content'] = 'Visitkort';
	$data['reference'] = '222114';
	$data['shipmentId'] = '';
	$data['information'] = '';
	$data['phone'] = '';
	$data['contact'] = 'Christina Dahlin';
	$data['service'] = 'DPD Företagspaket 16.00';
	$data['additionalServices'][0] = "A1 - COD";
	$data['additionalServices'][1] = "OPTIONAL SERVICE POINT";
	$data['serviceCode'] = '15';
	$data['items'] = '1 / 1';
	$data['weight'] = '1,00';
	$data['packageCode'] = '';
	$data['routing'] = 'ALF'; //will change the design!
	$data['codAmount'] = 'SEK 300,00'; //will change the design!
	$data['codAccount'] = '432423423';
	$data['issuerCustomerNo'] = "13 / 123 456 7";
	$data['servicePointAddress']['name'] = "Rimi Bekkestua";
	$data['servicePointAddress']['address'] = "Gamle Ringeriksv 35a";
	$data['servicePointAddress']['address2'] = "";
	$data['servicePointAddress']['countryCode'] = 'NO';
	$data['servicePointAddress']['zipCode'] = '1357';
	$data['servicePointAddress']['city'] = 'BEKKESTUA';
	$data['fromAddress']['name'] = "Company AB";
	$data['fromAddress']['address'] = "Exempelvägen 1";
	$data['fromAddress']['address2'] = "";
	$data['fromAddress']['countryCode'] = 'SE';
	$data['fromAddress']['zipCode'] = '111 34';
	$data['fromAddress']['city'] = 'STOCKHOLM';
	$data = (json_encode($data));
	
	
	if(!empty($_POST['payload'])) {
		pdf(($_POST['payload']));
	}
?>
<form action="" method="post">
<textarea name="payload">
<?=$data?>
</textarea>
<button type="submit">OK</button>
</form>