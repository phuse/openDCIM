<?php
        require_once( 'db.inc.php' );
        require_once( 'facilities.inc.php' );

        $dept=new Department();
        $DC=new DataCenter();

        $taginsert="";
        $status="";
	$row=1;
$allowedExts = "csv";
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);
$starttime = time();
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
  } else {
  	echo "Invalid file";
	echo '<form id="myForm" method="post" enctype="multipart/form-data">
    	<input type="file" name="file" />
    	<input type="submit" value="Send" />
	</form>';
  }

if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 600, ";")) !== FALSE) {
	//one row at a time
	$ignore=0; //this is to ignore some assets that should not be imported (VIRtual and POL (Server pools))
	$newdata=new DevicePorts();
	$port=new DevicePorts();
        $dev=new Device();
        $num = count($data);
	//$newdata=array();
        echo "<p> $num fields in line $row: <br /></p>\n";
	$oldconnectionid=$data[0]; 
	$hostid=$data[1];
	$server=$data[2];  //Serial
	$portname=$data[3];
	$portnamedef=$data[4]; //?
	$legacyhostid=$data[5];
	$mediatype=$data[6];
	$speed=$data[7];
	$legacyswitchid=$data[8];
	$switchname=$data[9];
	$switchport=$data[10];
	$cableNb=$data[11];
	$CableNr=$data[12];
        $conntype=$data[13]; 
        $ipaddr=$data[14]; //Location of rack, XY-coordinates 
        $datemod=$data[15]; 
        $LACP=$data[16]; 
        $USED=$data[17]; 
        $ilodomsuff=$data[24]; 
        $changedby=$data[25]; 
        $timestamp=$data[27]; 
	//transform speed to a proper format		
	switch ($speed){
		case "1000":
			$speed="1 Gbit";	//Identify type id 
			break;
		case "1G":
			$speed="1 Gbit";	//Identify type id 
			break;
		case "1gb":
			$speed="1 Gbit";	//Identify type id 
			break;
		case "1Gbit":
			$speed="1 Gbit";	//Identify type id 
			break;
		case "10000":
			$speed="10 Gbit";	//Identify type id 
			break;
		case "40Gbit":
			$speed="40 Gbit";	//Identify type id 
			break;
		case "na":
			$speed="N/A";	//Identify type id 
			break;
		case "1":
			$speed="1 Gbit";	//Identify type id 
			break;
		default :
			echo "<BR>speed:" . $speed; 
			//No change
	}	
	$media=trim($mediatype . " " . $speed);
	$newdata->DeviceID=findlegacyID($legacyhostid); 
	$newdata->PortNumber=findportID($portnamedef,$newdata->DeviceID);//check if port is already named, or if the defined ports are unmarked 
	$newdata->ConnectedDeviceID=findlegacyID($legacyswitchid); 
	$newdata->ConnectedPort=findportID($switchport,$newdata->ConnectedDeviceID);//check if port is already named, or if the defined ports are unmarked 
	$MediaID=getmediaID($media); 
	$newdata->MediaID=$MediaID; 
	//$newdata->ColorID=getcolorID($media); 
	$newdata->Label=$portnamedef; 
	//CableNR and IPadress goes into comments for now, this needs to be improved
	$newdata->Notes=trim($CableNr . "," . $ipaddr); //add Cable no and ip to notes for now 
	var_dump($newdata);
	$connectionID=findconnID($oldconnectionid);
	//create the connection if it's missing or update it if it exists
	if ($newdata->ConnectedDeviceID > 0 AND $newdata->DeviceID > 0 AND $ignore==0){
         	if (!$connectionID){
			$port->DeviceID=$newdata->DeviceID;
			$port->PortNumber=$newdata->PortNumber;
			$port->GetPort();
			$port=UpdatePortData($newdata);
			echo "created:<br />";
			$port->updatePort();
			var_dump($port);
		 }else{
			$port->DeviceID=$connectionID['DeviceID'];
			$port->PortNumber=$connectionID['PortNumber'];
			echo "updated:<br />";
			$port->GetPort();
			//var_dump($dev);
			$port=UpdatePortData($newdata);
			$port->updatePort();
			var_dump($port);
	 	}
		$port->getPort();
		putoldconnectionid($port->DeviceID,$port->PortNumber,$oldconnectionid);
	}else{
	 	echo $row . " ignored <BR />";
	}
        $row++;
    }
    fclose($handle);
	$endtime = time();
	echo "<P>time elapsed ".time_elapsed($endtime-$starttime);
}
// functions
//This needs to be improved, right now to resolve the issue with giving racks multiple names depending if on front or back
function UpdatePortData($indata) {
	global $port;
 	// Check if newdata variables are null and if not insert them into the cab variables 
 	 foreach ($indata as $prop => $value){
		if ($value!=NULL){
			echo "<br>" . $prop . ":" . $value; 
			$port->$prop=$value;
		} 
	}
	//if (preg_match('/Port\d{1,3}\z/',$port->Label){
	$port->Label=$indata->Label;
	//}
	return $port;
}

