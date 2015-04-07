<?php

require_once("clsSystem.php");
require_once("clsGroup.php");
require_once("clsGraph.php");

require_once(dirname(__FILE__).'/../function/utils.inc');


class clsDicts {
	private $folder = "dictionaries";
	
	private $Dictionaries = array();
	public $Groups = array();
	
	public $LdeNamespace = "http://schema.legsb.gov.uk/lde/";
	public $DictNamespace = "http://schema.legsb.gov.uk/lde/dictionary/";
	
	private $dotStyle = 1;
	private $dotConceptClusters = array();
	private $dotNodes = array();
	private $dotComplexGroups = array();
	private $dotDictId = null;
	
	private $System;
		
	public function __get($name){
		switch ($name){
			default:
				return $this->$name;
				break;
		}
		
	}
	
	
	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		$this->System = $System;
		
		$path = $System->path.$this->folder;
		
		if ($handle = opendir($path)) {
			
		    while (false !== ($entry = readdir($handle))) {
		        if ($entry != '.' && $entry != '..' && substr($entry, 0,1) != '.') {
		        	
		        	$temp = explode( '.', $entry );
					$ext = array_pop( $temp );
					$Id = implode( '.', $temp );
					
		        	$objDict = new clsDict($Id, $this);
		        	
		        	$this->Dictionaries[$Id] = $objDict;
		        }
		    }
		    closedir($handle);
		}

