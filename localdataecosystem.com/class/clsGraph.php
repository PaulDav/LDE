<?php


class clsGraph{
	
	private $GraphName = '';
	private $Label = '';
	private $ScriptFormat = 'dot';
	private $Script = "";
	private $Type = "main";
	private $Seq = "";
	private $Height = "0.8";
	private $Width = "1.0";
	
	private $SubGraphs = array();
		
	public function __get($name){
  	
  	switch ($name){
			case "script":
				return $this->ScriptHeader().$this->Script.$this->SubScripts().$this->ScriptFooter();
				break;
			default:
				return $this->$name;
				break;
		}
  	
  }

  
  public function __set($name,$value){
  	
  	switch ($name){
			case "ScriptFormat":
			case "GraphName":
				$this->$name = $value;
				break;
		}
  	
  }
  
	public function __construct($Type="main"){
		
		switch ($Type) {
			case "main":
			case "sub":
			case "cluster":
				$this->Type = $Type;
				break;
			default:
				throw new exception("invalid graph type");
				break;
		}
		
	}
  
  private function ScriptHeader(){
  	$ScriptHeader = '';
  	
  	if ($this->Type == "main"){  	
	  	$ScriptHeader .= "digraph ".$this->GraphName." { \n";
		$ScriptHeader .= "node [ color=black, fillcolor=lightblue ,fontname=".chr(34)."Arial".chr(34).", fontcolor=black, fontsize=7]; \n";
		$ScriptHeader .= "edge [fontname=".chr(34)."Arial".chr(34).", fontsize=7, labelfontname=".chr(34)."Arial".chr(34).", labelfontsize=7, len=3.0]; \n";
  	}
  	
	return $ScriptHeader;
  }

  
  private function SubScripts(){
  	$SubScript = '';

  	foreach ($this->SubGraphs as $SubGraph){
  		$SubScript .= "\n subgraph ".$SubGraph->Type.$SubGraph->Seq." { ";
  		if (!empty($SubGraph->Label)){
	  		$SubScript .= " label = ".chr(34).$SubGraph->Label.chr(34)." \n ";
  		}
  		
  		$SubScript .= $SubGraph->script;
  		$SubScript .= "} \n";
  	}  	
  	
	return $SubScript;
  }
  
  
  private function ScriptFooter(){

  	$ScriptFooter = '';
  	if ($this->Type == "main"){  	  	
	  	$ScriptFooter .= " } \n"; 	
  	}
	return $ScriptFooter;
  }
  
  
  public function addNode($Id, $Label="", $Shape="", $Color=null,$Height=null, $Width=null, $URL=null){
  	
  	
  	$Id = str_replace("-","_", $Id);

  	$this->Script .=	"$Id [ ";
  	
   	if (!empty($Shape)){
	  	switch ($Shape){
	  		case "Mrecord":
	  			$this->Script .= " fixedsize=false, shape=Mrecord, ";  			
	  			break;
	  		case "plaintext":
	  			$this->Script .= " shape=plaintext, ";	  			
	  			break;
	  		default:
	  			$this->Script .= " fixedsize=true, shape=$Shape ,";  			
 			
	  			break;
	  	}
  	}
  	
	$this->Script .= " label="; 	
  	
  	switch ($Shape){
  		case "plaintext":
		  	$this->Script .= $Label;  			
  			break;
  		default:
		  	$this->Script .=	chr(34).$Label.chr(34);
		  	if (!is_null($Color)){
		  		$this->Script .= ", fillcolor=$Color ";  			
  			}
  			if (empty($Height)){
  				$Height = $this->Height;
  			}
		  	$this->Script .= ", height=$Height ";

		  	if (empty($Width)){
		  		$Width = $this->Width;
		  	}
		  	$this->Script .= ", width=$Width ";

		  	$this->Script .= " , style=filled ";
		  	
		  	$this->Script .= ", fixedsize=true ";

		  	
  			break;
  	}

  	if (!is_null($URL)){
  		$this->Script .= ", URL=".chr(34).$URL.chr(34)." ";
  	}
  	
  	$this->Script .= "]; \n";  	
  }

  public function addEdge($FromId, $ToId, $Label=null, $Color=null, $Style = null){
  	
  	  $FromId = str_replace("-","_", $FromId);
  	  $ToId = str_replace("-","_", $ToId);
  	  	
  	
  	$Edge = "$FromId -> $ToId ";
  	
  	$Edge .= "[ ";
  	if (!empty($Color)){
  		$Edge .= " color=".$Color." ";
  	}                

  	if (!empty($Style)){
  		$Edge .= " style=".$Style." ";
  	}                
  	  	
  	if (!empty($Label)){
  		if (0 === strpos($Label, '<<')) {
  			$Edge .= " label=$Label ";
  		}
  		else
  		{  		
  			$Edge .= " label=".chr(34).$Label.chr(34)." ";
  		}
  	}
  	$Edge .= "]; \n";
  	
  	
  	if (strpos($this->Script,$Edge) === false){
  		$this->Script .= $Edge;
  	}
  	
  }
  
