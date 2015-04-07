<?php
require_once("class/clsDict.php");


class clsFilterClass{
	
	public $FieldName = null;
	public $Class = null;
	public $FilterProperties = array();
	public $FilterLinks = array();
}


class clsFilterLink{
	
	public $FieldName = "";
	public $Relationship = null;
	public $Inverse = false;
	public $FilterClass = null;
	
}


class clsFilterProperty{

	public $FieldName = "";
	public $Property = null;
	public $FilterValues = array();
	public $Selected = true;
	
	public $FilterProperties = array();
	
}

class clsFilterValue{
	
	public $FieldName = "";
	public $Type = null;
	public $Value = null;
	
}


?>