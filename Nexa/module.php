<?

require_once(__DIR__ . "/../libs/protocols.php");  
require_once(__DIR__ . "/../libs/logging.php");

class NexaSensor extends IPSModule
{

    
    public function Create() {
        parent::Create();
        $this->ConnectParent("{655884D6-7969-4DAF-8992-637BEE9FD70D}");
		
		$this->RegisterPropertyInteger ("house", 0 );
		$this->RegisterPropertyInteger ("unit", 0 );
		$this->RegisterPropertyBoolean ("log", false );
    }

    public function ApplyChanges() {
        parent::ApplyChanges();
        
        $this->RegisterVariableBoolean( "Status", "Status", "", false );
		
		$log = new Logging($this->ReadPropertyBoolean("log"), IPS_Getname($this->InstanceID));
		
		$house = $this->ReadPropertyInteger ("house");
		$unit = $this->ReadPropertyInteger ("unit");
		
		if($house>0 && $unit>0)
			$receiveFilter = ".*[0-9A-Fa-f]{2};[0-9A-Fa-f]{2};NewKaku;ID=0{0,}".strtolower(dechex($house)).";SWITCH=0{0,}".strtolower(dechex($unit)).";CMD=(ON|OFF);.*";
		else
			$receiveFilter = ".*[0-9A-Fa-f]{2};[0-9A-Fa-f]{2};NewKaku;ID=[0-9A-Fa-f]*;SWITCH=[0-9A-Fa-f]*;CMD=(ON|OFF);.*";
			
		$this->SetReceiveDataFilter($receiveFilter);
		$log->LogMessage("ReceiveDataFilter set to ".$receiveFilter);
		
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
			
		$log->LogMessage("Analyzing the message and updating values...");
		
		$unit = intval(GetParameter("switch", $message));
		$house = intval(GetParameter("id", $message));
					
		$log->LogMessage("Received command from: house ".$house."and unit ".$unit);
						
		$myUnit = $this->ReadPropertyInteger("unit");
		$myHouse = $this->ReadPropertyInteger("house");
			
		if($myUnit==$unit && $myHouse==$house) {
			$command = strtoupper(GetParameter("cmd", $message));
			SetValueBoolean($this->GetIDForIdent("Status"), ($command=='ON'?true:false)); 
			$log->LogMessage("The Status value was set to ".$command);
		} elseif($myUnit>0 && $myHouse>0)
			$log->LogMessage("Wrong House and Unit Id. This is not me!");
    }

}

?>
