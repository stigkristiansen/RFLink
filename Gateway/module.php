<?

require_once(__DIR__ . "/../libs/logging.php");
require_once(__DIR__ . "/../libs/protocols.php");

class RFLinkGateway extends IPSModule
{
    
    
    public function Create()
    {
        parent::Create();
        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");
        
        $this->RegisterPropertyBoolean ("log", false );
		
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    
		//$this->RegisterVariableString("Buffer", "Buffer");	
		//$this->RegisterVariableString("LastCommand", "LastCommand");

		//IPS_SetHidden($this->GetIDForIdent('Buffer'), true);
        //IPS_SetHidden($this->GetIDForIdent('LastCommand'), true);    
    }
    

    public function ReceiveData($JSONString) {
		$incomingData = json_decode($JSONString);
		$incomingBuffer = utf8_decode($incomingData->Buffer);
			
		$log = new Logging($this->ReadPropertyBoolean("log"), IPS_Getname($this->InstanceID));
		$log->LogMessage("Incoming from serial: ".$incomingBuffer);
		
		if (!$this->Lock("ReceiveLock")) {
			$log->LogMessage("Buffer is already locked. Aborting message handling!");
			return false; 
		} else
			$log->LogMessage("Buffer is locked");

		$data = $this->GetBuffer("SerialBuffer");
		$pos = strpos($data, "20;");
		if($pos!==false && $pos == 0) {
			$data = substr($data, 6);
		}
		
		$data .= $incomingBuffer;
		
		$log->LogMessage("Searching for a complete message...");	
		do{
			$foundMessage = false;
			$arr = str_split($data);
			$max = sizeof($arr);
			for($i=0;$i<$max-1;$i++) {
				if(ord($arr[$i])==0x0D && ord($arr[$i+1])==0x0A) {
					$foundMessage = true;
					
					$message = substr($data, 0, $i-1);
					$log->LogMessage("Found message: ".$message);
					
						try{
							if($this->SupportedMessage($message)) {
								$this->SendDataToChildren(json_encode(Array("DataID" => "{F746048C-AAB6-479D-AC48-B4C08875E5CF}", "Buffer" => $message)));
								$log->LogMessage("Message sent to children: ".$message);
							} else
								$log->LogMessage("The protocol in the message is not supported");
						}catch(Exeption $ex){
							$log->LogMessageError("Failed to send message to all children. Error: ".$ex->getMessage());
							$this->Unlock("ReceiveLock");
					
							return false;
						}
					
					if($i!=$max-2)
						$data = substr($data, $i+2);
					else
						$data = "";
					   
					break;
				} else
					$log->LogMessage("No complete message yet...");
			}
		} while($foundMessage && strlen($data)>0);
		
		$this->SetBuffer("SerialBuffer", $data);
				
		$this->Unlock("ReceiveLock");
		
		return true;
    }
	
	private function SupportedMessage($Message) {
		$data = explode(";", $Message);
		$protocol = $data[0];
		
		switch(strtolower($protocol)) {
			case "fineoffset":
				return true;
				break;
			case "oregon temphygro":
				return true;
				break;
			case "newkaku":
				return true;
				break;
		}
		
		return false;
	}
	
	private function DecodeMessage($Message) {
		$data = explode(";", $Message);
		$protocol = $data[0];
		
		$decodedMessage = "";
		
		switch(strtolower($protocol)) {
			case "fineoffset":
				$decodedMessage = DecodeFineOffset($Message);
				break;
			case "oregon temphygro":
				$decodedMessage = DecodeOregon($Message);
				break;
			case "newkaku":
				$decodedMessage = DecodeNexa($Message);
				break;
		}
		
		return $decodedMessage;
		
	}
 
    private function Lock($ident){
        for ($i = 0; $i < 100; $i++){
            if (IPS_SemaphoreEnter("TSG_".(string)$this->InstanceID.(string)$ident, 1)){
                return true;
            } else {
                $log = new Logging($this->ReadPropertyBoolean("log"), IPS_Getname($this->InstanceID));
				$log->LogMessage("Waiting for lock");
				IPS_Sleep(mt_rand(1, 5));
            }
        }
        return false;
    }

    private function Unlock($ident){
        IPS_SemaphoreLeave("TSG_".(string)$this->InstanceID.(string)$ident);
		$log = new Logging($this->ReadPropertyBoolean("log"), IPS_Getname($this->InstanceID));
		$log->LogMessage("Buffer is unlocked");
    }
}

?>