		if (isset($System->Config->Vars['external']['uri'])){
			foreach ($System->Config->Vars['external']['uri'] as $EcoUri){
				$EcoApi = $EcoUri.'/api.php';
				$domEco = new DOMDocument();
				if (@$domEco->load($EcoApi) === false){
					echo "cant load $EcoApi <br/>";
					continue;
				}
				$xpathEco = new domxpath($domEco);
				$xpathEco->registerNamespace('lde', $this->LdeNamespace);
				$xpathEco->registerNamespace('dict', $this->DictNamespace);
				foreach ($xpathEco->query("/lde:LocalDataEcosystem/lde:Groups/lde:Group/dict:Dictionaries/dict:Dictionary") as $xmlDict){
					
					$Id = $xmlDict->getAttribute("id");
					$domDict = new DOMDocument('1.0', 'utf-8');
					$domDict->appendChild($domDict->importNode($xmlDict,true));					
					$objDict = new clsDict($Id, $this, $domDict->saveXML(), $EcoUri);
					if (is_object($objDict)){
				        $this->Dictionaries[$Id] = $objDict;
					}									
				}				
			}
		}
	}

	public function getItem($DictId, $Id, $TypeId){
		
		$objResult = null;

		if (!isset($this->Dictionaries[$DictId])){
			return false;
		}
		$objDict = $this->Dictionaries[$DictId];
		
		switch ($TypeId){
			case 100:
				if (!isset($objDict->Classes[$Id])){
					return false;
				}
				$objResult = $objDict->Classes[$Id];
				break;
			case 200:
				if (!isset($objDict->Properties[$Id])){
					return false;
				}
				$objResult = $objDict->Properties[$Id];
				break;
			case 300:
				
				if (!isset($objDict->Relationships[$Id])){
					return false;
				}
				$objResult = $objDict->Relationships[$Id];
				break;
		}
		return $objResult;
	}
	

	public function getClass($DictId, $Id){
		
		$objResult = null;

		if (!isset($this->Dictionaries[$DictId])){
			return false;
		}
		$objDict = $this->Dictionaries[$DictId];
		
		if (!isset($objDict->Classes[$Id])){
			return false;
		}
		$objResult = $objDict->Classes[$Id];
		
		return $objResult;
	}
	
	
	public function getXmlClass($DictId, $Id){
		
		$System = $this->System;
		$nsLde = $System->Config->Namespaces['lde'];
		$nsMeta = $System->Config->Namespaces['meta'];
				
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;

		$objClass = $this->getClass($DictId, $Id);
		
		$xmlClass = null;
		
		if (!is_null($objClass)){
			
			$xmlClass = $dom->createElementNS($nsLde, 'Class');
			$dom->appendChild($xmlClass);
			
			$xmlClass->setAttribute('dictid',$objClass->DictId);
			$xmlClass->setAttribute('id',$objClass->Id);
			$xmlClass->setAttribute('label',$objClass->Label);
			$xmlClass->setAttribute('heading',$objClass->Heading);
			$xmlClass->setAttribute('description',$objClass->Description);
			$xmlClass->setAttribute('concept',strtoupper($objClass->Concept));
			
			$xmlProperties = $dom->createElementNS($nsLde, 'Properties');
			$xmlClass->appendChild($xmlProperties);

			foreach ($this->ClassProperties($objClass->DictId, $objClass->Id) as $objClassProperty){
				$objProperty = $this->getProperty($objClassProperty->PropDictId, $objClassProperty->PropId );
				if (is_object($objProperty)){
					$xmlProperty = $dom->createElementNS($nsLde, 'Property');
					$xmlProperties->appendChild($xmlProperty);
					$xmlProperty->setAttribute('dictid',$objProperty->DictId);
					$xmlProperty->setAttribute('id',$objProperty->Id);
					$xmlProperty->setAttribute('label',$objProperty->Label);
					$xmlProperty->setAttribute('description',$objProperty->Description);
					
					$this->getXmlComplexProperties($xmlProperty, $objProperty);
					
				}
			}


			$xmlExtensions = null;
			foreach ($this->RelationshipsFor($objClass->DictId, $objClass->Id) as $objRel){
				if ($objRel->Extending == true){
					if (is_null($xmlExtensions)){
						$xmlExtensions = $dom->createElementNS($nsLde, 'Extensions');
						$xmlClass->appendChild($xmlExtensions);						
					}
					
					$xmlExt = $dom->createElementNS($nsLde, 'Extension');
					$xmlExtensions->appendChild($xmlExt);
					$xmlExt->setAttribute('dictid',$objRel->DictId);
					$xmlExt->setAttribute('id',$objRel->Id);
					$xmlExt->setAttribute('label',$objRel->Label);
					$xmlExt->setAttribute('description',$objRel->Description);
					
					$xmlExt->appendChild($dom->importNode($this->getXmlClass($objRel->ObjectDictId, $objRel->ObjectId)->documentElement,true));
								
				}
			}
			foreach ($this->RelationshipsFor(null,null,$objClass->DictId, $objClass->Id) as $objRel){
				if ($objRel->InverseExtending == true){
					if (is_null($xmlExtensions)){
						$xmlExtensions = $dom->createElementNS($nsLde, 'Extensions');
						$xmlClass->appendChild($xmlExtensions);						
					}
					
					$xmlExt = $dom->createElementNS($nsLde, 'Extension');
					$xmlExtensions->appendChild($xmlExt);
					$xmlExt->setAttribute('dictid',$objRel->DictId);
					$xmlExt->setAttribute('id',$objRel->Id);
					$xmlExt->setAttribute('label',$objRel->InverseLabel);
					$xmlExt->setAttribute('description',$objRel->Description);
					
					$xmlExt->appendChild($dom->importNode($this->getXmlClass($objRel->SubjectDictId, $objRel->SubjectId)->documentElement,true));
								
				}
			}
			
			
			
		}
		
		return $dom;
		
	}
	
	
	private function getXmlComplexProperties($xmlParent, $ParentProperty){
		
		$System = $this->System;
		$nsLde = $System->Config->Namespaces['lde'];
		$nsMeta = $System->Config->Namespaces['meta'];		
		
		$dom = $xmlParent->ownerDocument;
		
		foreach ($ParentProperty->ElementGroups as $objElementGroup){
			$xmlComplexProperties = $dom->createElementNS($nsLde, 'ComplexProperties');
			$xmlParent->appendChild($xmlComplexProperties);
			
			foreach ($objElementGroup->Elements as $objElement){
				$objProperty = $this->getProperty($objElement->DictId, $objElement->PropId);
				if (is_object($objProperty)){
					$xmlProperty = $dom->createElementNS($nsLde, 'Property');
					$xmlComplexProperties->appendChild($xmlProperty);
					$xmlProperty->setAttribute('dictid',$objProperty->DictId);
					$xmlProperty->setAttribute('id',$objProperty->Id);
					$xmlProperty->setAttribute('label',$objProperty->Label);
					$xmlProperty->setAttribute('description',$objProperty->Description);
					
					$this->getXmlComplexProperties($xmlProperty, $objProperty);

				}		
			}
		}
		
	}
	
	public function getProperty($DictId, $Id){
		
		$objResult = null;

		if (!isset($this->Dictionaries[$DictId])){
			return false;
		}
		$objDict = $this->Dictionaries[$DictId];
		
		if (!isset($objDict->Properties[$Id])){
			return false;
		}
		$objResult = $objDict->Properties[$Id];
		
		return $objResult;
	}

	public function getList($DictId, $Id){
		
		$objResult = null;

		if (!isset($this->Dictionaries[$DictId])){
			return false;
		}
		$objDict = $this->Dictionaries[$DictId];
		
		if (!isset($objDict->Lists[$Id])){
			return false;
		}
		$objResult = $objDict->Lists[$Id];
		
		return $objResult;
	}

	public function getValue($DictId, $Id){
		
		$objResult = null;

		if (!isset($this->Dictionaries[$DictId])){
			return false;
		}
		$objDict = $this->Dictionaries[$DictId];
		
		if (!isset($objDict->Values[$Id])){
			return false;
		}
		$objResult = $objDict->Values[$Id];
		
		return $objResult;
	}
	
	
	public function getRelationship($DictId, $Id){
		
		$objResult = null;

		if (!isset($this->Dictionaries[$DictId])){
			return false;
		}
		$objDict = $this->Dictionaries[$DictId];
		
		if (!isset($objDict->Relationships[$Id])){
			return false;
		}
		$objResult = $objDict->Relationships[$Id];
		
		return $objResult;
	}
	
	
	public function ClassProperties($DictId=null, $ClassId=null, $arrClassProperties = null){
		if (is_null($arrClassProperties)){
			$arrClassProperties = array();
		}
		
		if (is_null($DictId)){
			return array();
		}
		if (is_null($ClassId)){
			return array();
		}
		
		if (!isset($this->Dictionaries[$DictId])){
			return array();
		}		
		$Dict = $this->Dictionaries[$DictId];
		
		if (!isset($Dict->Classes[$ClassId])){
			return array();
		}		
		$Class = $Dict->Classes[$ClassId];
		
		$ThisClassProperties = array();
// find properties for this class, so that they can be put at the start of the final array, so that super class properties come before sub class properties		
		
		foreach ($Class->Properties as $ClassProperty){
			
			$Select = true;
// check property exists
			if (!$this->getProperty($ClassProperty->PropDictId, $ClassProperty->PropId)){
				$Select = false;
			}
			
			if ($Select){
				foreach ($arrClassProperties as $existingClassProperty){				
					if ($ClassProperty->PropDictId == $existingClassProperty->PropDictId){
						if ($ClassProperty->PropId == $existingClassProperty->PropId){
							$Select = false;
							continue;
						}
					}
					
					foreach ($this->SubProperties($ClassProperty->PropDictId, $ClassProperty->PropId) as $SubProp){
	// ignore properties which already have a sub-property					
						if ($SubProp->DictId == $existingClassProperty->PropDictId){
							if ($SubProp->Id == $existingClassProperty->PropId){
								$Select = false;
								continue 2;
							}
						}					
					}
					
					
				}
			}
			if ($Select){
				$ThisClassProperties[] = $ClassProperty;				
			}
		}
		
		$arrClassProperties = array_merge($ThisClassProperties, $arrClassProperties );
		
		if (!is_null($Class->SubClassOf)){
			$SuperClassId = $Class->SubClassOf;
			$SuperDictId = $DictId;
			if (!is_null($Class->SubDictOf)){
				$SuperDictId = $Class->SubDictOf;
			}
			$arrClassProperties = $this->ClassProperties($SuperDictId, $SuperClassId, $arrClassProperties);
			
		}
		
		return $arrClassProperties;
		
	}
	

	public function RelProperties($DictId=null, $RelId=null, $arrHasProperties = null){
		if (is_null($arrHasProperties)){
			$arrHasProperties = array();
		}
		
		if (is_null($DictId)){
			return array();
		}
		if (is_null($RelId)){
			return array();
		}
		
		if (!isset($this->Dictionaries[$DictId])){
			return array();
		}		
		$Dict = $this->Dictionaries[$DictId];
		
		if (!isset($Dict->Relationships[$RelId])){
			return array();
		}		
		$Rel = $Dict->Relationships[$RelId];
		
		$ThisHasProperties = array();		
		
		foreach ($Rel->Properties as $HasProperty){
			
			$Select = true;
			
			
			// check property exists
			if (!$this->getProperty($HasProperty->PropDictId, $HasProperty->PropId)){
				$Select = false;
			}
			
			if ($Select){
				foreach ($arrHasProperties as $existingHasProperty){				
					if ($HasProperty->PropDictId == $existingHasProperty->PropDictId){
						if ($HasProperty->PropId == $existingHasProperty->PropId){
							$Select = false;
							continue;
						}
					}
					
					foreach ($this->SubProperties($HasProperty->PropDictId, $HasProperty->PropId) as $SubProp){
	// ignore properties which already have a sub-property					
						if ($SubProp->DictId == $existingHasProperty->PropDictId){
							if ($SubProp->Id == $existingHasProperty->PropId){
								$Select = false;
								continue 2;
							}
						}					
					}
					
					
				}
			}
			if ($Select){
				$ThisHasProperties[] = $HasProperty;
			}
		}
		
		$arrHasProperties = array_merge($ThisHasProperties, $arrHasProperties );
		
		
/*		if (!is_null($Class->SubClassOf)){
			$SuperClassId = $Class->SubClassOf;
			$SuperDictId = $DictId;
			if (!is_null($Class->SubDictOf)){
				$SuperDictId = $Class->SubDictOf;
			}
			$arrClassProperties = $this->ClassProperties($SuperDictId, $SuperClassId, $arrClassProperties);
			
		}
*/		
		return $arrHasProperties;
		
	}
	

	public function ConceptClasses($ConceptName){
// returns an array of classes
		$arrClasses = array();
		
		foreach ($this->Dictionaries as $objDict){
			foreach ($objDict->Classes as $objClass){
				if (is_null($objClass->SubClassOf)){
					if ($objClass->Concept == $ConceptName){
						$arrClasses[$objDict->Id][$objClass->Id] = $objClass;
					}
				}
			}
		}
		
		return $arrClasses;
			
	}

	
	
	public function SubClasses($DictId=null, $ClassId=null, $arrSubClasses = null){

		if (is_null($arrSubClasses)){
			$arrSubClasses = array();
		}
		
		if (is_null($DictId)){
			return array();
		}
		if (is_null($ClassId)){
			return array();
		}
		
		if (!isset($this->Dictionaries[$DictId])){
			return array();
		}		
		$Dict = $this->Dictionaries[$DictId];
		
		if (!isset($Dict->Classes[$ClassId])){
			return array();
		}		
		$Class = $Dict->Classes[$ClassId];

		foreach ($this->Dictionaries as $optDict){
			foreach ($optDict->Classes as $optClass){
				if ($optClass->SubDictOf == $DictId ){
					if ($optClass->SubClassOf == $ClassId){
						
						$Select = true;
						foreach ($arrSubClasses as $existingClass){				
							if ($optClass->DictId == $existingClass->DictId){
								if ($optClass->Id == $existingClass->Id){
									$Select = false;
								}
							}
						}
						if ($Select){
							$arrSubClasses[] = $optClass;
							$arrSubClasses = $this->SubClasses($optDict->Id, $optClass->Id, $arrSubClasses);
						}
						
						
					}
				}
			}
		}
		
		return $arrSubClasses;
		
	}

	public function SameAsClasses($DictId=null, $ClassId=null){
				
		if (is_null($DictId)){
			return array();
		}
		if (is_null($ClassId)){
			return array();
		}
		
		if (!isset($this->Dictionaries[$DictId])){
			return array();
		}		
		$Dict = $this->Dictionaries[$DictId];
		
		if (!isset($Dict->Classes[$ClassId])){
			return array();
		}		
		$Class = $Dict->Classes[$ClassId];
		
		$Classes = array();
		foreach ($Class->SameAsClasses as $SameAsClass){			
			$objClassSameAs = $this->getClass($SameAsClass->DictId, $SameAsClass->ClassId);
			if (is_object($objClassSameAs)){
				$Classes[$SameAsClass->Sequence] = $objClassSameAs;
			}
		}

		return $Classes;
		
	}
	public function SuperClasses($DictId=null, $ClassId=null, $arrSuperClasses = null){

		if (is_null($arrSuperClasses)){
			$arrSuperClasses = array();
		}
		
		if (is_null($DictId)){
			return array();
		}
		if (is_null($ClassId)){
			return array();
		}
		
		if (!isset($this->Dictionaries[$DictId])){
			return array();
		}		
		$Dict = $this->Dictionaries[$DictId];
		
		if (!isset($Dict->Classes[$ClassId])){
			return array();
		}		
		$Class = $Dict->Classes[$ClassId];
		
		if (!is_null($Class->SubDictOf)){
			if (!is_null($Class->SubClassOf)){
				if (isset($this->Dictionaries[$Class->SubDictOf])){
					if (isset($this->Dictionaries[$Class->SubDictOf]->Classes[$Class->SubClassOf])){
						$SuperClass = $this->Dictionaries[$Class->SubDictOf]->Classes[$Class->SubClassOf];

						$Select = true;
						foreach ($arrSuperClasses as $existingClass){				
							if ($Class->SubDictOf == $existingClass->DictId){
								if ($Class->SubClassOf == $existingClass->Id){
									$Select = false;
								}
							}
						}
						if ($Select){
							$arrSuperClasses[] = $SuperClass;
							$arrSuperClasses = $this->SuperClasses($SuperClass->DictId, $SuperClass->Id, $arrSuperClasses);
						}
					}
				}
			}
		}
		
		return $arrSuperClasses;
		
	}

	public function SubProperties($DictId=null, $PropId=null, $arrSubProperties = null){

		if (is_null($arrSubProperties)){
			$arrSubProperties = array();
		}
		
		if (is_null($DictId)){
			return array();
		}
		if (is_null($PropId)){
			return array();
		}
		
		if (!isset($this->Dictionaries[$DictId])){
			return array();
		}		
		$Dict = $this->Dictionaries[$DictId];
		
		if (!isset($Dict->Properties[$PropId])){
			return array();
		}				
		
		$Prop = $Dict->Properties[$PropId];

		foreach ($this->Dictionaries as $optDict){
			foreach ($optDict->Properties as $optProp){
				if ($optProp->SubDictOf == $DictId ){
					if ($optProp->SubPropertyOf == $PropId){
						
						$Select = true;
						foreach ($arrSubProperties as $existingProp){				
							if ($optProp->DictId == $existingProp->DictId){
								if ($optProp->Id == $existingProp->Id){
									$Select = false;
								}
							}
						}
						if ($Select){
							$arrSubProperties[] = $optProp;
							$arrSubProperties = $this->SubProperties($optDict->Id, $optProp->Id, $arrSubProperties);
						}
					}
				}
			}
		}
		
		return $arrSubProperties;
		
	}
	
	
	public function RelationshipsFor($SubjectDictId=null, $SubjectClassId=null, $ObjectDictId = null, $ObjectClassId = null,  $arrRelationships = array()){

// check each relationship to see if it is valid for Subject and Object.

		foreach ($this->Dictionaries as $RelDict){
			foreach ($RelDict->Relationships as $Rel){
				$Select = true;
				if (!is_null($SubjectDictId)){
					$Select = false;
					if (isset($this->Dictionaries[$SubjectDictId])){
						if (!is_null($SubjectClassId)){
							if (isset($this->Dictionaries[$SubjectDictId]->Classes[$SubjectClassId])){
								$Class = $this->Dictionaries[$SubjectDictId]->Classes[$SubjectClassId];
								if ($Rel->SubjectDictId == $Class->DictId && $Rel->SubjectId == $Class->Id){
									$Select = true;
								}
								else
								{
									foreach ($this->SuperClasses($Class->DictId, $Class->Id) as $SuperClass){
										if ($Rel->SubjectDictId == $SuperClass->DictId && $Rel->SubjectId == $SuperClass->Id){
											$Select = true;
										}										
									}
								}
							}
						}
					}
				}
				
				
				if (!is_null($ObjectDictId)){
					$Select = false;
					if (isset($this->Dictionaries[$ObjectDictId])){
						if (!is_null($ObjectClassId)){
							if (isset($this->Dictionaries[$ObjectDictId]->Classes[$ObjectClassId])){
								$Class = $this->Dictionaries[$ObjectDictId]->Classes[$ObjectClassId];
								if ($Rel->ObjectDictId == $Class->DictId && $Rel->ObjectId == $Class->Id){
									$Select = true;
								}
								else
								{
									foreach ($this->SuperClasses($Class->DictId, $Class->Id) as $SuperClass){
										if ($Rel->ObjectDictId == $SuperClass->DictId && $Rel->ObjectId == $SuperClass->Id){
											$Select = true;
										}										
									}
								}
							}
						}
					}
				}
				
				
				if ($Select === true){
					$arrRelationships[] = $Rel;
				}
				
			}
		}
		return $arrRelationships;
	}
	
	public function getDictDot($DictId, $Style=1){

		$Script = "";
		
		$this->dotStyle = $Style;
		$this->dotConceptClusters = array();
		$this->dotNodes = array();
		$this->dotComplexGroups = array();
		$this->dotDictId = $DictId;
		
		if (!isset($this->Dictionaries[$DictId])){
			return;
		}

		$this->dotDictId = $DictId;
		
		$objDict = $this->Dictionaries[$DictId];
		$objGraph = new clsGraph();			
		$this->dotConceptClusters = array();
		$this->dotNodes = array();

		foreach ($objDict->Classes as $optClass){
			$this->getClassDot($DictId, $optClass->Id, $this->dotStyle, $objGraph);
		}

		foreach ($objDict->Relationships as $optRel){
			$this->getRelationshipDot($DictId, $optRel->Id, $this->dotStyle, $objGraph);
		}
				
		$Script = $objGraph->script;
		
		return $Script;
		return;
	}
	
	public function getClassDot($DictId, $ClassId, $Style=1, $objGraph = null, $Level = 1){

		if (is_null($this->dotDictId)){
			$this->dotDictId = $DictId;
			$this->dotStyle = $Style;
			$this->dotConceptClusters = array();
			$this->dotNodes = array();
			$this->dotComplexGroups = array();
		}
		
		$objClass = $this->getClass($DictId, $ClassId);
		
		$Script = "";

		if (is_null($objGraph)){
			$objGraph = new clsGraph();
		}

		$NodeId = 'dict_'.$DictId.'_class_'.$objClass->Id;
		if (isset($this->dotNodes[$NodeId])){
			return;
		}
		
		$Color = 'lightblue';
		if (!($DictId == $this->dotDictId)){
			$Color = 'lightgrey';
		}
		
		$ThisGraph = $objGraph;

		$Shape = null;
		$Height = null;
		$Width = null;		
		
		switch ($Style){
			
			case 1:

				if ($this->dotStyle == 1){
					$Concept = $objClass->Concept;
					if (!IsEmptyString($Concept)){
						if (!isset($this->dotConceptClusters[$Concept])){					
							$Label = $Concept;
							$this->dotConceptClusters[$Concept] = $objGraph->addSubGraph("cluster",$Label);
						}
						$ThisGraph = $this->dotConceptClusters[$Concept];
					}
				}
				
				$Label = $ThisGraph->FormatDotLabel($objClass->Label,12);
				$Shape = 'circle';
				$Height = 0.7;
				$Width = 0.7;
				break;
				
			case 2:		
		
		
				$Shape="plaintext";
				
				$HeaderLabel = $objGraph->FormatDotCell($objClass->Label,50)."<br/>(".strtoupper($objClass->Concept).")";
				
				$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
		
				$Label .= "<tr><td colspan='3' bgcolor='$Color'>$HeaderLabel</td></tr>";
				
				$PropSeq = 0;
				foreach ($this->ClassProperties($DictId, $ClassId) as $objClassProperty){
					
					$PropSeq = $PropSeq + 1;
					
					$objProperty = $this->getProperty($objClassProperty->PropDictId, $objClassProperty->PropId);
					$DataType = '';
					
					switch ($objProperty->Type){
						case 'simple':
							if (is_object($objProperty->Field)){
								$DataType = $objProperty->Field->DataType;
							}
							break;
						case 'complex':
							$this->ComplexDot($NodeId.':'.$PropSeq, $objProperty,$objGraph);
							break;
					}
					
					$Cardinality = $objClassProperty->Cardinality;
					if ($Cardinality == 'one'){
						$Cardinality = '';
					}

					$PropColor = '';
					if (!($objClassProperty->DictId == $this->dotDictId)){
						$PropColor = "bgcolor='lightgrey'";
					}
					
					$Label .= "<tr><td PORT='$PropSeq' align='left' balign='left' valign='top' $PropColor>".$objGraph->FormatDotCell($objProperty->Label,20)."</td>  <td  align='left' balign='left' valign='top'>".$Cardinality."</td>  <td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($DataType,30)."</td></tr>";
				}
				
				$Label .= "</table>>";
			
				break;
				
			default:
				return;
				break;
		}

		$this->dotNodes[$NodeId] = $NodeId;		
		$NodeUrl = "class.php?dictid=$DictId&classid=$ClassId";
		$ThisGraph->addNode($NodeId,$Label,$Shape,$Color,$Height,$Width,$NodeUrl);
				
		if (!is_null($objClass->SubClassOf)){			
			$this->getClassDot($objClass->SubDictOf, $objClass->SubClassOf, 1, $objGraph, $Level);
			$SuperNodeId = 'dict_'.$objClass->SubDictOf.'_class_'.$objClass->SubClassOf;
			$ThisGraph->addEdge($NodeId,$SuperNodeId,"sub class of",null,'dotted');
		}

		if ($Level == 1){
			$arrClassRels = array_merge($this->RelationshipsFor($DictId, $ClassId), $this->RelationshipsFor(null, null,$DictId, $ClassId));
			foreach ($arrClassRels as $objRel){
				$this->getRelationshipDot($objRel->DictId, $objRel->Id, $this->dotStyle, $objGraph, $Level);
			}			
		}
		

		$Script = $objGraph->script;
		
		return $Script;
	}

	
	public function getRelationshipDot($DictId, $RelId, $Style, $objGraph = null, $Level = 1){
		
		if (is_null($this->dotDictId)){
			$this->dotDictId = $DictId;
			$this->dotStyle = $Style;
			$this->dotConceptClusters = array();
			$this->dotNodes = array();
			$this->dotComplexGroups = array();
		}
		
		$objRel = $this->getRelationship($DictId, $RelId);		
				
		$Script = "";

		if (is_null($objGraph)){
			$objGraph = new clsGraph();
		}
		
		$RelLabel = $objRel->Label;

		$NodeId = 'dict_'.$DictId.'_relationship_'.$RelId;
			
			
		switch ($Style){
			case 1:
				$Label = $objGraph->FormatDotLabel($RelLabel,12);
				break;
			case 2:
				$HeaderLabel = $objGraph->FormatDotCell($RelLabel,50);									
				
				$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
		
				$Label .= "<tr><td colspan='3' bgcolor='palegreen'>$HeaderLabel</td></tr>";
				
				$PropSeq = 0;
				foreach ($this->RelProperties($DictId, $RelId) as $objRelProperty){
					
					$PropSeq = $PropSeq + 1;
					
					$objProperty = $this->getProperty($objRelProperty->PropDictId, $objRelProperty->PropId);
					$DataType = '';
					
					switch ($objProperty->Type){
						case 'simple':
							if (is_object($objProperty->Field)){
								$DataType = $objProperty->Field->DataType;
							}
							break;
						case 'complex':
							$this->ComplexDot($NodeId.':'.$PropSeq, $objProperty,$objGraph);
							break;
					}
					
					$Cardinality = $objRelProperty->Cardinality;
					if ($Cardinality == 'one'){
						$Cardinality = '';
					}
								
					
					$PropColor = '';
					if (!($objProperty->DictId == $this->dotDictId)){
						$PropColor = "bgcolor='lightgrey'";
					}
					
					$Label .= "<tr><td PORT='$PropSeq' align='left' balign='left' valign='top' $PropColor >".$objGraph->FormatDotCell($objProperty->Label,20)."</td>  <td  align='left' balign='left' valign='top'>".$Cardinality."</td>  <td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($DataType,30)."</td></tr>";
				}
				
				
				$Label .= "</table>>";
				break;
				
			default:
				return;
				
		}

		$this->getClassDot($objRel->SubjectDictId, $objRel->SubjectId, $this->dotStyle, $objGraph, $Level+1);		
		$this->getClassDot($objRel->ObjectDictId, $objRel->ObjectId, $this->dotStyle, $objGraph, $Level+1);
		
		$FromNode = 'dict_'.$objRel->SubjectDictId.'_class_'.$objRel->SubjectId;
		$ToNode = 'dict_'.$objRel->ObjectDictId.'_class_'.$objRel->ObjectId;
		$objGraph->addEdge($FromNode,$ToNode,$Label);
		
		$Script = $objGraph->script;

		return $Script;
		
		
	}
	
	private function ComplexDot($Port, $objProperty, $objGraph){

		$GroupSeq = 0;
		foreach ($objProperty->ElementGroups as $objElementGroup){
			$GroupSeq = $GroupSeq + 1;
			
// check if the group has already been added	
			$NodeId = null;
			foreach ($this->dotComplexGroups as $ComplexNodeId=>$optComplexGroup){
				if ($optComplexGroup === $objElementGroup){
					$NodeId = ComplexNodeId;
				}				
			}
			if (is_null($NodeId)){
				$NodeId = 'complex_'.(count($this->dotComplexGroups) + 1);
				$this->dotComplexGroups[$NodeId] = $objProperty;

				$Shape="plaintext";
				$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
				
				$PropSeq = 0;
				foreach ($objElementGroup->Elements as $objElement){
					
					$PropSeq = $PropSeq + 1;
		
					$objElementProperty = $this->getProperty($objElement->DictId, $objElement->PropId);
					if (is_object($objElementProperty)){
						
						$DataType = "";
				
						switch ($objElementProperty->Type){
							case 'simple':
								if (is_object($objElementProperty->Field)){
									$DataType = $objElementProperty->Field->DataType;
								}
								break;
							case 'complex':
								$this->ComplexDot($NodeId.':'.$PropSeq,$objElementProperty,$objGraph);
								break;
						}
						
						
						$Cardinality = $objElement->Cardinality;
						if ($Cardinality == 'one'){
							$Cardinality = '';
						}

						$PropColor = '';
						if (!($objElementProperty->DictId == $this->dotDictId)){
							$PropColor = "bgcolor='lightgrey'";
						}
												
						$Label .= "<tr><td PORT='$PropSeq' align='left' balign='left' valign='top' $PropColor>".$objGraph->FormatDotCell($objElementProperty->Label,20)."</td> <td  align='left' balign='left' valign='top'>".$Cardinality."</td>     <td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($DataType,30)."</td></tr>";
					}
				}
			
				$Label .= "</table>>";
	
				$NodeUrl = "property.php?dictid=".$objProperty->DictId."&propid=".$objProperty->Id.'#elements';
				$objGraph->addNode($NodeId,$Label,$Shape,null,null,null,$NodeUrl);
				
			}
			
			$objGraph->addEdge($Port, $NodeId);	
			
		}
	}
	
}





