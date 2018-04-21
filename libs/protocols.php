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



?>
