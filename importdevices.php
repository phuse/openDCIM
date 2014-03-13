<?php
        require_once( 'db.inc.php' );
        require_once( 'facilities.inc.php' );

        $dept=new Department();
        $DC=new DataCenter();

        $taginsert="";
        $status="";
        $newdata="";
	$row=1;
if (($handle = fopen("cabinets.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 500, ",")) !== FALSE) {
        $cab=new Cabinet();
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $DataCenterID=CheckDataCenterID($data[14],$row); 
	//if the device has a parent set cabinetid to 0, and if the cabinet can not be found, produce a error and put the device in storage
	if ($data[16]{
		$CabinetID=0;
	}else{
        	$CabinetID=CheckCabinetExists($data[15],$DataCenterID); 
		if ($CabinetID=0){
		echo "error:" . $data[15] . " in row:" . $row . "is not defined, device " . $data[2] . "/" . $data[3] . " has been registered in storage<br>" 		
		$CabinetID=-1;
		}
  	}
	//populate data
	$newdata->CabinetHeight=$data[4]; 
	$newdata->Location=$data[1]; 
	$newdata->DataCenterID=$DataCenterID; 
	//only update existing if we have both model and manufacturer 
	if ($data[2] AND $data[3] OR $CabinetID==0){
		$newdata->Model=$data[2] . " " .$data[3]; 
	}else{
		$newdata->Model=$data[3]; 
	}
	$newdata->MaxKW=$data[5]; 
	$newdata->MaxWeight=$data[6]; 
	$newdata->Notes=$data[8]; 
	$newdata->InstallationDate=date('m/d/Y', strtotime($data[7])); 
	//create the cabinet if it's missing or update it if it exists
        if ($CabinetID==0){
		$cab->CabinetID=NULL;
		$cab=UpdateCabinetData($newdata);
		echo "created:<br />";
		$cab->CreateCabinet();
		var_dump($cab);
	}else{
		$cab->CabinetID=$CabinetID;
		echo "updated:<br />";
		$cab->GetCabinet();
		var_dump($cab);
		//$cab->CabinetID=$CabinetID;
		//$newdata->CabinetLocation=$data[1]; 
		//echo $newdata->InstallationDate;
		//$cab->Location=trim($data[1]);
		//data_dump($cab);
		$cab=UpdateCabinetData($newdata);
		//acutally push to db
		$cab->UpdateCabinet();
		var_dump($cab);
	}
        $row++;
        echo "Datacenter:" . $data[0] . " id: " . $DataCenterID . "<br />\n";
        echo "Name:" . $data[1] . " Id:" . $CabinetID . "<br />\n";
        echo "Manufacturer:" . $data[2] . "<br />\n";
        echo "model:" . $cab->CabinetModel . "<br />\n";
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
function UpdateCabinetData($indata) {
	global $cab;
 	// Check if newdata variables are null and if not insert them into the cab variables 
 	 foreach ($indata as $prop => $value){
		if ($value!=NULL){
			echo "<br>" . $prop . ":" . $value; 
			$cab->$prop=$value;
		} 
	}
	return $cab;
}


/*
function makesafe() {
	$this->DataCenterID=intval($this->DataCenterID);
}
*/
?>
