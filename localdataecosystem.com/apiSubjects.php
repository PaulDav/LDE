<?php

	require_once("class/clsSystem.php");		
	require_once("class/clsData.php");
	require_once("class/clsDict.php");	
	require_once("class/clsFilter.php");
	
	
	define('PAGE_NAME', 'apiSubjects');
	
	session_start();
	
	$System = new clsSystem();

	SaveUserInput(PAGE_NAME);
		
	$ClassDictId = null;
	$ClassId = null;
	$ShapeId = null;
	$SetId = null;
	$Context = null;
	$ReturnUrl = null;
	$SubjectIds = null;
	$DivId = '';
	
	if (isset($_SESSION['forms'][PAGE_NAME]['divid'])){
		$DivId = $_SESSION['forms'][PAGE_NAME]['divid'];		
	}
	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];		
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['classid'])){
		$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];		
	}	

	if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
		$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];		
	}	

	if (isset($_SESSION['forms'][PAGE_NAME]['context'])){
		$Context = $_SESSION['forms'][PAGE_NAME]['context'];		
	}	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
		$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];		
	}		
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}
	
	if (!isset($Dicts->Dictionaries[$DictId])){
		throw new Exception("Unknown Dictionary");
	}

	$objFilterClass = null;
	
	foreach ($_SESSION['forms'][PAGE_NAME] as $FieldId=>$Value){
		
		$KeyParts = explode('_', $FieldId);
		
		if (isset($KeyParts[0])){
			if ($KeyParts[0] == $DivId){
				unset ($KeyParts[0]);
				$KeyParts = array_values($KeyParts);
			}
		}
		
		
		if (isset($KeyParts[0])){
			if ($KeyParts[0] == 'filter'){
				unset ($KeyParts[0]);
				$KeyParts = array_values($KeyParts);
				if (is_null($objFilterClass)){
					$objFilterClass = new clsFilterClass();
				}				
				setFilterClass($objFilterClass, $KeyParts, $DictId, $ClassId);
			}
		}
	}
	
	$Subjects = new clsSubjects();
	
	if (!is_null($SetId)){
		$Subjects->SetId = $SetId;
	}
	
	switch ($Context){
		case 'reference':
			$Subjects->ContextId = 1;
			break;
	}
	
	$Subjects->FilterClass = $objFilterClass;
	$Subjects->ShapeId = $ShapeId;
	
	if (is_null($SubjectIds)){
		$SubjectIds = $Subjects->getClass($DictId, $ClassId);
	}
		
	unset($_SESSION['forms'][PAGE_NAME]);
	header ('Content-type: text/xml');
	
	echo $Subjects->xml;
	exit;
	
	
function setFilterClass($objFilterClass, $KeyParts, $DictId, $ClassId){
	
	global $Dicts;
	global $FieldId;
	global $Value;
	
//	$Class = $Dicts->getClass($DictId, $ClassId);
//	if (!is_object($Class)){
//		return;
//	}

	$ClassProperties = $Dicts->ClassProperties($DictId, $ClassId);	
	$ClassRels = $Dicts->RelationshipsFor($DictId, $ClassId);

	if (isset($KeyParts[0])){
		if ($KeyParts[0] == 'prop'){
		
			if (isset($KeyParts[1])){
				
				$ClassPropNum = $KeyParts[1];
									
				if (isset($ClassProperties[$ClassPropNum])){
					
					if (!isset($objFilterClass->FilterProperties[$ClassPropNum])){								
						$objFilterProperty = new clsFilterProperty();
						$objFilterClass->FilterProperties[$ClassPropNum] = $objFilterProperty;
					}
					$objFilterProperty = $objFilterClass->FilterProperties[$ClassPropNum];
					
					$objClassProperty = $ClassProperties[$ClassPropNum];
					$objProperty = $Dicts->getProperty($objClassProperty->PropDictId, $objClassProperty->PropId );

					unset ($KeyParts[0]);
					unset ($KeyParts[1]);
					$KeyParts = array_values($KeyParts);

					setFilterProperty( $objFilterProperty, $KeyParts, $objProperty, $Value);
				}
			}
		}
		
		
		elseif ($KeyParts[0] == 'rel'){
			if (isset($KeyParts[1])){				
				$ClassRelNum = $KeyParts[1];
				
				if (isset($ClassRels[$ClassRelNum])){
					if (!isset($objFilterClass->FilterLinks[$ClassRelNum])){								
						$objFilterLink = new clsFilterLink();
						$objFilterClass->FilterLinks[$ClassRelNum] = $objFilterLink;
					}
					$objFilterLink = $objFilterClass->FilterLinks[$ClassRelNum];
					
					$objRel = $ClassRels[$ClassRelNum];
					
					unset ($KeyParts[0]);
					unset ($KeyParts[1]);
					$KeyParts = array_values($KeyParts);
					
					setFilterLink( $objFilterLink, $KeyParts, $objRel);
					
				}
			}				
		}
	}
	
}

function setFilterProperty( $objFilterProperty, $KeyParts, $objProperty, $Value ){
		
	global $Dicts;
	global $FieldId;
	global $Value;
	
	
	if (is_object($objProperty)){

		$objFilterProperty->Property = $objProperty;		
		
		if (isset($KeyParts[0])){
			if ($KeyParts[0] == 'prop'){

				if (isset($KeyParts[1])){
				
					$ComplexPropNum = $KeyParts[1];

					$arrElements = array();
					foreach ($objProperty->ElementGroups as $ElementGroup){
						foreach ($ElementGroup->Elements as $objElement){
							$arrElements[] = $objElement;
						}
					}
					if (isset($arrElements[$ComplexPropNum])){
						$objElement = $arrElements[$ComplexPropNum];
						$objComplexProperty = $Dicts->getProperty($objElement->DictId, $objElement->PropId);
						if (is_object($objComplexProperty)){
							
							$objFilterComplexProperty = new clsFilterProperty();
							$objFilterProperty->FilterProperties[$ComplexPropNum] = $objFilterComplexProperty;
	
							unset ($KeyParts[0]);
							unset ($KeyParts[1]);
							$KeyParts = array_values($KeyParts);
	
							setFilterProperty( $objFilterComplexProperty, $KeyParts, $objComplexProperty, $Value );
							
						}
	
					}
				}
			}
							
		}
		else
		{
			$objFilterProperty->FieldName = "$FieldId";
			
			$objFilterValue = new clsFilterValue();
			$objFilterValue->Value = $Value;
			$objFilterProperty->FilterValues[] = $objFilterValue;
			
		}																
	}
	
	
}


function setFilterLink( $objFilterLink, $KeyParts, $objRel){

	global $Dicts;
	global $FieldId;
	global $Value;
	
	
	if (is_object($objRel)){

		$objFilterLink->Relationship = $objRel;
		$objFilterLink->FilterClass = new clsFilterClass();
		$ClassDictId = $objRel->ObjectDictId;
		$ClassId = $objRel->ObjectId;
		
		$objFilterLink->FilterClass->Class = $Dicts->getClass($ClassDictId, $ClassId);
		
		setFilterClass($objFilterLink->FilterClass, $KeyParts, $ClassDictId, $ClassId);
		
	}
	
	
}


	
?>