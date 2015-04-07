<?php

require_once(dirname(__FILE__).'/../libs/phpmailer/class.phpmailer.php');
require_once(dirname(__FILE__).'/../class/clsSystem.php');


class clsMail{
	
	private $To = null;
	private $Subject = null;
	private $Content = null;
	
	public function __get($name){
		
	  	switch ($name){
	  		default:
			  	return $this->$name;
			  	break;
	  	}
	}	

	public function __set($name,$value){
		
		if ($name == "To"){
			if (!valid_email($value)){
				throw new exception("Invalid email address");
			}
		}

	  	switch ($name){
	  		case "To":
	  		case "Subject":
	  		case "Content":
	  			break;
	  		default:
				throw new exception("invalid property");
				break;
	  	}
	  	
	  	$this->$name = $value;
	  	
	}
	
	public function Send(){
		
		global $objSystem;
		if (!isset($objSystem)){
			$objSystem = new clsSystem();
		}		
		
		if ($this->To == ""){
			throw new Exception("No To email address specified");
		}
		
		$from = $objSystem->Config->Vars['mail']['from'];

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';
		$mail->Host       = $objSystem->Config->Vars['mail']['host'];
		$mail->SMTPDebug  = 0;
		$mail->SMTPAuth   = true;
		$mail->Port       = $objSystem->Config->Vars['mail']['port'];
		$mail->Username   = $objSystem->Config->Vars['mail']['username'];
		$mail->Password   = $objSystem->Config->Vars['mail']['password'];
		$mail->SetFrom($from);
		$mail->Subject = $this->Subject;
		$mail->AddAddress($this->To);
		$mail->MsgHTML($this->Content);

		if(!$mail->Send()) {
		     throw new exception("Mail Error: " . $mail->ErrorInfo);
		}
	}
}


?>