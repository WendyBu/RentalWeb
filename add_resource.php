<?php

/* connect to database */
$connect = mysql_connect("localhost:3306", "ermsuser", "ermsuser123");
if (!$connect) {
	die("Failed to connect to database");
}
mysql_select_db("erms") or die("Unable to select database");


session_start();
if (!isset($_SESSION['username'])) {
	header('Location: login.php');
	exit();
}

/*
if (!empty($_POST['add_capability']) and $_POST['add_capability'] != '(add capability)' and trim($_POST['add_capability']) != '') {
	
	$capability = mysql_real_escape_string($_POST['add_capability']);
	$query =	"INSERT INTO Resource_Capabilities (Resource_ID, capability) " .
				"VALUES('$resource_id', '$capability'";
	
	if (!mysql_query($query)) {
		print '<p class="error">Error: Failed to add capability. ' . mysql_error() . '</p>';
	}
}*/


/* if form was submitted, then save new data */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	/* validate form */
	if (empty($_POST['resource_name'])) {
		$errorMsg .= "Error: You must provide a resource name. <br>\n ";
	}
	
	if (empty($_POST['primary_esf'])) {
		$errorMsg .= "Error: You must select a primary ESF. <br>\n ";
	}

	foreach ($_POST['secondary_esf'] as $secESF) {
		if ($secESF == ($_POST['primary_esf'])) {
			$errorMsg .= "Error: Primary ESF cannot be included as a Secondary ESF. <br>\n";
		}
	}
	
	if (empty($_POST['latitude'])) {
		$errorMsg .= "Error: You must provide a latitude value. <br>\n ";
	}

	if (!empty($_POST['latitude']) AND (($_POST['latitude'])<=-90 OR ($_POST['latitude'])>=90)) {
		$errorMsg .= "Error: Enter a latitude value between -90 degrees and 90 degrees. <br>\n ";
    }
	
	if (empty($_POST['longitude'])) {
		$errorMsg .= "Error: You must provide a longitude value. <br>\n ";
	}

	if (!empty($_POST['longitude']) AND (($_POST['longitude'])<=-180 OR ($_POST['longitude'])>=180)) {
		$errorMsg .= "Error: Enter a longitude value between -180 degrees and 180 degrees. <br>\n ";
	}
	
	if (empty($_POST['cost_amount'])) {
		$errorMsg .= "Error: You must provide a cost amount. <br>\n ";
	}
	
	if (($_POST['cost_amount']) <0) {
		$errorMsg .= "Error: Cost cannot be negative. <br>\n ";
	}
	
	if (empty($_POST['cost_unit'])) {
		$errorMsg .= "Error: You must select a cost time unit. <br>\n ";
	}
	
	foreach ($_POST['secondary_esf'] as $secESF) {
	    if ($secESF == ($_POST['primary_esf'])) {
	    $errorMsg .= "Error: Primary ESF cannot be included as a Secondary ESF.  <br>\n ";
	    }
	}

	if(empty($errorMsg)) {

		/* obtain user provided attribute values */
		$resource_name = mysql_real_escape_string($_POST['resource_name']);
		$model = mysql_real_escape_string($_POST['model']);
		$latitude = mysql_real_escape_string($_POST['latitude']);
		$longitude = mysql_real_escape_string($_POST['longitude']);
		$primary_esf = mysql_real_escape_string($_POST['primary_esf']);
		$cost_amount = mysql_real_escape_string($_POST['cost_amount']);
		$cost_unit = mysql_real_escape_string($_POST['cost_unit']);
		$cost_amount = mysql_real_escape_string($_POST['cost_amount']);
		$username = $_SESSION['username'];

		/* Insert Resource */
		$resource_insert_query = "INSERT INTO Resource (`name`, model, latitude, longitude, primary_esf, cost_amount, cost_unit, `owner`)" .
			"VALUES ('$resource_name', '$model', '$latitude', '$longitude', '$primary_esf', '$cost_amount', '$cost_unit', '$username')";
		$result = mysql_query($resource_insert_query);
		if (!$result) {
			print '<p class="error">Error: ' . mysql_error() . '</p>';
				exit();
		}

		$last_id = mysql_insert_id(); // the last inserted ID

		/* Insert Secondary ESFs */
		foreach ($_POST['secondary_esf'] as $secESF) {
			$secondary_esf_insert_query = "INSERT INTO Resource_Secondary_ESF (ESF_ID, Resource_ID)" .
				"VALUES ('$secESF', '$last_id')";
			$result = mysql_query($secondary_esf_insert_query);
			if (!$result) {
				print '<p class="error">Error: ' . mysql_error() . '</p>';
				exit();
			}
		}

		/* Insert capabilities */
		foreach ($_POST['cap'] as $capability) {
			$capability_query = "INSERT INTO Resource_Capabilities (resource_ID, capability)" .
				"VALUES ('$last_id', '$capability')";
			$result = mysql_query($capability_query);

			if (!$result) {
				print '<p class="error">Error: ' . mysql_error() . '</p>';
				exit();
			}
		}

		header("Location: add_resource.php");
		unset($result);
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Add New Resource</title>
    <link rel="stylesheet" type="text/css" href="style.css"/>
</head>

<script>
	// function to add capability to list
	function addCapability() {
		var list = document.getElementById('capabilityList');
		var input = document.getElementById('capInput').value;
		var entry = document.createElement('li');
		entry.appendChild(document.createTextNode(input));

		var hidden = document.createElement('input');
		hidden.setAttribute("type", "hidden");
		hidden.setAttribute("name", "cap[]");
		hidden.setAttribute("value", input);
		entry.appendChild(hidden);

		list.appendChild(entry);
		document.getElementById('capInput').value = "";
	}
</script>

<body>

<div id="main_container">

    <div id="header">
        <div class="logo"><img src="images/erms_logo.png" border="0" alt="" title=""/></div>
    </div>

    <div class="menu">
        <ul>
            <li><a href="home.php">home</a></li>
            <li class="selected"><a href="add_resource.php">add resource</a></li>
            <li><a href="add_incident.php">add incident</a></li>
            <li><a href="search.php">search resources</a></li>
            <li><a href="status.php">resource status</a></li>
            <li><a href="report.php">resource report</a></li>
            <li><a href="logout.php">log out</a></li>
        </ul>
    </div>

    <div class="center_content">

        <div class="center_left">
			
			<h2>New Resource Info</h2>

			<?php
			// error message display
			if (!empty($errorMsg)) {
				print "<div class='profile_section' style='color:red'>$errorMsg</div>";
			}
			?>

			<form name="addresourceform" action="add_resource.php" method="post">
				
				<table width="80%">

					<tr>
						<td class="item_label">Resource ID</td>
						<?php

						$query = "SELECT `ID` FROM `resource` " .
							"ORDER BY `ID` DESC LIMIT 1; ";

						$result = mysql_query($query);

						if (!$result) {
							print '<p class="error">Error: ' . mysql_error() . '</p>';
							exit();
						}
						$row = mysql_fetch_assoc($result);
						$new_id = $row['ID'] + 1;

						print "<td>{$new_id}</td>";
						?>

					</tr>

					<tr>
						<td class="item_label">Owner</td>
						<?php
						
						$query = "SELECT name FROM User WHERE username = '{$_SESSION['username']}'";
						
						$result = mysql_query($query);
						
						if (!$result) {
							print "<p class='error'>Error: " . mysql_error() . "</p>";
							exit();
						}
						$row = mysql_fetch_assoc($result);
						$owner = $row['name'];
						
						print "<td>{$owner}</td>";
						?>

					</tr>


					<tr>
						<td class="item_label">Resource Name</td>
						<td><input type="text" name="resource_name" /></td>
					</tr>

					<tr>
						<td class="item_label">Primary ESF</td>
						<td>
							<?php								
								$esf_query = "SELECT ID, description " .
											 "FROM ESF ";
									  
								$esf_result = mysql_query($esf_query);
								if (!$esf_result) {
									print "<p class='error'>Error: " . mysql_error() . "</p>";
									exit();
								}

								$select = '<select name="primary_esf">  <option disabled selected value> -- select an option -- </option>';
								while ($row2 = mysql_fetch_array($esf_result)){
									$select.='<option value="'.$row2['ID'].'">'.'(#'.$row2['ID'].') '.$row2['description'].'</option>';
								}
								$select.='</select>';
								echo $select;
							?>
						</td>
					</tr>

					<tr>
						<td class="item_label">Secondary ESF</td>
						<td>
							<?php								
								$esf_query = "SELECT ID, description " .
											 "FROM ESF ";
									  
								$esf_result = mysql_query($esf_query);
								if (!$esf_result) {
									print "<p class='error'>Error: " . mysql_error() . "</p>";
									exit();
								}

								$select = '<select name="secondary_esf[]" multiple="multiple">';
								while ($row3 = mysql_fetch_array($esf_result)){
									$select.='<option value="'.$row3['ID'].'">'.'(#'.$row3['ID'].') '.$row3['description'].'</option>';
								}
								$select.='</select>';
								echo $select;
							?>
						</td>
					</tr>

					<tr>
						<td class="item_label">Model</td>
						<td><input type="text" name="model" /></td>
					</tr>

					<tr>
						<td class="item_label">Capabilities</td>
						<td>
							<ul name="capabilityList" id="capabilityList"> </ul>
							<input type="text" id="capInput">
							<input type='button' onclick='addCapability()' value='Add' />
						</td>
					</tr>

					<tr>
						<td class="item_label">Latitude</td>
						<td><input type="floatval" name="latitude" /> </td>
                    </tr>

					<tr>
						<td class="item_label">Longitude</td>
						<td><input type="floatval" name="longitude" /> </td>
					</tr>

					<tr>
						<td class="item_label">Cost </td>
						<td> $ <input type="text" name="cost_amount" />
							per
							<?php
							$costUnit_query = 	"SELECT time_unit " .
								"FROM Cost_unit ";

							$costUnit_result = mysql_query($costUnit_query);
							if (!$costUnit_result) {
								print "<p class='error'>Error: " . mysql_error() . "</p>";
								exit();
							}

							$select = '<select name="cost_unit">';
							while ($row9 = mysql_fetch_array($costUnit_result)){
								$select.='<option value="'.$row9['time_unit'].'">'.''.$row9['time_unit'].'</option>';
							}
							$select.='</select>';
							echo $select;
							?>

						</td>
					</tr>

				</table>

			</form>
				<a href="javascript:addresourceform.submit();" class="fancy_button">submit</a>


        </div>

        <div class="clear"></div>

	</div>


    <div id="footer">
        <div class="right_footer"><a href="http://csstemplatesmarket.com"
                                     target="_blank">http://csstemplatesmarket.com</a></div>
    </div>


</div>

</body>
</html>
