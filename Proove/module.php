<?

require_once(__DIR__ . "/../TellstickUtil.php");  
require_once(__DIR__ . "/../Logging.php");

class ProoveThermometerHygrometer extends IPSModule
{

    
    public function Create()
    {
        parent::Create();
        $this->ConnectParent("{655884D6-7969-4DAF-8992-637BEE9FD70D}");
		
		//$this->RegisterPropertyInteger ("model", 0 );
		$this->RegisterPropertyInteger ("id", 0 );
		$this->RegisterPropertyInteger ("timeout", 2 );
		$this->RegisterPropertyBoolean ("log", false );
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->RegisterVariableFloat( "Temperature", "Temperature", "~Temperature", 0 );
		//$this->RegisterVariableInteger( "Last", "Last", "", 2 );
		//IPS_SetHidden($this->GetIDForIdent('Last'), true);
		
		//.*;protocol:fineoffset;id:\d*;model:temperature;.*
		
		$id = $this->ReadPropertyInteger("id");
		$this->SetReceiveDataFilter(".*;protocol:fineoffset;id:".$id.";model:temperature.*;.*");
		
    }
	
    public function ReceiveData($JSONString) {
		$data = json_decode($JSONString);
        $message = utf8_decode($data->Buffer);
        
        $log = new Logging($this->ReadPropertyBoolean("log"), IPS_Getname($this->InstanceID));
        
        $log->LogMessage("Received ".$message);
        
        if($data->DataID!="{F746048C-AAB6-479D-AC48-B4C08875E5CF}") {
        	$log->LogMessage("This is not for me! (unsupported GUID in DataID)");
        	return;
        }

        $protocol = GetParameter("protocol", $message);

		if(stripos($protocol, "fineoffset")!==false) {
			//$decodedMessage = DecodeFineOffset($message);
			//$log->LogMessage("Decoded message: ".$decodedMessage);
			$log->LogMessage("Analyzing the message and updating values...");
		} else {
			$log->LogMessage("This is not for me! (unsupported protocol: ".$protocol.")");
			return;
		}
	
		//if(strlen($decodedMessage)>0) {
		if(strlen($message)>0) {
			//$model = GetParameter("model", $decodedMessage);
			//$id = intval(GetParameter("id", $decodedMessage));
			
			$model = GetParameter("model", $message);
			$id = intval(GetParameter("id", $message));
			
			$log->LogMessage("Received command from: ".$model.":".$id);
			
			$myId = $this->ReadPropertyInteger("id");
			
			if($myId==$id) {
				$interval = $this->ReadPropertyInteger("timeout");
				$now = time();
						
				$lastProcessed = intval($this->GetBuffer("LastProcessed"));
				if($lastProcessed+$interval<$now) {
					
					$temperature = GetParameter("temp", $message);
					SetValueFloat($this->GetIDForIdent("Temperature"), $temperature);
					$log->LogMessage("The temperature value was set to ".$temperature);
					
					if($model=="temperaturehumidity") {
						$humidity = GetParameter("humidity", $message);
						$humidityId = $this->GetIDForIdent("Humidity");
						if($humidityId==false)
							$humidityId= $this->RegisterVariableInteger( "Humidity", "Humidity", "~Humidity", 1 );
						
						SetValueInteger($humidityId, $humidity);
					}
					
					$this->SetBuffer("LastProcessed", $now);
					
				} else
					$log->LogMessage("To many messages in the last ".$interval." seconds. Skipping the message");
			} else 
				$log->LogMessage("This is not me!"); 
	
		} else {
			$log->LogMessage("Unsupported model");
		}
    }
}

?>
