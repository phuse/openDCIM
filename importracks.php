<?php
        require_once( 'db.inc.php' );
        require_once( 'facilities.inc.php' );

        $cab=new Cabinet();
        $dept=new Department();
        $DC=new DataCenter();

        $taginsert="";
        $status="";
        $newdata="";
	$row=1;
if (($handle = fopen("cabinets.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 500, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $DataCenterID=CheckDataCenterID($data[0],$row); 
        $CabinetID=CheckCabinetExists($data[1],$DataCenterID); 
	//create the cabinet if it's missing or update it if it exists
        if ($CabinetID==0){
		$cab->CabinetID=null;
		echo "created:<br />";
	}else{
		echo "updated:<br />";
		$cab->CabinetID=$CabinetID;
		$cab->GetCabinet();
		//$newdata->CabinetLocation=$data[1]; 
		$newdata->CabinetHeight=$data[4]; 
		$newdata->DatacenterID=$DataCenterID; 
		$newdata->CabinetModel=$data[3]; 
		$newdata->CabinetMaxKW=$data[5]; 
		$newdata->CabinetMaxWeight=$data[6]; 
		$newdata->CabinetNotes=$data[8]; 
		$newdata->InstallationDate=date('m/d/Y', strtotime($data[7])); 
		echo $newdata->InstallationDate;
		//$cab->Location=trim($data[1]);
		//data_dump($cab);
	}
        $row++;
        echo "Datacenter:" . $data[0] . " id: " . $DataCenterID . "<br />\n";
        echo "Name:" . $data[1] . " Id:" . $CabinetID . "<br />\n";
        echo "Manufacturer:" . $data[2] . "<br />\n";
        echo "model:" . $data[3] . "<br />\n";
        echo "height" . $data[4] . "<br />\n";
        echo "maxkw: " . $data[5] . "<br />\n";
        echo "maxweight:" . $data[6] . "<br />\n";
        echo "installdate:" . $data[7] . "<br />\n";
        echo "<pre>" . $data[8] . "</pre><br />\n";
        for ($c=9; $c < $num; $c++) {
            echo "tag" . $c . ":" . $data[$c] . "<br />\n";
        } 
    }
    fclose($handle);
}
/* function setvars() {
	$this->MakeSafe();
        $this->DataCenterID=getDataCenterID($data[0]); 
}
*/
function CheckDataCenterID($name,$row) {
	//gets the id of the datacenter in the csv and creates a new datacenter if needed
	global $dbh;
	$sql="SELECT * FROM fac_DataCenter WHERE Name=\"$name\" LIMIT 1;";
	if ($DCrow=$dbh->query($sql)->fetch()){
        	return $DCrow['DataCenterID'];
        }else{
		$sql2="INSERT INTO fac_DataCenter (DataCenterID,Name) VALUES (NULL,\"$name\");";
		if ($dbh->query($sql2)){
			//return the created ID
			return $dbh->lastInsertId();
		} else {
			echo "aborting due to error creating new DC! in line " . $row;
			break;
		}
	}
}
function CheckCabinetExists($name,$DCid) {
	global $dbh;
	$sql="SELECT * FROM fac_Cabinet WHERE Location=\"$name\" AND DataCenterID=\"$DCid\" LIMIT 1;";
	if ($cabrow=$dbh->query($sql)->fetch()){
        	return $cabrow['CabinetID'];
        }else{
		return 0;
	}
}


/*
function makesafe() {
	$this->DataCenterID=intval($this->DataCenterID);
}
*/
?>