class clsDict {
	
	public $Dicts = null;
	
	public $EcoSystem = null;
	
	private $FilePath = null;
	
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	
	public $xml = null;

	private $Id = null;
	private $GroupId = null;
	private $OwnerId = null;
	private $Name = null;
	private $Description = null;
	private $Publish = false;
	
	private $Exists = false;
	
	private $Classes = array();
	private $Properties = array();
	private $Relationships = array();
	private $Lists = array();
	private $Values = array();
	
	private $canView = false;
	private $canEdit = false;
	private $canControl = false;
	
	
	private $folder = "dictionaries";
	private $filename = "";
	
	private $DictNamespace = "http://schema.legsb.gov.uk/lde/dictionary/";
	private $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";
	
	private $System;
	
	
	public function __get($name){
		switch ($name){
			default:
				return $this->$name;
				break;
		}
		
	}

	public function __set($name,$value){
		switch ($name){
			case "GroupId":
				$this->dom->documentElement->setAttribute("groupid",$value);
				break;
			case "Name":
				$xmlMeta = $this->xpath->query("/dict:Dictionary/meta:Meta")->item(0);
				
				$xmlMeta->setAttribute("name",$value);
				break;
			case "Description";
				$xmlMeta = $this->xpath->query("/dict:Dictionary/meta:Meta")->item(0);
				$xmlDesc = $this->xpath->query("meta:Description",$xmlMeta)->item(0);
				if (!is_object($xmlDesc)){
					$xmlDesc = $this->dom->createElementNS($this->MetaNamespace, 'Description');
				}
				$xmlDesc->nodeValue = $value;
				$xmlMeta->appendChild($xmlDesc);
				break;
			case "Publish":
				
				switch ($value){
					case true:
						$this->dom->documentElement->setAttribute("publish","yes");
						break;
					default:
						$this->dom->documentElement->setAttribute("publish","no");
						break;
				}
				
				break;
		}
	}

