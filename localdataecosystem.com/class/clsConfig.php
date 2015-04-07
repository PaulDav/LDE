<?php

class clsConfig {

	public $Vars = array();
	
	public $Namespaces = array();

	public $RelCardinalities = array();
	public $RelSubCardinalities = array();
	
	public $Cardinalities = array();
	public $SubCardinalities = array();
	
	public $DataTypes = array();
	public $ValueTables = array();	
	public $PropertyTypes = array();
	public $StatementTypes = array();
	public $ImportFileTypes = array();
	public $FilterTypes = array();
	public $DefTypes = array();
	public $OrgDefs = array();	
	public $LicenceDefs = array();	
	public $SetStatusTypes = array();
	public $SetContextTypes = array();
	public $SetLicenceTypeTypes = array();
	
	public $DotRenderer = null;
	public $VizFormats = array();

	public $MapRenderer = null;	
	
	public $Visualizers = array();
	
	public $Defaults = null;

	public function __construct($path=null){
		
	 	if (is_null($path)){
			$parse = parse_url($_SERVER["REQUEST_URI"]);
			$path = $parse['path'];
	 	}
		if (!(substr($path,-1) == '/')){
			$path = dirname($path)."/";
	 	} 
		$path = $_SERVER['DOCUMENT_ROOT']."/".$path."config";
	 	 	
	 	$this->Vars = parse_ini_file("$path/config.php",true);
	 	
	 	$this->Namespaces['lde'] = "http://schema.legsb.gov.uk/lde/";
	 	$this->Namespaces['meta'] = "http://schema.legsb.gov.uk/lde/metadata/";	 	
	 	$this->Namespaces['rights'] = "http://schema.legsb.gov.uk/lde/rights/";
	 	$this->Namespaces['dict'] = "http://schema.legsb.gov.uk/lde/dictionary/";
	 	

	 	$this->SetStatusTypes[1] = 'created';	 	
	 	$this->SetStatusTypes[20] = 'published';	 	
	 	$this->SetStatusTypes[30] = 'withdrawn';	 	
	 	$this->SetStatusTypes[90] = 'removed';	 	

	 	
	 	$objContext = new clsContext();
	 	$objContext->Id = 1;
	 	$objContext->Name = 'reference';
	 	$objContext->Color = 'gold';	 	
	 	$this->SetContextTypes[$objContext->Id] = $objContext;
	 	
	 	$objContext = new clsContext();
	 	$objContext->Id = 10;
	 	$objContext->Name = 'operational';
	 	$objContext->Color = 'lightblue';	 	
	 	$this->SetContextTypes[$objContext->Id] = $objContext;
	 	
	 	$objContext = new clsContext();
	 	$objContext->Id = 20;
	 	$objContext->Name = 'statistical';
	 	$objContext->Color = 'green';
	 	$this->SetContextTypes[$objContext->Id] = $objContext;
	 	
	 	$objContext = new clsContext();
	 	$objContext->Id = 30;
	 	$objContext->Name = 'analytical';
	 	$objContext->Color = 'yellow';
	 	$this->SetContextTypes[$objContext->Id] = $objContext;
	 	
	 	$objContext = new clsContext();
	 	$objContext->Id = 40;
	 	$objContext->Name = 'strategic';
	 	$objContext->Color = 'red';
	 	$this->SetContextTypes[$objContext->Id] = $objContext;	 	
	 	
	 	$this->SetLicenceTypeTypes[1] = 'open';
	 	$this->SetLicenceTypeTypes[10] = 'protected';	 		 	
	 	
	 	$this->RelCardinalities[] = 'one';
	 	$this->RelCardinalities[] = 'many';

	 	$this->RelSubCardinalities['one'] = array('one');
	 	$this->RelSubCardinalities['many'] = array('many','one');
	 	
	 	
	 	$this->Cardinalities[] = 'one';	 	
	 	$this->Cardinalities[] = 'many';
	 	
	 	$this->SubCardinalities['one'] = array('one');
	 	$this->SubCardinalities['many'] = array('many','one');
	 	
	 	
	 	$this->DataTypes[100] = 'line';	 	
	 	$this->DataTypes[200] = 'text';	 	
	 	$this->DataTypes[300] = 'date';	 	
	 	$this->DataTypes[400] = 'time';
	 	$this->DataTypes[500] = 'whole number';
	 	$this->DataTypes[600] = 'currency';
	 	$this->DataTypes[700] = 'number';
	 	$this->DataTypes[800] = 'value';
	 	$this->DataTypes[900] = 'URI';
	 	$this->DataTypes[950] = 'URL';
	 	

	 	$this->ValueTables[100] = 'tbl_value_string';	 	
	 	$this->ValueTables[200] = 'tbl_value_memo';	 	
	 	$this->ValueTables[300] = 'tbl_value_datetime';	 	
	 	$this->ValueTables[400] = 'tbl_value_datetime';	 	
	 	$this->ValueTables[500] = 'tbl_value_integer';	 	
	 	$this->ValueTables[600] = 'tbl_value_integer';	 	
	 	$this->ValueTables[700] = 'tbl_value_number';
	 	$this->ValueTables[800] = 'tbl_value_string';	 	
	 	$this->ValueTables[900] = 'tbl_value_string';
	 	$this->ValueTables[950] = 'tbl_value_string';
	 	
	 	
	 	$this->PropertyTypes[] = 'simple';	 	
	 	$this->PropertyTypes[] = 'complex';

	 	
	 	$this->StatementTypes[100] = 'subject';
	 	$this->StatementTypes[110] = 'matched to';
	 	$this->StatementTypes[200] = 'attribute';
	 	$this->StatementTypes[300] = 'link';

	 	
	 	$ValidDotRenderers = array();
	 	$ValidDotRenderers[] = 'google chart';
	 	$ValidDotRenderers[] = 'viz.js';

	 	if (isset($this->Vars['instance']['dotrenderer'])){
	 		if (in_array($this->Vars['instance']['dotrenderer'],$ValidDotRenderers)){
				$this->DotRenderer = $this->Vars['instance']['dotrenderer'];
	 		}
		}	 	
		
		$this->VizFormats[1] = 'image';
		$this->VizFormats[9] = 'dot';
		
		
		
		$ValidMapRenderers = array();
	 	$ValidMapRenderers[] = 'google javascript';
	 	$ValidMapRenderers[] = 'google static';

	 	if (isset($this->Vars['map']['renderer'])){
	 		if (in_array($this->Vars['map']['renderer'],$ValidMapRenderers)){
				$this->MapRenderer = $this->Vars['map']['renderer'];
	 		}
		}	 	
		
		
		$this->ImportFileTypes[] = 'csv';
		$this->ImportFileTypes[] = 'xml';
		
		$this->FilterTypes[] = 'is';
		$this->FilterTypes[] = 'is not';		
		$this->FilterTypes[] = 'contains';
		$this->FilterTypes[] = 'more than';
		$this->FilterTypes[] = 'less than';
		
		
		
		$objVisualizer = new clsVisualizer();
		$objVisualizer->Id = 1;
		$objVisualizer->Name = "map";
		$objVisualizer->Class = "vizMap";
		$objParam = new clsVisualiserParam();
		$objParam->Name = "Coordinate Reference System";
		$objVisualizer->Params[] = $objParam;
		$objParam = new clsVisualiserParam();
		$objParam->Name = "x Coordinate";
		$objVisualizer->Params[] = $objParam;
		$objParam = new clsVisualiserParam();
		$objParam->Name = "y Coordinate";
		$objVisualizer->Params[] = $objParam;
		
		$this->Visualizers[$objVisualizer->Id] = $objVisualizer;
		
		$objDefType = new clsDefinitionType;
		$objDefType->Id = 10;
		$objDefType->Name = 'Enabler';
		$objDefType->Heading = 'Enablers';
		$this->DefTypes[$objDefType->Id] = $objDefType;

		$objDefType = new clsDefinitionType;
		$objDefType->Id = 20;
		$objDefType->Name = 'Undertaking';
		$objDefType->Heading = 'Undertakings';
		$this->DefTypes[$objDefType->Id] = $objDefType;
		
		$objDefType = new clsDefinitionType;
		$objDefType->Id = 30;
		$objDefType->Name = 'Purpose';
		$objDefType->Heading = 'Purposes';
		$this->DefTypes[$objDefType->Id] = $objDefType;
		
		$objDefType = new clsDefinitionType;
		$objDefType->Id = 40;
		$objDefType->Name = 'Agreement';
		$objDefType->Heading = 'Agreements';
		$this->DefTypes[$objDefType->Id] = $objDefType;
		
		$objDefType = new clsDefinitionType;
		$objDefType->Id = 50;
		$objDefType->Name = 'Accreditation';
		$objDefType->Heading = 'Accreditations';
		$this->DefTypes[$objDefType->Id] = $objDefType;
		
		
		$objOrgDef = new clsOrgDefinition();
		$objOrgDef->DefTypeId = 40;
		$this->OrgDefs[40] = $objOrgDef;
		
		$objOrgDef = new clsOrgDefinition();
		$objOrgDef->DefTypeId = 50;
		$this->OrgDefs[50] = $objOrgDef;


		$objLicenceDef = new clsLicenceDefinition();
		$objLicenceDef->DefTypeId = 10;
		$this->LicenceDefs[10] = $objLicenceDef;

		$objLicenceDef = new clsLicenceDefinition();
		$objLicenceDef->DefTypeId = 20;
		$this->LicenceDefs[20] = $objLicenceDef;
		
		$objLicenceDef = new clsLicenceDefinition();
		$objLicenceDef->DefTypeId = 30;
		$this->LicenceDefs[30] = $objLicenceDef;
		
		$objLicenceDef = new clsLicenceDefinition();
		$objLicenceDef->DefTypeId = 40;
		$this->LicenceDefs[40] = $objLicenceDef;
		
		$objLicenceDef = new clsLicenceDefinition();
		$objLicenceDef->DefTypeId = 50;
		$this->LicenceDefs[50] = $objLicenceDef;
		
	 	$this->Defaults = new clsDefaults();
	 	
	}
		
}


class clsDefaults{
	public $LineLength = 30;
	
}

class clsVisualizer{
	public $Id = null;
	public $Name = null;
	public $Function = null;
	public $Params = array();	
}

class clsVisualiserParam{
	public $Name = null;
}

class clsDefinitionType{
	
	public $Id = null;
	public $Name = null;
	Public $Heading = null;
	
}


class clsOrgDefinition{
// sets if organisations can be associated with a definition for a period of time
	public $DefTypeId = null;	
	
}

class clsLicenceDefinition{
// sets if licence can be associated with a definition
	public $DefTypeId = null;	
	
}


class clsContext{
	public $Id = null;
	public $Name = null;
	public $Color = null;
}