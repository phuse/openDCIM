<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );
?>
<!doctype html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  
  <title>openDCIM Device Logs Reporting</title>
  <link rel="stylesheet" href="css/inventory.php" type="text/css">
  <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
  <!--[if lt IE 9]>
  <link rel="stylesheet"  href="css/ie.css" type="text/css" />
  <![endif]-->
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
</head>
<body>
<div id="header"></div>
<div class="page reports">
<?php
	include( "sidebar.inc.php" );

echo '<div class="main">
<h2>',$config->ParameterArray["OrgName"],'</h2>
<h3>',__("Device Log"),'</h3>
<div class="center"><div id="reports">
<div>';
echo '</div>

<div>
<fieldset>';
 if(isset($_GET['deviceid'])){
                $ID=$_GET['deviceid'];
		echo '

<legend>',__("Device Log"),'</legend>';
  echo '<div class="table centermargin">';
                echo "<div class='row'><div>Time</div>|<div>  Action</div>|<div>Username</div></div>";
                foreach(LogActions::ShowDeviceLog($ID) as $Loglist){
                echo "<div class='row'><div>" . $Loglist->Time . "</div>|<div> " . $Loglist->Action . " </div>|<div>" . $Loglist->Username ."</div></div>";
                }
                echo "<div><A HREF=devices.php?deviceid=" . $ID . ">back to device</A></div>";
                echo '</div>';
}else{
echo 'No device chosen';
}
echo '</fieldset>
</div>';
?>


</div></div>

</div>
<div class="clear"></div>
</div>


</body>
</html>