	public function __construct($Id=null, $Dicts=null, $xml=null, $EcoUri = null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		$this->System = $System;
		
		$this->Dicts = $Dicts;
		
		$this->Id = $Id;
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		
		if (!is_null($xml)){
			if (@$this->dom->loadXML($xml) === false){
				return false;
			}
			$this->Exists = true;
			$this->EcoSystem = $EcoUri;			
		}
		else
		{
		
			$this->filename = $this->Id.".xml";		
			$this->FilePath = $System->path.$this->folder."//".$this->filename;
			
			
					
			if (@$this->dom->load($this->FilePath) === false){
	
				$this->dom = new DOMDocument('1.0', 'utf-8');
				$this->dom->formatOutput = true;
	
				$DocumentElement = $this->dom->createElementNS($this->DictNamespace, 'Dictionary');
				$this->dom->appendChild($DocumentElement);
				$DocumentElement->setAttribute("xmlns:meta", $this->MetaNamespace);
				
				
				$DocumentElement->setAttribute("id",$this->Id);
				
				$xmlMeta = $this->dom->createElementNS($this->MetaNamespace, 'Meta');
				$DocumentElement->appendChild($xmlMeta);			
				
				$xmlMeta->setAttribute("created",date('Y-m-d'));
				$xmlMeta->setAttribute("by",$System->User->Id);						
			}
			else
			{
				$this->Exists = true;
			}
		}
		
		if (!is_null($EcoUri)){
			$this->dom->documentElement->setAttribute('ecosystem',$EcoUri);
		}
		
		$this->xml = $this->dom->documentElement;
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		$this->RefreshMeta();		
		$this->RefreshClasses();
		$this->RefreshProperties();
		$this->RefreshRelationships();
		$this->RefreshLists();
		$this->RefreshValues();
		
		if ($this->Publish == true){
			$this->canView = true;
		}
		
		if (is_null($this->EcoSystem)){
			if ($System->LoggedOn){			
				if ($this->OwnerId == $System->User->Id){
					$this->canView = true;
					$this->canEdit = true;
					$this->canControl = true;				
				}
			}
		}

		
		if ($this->Exists){
			if (!is_null($this->GroupId)){
				if (isset($this->Dicts->Groups[$this->GroupId])){
					$objGroup = $this->Dicts->Groups[$this->GroupId];			
				}
				else
				{
					$objGroup = new clsGroup($this->GroupId);
					$this->Dicts->Groups[$this->GroupId] = $objGroup;
				}
				
				if ($objGroup->canView == true){
					$this->canView = true;
				}
				if ($objGroup->canEdit == true){
					$this->canEdit = true;
				}
			}
		}
				
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('dict', $this->DictNamespace);
		$this->xpath->registerNamespace('meta', $this->MetaNamespace);
		
	}

