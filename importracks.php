<?php
        require_once( 'db.inc.php' );
        require_once( 'facilities.inc.php' );

        $cab=new Cabinet();
        $dept=new Department();
        $DC=new DataCenter();

        $taginsert="";
        $status="";
	$row=1;
if (($handle = fopen("cabinets.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 500, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $DataCenterID=CheckDataCenterID($data[0],$row); 
        $row++;
        echo "Datacenter:" . $data[0] . " id: " . $DataCenterID . "<br />\n";
        echo "Name:" . $data[1] . "<br />\n";
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
		$sql2="INSERT INTO fac_DataCenter (DataCenterID,Name) VALUES ('NULL,\"$name\");";
		if ($dbh->query($sql2)){
			//return the created ID
			return $dbh->lastInsertId();
		} else {
			echo "aborting due to error creating new DC! in line " . $row;
			break;
		}
	}
}

/*
function makesafe() {
	$this->DataCenterID=intval($this->DataCenterID);
}
*/
?>
