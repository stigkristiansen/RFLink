<?

require_once(__DIR__ . "/../libs/protocols.php");  
require_once(__DIR__ . "/../libs/logging.php");

class ProoveThermometerHygrometer extends IPSModule
{

    
    public function Create()
    {
        parent::Create();
        $this->ConnectParent("{655884D6-7969-4DAF-8992-637BEE9FD70D}");
		
		$this->RegisterPropertyInteger ("id", 0 );
		$this->RegisterPropertyBoolean ("log", false );
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

		$log = new Logging($this->ReadPropertyBoolean("log"), IPS_Getname($this->InstanceID));
		
        $this->RegisterVariableFloat( "Temperature", "Temperature", "~Temperature", 0 );
				
		$id = $this->ReadPropertyInteger("id");
		if($id>0) {
			$receiveFilter = ".*[0-9A-Fa-f]{2};[0-9A-Fa-f]{2};FineOffset;ID=0{0,}(".strtolower(dechex($id))."|".strtoupper(dechex($id)).");TEMP=.*";
		} else 
			$receiveFilter = ".*[0-9A-Fa-f]{2};[0-9A-Fa-f]{2};FineOffset;ID=[0-9A-Fa-f]{4};TEMP=.*";
		
		$log->LogMessage("ReceiveDataFilter set to ".$receiveFilter);
		$this->SetReceiveDataFilter($receiveFilter);
    }
	
    public function ReceiveData($JSONString) {
		$data = json_decode($JSONString);
        $message = utf8_decode($data->Buffer);
        
        $log = new Logging($this->ReadPropertyBoolean("log"), IPS_Getname($this->InstanceID));
        
        $log->LogMessage("Received ".$message);
        
        if($data->DataID!="{C466EF5C-68FD-4B48-B833-4D65AFF90B12}") {
        	$log->LogMessage("This is not for me! (unsupported GUID in DataID)");
        	return;
        }
		
		$dataArray = Explode(";", $message);
        $protocol = $dataArray[2];

		if(stripos($protocol, "fineoffset")!==false) {
			$log->LogMessage("Analyzing the message and updating values...");
		} else {
			$log->LogMessage("This is not for me! (unsupported protocol: ".$protocol.")");
			return;
		}
		
		$id = hexdec(GetParameter("id", $message));
		$log->LogMessage("Received message from a device with id ".$id);
		$myId = $this->ReadPropertyInteger("id");
		
		if($myId==$id) {
			$log->LogMessage("This is my Id");
			
			$temperature = ConvertTemperature(GetParameter("temp", $message));
			SetValueFloat($this->GetIDForIdent("Temperature"), $temperature);
			$log->LogMessage("The temperature value was set to ".$temperature);
			
			$humidity = GetParameter("hum", $message);
			if(strlen($humidity)>0) {
				$humidityId = $this->GetIDForIdent("Humidity");
				if($humidityId==false)
					$humidityId= $this->RegisterVariableInteger( "Humidity", "Humidity", "~Humidity", 1 );
				
				SetValueInteger($humidityId, $humidity);
				$log->LogMessage("The humidity value was set to ".$humidity);
			}
		} elseif($myId>0)
			$log->LogMessage("Wrong Id. This is not me!"); 
    }
}

?>
