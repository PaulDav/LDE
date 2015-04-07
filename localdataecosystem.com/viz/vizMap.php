<?php
require_once(dirname(__FILE__).'/../class/clsMap.php');
	


class vizMap {

	private $Map;
	public $Script = null;
	public $Html = null;

	public function __construct(){
		$this->Map = new clsMap;
	}
	
	public function addSubject($SubjectId, $Params=null){

		$CRS = null;
		$X = null;
		$Y = null;
		
		$Subject = new clsSubject($SubjectId);
		
		if (is_array($Params)){
			
			if (isset($Params[1])){
				$CRS = $Params[1];
			}
			
			if (isset($Params[2])){
				$X = $Params[2];
			}
			
			if (isset($Params[3])){
				$Y = $Params[3];
			}
			
			
		}
		
		$objMarker = new clsMarker();
		$objMarker->Coordinates->Type = $CRS;
		$objMarker->Coordinates->X = $X;
		$objMarker->Coordinates->Y = $Y;
		
		$objMarker->Text = trim($Subject->Identifier.' '.$Subject->Name);
		$objMarker->Url = "subject.php?subjectid=".$SubjectId;
		
		$this->Map->Markers[] = $objMarker;
		
	}
	
	public function Generate(){
		$this->Map->Generate();
		
		$this->Script = $this->Map->Script;
		$this->Html = $this->Map->Html;
	}
	
}



?>