function CheckDomain($domain) {
	global $dbh;
	$domain = strtolower($domain);
	$sql="SELECT * FROM fac_DomainName WHERE DomainName=\"$domain\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row['DomainID'];
        }else if($domain){
		$sql2="INSERT INTO fac_DomainName (DomainID,DomainName) VALUES (NULL,\"$domain\");";
                if ($dbh->query($sql2)){
                        //return the created ID
                        return $dbh->lastInsertId();
                } else {
                        echo "<P>aborting due to error creating new Domain! " . $domain;
                        exit;
                }

		//so the serial number is not there, lets check for hostname
		return 0;
	}
}
//STores the reference from original database in a table to be able to handle devices without name or serial, also to be able to find reference to parent devices 
function putoldconnectionid($id,$port,$oldid) {
	global $dbh;
	$sql2="INSERT INTO fac_LegacyConnectionID (DeviceID,PortNumber,OldID) VALUES (\"$id\",\"$port\",\"$oldid\") ON DUPLICATE KEY UPDATE OldID=\"$oldid\";";
                if ($dbh->query($sql2)){
                        return 0;
                } else {
                        echo "aborting due to error registering id! " . $oldid;
                        exit;
                }
}
//find parent based on id from old db 
function findconnID($legacyid) {
	global $dbh;
	
	$sql="SELECT * FROM fac_LegacyConnectionID WHERE OldID=\"$legacyid\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
        	return $row;
	}else{
		echo "<P>Unable to locate connection with connectionID: " . $legacyid;
	}
return 0;
}
function findlegacyID($legacyid) {
	global $dbh;
	
	$sql="SELECT * FROM fac_LegacyID WHERE OldID=\"$legacyid\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
		$ID=$row['ID'];
        	return $ID;
	}else{
		echo "<P>Unable to locate connection with connectionID: " . $legacyid;
	}
return 0;
}

function getmediaID($mediatype) {
	global $dbh;
	
	$sql="SELECT * FROM fac_MediaTypes WHERE MediaType=\"$mediatype\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
		$ID=$row['MediaID'];
        	return $ID;
	}else{
		$mediatype = addslashes(trim($mediatype));
		$sql="INSERT INTO fac_MediaTypes (MediaID,MediaType) VALUES (NULL,\"$mediatype\");";
		if ($dbh->query($sql)){
                        //return the created ID
                        return $dbh->lastInsertId();
                }
		echo "<P>Unable to locate or create mediaID of type : " . $mediatype;
	}
return 0;
}

function findportID($portname,$host) {
	global $dbh;
	//search for ports connected to device, this one will be a bit tricky since we need to assign 'unruised' ports if it is not found, defined as Portz
	
	$sql="SELECT * FROM fac_Ports WHERE DeviceID=\"$host\" and Label=\"$portname\" LIMIT 1;";
	if ($row=$dbh->query($sql)->fetch()){
		//verify that parent is really a chassis and try to locate the correct chassis if not
        	return $row['PortNumber'];
	} else {
	//FInd ports for device and check if they are unused
		$sql="SELECT * FROM fac_Ports WHERE DeviceID=\"$host\" ORDER BY PortNumber ASC;";
		$line=1;
		//$portlist=array();
		foreach($dbh->query($sql) as $row){
			$rowfiltered=array_filter($row);
			//var_dump($rowfiltered);
			$narwhal=count($rowfiltered);
			//echo "<BR>Narwhal:" . $narwhal . " Label : " . $rowfiltered['Label'];
			if (preg_match('/Port\d{1,3}\z/',$row['Label']) AND count($rowfiltered) <= 6){ //port seem to be unused, matching up to 999, maybe improve this to match switches with up to 256 ports?
			//well take this
			echo "<BR>Port " . $line . " has been taken on host " . $host . "\n"; 
			return $row['PortNumber'];		
			}else{
			$line++;
			}
			//seems nothing was found, create a new port and return that
			echo "Port is missing for device " . $host . " please check"; 
		}

		
		echo "<P>Unable to locate port for " . $host . " with portname: " . $portname;
	}
	return 0;
}
function fixdate($date) {
	if ($date=='1900-01-00'){
        	return 0;
	}else{
		return date('m/d/Y', strtotime($date));
	}
}

function time_elapsed($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60
        );
    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;
       
    return join(' ', $ret);
}
?>