	public function refreshMeta(){

		$xmlMeta = $this->xpath->query("/dict:Dictionary/meta:Meta")->item(0);		
		
		if (is_null($this->EcoSystem)){
			$this->GroupId = $this->dom->documentElement->getAttribute("groupid");
			$this->OwnerId = $xmlMeta->getAttribute("by");
		}
		
		$this->Name = $xmlMeta->getAttribute("name");
		$this->Description = xmlElementValue($xmlMeta, "Description");
		
		$this->Publish = false;
		if ($this->dom->documentElement->getAttribute("publish") == "yes"){
			$this->Publish = true;
		}
		
	}

	public function refreshClasses(){

		$this->Classes= array();
		
		foreach ($this->xpath->query("/dict:Dictionary/dict:Classes/dict:Class") as $xmlClass){
			$objClass = new clsClass();
			$objClass->xml = $xmlClass;
			$objClass->Id = $xmlClass->getAttribute("id");
			$objClass->DictId = $this->Id;
			$objClass->Concept = $xmlClass->getAttribute("concept");
			$objClass->Label = xmlElementValue($xmlClass, "Label");
			$objClass->Heading = xmlElementValue($xmlClass, "Heading");
			$objClass->Description = xmlElementValue($xmlClass, "Description");
			$objClass->Source = xmlElementValue($xmlClass, "Source");
			
			$SuperDictId = "";
			$SuperClassId = $xmlClass->getAttribute("subClassOf");			
			if (!empty($SuperClassId)){
				$SuperDictId = $this->Id;
				if (!($xmlClass->getAttribute("subDictOf") == "")){
					$SuperDictId = $xmlClass->getAttribute("subDictOf");
				}				
				$objClass->SubClassOf = $SuperClassId;
				$objClass->SubDictOf = $SuperDictId;
			}

			$Seq = 0;
			foreach ($this->xpath->query("dict:ClassProperties/dict:ClassProperty",$xmlClass) as $xmlClassProperty){
				$Seq = $Seq + 1;
				$objClassProperty = new clsClassProperty();
				
				$objClassProperty->xml = $xmlClassProperty;
				
				$objClassProperty->Id = $xmlClassProperty->getAttribute("id");
				$objClassProperty->DictId = $this->Id;
				$objClassProperty->ClassId = $objClass->Id;

				$objClassProperty->PropDictId = $this->Id;
				if (!($xmlClassProperty->getAttribute("propdictid") == "")){
					$objClassProperty->PropDictId = $xmlClassProperty->getAttribute("propdictid");					
				}
				$objClassProperty->PropId = $xmlClassProperty->getAttribute("propid");
				
				$objClassProperty->Sequence = $Seq;
				
				if (!($xmlClassProperty->getAttribute("cardinality") == "")){
					if (in_array($xmlClassProperty->getAttribute("cardinality"),$this->System->Config->Cardinalities)){
						$objClassProperty->Cardinality = $xmlClassProperty->getAttribute("cardinality");
					}
				}

				if ($xmlClassProperty->getAttribute("useAsName") == "true"){
					$objClassProperty->UseAsName = true;
				}
				if ($xmlClassProperty->getAttribute("useAsIdentifier") == "true"){
					$objClassProperty->UseAsIdentifier = true;
				}

				if (!($xmlClassProperty->getAttribute("useInLists") == "")){
					if (!($xmlClassProperty->getAttribute("useInLists") == "true")){
						$objClassProperty->UseInLists = false;
					}
				}
				
				$objClass->Properties[$objClassProperty->Id] = $objClassProperty;
			}
			
			
			$Seq = 0;
			foreach ($this->xpath->query("dict:SameAsClasses/dict:SameAsClass",$xmlClass) as $xmlSameAsClass){
				$Seq = $Seq + 1;
				$objSameAsClass = new clsSameAsClass();
				
				$objSameAsClass->xml = $xmlSameAsClass;
				
				$objSameAsClass->DictId = $this->Id;
				if (!($xmlSameAsClass->getAttribute("dictid") == "")){
					$objSameAsClass->DictId = $xmlSameAsClass->getAttribute("dictid");					
				}
				$objSameAsClass->ClassId = $xmlSameAsClass->getAttribute("classid");
				
				$objSameAsClass->Sequence = $Seq;
												
				$objClass->SameAsClasses[$Seq] = $objSameAsClass;
			}

			$xmlViz = $this->xpath->query("dict:Visualizer",$xmlClass)->item(0);
			if (is_object($xmlViz)){
				$objClassViz = new clsClassViz();
				$objClassViz->TypeId = $xmlViz->getAttribute('typeid');
				$objClass->Viz = $objClassViz;
				foreach ($this->xpath->query("dict:Params/dict:Param",$xmlViz) as $xmlParam){
					$objVizParam = new clsClassVizParam();
					$objVizParam->PropDictId = $xmlParam->getAttribute('propdictid');
					$objVizParam->PropId = $xmlParam->getAttribute('propid');
					$objClassViz->Params[$xmlParam->getAttribute('num')] = $objVizParam;
				}
			}
					
			$this->Classes[$objClass->Id] = $objClass;
			
		}
				
	}
	
