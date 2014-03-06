<?php
	require_once('db.inc.php');
	require_once('facilities.inc.php');

	/* if(!$user->ContactAdmin){
		// No soup for you.
		header('Location: '.redirect());
		exit;
	} */

	$svc=new LogicalService();

	if(isset($_REQUEST['svcid'])&&($_REQUEST['svcid']>0)){
		$svc->ServiceID=(isset($_POST['svcid']) ? $_POST['svcid'] : $_GET['svcid']);
		$svc->GetServiceByID();
	}

	if(isset($_POST['action'])&& (($_POST['action']=='Create') || ($_POST['action']=='Update'))){
		$svc->ServiceID=$_POST['svcid'];
		$svc->ServiceName=trim($_POST['svcname']);
		$svc->SOM=$_POST['som'];
		$svc->ServiceColor=$_POST['svccolor'];

		/* if($svc->ServiceName!=''){
			if($_POST['action']=='Create'){
				$svc->CreateService();
			}else{
				$svc->UpdateService();
			}
		} */
		// Refresh object
		$svc->GetServiceByID();
	}
	$svcList=$svc->GetServiceList();
?>
<!doctype html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  
  <title>openDCIM Department Information</title>
  <link rel="stylesheet" href="css/inventory.php" type="text/css">
  <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
  <link rel="stylesheet" href="css/jquery.miniColors.css" type="text/css">
  <!--[if lt IE 9]>
  <link rel="stylesheet"  href="css/ie.css" type="text/css">
  <![endif]-->
  
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
  <script type="text/javascript" src="scripts/jquery.miniColors.js"></script>
<script type="text/javascript">
function showgroup(obj){
	self.frames['groupadmin'].location.href='dept_groups.php?deptid='+obj;
	document.getElementById('groupadmin').style.display = "block";
	document.getElementById('svcname').readOnly = true
	document.getElementById('svcmgr').readOnly = true
	document.getElementById('svccolor').readOnly = true
	document.getElementById('controls').id = "displaynone";
	$('.color-picker').minicolors('destroy');
}
	$(document).ready( function() {
		$(".color-picker").minicolors({
			letterCase: 'uppercase',
			change: function(hex, rgb) {
				logData(hex, rgb);
			}
		});
	});
</script>
</head>
<body>
<div id="header"></div>
<div class="page">
<?php
	include( 'sidebar.inc.php' );
	echo '<div class="main">
<h2>',$config->ParameterArray["OrgName"],'</h2>
<h3>',__("Data Center Department Detail"),'</h3>
<div class="center"><div>
<form action="',$_SERVER["PHP_SELF"],'" method="POST">
<div class="table centermargin">
<div>
   <div>',__("Service"),'</div>
   <div><input type="hidden" name="action" value="query"><select name="svcid" onChange="form.submit()">
   <option value=0>',__("New Service"),'</option>';

	foreach($svcList as $svcRow){
		if($svc->ServiceID == $svcRow->ServiceID){$selected=" selected";}else{$selected="";}
		print "   <option value=\"$svcRow->ServiceID\"$selected>$svcRow->ServiceName</option>\n";
	}

	echo '	</select></div>
</div>
<div>
   <div><label for="svcname">',__("Service Name"),'</label></div>
   <div><input type="text" size="50" name="name" id="svcname" maxlength="80" value="',$svc->ServiceName,'"></div>
</div>
<div>
   <div><label for="svcmgr">',__("Service Operations Manager"),'</label></div>
   <div><input type="text" size="50" name="som" id="svcmgr" maxlength="80" value="',$svc->SOM,'"></div>
</div>
<div>
   <div><label for="svcCode">',__("Service Code"),'</label></div>
   <div><div class="cp"><input type="text" name="svccode" id="svccode" maxlength="12" value="',$svc->ServiceCode,'"></div></div>
</div>';
<div class="caption" id="controls">
<?php
	if($svc->ServiceID > 0){
		echo '<button type="submit" name="action" value="Update">',__("Update"),'</button>';
	}else{
    	echo '<button type="submit" name="action" value="Create">',__("Create"),'</button>';
	}
?>
</div>
</div> <!-- END div.table -->
</form>
<iframe name="groupadmin" id="groupadmin" frameborder=0 scrolling="no"></iframe>
<br>
</div></div>
<?php echo '<a href="index.php">[ ',__("Return to Main Menu"),' ]</a>'; ?>
</div> <!-- END div.main -->
</div> <!-- END div.page -->
</body>
</html>
