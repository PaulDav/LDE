<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlClass.php');
require_once('pnlProperty.php');
require_once('pnlRel.php');


Function pnlPredicate( $DictId=null, $LinkId=null, $LinkType=100){

	switch ($LinkType){
		case 100:
			return pnlClass($DictId, $LinkId);
			break;
		case 200:
			return pnlProperty($DictId, $LinkId);
			break;
		case 300:
			return pnlRel($DictId, $LinkId);
			break;
	}
	return;
	
}


?>