	public function refreshProperties(){

		$this->Properties= array();
		
		foreach ($this->xpath->query("/dict:Dictionary/dict:Properties/dict:Property") as $xmlProp){
			$objProp = new clsProperty();
			$objProp->xml = $xmlProp;
			
			$objProp->Id = $xmlProp->getAttribute("id");
			$objProp->DictId = $this->Id;

			if (!($xmlProp->getAttribute("type") == "")){
				$objProp->Type = $xmlProp->getAttribute("type");
			}
			
			$objProp->Label = xmlElementValue($xmlProp, "Label");
			$objProp->Description = xmlElementValue($xmlProp, "Description");
			
			$SuperDictId = "";
			$SuperPropId = $xmlProp->getAttribute("subPropertyOf");			
			if (!empty($SuperPropId)){
				$SuperDictId = $this->Id;
				if (!($xmlProp->getAttribute("subDictOf") == "")){
					$SuperDictId = $xmlProp->getAttribute("subDictOf");
				}
				$objProp->SubPropertyOf = $SuperPropId;
				$objProp->SubDictOf = $SuperDictId;
			}
			
			switch ($objProp->Type){
				case 'simple':
					$xmlField = $this->xpath->query("dict:Field",$xmlProp)->item(0);
					
					$objProp->Field = new clsField($xmlField);
					$objProp->Field->DictId = $this->Id;
					$objProp->Field->PropId = $objProp->Id;
					
					
					foreach ($this->xpath->query("dict:PropertyLists/dict:PropertyList",$xmlProp) as $xmlPropertyList){
						$objPropertyList = new clsPropertyList();
						
						$objPropertyList->xml = $xmlPropertyList;
	
						$objPropertyList->ListId = $xmlPropertyList->getAttribute("listid");
						$objPropertyList->ListDictId = $this->Id;
						
						if (!($xmlPropertyList->getAttribute("listdictid") == "")){
							$objPropertyList->ListDictId = $xmlPropertyList->getAttribute("listdictid");
						}
						
						$objProp->Lists[] = $objPropertyList;
					}
					
					break;
				case 'complex':
					$Seq = 0;
					foreach ($this->xpath->query('dict:ElementGroups/dict:ElementGroup',$xmlProp) as $xmlElementGroup){
						$Seq = $Seq+1;
						$objElementGroup = new clsElementGroup();
						$objElementGroup->xml = $xmlElementGroup;
						$objProp->ElementGroups[$Seq] = $objElementGroup;
						
						foreach ($this->xpath->query('dict:Element',$xmlElementGroup) as $xmlElement){
							$objElement = new clsElement();
							$objElement->xml = $xmlElement;
							$objElement->DictId = $xmlElement->getAttribute('dictid');
							$objElement->PropId = $xmlElement->getAttribute('propid');
							$Cardinality = $xmlElement->getAttribute('cardinality');							
							if (!IsEmptyString($Cardinality)){
								if (in_array($Cardinality,$this->System->Config->Cardinalities)){
									$objElement->Cardinality = $Cardinality;
								}
							}
							$objElementGroup->Elements[] = $objElement;
						}
					}

					break;
			}
			
			
			$this->Properties[$objProp->Id] = $objProp;
			
		}
				
	}
	
