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
		//if we can't find the cabinet listed, add the device to storage 
		if ($CabinetID=0){
		echo "error! cabinet :" . $data[15] . " in row:" . $row . "is not defined, device " . $data[2] . "/" . $data[3] . " has been registered in storage<br>" 		
		$CabinetID=-1;
		}
  	}
	//populate data
	$newdata->Manufacturer=$data[0]; 
	$newdata->Model=$data[1]; 
	// insert function to create deviceclass id based on manufacturer, model, height, dataports, wattage,psus and device type
	// insert function to find deviceclass id based on manufacturer and model
	$newdata->SerialNo=$data[2]; 
	$newdata->Label=$data[3]; 
	$newdata->Domain=$data[4]; 
	$newdata->AssetTag=$data[5]; 
	$newdata->PrimaryIP=$data[6]; 
	$newdata->MfgDate=$data[7]; 
	$newdata->InstallDate=$data[8]; 
	$newdata->WarrantyCo=$data[9]; 
	$newdata->WarrantyExpire=$data[10]; 
	$newdata->Owner=$data[11]; 
	$newdata->AssetLifeCycle=$data[12]; 
	$newdata->Primary Contact=$data[13]; 
	$newdata->Parent=$data[16]; // parent needs to be found 
	$newdata->Height=$data[17]; 
	$newdata->Position=$data[18]; 
	$newdata->Ports=$data[19]; 
	$newdata->BackSide=$data[20];//value needs to be changed? 
	$newdata->HalfDepth=$data[21];//value needs to be changed? 
	$newdata->ESX=$data[22];//value needs to be changed? 
	$newdata->PowerSupplyCount=$data[23]; 
	$newdata->NominalWatts=$data[24]; 
	$newdata->DeviceType=$data[25];//Identify type id 
	$newdata->DecomDate=$data[26]; 
	$newdata->Notes=$data[27]; 
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
        if ($DeviceID==0){
		$dev->DeviceID=NULL;
		$dev=UpdateDeviceData($newdata);
		echo "created:<br />";
		//$dev->CreateDevice();
		var_dump($dev);
	}else{
		$dev->DeviceID=$DeviceID;
		echo "updated:<br />";
		$dev->GetDevice();
		var_dump($dev);
		//$cab->CabinetID=$CabinetID;
		//$newdata->CabinetLocation=$data[1]; 
		//echo $newdata->InstallationDate;
		//$cab->Location=trim($data[1]);
		//data_dump($cab);
		$dev=UpdateDeviceData($newdata);
		//acutally push to db
		//$cab->UpdateCabinet();
		var_dump($dev);
	}
        $row++;
        /* echo "Datacenter:" . $data[0] . " id: " . $DataCenterID . "<br />\n";
        echo "Name:" . $data[1] . " Id:" . $CabinetID . "<br />\n";
        echo "Manufacturer:" . $data[2] . "<br />\n";
        echo "model:" . $cab->CabinetModel . "<br />\n";
        echo "height" . $data[4] . "<br />\n";
        echo "maxkw: " . $data[5] . "<br />\n";
        echo "maxweight:" . $data[6] . "<br />\n";
        echo "installdate:" . $data[7] . "<br />\n";
        echo "<pre>" . $data[8] . "</pre><br />\n";
        for ($c=28; $c < $num; $c++) {
            echo "tag" . $c . ":" . $data[$c] . "<br />\n";
        } 
	*/
    }
    fclose($handle);
}
// functions
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
function CheckDeviceExists($serial,$hostname) {
	global $dbh;
	$sql="SELECT * FROM fac_Devices WHERE SerialNo=\"$serial\" OR Label=\"$hostname\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row['DeviceID'];
        }else{
           
		//so the serial number is not there, lets check for hostname
		return 0;
	}
}

function UpdateDeviceData($indata) {
	global $dev;
 	// Check if newdata variables are null and if not insert them into the cab variables 
 	 foreach ($indata as $prop => $value){
		if ($value!=NULL){
			echo "<br>" . $prop . ":" . $value; 
			$dev->$prop=$value;
		} 
	}
	return $dev;
}
?>
