<?

function GetParameter($Parameter, $Message) {
	$arr = explode (";", $Message);
	$max = sizeof($arr);

	for($i=0;$i<$max;$i++){
	   if(stripos($arr[$i], $Parameter.":")!==false){
			break;
	   }
	}

	if($i<$max){
		$startPos = stripos($arr[$i], ":")+1;
		$value = substr($arr[$i], $startPos);
		return $value;
	} else {
	   return "";
	}

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

function DecodeOregon($Message) {
/*
	$model = GetParameter("model", $Message);
	$data = GetParameter("data", $Message);

	if(stripos($model, "0xF824")!==false)
		return DecodeOregonF824($data);
*/
}

function DecodeOregonF824($data) {
/*
	//IPS_LogMessage("Tellstick 2","Decoding Oregon model F824 with data: ".$data);

	$arr=str_split($data);

	$crcCheck = $arr[13];
	$messageChecksum1 = HexDec($arr[12]);
	$messageChecksum2 = HexDec($arr[11]);
	$unknown = HexDec($arr[10]);
	$hum1 = HexDec($arr[9]);
	$hum2 = HexDec($arr[8]);
	$neg = HexDec($arr[7]);
//	$neg = $arr[7];
	$temp1 = HexDec($arr[6]);
	$temp2 = HexDec($arr[5]);
	$temp3 = HexDec($arr[4]);
	$battery = HexDec($arr[3]); //PROBABLY battery
	$checksum = HexDec($arr[1]) + HexDec($arr[2]);
	$rollingcode = $checksum;
	$channel = HexDec($arr[0]);

	//IPS_LogMessage("Tellstick 2","Neg: ".$neg);

	$checksum += $unknown + $hum1 + $hum2 + $neg + $temp1 + $temp2 + $temp3 + $battery + $channel + 0xF + 0x8 + 0x2 + 0x4;

	if ((($checksum >> 4) & 0xF) != $messageChecksum1 || ($checksum & 0xF) != $messageChecksum2){
		//checksum error
		return "";
	}

	$temperature = (($temp1 * 100) + ($temp2 * 10) + $temp3)/10.0;
	if ($neg>0) {
		$temperature *= -1;
	}
	$humidity = ($hum1 * 10.0) + $hum2;

	$decoded = "class:sensor;protocol:oregon;model:F824;id:".$rollingcode.";temp:".$temperature.";humidity:".$humidity.";";

//  	IPS_LogMessage("Tellstick 2", "Decoded Oregon message: ".$decoded);

	return $decoded;
*/
}

function DecodeFineOffset ($Data) {
	/*
	$data = GetParameter("data", $Data);
	
	if(strlen($data)<8)
		return "";
	
	// CRC
	$crc = HexDec(substr($data, strlen($data)-2));
	
	$data = substr($data, 0, strlen($data) -2);
	
	$humidity = HexDec(substr($data, strlen($data)-2));
	
	$data = substr($data, 0, strlen($data) -2);
	
	$value = HexDec(substr($data, strlen($data)-3));
	$temperature = $value/10;
	
	$value >>= 11;
	if($value & 1)
		$temperaure *= -1;
		
	$data = substr($data, 0, strlen($data)-3);
	
	$id = HexDec($data) & 0xFF;
	
	$decoded = "class:sensor;protocol:fineoffset;id:".$id.";model:";
	
	if($humidity<=100)
		$decoded .= "temperaturehumidity;humidity:".$humidity.";";
	else if($humidity==255)
		$decoded .= "temperature;";
	else
		return "";
	
	$decoded .= "temp:".$temperature.";";
	
	return $decoded;
	
	*/
}



?>