	public function refreshRelationships(){

		$this->Relationships= array();
		
		foreach ($this->xpath->query("/dict:Dictionary/dict:Relationships/dict:Relationship") as $xmlRel){
			$objRel = new clsRelationship();
			$objRel->xml = $xmlRel;
			$objRel->Id = $xmlRel->getAttribute("id");
			$objRel->DictId = $this->Id;
			$objRel->ConceptRelationship = $xmlRel->getAttribute("conceptRelationship");
			$objRel->Label = xmlElementValue($xmlRel, "Label");
			$objRel->Description = xmlElementValue($xmlRel, "Description");
						
			$xmlInverse = $this->xpath->query("dict:Inverse",$xmlRel)->item(0);
			if (is_object($xmlInverse)){
				$objRel->InverseLabel = xmlElementValue($xmlInverse, "Label");
			}

			if (!($xmlRel->getAttribute('cardinality') == "")){
				$objRel->Cardinality = $xmlRel->getAttribute('cardinality');			
			}		
			
			if ($xmlRel->getAttribute('extending') == 'true'){
				$objRel->Extending = true;			
			}		
			if ($xmlRel->getAttribute('inverseextending') == 'true'){
				$objRel->InverseExtending = true;			
			}		
			
			
			$xmlSubject = $this->xpath->query("dict:Subject",$xmlRel)->item(0);
			if (is_object($xmlSubject)){
				$objRel->SubjectId = $xmlSubject->getAttribute("class");
				
				$objRel->SubjectDictId = $this->Id;
				$SubjectDictId = $xmlSubject->getAttribute("dict");
				if (!empty($SubjectDictId)){
					if (!($SubjectDictId == $this->Id)){
						$objRel->SubjectDictId = $SubjectDictId;
					}
				}
			}
			
			$xmlObject = $this->xpath->query("dict:Object",$xmlRel)->item(0);
			if (is_object($xmlObject)){
				$objRel->ObjectId = $xmlObject->getAttribute("class");
				
				$objRel->ObjectDictId = $this->Id;				
				$ObjectDictId = $xmlObject->getAttribute("dict");
				if (!empty($ObjectDictId)){
					if (!($ObjectDictId == $this->Id)){
						$objRel->ObjectDictId = $ObjectDictId;
					}
				}
			}
			
			$this->refreshHasProperties($objRel);
			
			$this->Relationships[$objRel->Id] = $objRel;
			
		}
				
	}
	
	public function refreshLists(){

		$this->Lists = array();
		
		foreach ($this->xpath->query("/dict:Dictionary/dict:Lists/dict:List") as $xmlList){
			$objList = new clsList();
			$objList->xml = $xmlList;
			$objList->Id = $xmlList->getAttribute("id");
			$objList->DictId = $this->Id;
			$objList->Label = xmlElementValue($xmlList, "Label");
			$objList->Description = xmlElementValue($xmlList, "Description");
			$objList->Source = xmlElementValue($xmlList, "Source");
			$objList->DescribedAt = xmlElementValue($xmlList, "DescribedAt");
			

			foreach ($this->xpath->query("dict:ListValues/dict:ListValue",$xmlList) as $xmlListValue){
				$objListValue = new clsListValue();
				
				$objListValue->xml = $xmlListValue;
				
				$objListValue->Id = $xmlListValue->getAttribute("id");
				$objListValue->DictId = $this->Id;
				$objListValue->ListId = $objList->Id;

				$objListValue->ValueDictId = $this->Id;
				if (!($xmlListValue->getAttribute("valuedictid") == "")){
					$objListValue->ListValueId = $xmlListValue->getAttribute("valuedictid");
				}
				$objListValue->ValueId = $xmlListValue->getAttribute("valueid");
								
				$objList->Values[$objListValue->Id] = $objListValue;
			}
						
			$this->Lists[$objList->Id] = $objList;
			
		}
				
	}
	

