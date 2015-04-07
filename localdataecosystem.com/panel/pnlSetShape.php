<?php

require_once(dirname(__FILE__).'/../class/clsData.php');
require_once('pnlShape.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlSetShape( $SetId, $SetShapeId){

	$objSet = new clsSet($SetId);
	$objSetShape = $objSet->SetShapes[$SetShapeId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='setshape.php?setid=$SetId&setshapeid=$SetShapeId'>".$SetShapeId."</a></td></tr>";
	$Content .= "<tr><th>Shape</th><td>".pnlShape($objSetShape->ShapeId)."</td></tr>";	
    $Content .= '</table>';
	    
    return $Content;
}


?>