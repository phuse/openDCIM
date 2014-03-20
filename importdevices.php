<?php
        require_once( 'db.inc.php' );
        require_once( 'facilities.inc.php' );
	error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));	

        $dept=new Department();
        $DC=new DataCenter();

        $taginsert="";
        $status="";
        $newdata="";
	$row=1;
$allowedExts = "csv";
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);
//if (($_FILES["file"]["type"] == "text/csv")
//& ($extension==$allowedExts))
if ($extension==$allowedExts) {
  if ($_FILES["file"]["error"] > 0)
    {
    echo "Error: " . $_FILES["file"]["error"] . "<br>";
    }
  else
    {
    echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    echo "Type: " . $_FILES["file"]["type"] . "<br>";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    echo "Stored in: " . $_FILES["file"]["tmp_name"];
    }
  }
else
  {
  echo "Invalid file";
  }

//if (($handle = fopen("cabinets.csv", "r")) !== FALSE) {
echo "opening file.. <BR />";
if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {

//if (($handle = fopen("devices.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 500, ",")) !== FALSE) {
        $cab=new Cabinet();
        $dev=new Device();
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $DataCenterID=CheckDataCenterID($data[14],$row); 
	// Find parent device, do not put chassis in chassis
	/*if ($data[16] AND $data[17]!=="Chassis"){  
		//$newdata->Parent=CheckParent(data[13]);	
	} else {
		if ($data[17]=="Chassis"){
		echo "<BR /> can't put chassis in chassis for " . $row . " putting it in storage";
		$CabinetID=-1;
		}else{
		echo "<BR /> unable to locate parent for " . $row;
		}
		$newdata->Parent=NULL;
	}
	*/
	//if the device has a parent set cabinetid to 0, and if the cabinet can not be found, produce a error and put the device in storage
	if ($newdata->Parent){
		$CabinetID=0;
	}else{
        	$CabinetID=CheckCabinetExists($data[15],$DataCenterID); 
		//if we can't find the cabinet listed, add the device to storage 
		if ($CabinetID=0){
		echo "error! cabinet :" . $data[15] . " in row:" . $row . "is not defined, device " . $data[2] . "/" . $data[3] . " has been registered in storage<br>";		
		$CabinetID=-1;
		}
  	}
	$DeviceID=CheckDeviceExists($data[2],$data[3]);	
	//populate data
	echo "<br />TEst";
	$newdata->SerialNo=$data[2]; 
	$newdata->Label=$data[3]; 
        //populate tags, from field 28
        for ($c=28; $c < $num; $c++) {
            array_push($tagarray, $data[$c]);
        }
	$newdata->DomainName=CheckDomain($data[4]);//This should be looked up in future version 
	$newdata->AssetTag=$data[5]; 
	$newdata->PrimaryIP=$data[6]; 
	$newdata->MfgDate=$data[7]; 
	$newdata->InstallDate=$data[8]; 
	$newdata->WarrantyCo=$data[9]; 
	$newdata->WarrantyExpire=$data[10]; 
	$newdata->Owner=$data[11]; 
	$newdata->AssetLifeCycle=$data[12]; 
	// lookup contact if defined
	if ($data[13]){  
		$newdata->PrimaryContact=CheckContactExists($data[13]);	
	} else {
		echo "<BR /> Contact error, contact removed for " . $newdata->Label;
		$newdata->PrimaryContact=NULL;
	}
	//$newdata->Parent=$data[16]; // parent needs to be found 
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
	// insert function to create Template id based on manufacturer, model, height, dataports, wattage,psus and device type
	// insert function to find Template id based on manufacturer and model
	//only create template if we have both model and manufacturer 
        $Manufacturer=CheckMFExists($data[0]);
	if ($Manufacturer!==0 AND $data[1]){
		$Model=CheckTemplateExists($Manufacturer,$data[1],$newdata->DeviceType);
	}else{
		echo "<BR />missing Manufacturer/Model in row " . $row . " not populating template information<BR />"; 
	}
	$newdata->MaxKW=$data[5]; 
	$newdata->MaxWeight=$data[6]; 
	$newdata->Notes=$data[8]; 
	$newdata->InstallationDate=date('m/d/Y', strtotime($data[7])); 
	//create the device if it's missing or update it if it exists
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
	var_dump($tagarray);
        //$dev->SetTags($tagarray);
        $row++;
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
	$sql="SELECT * FROM fac_Device WHERE SerialNo=\"$serial\" LIMIT 1;";
	//$sql="SELECT * FROM fac_Devices WHERE SerialNo=\"$serial\" OR Label=\"$hostname\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row['DeviceID'];
        }else{
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
function CheckMFExists($name) {
	global $dbh;
	$sql="SELECT * FROM fac_Manufacturer WHERE Name=\"$name\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row['ManufacturerID'];
        }else if($name){
		$sql2="INSERT INTO fac_Manufacturer (ManufacturerID,Name) VALUES (NULL,\"$name\");";
		if ($dbh->query($sql2)){
			//return the created ID
			return $dbh->lastInsertId();
		} else {
			echo "aborting due to error creating new Manufacturer! in line " . $row;
			break;
		}
	}
}
function CheckTemplateExists($manufacturer,$name,$type) {
	global $dbh;
	global $newdata;
	global $row;
	$sql="SELECT * FROM fac_DeviceTemplate WHERE Model=\"$name\" AND ManufacturerID=\"$manufacturer\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row['TemplateID'];
        }else if($name AND $manufacturer){ //Create a new template 
		$sql2="INSERT INTO fac_DeviceTemplate (TemplateID,ManufacturerID,Model) VALUES (NULL,\"$manufacturer\",\"$name\");";
		//$sql2="INSERT INTO fac_DeviceTemplate (TemplateID,ManufacturerID,Model,Height,Weight,Wattage,DeviceType,PSCount,NumPorts) VALUES (NULL,\"$manufacturer\",\"$name\",NULL,NULL,NULL,\"$type\",NULL,NULL);";
		if ($dbh->query($sql2)){
			//return the created ID
			echo "<BR />created new template <A HREF=/device_templates.php?templateid=" . $dbh->lastInsertId() . ">" . $manufacturer . "-". $name . "</A>"; 
			return $dbh->lastInsertId();
		} else {
			echo "aborting due to error creating new Template! in line " . $row;
			exit;
		}
	}
}

function CheckContactExists($name) {
	global $dbh;
	$sql="SELECT * FROM fac_Contact WHERE UserID=\"$name\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row['ContactID'];
        }else if($name){
			// add ldap lookups?
		$sql2="INSERT INTO fac_Contact (ContactID,UserID) VALUES (NULL,\"$name\");";
		if ($dbh->query($sql2)){
			//return the created ID
			return $dbh->lastInsertId();
		} else {
			echo "aborting due to error creating new Contact! in line " . $row;
			break;
		}
	}
}
function CheckDomain($domain) {
	global $dbh;
	$domain = strtolower($domain);
	$sql="SELECT * FROM fac_DomainName WHERE DomainName=\"$domain\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row['DomainID'];
        }else if($domain){
		$sql2="INSERT INTO fac_Domain (DomainID,DomainName) VALUES (NULL,\"$domain\");";
                if ($dbh->query($sql2)){
                        //return the created ID
                        return $dbh->lastInsertId();
                } else {
                        echo "aborting due to error creating new Domain! in line " . $row;
                        break;
                }

		//so the serial number is not there, lets check for hostname
		return 0;
	}
}
?>
