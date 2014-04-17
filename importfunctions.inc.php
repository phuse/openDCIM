<?php
// functions
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
function CheckBackside($name,$dataroom) {
        global $dbh;
        $sql="SELECT * FROM fac_Backsides WHERE Dataroom=\"$dataroom\" AND BackName LIKE \"$name\" LIMIT 1;";
        if ($cabrow=$dbh->query($sql)->fetch()){
                return $cabrow['CabinetID'];
        }else{
                return $name;
        }
}
?>