  public function addSubGraph($Type="sub",$Label=""){
  	
  	
  	$SubGraph = new clsGraph($Type);
  	$SubGraph->Label = $Label;
  	
  	$this->SubGraphs[] = $SubGraph;
  	$SubGraph->Seq = $this->Seq."_".count($this->SubGraphs);
  	return $SubGraph;
  	
  }
  
    private function getHtml(){
    	
    	$html = "";
    	
 //   	$html .= "<script src='libs/jquery/1.10.2/jquery-1.10.2.min.js'></script>";
    	
//		$html .= "<script type='text/javascript' src='http://gviz.oodavid.com/gviz.js'></script>";
//		$html .= "<script type='gviz' data-layout='dot'><![CDATA[";
//		$html .= $this->ScriptHeader();
//		$html .= $this->Script;
//		$html .= "]]></script>";

    	$html = "<img src='https://chart.googleapis.com/chart?cht=gv&chl=";
		$html .= urlencode($this->ScriptHeader());
		$html .= urlencode($this->Script);
    	$html .= "'/>";
		
		return $html;
 
    }  

    
    public function FormatDotLabel($Label,$Length=12, $Align='center'){
	
    	$NewLine = "\\n";
    	switch ($Align){
    		case 'left':
    			$Align = "\\l";
    			break;
    		case 'right':
    			$Align = "\\r";
    			break;
    		default:
    			$Align = "\\n";
    			break;
    	}
    	
		if (!(strlen($Label) > $Length)){
			return $Label.$Align;
		}
	
		$NewLabel = "";
		$NewLine = "";
		$arrLabel = explode(" ",$Label);
	
		foreach ($arrLabel as $Word){
			if ($NewLine == ""){
				$NewLine = $Word;
			}
			else
			{
				if (strlen($NewLine) + strlen($Word) > $Length){
					if (!($NewLabel == "")){
						$NewLabel .= $Align;
					}
					$NewLabel .= $NewLine;
					$NewLine = "";
				}
				if (!($NewLine == "")){
					$NewLine .= " ";
				}
				$NewLine .= $Word;
			}		
		}
		if (!($NewLabel == "")){
			$NewLabel .= $Align;
		}
		$NewLabel .= $NewLine;
		
		return $NewLabel.$Align;	
		
	}
	
    public function FormatDotCell($Label,$Width=20){

    	$Cell = "";
    	
		$NewLabel = "";
		$NewLine = "";
		$arrLabel = explode(" ",$Label);

		foreach ($arrLabel as $Word){
			if ($NewLine == ""){
				$NewLine = $Word;
			}
			else
			{
				if (strlen($NewLine) + strlen($Word) > $Width){
					if (!($NewLabel == "")){
						$NewLabel .= "<br/>";
					}
					$NewLabel .= $NewLine;
					$NewLine = "";
				}
				if (!($NewLine == "")){
					$NewLine .= " ";
				}
				$NewLine .= htmlentities($Word);
			}		
		}

		if (!($NewLabel == "")){			
			$NewLabel .= "<br/>";
		}
		$NewLabel .= $NewLine;
		
		$Cell .= $NewLabel;
		
		return $Cell;
				
	}
		
}



class clsImageMap{
	
	public $Map = null;

	public function __construct($Script){
				
		
	// find URLs in the script
		$ScriptLines = preg_split("/\\r\\n|\\r|\\n/", $Script);
		$Urls = array();
		foreach ($ScriptLines as $ScriptLine){
			$ScriptWords = explode(' ',$ScriptLine);
			switch (reset($ScriptWords)){
				case 'digraph':
				case 'node':
				case 'edge':
					break;
				default:
					if (!(in_array('->',$ScriptWords))){
						$UrlPos = strrpos ( $ScriptLine, ', URL="');    
						if (!($UrlPos === false)){
							$QuotePos = strpos($ScriptLine,'"',$UrlPos+7);
							if (!($QuotePos === false)){
								$Urls[] = substr($ScriptLine,$UrlPos+7, ($QuotePos - ($UrlPos+7)));
							}
						}
					}
					break;
			}
		}
		
			
	  	$url = 'https://chart.googleapis.com/chart';
	
		$fields = array('cht' => urlencode('gv'),'chl' => urlencode($Script));
		$fields['chof']='json';
					
		$fields_string = "";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
	
		$ch = curl_init();
		
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			
		$result = curl_exec($ch);
		
		curl_close($ch);

		$arrMap = json_decode($result, true);
		$Map = "";
		
		reset($Urls);
		foreach ($arrMap['chartshape'] as $arrShape){
			if ($arrShape['name']== htmlentities("<TABLE>")){
				$Map .= "<area shape='".$arrShape['type']."' coords='";
				$Coords = "";
				foreach ($arrShape['coords'] as $coord){
					if (!($Coords == "")){
						$Coords .= ",";
					}
					$Coords .= $coord;
				}
				$Map .= "$Coords";
				$Map .= "' href='".current($Urls)."'>";
				next($Urls);
			}
		}
		
		if (!($Map == "")){
			$Map = "<map name='subjectmap'>$Map</map>";
		}		
		
		$this->Map = $Map;
		
	}	

}
?>