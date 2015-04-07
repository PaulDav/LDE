<?php

// require_once(dirname(__FILE__).'/../data/database.inc');
require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsUser.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');
require_once(dirname(__FILE__).'/../class/clsSystem.php');


function dataGroupUpdate($Mode, $Id = null,  $Name = null, $Description = null, $Publish = false) {

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a group');
	}
	
	$UserId = $System->User->Id;

	switch ($Mode) {
		case 'edit':
			
			$objGroup = new clsGroup($Id);
			if ($objGroup === false){
				throw new exception("Group does not exist");
			}
			
			if (!($objGroup->OwnerId == $System->User->Id)){
				throw new exception("You cannot update this Group");
			}
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}

	$Name  = $db->real_escape_string($Name);		
	$Description  = $db->real_escape_string($Description);
	
	if ($Publish === false){
		$Publish = 'FALSE';
	}
	else
	{
		$Publish = 'TRUE';
	}
	
	switch ($Mode){
		case 'edit':
			$sql = "update tbl_group set grpName='$Name', grpDescription = '$Description', grpPublish = $Publish WHERE grpRecnum = $Id";
			$result = $System->DbExecute($sql);
			break;
		case 'new':

			$sql = "insert into tbl_group ( grpName, grpDescription, grpOwner, grpPublish ) values
                        ('$Name', '$Description', $UserId, $Publish) ";
			$result = $System->DbExecute($sql);
			$Id = $System->db->insert_id;

			break;
	}	
	
  	
	switch ($Mode){
		case 'new':
			break;
	}
	
	return $Id;
	
}  	


function dataGroupBase($Id = null,  $Base) {

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a group');
	}
	
	$UserId = $System->User->Id;

	$objGroup = new clsGroup($Id);
	if ($objGroup === false){
		throw new exception("Group does not exist");
	}
	
	if (!($objGroup->canControl)){
		throw new exception("You cannot set the base for this Group");
	}
			
	$Base  = $db->real_escape_string($Base);
	
	$sql = "update tbl_group set grpBase='$Base' WHERE grpRecnum = $Id";
	$result = $System->DbExecute($sql);
	
	return true;
	
}  	


?>