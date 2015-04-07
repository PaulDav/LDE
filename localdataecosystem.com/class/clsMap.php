<?php

require_once(dirname(__FILE__).'/../function/funOSGB36.php');

class clsCoordinates{	
	public $Type = 'WGS84';
	public $X = null;
	public $Y = null;	
	
	public function WGS84(){

		$wgs84 = new clsWGS84();
		
		switch ($this->Type){
    		case 'OSGB36':
    			$LatLong = OSGB36toWGS84($this->X, $this->Y) ;
				$wgs84->Lat = $LatLong->lat;
				$wgs84->Long = $LatLong->lng;			    			
				break;			    			
    		case 'WGS84':
    			$Lat = $this->X;
    			$Long = $this->Y;
    			break;
    	}
		return $wgs84;
	}
	
}

class clsWGS84{
	public $Lat = null;
	public $Long = null;
}


class clsMarker{	
	public $Coordinates;
	public $Text = null;
	public $Url = null;
		
	function __construct(){
		$this->Coordinates = new clsCoordinates();
	}
	
}


class clsMap{
	
	private $MapName = 'mapcanvas';
	private $Label = '';
	private $Height = "400px";
	private $Width = "400px";
	
	private $Center = null;
	private $Zoom = 15;
	
	private $ApiKey = null;
	public $Markers = array();
	private $Script = "";
	private $Html = "";

	
	private $Renderer = null;
	
	public function __get($name){  	
		return $this->$name;
	}
  	

  
	public function __set($name,$value){
		$this->$name = $value;
	}
  
	public function Generate(){

		global $System;
		if (isset($System)){
			$this->Renderer = $System->Config->MapRenderer;
		}

		$Script = "";
		$Html = "";
		
		switch ($this->Renderer) {
			case 'google javascript':

/*
				$Script .= "<style type='text/css'>\n";
				$Script .= "html { height: 100% }\n";
				$Script .= "body { height: 100%; margin: 0; padding: 0 }\n";
				$Script .= "#".$this->MapName." { height: 100% }\n";
				$Script .= "</style>\n";   	
*/				
				
				
				
				$Script .= "\n<script type='text/javascript' ";
				$Script .= "src='https://maps.googleapis.com/maps/api/js?";
				if (!is_null($this->ApiKey)){
					$Script .= "key=$ApiKey&";
				}
				$Script .= "sensor=false'>";
				$Script .= "</script>\n";
		
				$Html = "<div id='".$this->MapName."' style='width: ".$this->Width."; height: ".$this->Height.";'></div>";

				$Script .= "<script type='text/javascript'>\n";
				
				$Script .= "var ".$this->MapName.";\n";
				
			    $Script .= "function ".$this->MapName."_init() {\n";
			    
			    if (!is_null($this->Center)){
			    	$objWGS84 = $this->Center->WGS84();			    	
			    	$Lat = $objWGS84->Lat;
			    	$Long = $objWGS84->Long;
			    	
      				$Script .= "var ".$this->MapName."_CenterLatLng = new google.maps.LatLng($Lat,$Long);\n";
					$Script .= "var ".$this->MapName."_Options = {center: ".$this->MapName."_CenterLatLng,zoom: ".$this->Zoom."};\n";
				}
				else
				{
					$Script .= "var ".$this->MapName."_Options = {zoom: ".$this->Zoom."};\n";
				}
			    

				$Script .= $this->MapName." = new google.maps.Map(document.getElementById('".$this->MapName."'),".$this->MapName."_Options);\n";
				
				$Script .= "var markerBounds = new google.maps.LatLngBounds();\n";
				
				$MarkerNum = 0;
			    foreach ($this->Markers as $objMarker){
			    	$MarkerNum = $MarkerNum + 1;
			    	
			    	$objWGS84 = $objMarker->Coordinates->WGS84();			    	
			    	$Lat = $objWGS84->Lat;
			    	$Long = $objWGS84->Long;
			    	
      				$Script .= "var ".$this->MapName."_LatLng$MarkerNum = new google.maps.LatLng($Lat,$Long);\n";
      				
					$Script .= "var ".$this->MapName."_Marker$MarkerNum = new google.maps.Marker({";
      				$Script .= "position: ".$this->MapName."_LatLng$MarkerNum,";
      				$Script .= "map: ".$this->MapName;
      				
      				if (!is_null($objMarker->Text)){
      					$Script .= ",title: '".$objMarker->Text."'";
      				}

      				if (!is_null($objMarker->Url)){
      					$Script .= ",url: '".$objMarker->Url."'";
      				}
      				
  					$Script .= "});\n";

  					if (!is_null($objMarker->Url)){
  						$Script .= "google.maps.event.addListener(".$this->MapName."_Marker$MarkerNum, 'click', function() {  window.location.href = ".$this->MapName."_Marker$MarkerNum.url;});\n";
  					}
 
  					$Script .= "markerBounds.extend(".$this->MapName."_LatLng$MarkerNum);\n";
	
			    }

			    
			    
			    if ($MarkerNum > 0){
			    	$Script .= $this->MapName.".setCenter(markerBounds.getCenter());\n";
	  				$Script .= $this->MapName.".fitBounds(markerBounds);\n";
			    }

			    
			    
			    $Script .= "var zoomlistener = google.maps.event.addListener(".$this->MapName.", 'idle', function() { \n";
  				$Script .= "if (".$this->MapName.".getZoom() > 12) ".$this->MapName.".setZoom(12);\n"; 
  				$Script .= "google.maps.event.removeListener(zoomlistener);\n";
				$Script .= "});\n";
			    
				
				$Script .= "}\n";            
                  
				$Script .= "google.maps.event.addDomListener(window, 'load', ".$this->MapName."_init);\n";
    			$Script .= "</script>";
				
		  
      }
      
      $this->Script = $Script;
      $this->Html = $Html;
	}

}
?>