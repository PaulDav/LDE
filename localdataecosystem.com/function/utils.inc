<?php

date_default_timezone_set("Europe/London");
	require_once(dirname(__FILE__).'/../class/clsSession.php');


function valid_email($address) {
  // check an email address is possibly valid
  if (preg_match('/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/', $address)) {
    return true;
  } else {
    return false;
  }
}


Function PrepUserInput($String){

	$ReturnString = $String;
	if (!get_magic_quotes_gpc()){
		$ReturnString = addslashes($String);
	}
	
	return $ReturnString;
	
}

function getUserIdForEmail($Email){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem;
	}

	$sql = "SELECT DISTINCT emlUser FROM tbl_user_email WHERE emlEmail='$Email'";
	$result = $System->db->query($sql);
  	if (!$result) {
		return false;
	}

	if ($result->num_rows>0) {
		$rstRow = $result->fetch_assoc();
		return $rstRow['emlUser'];
	}
	
	return false;
	
}

Function LoggedOn() {
	
	$Session = new clsSession();
			
	// check for hash login and only allow certain scripts to run 

	if ($Session->HashLogin === true){
		switch (basename($_SERVER['SCRIPT_NAME'])){
			case 'account.php':
			case 'doAccount.php':
				break;
			default:
				$Session->ErrorMessage = "You must update your account";
				return FALSE;
				break;
		}
	}
	
		
	if (!($Session->UserId == "")) {
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}


Function SaveUserInput($FormName) {
	
	foreach ($_REQUEST as $Name=>$Value){
		
		if (is_array($Value)){
			
			foreach ($Value as $ValueElement){
				
				if ( strlen($ValueElement) > 100000) {
					throw new Exception("Input too long");
				}
				if (!(stripslashes(trim($ValueElement)) == "")){
					$_SESSION['forms'][$FormName][$Name][] = convertUTF8(stripslashes(trim($ValueElement)));
				}
			}	
		}
		else
		{
			if ( strlen($Value) > 100000) {
				throw new Exception("Input too long");
			}
			if (!(stripslashes(trim($Value)) == "")){
				$_SESSION['forms'][$FormName][$Name] = convertUTF8(stripslashes(trim($Value)));
			}
		}
	}
	
}

Function GetUserInput($FormName) {
	
	$Fields = array();
	
	if (isset($_SESSION['forms'][$FormName])){
		$Fields = $_SESSION['forms'][$FormName];
	}
	unset($_SESSION['forms'][$FormName]);
	
	return $Fields;
}


function CheckTimeInRange($StartTime, $EndTime, $CheckTime) 
{ 
 
  $StartTimestamp = strtotime($StartTime); 
  $EndTimestamp = strtotime($EndTime);
  $CheckTimestamp = strtotime($CheckTime); 
       
  return (($CheckTimestamp >= $StartTimestamp) && ($CheckTimestamp <= $EndTimestamp)); 
 
} 


function UpdateUrl($Params = array(),$URL = null){

	if (is_null($URL)){
		$URL = $_SERVER['REQUEST_URI'];
	}
	
	$URL = explode('?', $URL);
	$URL = $URL[0];
	
	
	$FirstParam = true;

	
	foreach ($_GET as $ExistingParamName=>$Value){
		
		if (isset($Params[$ExistingParamName])){
			$Value=$Params[$ExistingParamName];
			unset($Params[$ExistingParamName]);
			if (IsEmptyString($Value)){
				continue;
			}
		}
		if ($FirstParam){
			$FirstParam = false;
			$URL .= "?";
		}
		else
		{
			$URL .= "&";
		}
				
		$URL .= $ExistingParamName."=".$Value;
		
	}

	foreach ($Params as $ParamName=>$Value){
		
		if (IsEmptyString($Value)){
			continue;
		}
				
		if ($FirstParam){
			$URL .= "?";
			$FirstParam = false;			
		}
		else
		{
			$URL .= "&";
		}
				
		$URL .= $ParamName."=".$Value;
	}
	
	return $URL;
}

function make_links($text, $class='', $target='_blank'){
    return preg_replace('!((http\:\/\/|ftp\:\/\/|https\:\/\/)|www\.)([-a-zA-Z0-9\~\!\@\#\$\%\^\&\*\(\)_\-\=\+\\\/\?\.\:\;\'\,]*)?!ism', 
    '<a class="'.$class.'" href="//$3" target="'.$target.'">$1$3</a>', 
    $text);
}

Function xmlElementValue($Parent, $ChildName){
	
	if (is_object($Parent)){
		unset ($xmlElement);
		$xmlElement = $Parent->getElementsByTagName($ChildName)->item(0);	
		if (is_object($xmlElement)){
			return $xmlElement->nodeValue;
		}
	}
	
}

Function xmlXpathValue($Parent, $Xpath){

	$value = "";

	if (is_object($Parent)){
		
		$domXpath = new domxpath($Parent->ownerDocument);
		
		unset ($xmlElement);
		$xmlNodeList = $domXpath->query($Xpath,$Parent);
		if ($xmlNodeList->length > 0) {
			$value = $xmlNodeList->item(0)->nodeValue;
		}
	}

	return $value;

}


function xmlSetElementText($xmlElement, $Text){	
	$xmlText = $xmlElement->ownerDocument->createTextNode($Text);
    $xmlElement->nodeValue = "";
    $xmlElement->appendChild($xmlText);
}


Function xmlSetElement($Parent, $ChildName = null, $Value=null,$cdata=false){
	
	if (is_object($Parent)){
		unset ($xmlElement);
		
		if (is_null($ChildName)){
			$xmlElement = $Parent;
		}
		else
		{		
			$xmlElement = $Parent->getElementsByTagName($ChildName)->item(0);	
			if (!is_object($xmlElement)){
				$xmlElement = $Parent->ownerDocument->createElement($ChildName);
				$Parent->appendChild($xmlElement);
			}
		}
		if (!is_null($Value)){
			switch($cdata){
				case true:
// find existing cdata node if present					
					$ExistingCDATAnode = null;
					$NewCDATAnode = $Parent->ownerDocument->createCDATASection($Value);
					foreach ($xmlElement->childNodes as $node){
						if ($node->nodeType == 4){
							$ExistingCDATAnode = $node;
						}
					}

					if (is_null($ExistingCDATAnode)){
						$xmlElement->appendChild($NewCDATAnode);
					}
					else
					{
						$xmlElement->replaceChild($NewCDATAnode, $ExistingCDATAnode);
					}
					
					
					break;
				default:
					
					$textNode = $Parent->ownerDocument->createTextNode($Value);
					
					$xmlElement->nodeValue = '';					
					$xmlElement->appendChild($textNode);
									
//					$xmlElement->nodeValue = $Value;
					break;
			}
		}
		return $xmlElement;
	}
	
}


function getXmlFromUrl($URL){

	$headers = array();
	$headers[] = 'Accept: application/rdf+xml, text/rdf+xml, text/xml;q=0.1, application/xml;q=0.1, text/plain;q=0.1';
	
	$process = curl_init($URL); 
	curl_setopt($process, CURLOPT_HTTPHEADER, $headers); 
	curl_setopt($process, CURLOPT_HEADER, 0);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
	$XML = @curl_exec($process); 
	curl_close($process);
	
	return $XML;
}

function CheckPositiveInteger($inVal){
	
	if (!is_numeric($inVal) || $inVal < 1 || $inVal != round($inVal)) {
	  return FALSE;
	}	
	
//	if (!is_numeric($inVal)){
//		return false;
//	}
//	if (!ctype_digit((string)$inVal)){
//		return false;
//	}			
	
	return true;
	
}

function Truncate($string, $length = 300, $stopanywhere=false) {
	
	//truncates a string to a certain char length, stopping on a word if not specified otherwise.
    if (strlen($string) > $length) {
        //limit hit!
        $string = substr($string,0,($length -3));
        if ($stopanywhere) {
            //stop anywhere
            $string .= '...';
        } else{
            //stop on a word.
            $string = substr($string,0,strrpos($string,' ')).'...';
        }
    }
    return $string;
}


function Encode($string){
	
	return htmlentities($string,ENT_QUOTES,"UTF-8");
	
}


function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

   return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

function IsEmptyString($str){
    return (!isset($str) || trim($str)==='');
}


function convertDate($inDate, $outFormat = 'd/m/Y'){
	
	if (!isemptystring($inDate)){
		$date = DateTime::createFromFormat('!Y-m-d h:i:s', $inDate);
		if (is_object($date)){
			return $date->format($outFormat);
		}
		
		$date = DateTime::createFromFormat('!Y-m-d', $inDate);
		if (is_object($date)){
			return $date->format($outFormat);
		}
		
		
	}
	return;
	
}


function convertUTF8($inString){
//	return $inString;

	return	@iconv(mb_detect_encoding($inString, mb_detect_order(), true), "UTF-8", $inString);
}



function makeJavaString($inString){
	$str = addslashes($inString);	
	$str = str_replace(PHP_EOL, '', $str);
	return $str;
}

function time_elapsed()
{
    static $last = null;

    $now = microtime(true);

    if ($last != null) {
        echo '<!-- ' . ($now - $last) . ' -->';
    }

    $last = $now;

}

?>