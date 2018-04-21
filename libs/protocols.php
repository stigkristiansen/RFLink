<?

function GetParameter($Parameter, $Message) {
	$arr = explode (";", $Message);
	$max = sizeof($arr);

	for($i=0;$i<$max;$i++){
	   if(stripos($arr[$i], $Parameter."=")!==false){
			break;
	   }
	}

	if($i<$max){
		$startPos = stripos($arr[$i], "=")+1;
		$value = substr($arr[$i], $startPos);
		return $value;
	} else {
	   return "";
	}

}

function ConvertTemperature($Value) {
	$temp = (hexdec($Value) & 0x00FF)/10;
	if(($Value & 0x8000)==0x8000)
		$temp *= -1;
	
	return $temp;
}

function DecodeNexa($Message){

	/*$model = GetParameter("model", $Message);
  	$data = GetParameter("data", $Message) + 0;

	if(stripos($model, "selflearning")!==false)
	   return DecodeNexaSelflearning($data);
	else
	   return DecodeNexaCodeSwitch($data);
*/
	
}


function DecodeNexaSelflearning($Data) {
//  	IPS_LogMessage("Tellstick 2","Decoding Nexa Selflearning");

  	//$Data = GetParameter("data", $Message) + 0;
/*
  	$house = $Data & 0xFFFFFFC0;
	$house >>= 6;

	$group = $Data & 0x20;
	$group >>= 5;

	$method = $Data & 0x10;
	$method >>= 4;

	$unit = $Data & 0xF;
	$unit++;

	$stringMethod ="";
	if($method==1)
	   $stringMethod="turnon";
	else if ($method==0)
	   $stringMethod="turnoff";


   $decoded = "class:command;protocol:arctech;model:selflearning;house:".$house.";unit:".$unit.";group:".$group.";method:".$stringMethod;

//  	IPS_LogMessage("Tellstick 2", "Decoded Nexa message: ".$decoded);

  	return $decoded;
	
	*/
}

function DecodeNexaCodeSwitch($Message) {
//	IPS_LogMessage("Tellstick 2","Decoding Nexa Code Switch");
}


?>
