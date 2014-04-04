<?php
        require_once( 'db.inc.php' );
        require_once( 'facilities.inc.php' );
        require_once( 'importfunctions.inc.php' );

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
if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 500, ";")) !== FALSE) {
    // KISTA while (($data = fgetcsv($handle, 500, ",")) !== FALSE) {
	$tagarray=array();
        $cab=new Cabinet();
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
	if ($data[0]){
        	$DataCenterID=CheckDataCenterID($data[0],$row); 
	}else{
		$DataCenterID=-2;
	}
        $frontname=CheckBackside($data[1],$data[0]); // we don't want to add alrerady registered backsides 
	echo "<BR>" . $data[1] . " is " . $frontname;
	if ($frontname !== $data[1]){
	echo "<BR>" . $data[1] . " is a backside.. not adding" . $frontname;
	}else{  
       	 	$CabinetID=CheckCabinetExists($data[1],$DataCenterID); 
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
		//populate tags
       		 for ($c=9; $c < $num; $c++) {
       	   	  array_push($tagarray, $data[$c]);
       		 } 
      		  echo "CabinetName:" . $data[1] . " Id:" . $CabinetID . "<br />\n";
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
		echo "old data<br />";
		var_dump($cab);
		$cab=UpdateCabinetData($newdata);
		//actually push to db
		$cab->UpdateCabinet();
		echo "<p>new data<br />";
		var_dump($cab);
		}
	}
	var_dump($tagarray);
	$cab->SetTags($tagarray);
        $row++;
    }
    fclose($handle);
} else {
echo "error handling file " . $_FILES["file"]["name"];
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
			//echo "<br>" . $prop . ":" . $value; 
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