	public function refreshValues(){

		$this->Values = array();
		
		foreach ($this->xpath->query("/dict:Dictionary/dict:Values/dict:Value") as $xmlValue){
			$objValue = new clsValue();
			$objValue->xml = $xmlValue;
			$objValue->Id = $xmlValue->getAttribute("id");
			$objValue->DictId = $this->Id;
			$objValue->Label = xmlElementValue($xmlValue, "Label");
			$objValue->Description = xmlElementValue($xmlValue, "Description");
			$objValue->Code = xmlElementValue($xmlValue, "Code");
			$objValue->URI = xmlElementValue($xmlValue, "URI");
			
			$this->Values[$objValue->Id] = $objValue;
			
		}
				
	}
	
	
	public function refreshHasProperties($Parent){
		
		foreach ($this->xpath->query("dict:HasProperties/dict:HasProperty",$Parent->xml) as $xmlHasProperty){
			$objHasProperty = new clsHasProperty();
			
			$objHasProperty->xml = $xmlHasProperty;
			
			$objHasProperty->ParentId = $Parent->Id;
			switch (get_class($Parent)){
				case 'clsClass':
					$objHasProperty->ParentType = 'class';
					break;
				case 'clsRelationship':
					$objHasProperty->ParentType = 'relationship';
					break;					
			}
						
			$objHasProperty->Id = $xmlHasProperty->getAttribute("id");
			$objHasProperty->DictId = $this->Id;

			$objHasProperty->PropDictId = $this->Id;
			if (!($xmlHasProperty->getAttribute("propdictid") == "")){
				$objHasProperty->PropDictId = $xmlHasProperty->getAttribute("propdictid");					
			}
			$objHasProperty->PropId = $xmlHasProperty->getAttribute("propid");
			
			if (!($xmlHasProperty->getAttribute("cardinality") == "")){
				$objHasProperty->Cardinality = $xmlHasProperty->getAttribute("cardinality");
			}

			if ($xmlHasProperty->getAttribute("useAsName") == "true"){
				$objHasProperty->UseAsName = true;
			}
			if ($xmlHasProperty->getAttribute("useAsIdentifier") == "true"){
				$objHasProperty->UseAsIdentifier = true;
			}
			if (!($xmlHasProperty->getAttribute("useInLists") == "true")){
				$objHasProperty->UseInLists = false;
			}
			
			$Parent->Properties[$objHasProperty->Id] = $objHasProperty;
		}
		
	}
	
	
	public function Save(){
		global $System;
		if (!isset($System)){
			$System = clsSystem();
		}
		
		$this->dom->save($this->FilePath);
	}


}	

class clsClass{
	
	public $xml = null;
	
	public $Id = null;
	public $DictId = null;
	public $Concept = null;
	
	public $SubClassOf = null;
	public $SubDictOf = null;
	
	public $Label = "";
	public $Heading = "";
	public $Description = "";
	public $Source = "";

	public $Properties = array();
	public $SameAsClasses = array();

	public $Viz = null;
	
	
}

class clsProperty{

	public $xml = null;
	
	public $Id = null;
	public $DictId = null;
	
	public $Type = 'simple';
	
	public $SubPropertyOf = null;
	public $SubDictOf = null;
	
	public $Label = "";
	public $Description = "";
	
	public $Field = null;
	
	
	public $ElementGroups = array();
	public $Lists = array();	
	
}

class clsClassViz{
	public $TypeId = null;
	public $Params = array();
}


class clsClassVizParam{
	public $PropDictId = null;
	public $PropId = null;
}


class clsRelationship{

	public $xml = null;
	
	public $Id = null;
	public $DictId = null;
	public $ConceptRelationship = null;
	
	public $SubRelOf = null;
	public $SubDictOf = null;
	
	public $Label = "";
	public $Description = "";

	public $InverseLabel;
	public $Cardinality = 'many';
	
	public $Extending = false;
	public $InverseExtending = false;
	
	
	public $SubjectDictId = null;
	public $SubjectId = null;

	public $ObjectDictId = null;
	public $ObjectId = null;
	
	public $Properties = array();
	
}


class clsClassProperty{

	public $xml = null;

	public $Id = null;
	public $ClassId = null;
	public $DictId = null;

	public $PropId = null;
	public $PropDictId = null;

	public $Sequence = null;
	public $Cardinality = 'one';
	public $UseAsName = false;
	public $UseAsIdentifier = false;
	public $UseInLists = true;
	
	
}


class clsSameAsClass{

	public $xml = null;

	public $ClassId = null;
	public $DictId = null;

	public $Sequence = null;

}


class clsHasProperty{

	public $xml = null;

	public $Id = null;
	
	public $DictId = null;
	public $ParentType = null;
	public $ParentId = null;

	public $PropId = null;
	public $PropDictId = null;

	public $Cardinality = 'one';
	public $UseAsName = false;
	public $UseAsIdentifier = false;
	public $UseInLists = true;
	
}


class clsPropertyList{
	
	public $xml = null;
		
	public $ListId = null;
	public $ListDictId = null;
	
}


class clsHasPropertyList{
	
	public $xml = null;

	public $ParentType = null;
	public $ParentId = null;
	
	public $HasPropertyId = null;	
	
	public $ListId = null;
	public $ListDictId = null;
	
}


class clsElementGroup{

	public $xml = null;
	public $Elements = array();
	
	
	public function getElement($DictId, $PropId){
		foreach ($this->Elements as $optElement){
			if ($optElement->DictId == $DictId){
				if ($optElement->PropId == $PropId){
					return $optElement;
				}
			}
		}
		return false;
		
	}
	
	
}


class clsElement{
	
	public $xml = null;
	
	public $DictId = null;
	public $PropId =  null;
	public $Cardinality;	
}


class clsPart{

	public $xml = null;
	
	public $Id = null;
	public $DictId = null;	
	public $PropId = null;
	
	public $Type = 'simple';
	
	public $Label = "";
	public $Description = "";
	
	public $Cardinality = 'mandatory';
	
	public $Field = null;
			
}


class clsField{
	
	public $DictId = null;
	public $PropId = null;
	public $PartId = null;
	
	public $DataType = 'line';
	public $Length = null;
	
	public function __construct($xmlField = null){
		
		if (is_object($xmlField)){
			if (!(xmlElementValue($xmlField, "DataType")) == ""){
				$this->DataType = xmlElementValue($xmlField, "DataType");
			}
			if (!(xmlElementValue($xmlField, "Length")) == ""){
				$this->Length = xmlElementValue($xmlField, "Length");
			}
		}
	}
	
}


class clsList{

	public $xml = null;
	
	public $Id = null;
	public $DictId = null;
		
	public $Label = "";
	public $Description = "";
	public $Source = "";
	public $DescribedAt = "";
	
	public $Values = array();
	
}


class clsValue{

	public $xml = null;
	
	public $Id = null;
	public $DictId = null;
		
	public $Label = "";
	public $Description = "";
	public $Code = "";
	public $URI = "";
		
}

class clsListValue{

	public $xml = null;
	
	public $Id = null;
	public $ListId = null;
	public $DictId = null;
	
	public $ValueId = null;
	public $ValueDictId = null;
	
}

?>