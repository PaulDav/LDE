<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');


 require_once(dirname(__FILE__).'/../function/utils.inc');
 require_once(dirname(__FILE__).'/../class/clsUser.php');
 require_once(dirname(__FILE__).'/../class/clsSession.php');
 require_once(dirname(__FILE__).'/../class/clsConfig.php');

require_once(dirname(__FILE__).'/../panel/pnlMenus.php');
	
class clsPage {
  
  public $ContentPanelA;
  public $ContentPanelB;
  public $ContentPanelC;
  public $ContentPanelD;
  public $ContentPanelE;
  public $Message;
  public $ErrorMessage;
  public $Script = '';
  
  
  public $AppName = "";
  public $Title = "";
  public $keywords = "";


  private $TitlePrefix = "";
  private $Logo = "";
  
  private $System = null;
  private $Config = null;
  private $Session = null;
  
  public function __construct(){
  	
  		global $System;
  		if (!isset($System)){
  			$System = new clsSystem();
  		}
  		$this->System = $System;
  		$this->Config = $System->Config;
  		$this->Session = $System->Session;
  		
  }
  
  
  public function __set($name, $value)
  {
    $this->$name = $value;
  }

  public function Display(){
   		  	
	try {
  		  	
	  	$this->ErrorMessage .= $this->Session->ErrorMessage;
	  	$this->Session->Clear("ErrorMessage");

	  	$this->Message .= $this->Session->Message;
	  	$this->Session->Clear("Message");
	  	
	  	if (isset($this->Config->Vars['instance']['appname'])){
	  		if ($this->AppName == ""){
	  			$this->AppName = $this->Config->Vars['instance']['appname'];
	  		}
	  	}
	  	
	  	if ($this->Title == ""){
	  		$this->Title = $this->AppName;
	  	}
	  	
	  	if (isset($this->Config->Vars['instance']['logo'])){
	  		if ($this->Logo == ""){
	  			$this->Logo = $this->Config->Vars['instance']['logo'];
	  		}
	  	}
	  	
		if ($this->System->LoggedOn) {
			
		  	$ThisUser = $this->System->User;
			
			if (!is_null($ThisUser->PictureOf)) {
				$this->ContentPanelA .= '<img height = "70" src="image.php?Id='.$ThisUser->PictureOf.'" /><br/>'."\n";
			}
			
			$this->ContentPanelA .= $ThisUser->Name;
		
			$this->ContentPanelA .= '<hr/>';
									
		}

		$this->ContentPanelA .= pnlStandardMenu();
		
		if ($this->System->LoggedOn) {
					
			$this->ContentPanelA .= '<hr/>';
			
			$this->ContentPanelA .= pnlLoggedOnMenu();
						
		}
		
	  	
	}
  	catch(exception $e) {
  		$this->ErrorMessage .= $e->getMessage();  		
  	}

  	$this->Session->Clear('ErrorMessage');

  	?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8" />
    <?php
    $this -> DisplayTitle();
    $this -> DisplayKeywords();
    $this -> DisplayStyles();
    
 $Script = '';
 $Script .= "<script type='text/javascript' src='java/window.js'></script>";
 $Script .= "<script type='text/javascript' src='java/wait.js'></script>";
 $Script .= "<script type='text/javascript' src='java/tabs.js'></script>";


 $Script .= $this->Script;

    
	echo $Script;

?>	


	
    </head>
    
    <body onload='initTab();init()' class='body'>
    
    <table style='width:100%;'>
    	<tr>
    		<td style='padding:0;'>
  				<?php
				    $this->DisplayHeader();
  				?>
    		</td>
    	</tr>
    	<tr>
    		<td style='padding:0;'>
  				<?php
				    $this->DisplayContent();
  				?>
    		</td>
    	</tr>
    	<tr>
    		<td style='padding:0;'>
	    		<?php    
    				$this->DisplayFooter();
    			?>
    		</td>
    	</tr>
    </table>
    
    </body>
    
   </html>
    <?php
    

  }

  private function DisplayTitle()
  {
    echo "<title>".$this->Title."</title>";
  }

  private function DisplayKeywords()
  {
    echo "<meta name=\"keywords\" 
          content=\"".$this->keywords."\"/>";
  }

  private function DisplayStyles()
  { 
?>   
	<link rel="stylesheet" type="text/css" href="css/style.css" /><?php

  
  }

  private function DisplayContent()
  { 
  	?>
  	
  	<table style='width:100%; '>
  		<tr>
  			<td style='border-style:solid;'>
  				<div style='min-width: 100px; max-width: 120px;'> 
	  			<?php
	  				if (isset($this->ContentPanelA)){
						echo $this->ContentPanelA;
	  				}	  				
	  			?>
	  			</div>
	  		</td>
  			<td  style='border-style:solid; width:100%;'>
	  			<?php
	  				if (!(empty($this->ErrorMessage))){
	  					echo '<div class="errorbox">'.$this->ErrorMessage.'</div>';
	  				}
	  				if (!(empty($this->Message))){
	  					echo '<div class="infobox">'.$this->Message.'</div>';
	  				}
	  				
	  				if (isset($this->ContentPanelB)){
						echo $this->ContentPanelB;
	  				}
	  			?>
	  		</td>
  			<td style='border-style:solid;'>
  				<div style='min-width: 50px; max-width: 120px;'>   			
	  			<?php
	  				if (isset($this->ContentPanelC)){
	  					echo "<div>";
						echo $this->ContentPanelC;
						echo "</div>";
	  				}
	  			?>
	  			</div>
	  		</td>
  		</tr>
  	</table>
  	  	
<?php    
  }
    
  
  private function DisplayHeader()
  { 
?>   
  <table style='width:100%; border-spacing:0; '>
  <tr>
    <td style='align:left; padding:12'><a href=".">
<?php
	if (!($this->Logo == "")){
?>      
    <img src="
<?php 
	echo $this->Logo;
?>" height = "40" alt="logo"/></a>
<?php
	}

?>



</td>

	<td style='align:center;'>
	<?php echo "<div><h2>".$this->AppName."</h2></div>"; ?>

	</td>

    <td style='align:right'>
 		<div class='hmenu'>
 			<ul>
 			<li><a href=".">&bull; Home</a></li>
 			
 			<?php
 				if ($this->System->LoggedOn){
 					?>
 						<li><a href="account.php">&bull; My Account</a></li>
 						<li><a href="logout.php">&bull; Log out</a></li>
 					<?php
 				}
 				else {
 					?>
 						<li><a href="account.php">&bull; Register</a></li>
 						<li><a href="login.php">&bull; Log in</a></li> 					
 					<?php
 				}
 			?>
 			</ul>
	 	</div>
    </td>
  </tr>
  </table>
<?php
  }

  private function DisplayMenu($buttons)
  {
    echo "<table style='width:100%; bgcolor:white; border-spacing:4;>";
    echo "<tr>\n";

    //calculate button size
    $width = 100/count($buttons);

    while (list($name, $url) = each($buttons)) {
      $this -> DisplayButton($width, $name, $url, 
               !$this->IsURLCurrentPage($url));
    }
    echo "</tr>\n";
    echo "</table>\n";
  }

  private  function IsURLCurrentPage($url)
  {
    if(strpos($_SERVER['PHP_SELF'], $url )==false)
    {
      return false;
    }
    else
    {
      return true;
    }
  }


private function DisplayFooter()
  {
?>
<table style='width:100%;'>
<tr>
<td>
    <p>&copy; legsb</p>
</td>
</tr>
</table>
<?php
  }
}
?>