<?

require_once(__DIR__ . "/../TellstickUtil.php");  
require_once(__DIR__ . "/../Logging.php");

class NexaSensor extends IPSModule
{

    
    public function Create() {
        parent::Create();
        $this->ConnectParent("{655884D6-7969-4DAF-8992-637BEE9FD70D}");
		
		$this->RegisterPropertyInteger ("house", 0 );
		$this->RegisterPropertyInteger ("unit", 0 );
		$this->RegisterPropertyInteger ("timeout", 2 );
		$this->RegisterPropertyBoolean ("log", false );
    }

    public function ApplyChanges() {
        parent::ApplyChanges();
        
        $this->RegisterVariableBoolean( "Status", "Status", "", false );
		//$this->RegisterVariableInteger( "Last", "Last", "", 2 );
		//IPS_SetHidden($this->GetIDForIdent('Last'), true);
		
		$house = $this->ReadPropertyInteger ("house");
		$unit = $this->ReadPropertyInteger ("unit");
		
		$this->SetReceiveDataFilter(".*protocol:arctech;model:selflearning;house:".$house.";unit:".$unit.";group:\d*;method:.*");
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

		if(stripos($protocol, "arctech")!==false) {
			//$decodedMessage = DecodeNexa($message);
			//$log->LogMessage("Decoded message: ".$decodedMessage);
			$log->LogMessage("Analyzing the message and updating values...");
		} else {
			$log->LogMessage("This is not for me! (unsupported protocol: ".$protocol.")");
			return;
		}

		//if(strlen($decodedMessage)>0) {
		if(strlen($message)>0) {
			//$unit = intval(GetParameter("unit", $decodedMessage));
			//$house = intval(GetParameter("house", $decodedMessage));
			
			$unit = intval(GetParameter("unit", $message));
			$house = intval(GetParameter("house", $message));
			
			
			$log->LogMessage("Received command from: ".$house.":".$unit);
							
			$myUnit = $this->ReadPropertyInteger("unit");
			$myHouse = $this->ReadPropertyInteger("house");
				
			$log->LogMessage("I am:".$myHouse.":".$myUnit);
				
			if($myUnit==$unit && $myHouse==$house) {
				$interval = $this->ReadPropertyInteger("timeout");
				$now = time();
		
				//$lastId = $this->GetIDForIdent("Last");
				//$lastProcessed = GetValueInteger($lastId);
				$lastProcessed = intval($this->GetBuffer("LastProcessed"));
				if($lastProcessed+$interval<$now) {
					$log->LogMessage("It is a match, updating status...");

					//$method = GetParameter("method", $decodedMessage);
					$method = GetParameter("method", $message);
					SetValueBoolean($this->GetIDForIdent("Status"), ($method=='turnon'?true:false)); 
					$log->LogMessage("The Status value was set to ".$method);
					//SetValueInteger($lastId, $now);
					$this->SetBuffer("LastProcessed", $now);
				} else
					$log->LogMessage("To many messages in the last ".$interval." seconds. Skipping the message");
			} else {
				$log->LogMessage("This is not me!");
			}
		}
		
    }

}

?>
