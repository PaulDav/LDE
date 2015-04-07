<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');
// require_once(dirname(__FILE__).'/../data/database.inc');

class clsRecordset {
	
private $sql = null;
private $rst = null;

private $Paged = false;

private $RowCount = 0;
private $RowsPerPage = 20;
private $PageNo = 0;
private $NumPages = 0;


	public function __get($name){
		if ($name == "rst"){
			if (is_null($this->rst)){
				$this->Get();
			}
		}
		
	  	switch ($name){
	  		default:
			  	return $this->$name;
			  	break;
	  	}
	}

	public function __set($name,$value){
		
		if ($name == "PageNo"){
			if (!CheckPositiveInteger($value)){
				throw new exception("Invalid page number");
			}			
		}
		
	  	switch ($name){
	  		case "sql":
	  		case "Paged":
	  		case "RowsPerPage":
	  		case "PageNo":
	  			$this->$name = $value;
	  			break;
	  		default:
				throw "invalid property";
				break;
	  	}
	}
	
	public function __construct($sql=null){		
		if (!is_null($sql)){
			$this->sql = $sql;
		}
	}
	
	
	
	public function Get(){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		
		$Limit = "";
		
		if ($this->Paged === true){
// count number of rows
			$sqlCount = "SELECT COUNT(*) AS NumRows FROM (".$this->sql.") AS Rows ";			
			$rstCount = $System->DbExecute($sqlCount);
			if (!$rstCount->num_rows > 0){
				throw new ErrorException("Can't paginate query - $sqlCount");
			}
			$rstRowCount = $rstCount->fetch_assoc();	
			$this->RowCount = $rstRowCount["NumRows"];
			if ($this->RowCount == 0){
				$this->NumPages = 0;
			}
			else
			{
				$this->NumPages = floor(($this->RowCount -1) / $this->RowsPerPage) + 1;
			}
			
			if ($this->PageNo == 0){
				$this->PageNo = 1;
			}
			if ($this->PageNo > $this->NumPages){
				$this->PageNo = $this->NumPages;
			}
			
			if ($this->NumPages > 0){
				$StartRow = 0;
				
				$StartRow = ($this->PageNo -1) * $this->RowsPerPage;
								
				$Limit = " LIMIT $StartRow, $this->RowsPerPage "; 
			}
			
		}
		
		$sql = $this->sql . $Limit;
		$this->rst = $System->DbExecute($sql);
		
		return $this->rst;
		
	}
	
	
}