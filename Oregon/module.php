<?

require_once(__DIR__ . "/../TellstickUtil.php");  
require_once(__DIR__ . "/../Logging.php");

class OregonWeatherStation extends IPSModule
{

    
    public function Create()
    {
        parent::Create();
        $this->ConnectParent("{655884D6-7969-4DAF-8992-637BEE9FD70D}");
		
		$this->RegisterPropertyInteger ("model", 0 );
		$this->RegisterPropertyInteger ("id", 0 );
		$this->RegisterPropertyInteger ("timeout", 2 );
		$this->RegisterPropertyBoolean ("log", false );
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        
        $this->RegisterVariableInteger( "Humidity", "Humidity", "~Humidity", 1 );
        $this->RegisterVariableFloat( "Temperature", "Temperature", "~Temperature", 0 );
		//$this->RegisterVariableInteger( "Last", "Last", "", 2 );
		//IPS_SetHidden($this->GetIDForIdent('Last'), true);
		
		//.*protocol:oregon;model:.*;id:\d*;.*
		
		$model = $this->GetModelByNumber($this->ReadPropertyInteger("model"));
		$id = $this->ReadPropertyInteger("id");
		$this->SetReceiveDataFilter(".*protocol:oregon;model:".$model.";id:".$id.";.*");
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

		if(stripos($protocol, "oregon")!==false) {
			//$decodedMessage = DecodeOregon($message);
			//$log->LogMessage("Decoded message: ".$decodedMessage);
			$log->LogMessage("Analyzing the message and updating values...");
		} else {
			$log->LogMessage("This is not for me! (unsupported protocol: ".$protocol.")");
			return;
		}
	
		// if(strlen($decodedMessage)>0) { 
		if(strlen($message)>0) {
			//$model = GetParameter("model", $decodedMessage);
			//$id = intval(GetParameter("id", $decodedMessage));
			
			$model = GetParameter("model", $message);
			$id = intval(GetParameter("id", $message));
			
			$log->LogMessage("Received command from: ".$model.":".$id);
			
			$myModelInt = $this->ReadPropertyInteger("model");
			switch($myModelInt) {
				case 0:
					$myModel="F824";
					break;
				case 1:
					$myModel="EA4C";
					break;
				default:
					$myModel="";
			}	
			
			$myId = $this->ReadPropertyInteger("id");
			
			if($myModel==$model && $myId==$id) {
				$interval = $this->ReadPropertyInteger("timeout");
				$now = time();
		
				//$lastId = $this->GetIDForIdent("Last");
				//$lastProcessed = GetValueInteger($lastId);
				$lastProcessed = intval($this->GetBuffer("LastProcessed"));
				if($lastProcessed+$interval<$now) {

					//$temperature = GetParameter("temp", $decodedMessage);
					//$humidity = GetParameter("humidity", $decodedMessage);
					
					$temperature = GetParameter("temp", $message);
					$humidity = GetParameter("humidity", $message);
			
			
					SetValueInteger($this->GetIDForIdent("Humidity"), $humidity); 
					SetValueFloat($this->GetIDForIdent("Temperature"), $temperature);
					$log->LogMessage("The temperature and humidity values was set to ".$temperature." and ".$humidity);
					//SetValueInteger($lastId, $now);
					$this->SetBuffer("LastProcessed", $now);
				} else
					$log->LogMessage("To many messages in the last ".$interval." seconds. Skipping the message");
			} else 
				$log->LogMessage("This is not me!"); 
	
		} else {
			$log->LogMessage("Unsupported model");
		}
 
    }
	
	private function GetModelByNumber($Number) {
		switch($Number) {
				case 0:
					return "F824";
					break;
				case 1:
					return "EA4C";
					break;
				default:
					return "";
			}
	}

}

?>
