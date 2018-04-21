<?

require_once(__DIR__ . "/../libs/protocols.php");  
require_once(__DIR__ . "/../libs/logging.php");

class OregonWeatherStation extends IPSModule
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
        
        $this->RegisterVariableInteger( "Humidity", "Humidity", "~Humidity", 1 );
        $this->RegisterVariableFloat( "Temperature", "Temperature", "~Temperature", 0 );
		
		$id = $this->ReadPropertyInteger("id");
		
		if($id>0)
			$receiveFilter = ".*[0-9A-F]{2};[0-9A-F]{2};Oregon TempHygro;ID=\d*".dechex($id)."\d*;TEMP=\d*;HUM=\d*;HSTATUS=\d;BAT=(OK|LOW);.*";
		else
			$receiveFilter = ".*[0-9A-F]{2};[0-9A-F]{2};Oregon TempHygro;ID=\d*;TEMP=\d*;HUM=\d*;HSTATUS=\d;BAT=(OK|LOW);.*";
		
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

		$log->LogMessage("Analyzing the message and updating values...");

		$id = hexdec(GetParameter("id", $message));
		$log->LogMessage("Received message from Id ".$id);
		$myId = $this->ReadPropertyInteger("id");
		
		if($myId==$id) {
			$temperature = ConvertTemperature(GetParameter("temp", $message));
			$humidity = GetParameter("hum", $message);
	
			SetValueInteger($this->GetIDForIdent("Humidity"), $humidity); 
			SetValueFloat($this->GetIDForIdent("Temperature"), $temperature);
			$log->LogMessage("The temperature and humidity values was set to ".$temperature." and ".$humidity);
		} elseif($myId>0)
			$log->LogMessage("Wrong Id. This is not me!"); 
    }
}

?>
