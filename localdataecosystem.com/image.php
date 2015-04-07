<?php

	require_once("class/clsSystem.php");
	
	$System = new clsSystem();
	
	if (isset($_REQUEST['Id'])){
		$ImageId = $_REQUEST['Id'];
		
		$sql = "select * from tbl_image WHERE imgRecnum = $ImageId";
		$result = $System->db->query($sql);

		if ($result->num_rows>0) {
			$rstRow = $result->fetch_assoc();
			header('Content-type: ' . $rstRow['imgMimeType']);
		    header('Content-length: ' . $rstRow['imgSize']);
	    	echo $rstRow['imgData'];
		}
	}

?>