<?php

/* connect to database */
$connect = mysql_connect("localhost:3306", "ermsuser", "ermsuser123");
if (!$connect) {
	die("Failed to connect to database");
}
mysql_select_db("erms") or die( "Unable to select database");

session_start();
if (!isset($_SESSION['username'])) {
	header('Location: login.php');
	exit();
}

/* if form was submitted, then execute query to search for resources */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /* deploy resource from search results page */
    if (!empty($_POST['resource_id']) AND $_POST['deploy'] AND !empty($_POST['date']) AND !empty($_POST['owner'])) {

        $username = $_SESSION['username'];
        $currDate = date("Y-m-d");
        $resource_id = $_POST['resource_id'];
        $incident_id = $_SESSION["incident"];
        $owner = $_POST['owner'];

        // create properly formatted date object from user input date
        $inputDate = $_POST['date'];
        $dateObj = \DateTime::createFromFormat('Y-m-d', $inputDate);
        if ($dateObj)
        {
            $endDate = $dateObj->format("Y-m-d");
        }
        if(!$dateObj || $endDate <= $currDate) {
            $errorMsg .= "Error: You must provide a valid future end date. <br>\n ";
        }

        if(!$errorMsg) {

            $insertQuery = "INSERT INTO Request (resource_id, requester_id, start_date, end_date, incident_id, `type`, `owner`)" .
                "VALUES ('$resource_id', '$username', '$currDate', '$endDate', '$incident_id', 2, '$owner')";
            $insertResult = mysql_query($insertQuery);
            if (!$insertResult) {
                print '<p class="error">Error: ' . mysql_error() . '</p>';
                exit();
            }

            $updateQuery = "UPDATE Resource SET `status`=1 WHERE Resource.ID = '$resource_id' AND `owner`= '$owner';";
            $updateResult = mysql_query($updateQuery);
            if (!$updateResult) {
                print '<p class="error">Error: ' . mysql_error() . '</p>';
                exit();
            }
            $successMsg .= "Successfully deployed resource " . $resource_id;
            unset($errorMsg);

        }
    }

    /* request resource from search results page */
    if (!empty($_POST['resource_id']) AND $_POST['request'] AND !empty($_POST['date']) AND !empty($_POST['owner'])) {

        $username = $_SESSION['username'];
        $currDate = date("Y-m-d");
        $resource_id = $_POST['resource_id'];
        $incident_id = $_SESSION["incident"];
        $owner = $_POST['owner'];

        // create properly formatted date object from user input date
        $inputDate = $_POST['date'];
        $dateObj = \DateTime::createFromFormat('Y-m-d', $inputDate);
        if ($dateObj)
        {
            $endDate = $dateObj->format("Y-m-d");
        }
        if(!$dateObj || $endDate <= $currDate) {
            $errorMsg .= "Error: You must provide a valid future end date. <br>\n ";
        }
        if(!$errorMsg) {

            $insertQuery = "INSERT INTO Request (resource_id, requester_id, start_date, end_date, incident_id, `type`, `owner`)" .
                "VALUES ('$resource_id', '$username', '$currDate', '$endDate', '$incident_id', 1, '$owner')";
            $insertResult = mysql_query($insertQuery);
            if (!$insertResult) {
                print '<p class="error">Error: ' . mysql_error() . '</p>';
                exit();
            }
            $successMsg .= "Successfully requested resource " . $resource_id;
            unset($errorMsg);

        }

    }

    /* repair any resource from search results page */
    if (!empty($_POST['resource_id']) AND $_POST['repair'] AND !empty($_POST['date']) AND !empty($_POST['owner'])) {

        $username = $_SESSION['username'];
        $currDate = date("Y-m-d");
        $resource_id = $_POST['resource_id'];
        $incident_id = $_SESSION["incident"];
        $owner = $_POST['owner'];
        $status = $_POST['status'];
        $available_date = $_POST['end_date'];

        if($status == 2){
            $errorMsg .= "Error: You cannot repair a resource already being repaired. <br>\n ";
        }

        if(!$errorMsg) {
            if($status == 0) { // resource is available

                // create properly formatted date object from user input date
                $end_date = date('Y-m-d', strtotime($currDate. ' + ' . $numDays .'days'));

                if (isset($incident_id)) {
                    $insertQuery = "INSERT INTO Request (resource_id, requester_id, start_date, end_date, incident_id, `type`, `owner`)" .
                        "VALUES ('$resource_id', '$username', '$currDate', '$end_date', '$incident_id', 0, '$owner')";
                } else {
                    $insertQuery = "INSERT INTO Request (resource_id, requester_id, start_date, end_date, incident_id, `type`, `owner`)" .
                        "VALUES ('$resource_id', '$username', '$currDate', '$end_date', NULL, 0, '$owner')";
                }
                $insertResult = mysql_query($insertQuery);
                if (!$insertResult) {
                    print '<p class="error">Error: ' . mysql_error() . '</p>';
                    exit();
                }

                $updateQuery = "UPDATE Resource SET `status`=2 WHERE Resource.ID = '$resource_id' AND `owner`= '$owner';";
                $updateResult = mysql_query($updateQuery);
                if (!$updateResult) {
                    print '<p class="error">Error: ' . mysql_error() . '</p>';
                    exit();
                }
            } else if ($status == 1) { // resource is in use

                // create properly formatted date object from user input date
                $end_date = date('Y-m-d', strtotime($available_date. ' + ' . $numDays .'days'));

                if (isset($incident_id)) {
                    $insertQuery = "INSERT INTO Request (resource_id, requester_id, start_date, end_date, incident_id, `type`, `owner`)" .
                        "VALUES ('$resource_id', '$username', '$available_date', '$end_date', '$incident_id', 0, '$owner')";
                } else {
                    $insertQuery = "INSERT INTO Request (resource_id, requester_id, start_date, end_date, incident_id, `type`, `owner`)" .
                        "VALUES ('$resource_id', '$username', '$available_date', '$end_date', NULL, 0, '$owner')";
                }
                $insertResult = mysql_query($insertQuery);
                if (!$insertResult) {
                    print '<p class="error">Error: ' . mysql_error() . '</p>';
                    exit();
                }
            }
            $successMsg .= "Successfully set resource " . $resource_id . " to be repaired.";
            unset($errorMsg);

        }
    }

	$keyword  = mysql_real_escape_string($_POST['keyword']);
	$esf      = mysql_real_escape_string($_POST['esf']);
	$distance = mysql_real_escape_string($_POST['distance']);
	$incident = mysql_real_escape_string($_POST['incident']);

    if(!empty($incident)){
        $_SESSION["incident"] = $incident;

    } else {
        $_SESSION["incident"] = null;

    }

    $query = "";

    $incidentLat = "SELECT @incidentLat := latitude FROM Incident WHERE id = '$incident';";
    $incidentLatResult = mysql_query($incidentLat);

    $incidentLng = "SELECT @incidentLng := longitude FROM Incident WHERE id = '$incident';";
    $incidentLngResult = mysql_query($incidentLng);


    $makeTempDistance = "CREATE TEMPORARY TABLE DistanceTemp AS (SELECT id, ( 6371 * acos( cos( radians(@incidentLat) ) * cos( radians( latitude ) ) 
          * cos( radians( longitude ) - radians(@incidentLng) ) + sin( radians(@incidentLat) ) * sin(radians(latitude)) ) ) AS distance FROM resource);";
    $makeTempDistanceResult =  mysql_query($makeTempDistance);

    $makeFinalDistance = "CREATE TEMPORARY TABLE DistanceResults AS (SELECT * FROM DistanceTemp NATURAL JOIN Resource);";
    $makeFinalDistanceResult =  mysql_query($makeFinalDistance);


    /* All options are selected */
	if (!empty($keyword) and !empty($esf) and !empty($distance) and !empty($incident)) {

		$query = "SELECT dr.ID, dr.name, dr.owner, dr.cost_amount, dr.cost_unit, dr.status, Request.end_date, dr.distance
			FROM DistanceResults AS dr
			
			JOIN Resource_Secondary_ESF AS s ON dr.ID = s.resource_id
			
			JOIN Resource_Capabilities ON dr.ID = Resource_Capabilities.resource_id
			
			LEFT OUTER JOIN Request
			ON  dr.ID = Request.resource_id 
			 
			WHERE dr.distance <= '$distance'
			AND
			(
				dr.primary_esf = '$esf'
				OR s.ESF_ID = '$esf'
			)
			AND
			(
				dr.name LIKE '$keyword'
				OR dr.model LIKE '$keyword'  
				OR Capability LIKE '$keyword'
			) GROUP BY dr.ID ORDER BY dr.distance, dr.status, dr.name;";
	}

	/* ESF, Location and Incident are selected */
	elseif (!empty($esf) and !empty($distance) and !empty($incident)) {

        $query = "SELECT dr.ID, dr.name, dr.owner, dr.cost_amount, dr.cost_unit, dr.status, Request.end_date, dr.distance
			FROM DistanceResults AS dr
			LEFT OUTER JOIN Request
			ON  dr.ID = Request.resource_id 
			JOIN Resource_Secondary_ESF AS s 
			ON dr.ID = s.resource_id
			WHERE distance <= '$distance'
			AND
			(
				dr.primary_esf = '$esf'
				OR s.ESF_ID = '$esf'
			) GROUP BY dr.ID ORDER BY dr.distance, dr.status, dr.name;";
	}

	/* Keyword, Location and Incident selected */
	elseif (!empty($keyword) and !empty($distance) and !empty($incident)) {

        $query = "SELECT dr.ID, dr.name, dr.owner, dr.cost_amount, dr.cost_unit, dr.status, Request.end_date, dr.distance
			FROM DistanceResults AS dr
			LEFT OUTER JOIN Request
			ON  dr.ID = Request.resource_id
			JOIN Resource_Capabilities 
			ON dr.ID = Resource_Capabilities.resource_id
			WHERE distance <= '$distance'
			AND
			(
				dr.name LIKE '%$keyword%'
				OR dr.model LIKE '%$keyword%'  
				OR Capability LIKE '%$keyword%'
			) GROUP BY dr.ID ORDER BY dr.distance, dr.status, dr.name;";
	}

	/* Keyword and ESF are selected */
	elseif (!empty($keyword) and !empty($esf)) {

        $query = "SELECT cap.ID, cap.name, cap.owner, cap.cost_amount, cap.cost_unit, cap.status, Request.end_date
			FROM (SELECT * FROM Resource JOIN Resource_Capabilities WHERE Resource.ID = Resource_Capabilities.resource_id) AS cap JOIN (SELECT * FROM Resource JOIN Resource_Secondary_ESF WHERE Resource.ID = Resource_Secondary_ESF.resource_id) AS sec LEFT OUTER JOIN request
			ON sec.ID=Request.resource_id
			WHERE
			(
				cap.name LIKE '%$keyword%'
				OR cap.model LIKE '%$keyword%'  
				OR Capability LIKE '%$keyword%'
			) 
			AND
			(
				sec.primary_esf = '$esf'
				OR sec.ESF_ID = '$esf'
			)
			GROUP BY cap.ID ORDER BY cap.status, cap.name ASC;";
	}
	
	/* Location and Incident are selected */
	elseif (!empty($distance) and !empty($incident)) {

        $query = "SELECT DistanceResults.ID, DistanceResults.name, DistanceResults.owner, DistanceResults.distance,
			DistanceResults.cost_amount, DistanceResults.cost_unit, DistanceResults.status, Request.end_date
			FROM DistanceResults LEFT OUTER JOIN Request ON DistanceResults.ID = Request.resource_id
			WHERE DistanceResults.distance <= '$distance'
			GROUP BY DistanceResults.ID ORDER BY DistanceResults.distance ASC,
		    	DistanceResults.status, DistanceResults.name ASC;";
	}

	/* Only ESF is selected */
	elseif (!empty($esf)) {

		$query = "SELECT t.ID, t.name, t.owner, t.cost_amount, t.cost_unit, t.status, Request.end_date
			FROM (SELECT * FROM Resource JOIN Resource_Secondary_ESF WHERE Resource.ID = Resource_Secondary_ESF.resource_id) AS t LEFT OUTER JOIN Request ON t.ID=request.resource_id
			WHERE 
			(
				primary_esf = '$esf'
				OR ESF_ID = '$esf'
			) GROUP BY t.ID ORDER BY t.status, t.name ASC;";
	}

	/* Only keyword is selected */
	elseif (!empty($keyword)) {

        $query = "SELECT t.ID, t.name, t.owner, t.cost_amount, t.cost_unit, t.status, Request.end_date
			FROM (SELECT * FROM Resource JOIN Resource_Capabilities WHERE Resource.ID = Resource_Capabilities.resource_id) AS t LEFT OUTER JOIN Request ON t.ID=Request.resource_id
			WHERE 
			(
				t.name LIKE '%$keyword%'
				OR t.model LIKE '%$keyword%'  
				OR Capability LIKE '%$keyword%'
			) GROUP BY t.ID ORDER BY t.status, t.name ASC;";
	} 

	/* No search parameters selected */
	else {
        $query = "SELECT Resource.ID, Resource.name, Resource.owner, Resource.cost_amount, Resource.cost_unit,
              Resource.status, Request.end_date FROM Resource LEFT OUTER JOIN Request ON Resource.ID = Request.resource_id 
              GROUP BY Resource.ID ORDER BY Resource.status, Resource.name;";
   	 }
	
	$result = mysql_query($query);
	if (!$result) {
		print '<p class="error">Error: ' . mysql_error() . '</p>';
		exit();
	}
		
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Search Resource</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	
	<body>

		<div id="main_container">
			
			<div id="header">
				<div class="logo"><img src="images/erms_logo.png" border="0" alt="" title="" /></div>       
			</div>
			
			<div class="menu">
				<ul>
					<li><a href="home.php">home</a></li>
					<li><a href="add_resource.php">add resource</a></li>
					<li><a href="add_incident.php">add incident</a></li>
					<li class="selected"><a href="search.php">search resources</a></li>
					<li><a href="status.php">resource status</a></li>
					<li><a href="report.php">resource report</a></li>
					<li><a href="logout.php">log out</a></li>
				</ul>
            </div>	

			<div class="center_content">
			
				<div class="center_left">
					
					<div class="title_name"><?php print $user_name; ?></div>          
					
										
					<div class="features">   
						
						<div class="profile_section">

                            <?php
                            // error message display
                            if (!empty($errorMsg)) {
                                print "<div class='profile_section' style='color:red'>$errorMsg</div>";
                            }
                            ?>

                            <?php
                            // error message display
                            if (!empty($successMsg)) {
                                print "<div class='profile_section' style='color:green'>$successMsg</div>";
                            }
                            ?>

                            <div class="subtitle">Search Resource</div>
							
							<form name="searchform" action="search.php" method="post">
							<table width="80%">								
								<tr>
									<td class="item_label">Keyword</td>
									<td><input type="text" name="keyword" /></td>
								</tr>
								<tr>
									<td class="item_label">ESF</td>
                                    <td>
                                        <?php								
                                            $esf_query = "SELECT ID, description " .
                                                     "FROM ESF ";
                                              
                                            $esf_result = mysql_query($esf_query);
                                            if (!$esf_result) {
                                                print "<p class='error'>Error: " . mysql_error() . "</p>";
                                                exit();
                                            }

                                            $select = '<select name="esf">  <option disabled selected value> -- select an option -- </option>';

                                            while ($row2 = mysql_fetch_array($esf_result)){
                                                $select.='<option value="'.$row2['ID'].'">'.'(#'.$row2['ID'].') '.$row2['description'].'</option>';
                                            }
                                            $select.='</select>';
                                            print "$select";
                                        ?>
                                    </td>
                                 </tr>
								<tr>
									<td class="item_label">Location</td>
									<td>Within &nbsp <input type="number" name="distance" /> kilometers of the incident</td>
                                </tr>
								<tr>
									<td class="item_label">Incident</td>
                                    <td>
                                         <?php
                                            $username = $_SESSION['username'];
                                            $incident_query = "SELECT ID, description FROM incident WHERE owner='$username';";
                                            $incident_result = mysql_query($incident_query);
                                            if (!$incident_result) {
                                               print "<p class='error'>Error: " . mysql_error() . "</p>";
                                                exit();
                                            }

                                                
                                            $select = '<select name="incident">  <option disabled selected value> -- select an option -- </option>';
                                            while ($row3 = mysql_fetch_array($incident_result)){
                                                $select.='<option value="'.$row3['ID'].'">'.'('.$row3['ID'].') '.$row3['description'].'</option>';
                                            }
                                            $select.='</select>';
                                            print "$select";
                                        ?>
                                    </td>
								</tr>
							</table>
							
							<a href="javascript:searchform.submit();" class="fancy_button">search</a> 
							
							</form>
														
						</div>

						<?php
						if (isset($result)) {
													
							print "<div class='profile_section'>";
							print "<div class='subtitle'>Search Results for Incident</div>";							
							print "<table width='80%'>";
                            print "<tr><td class='heading'>ID</td><td class='heading'>Name</td><td class='heading'>Owner</td>
                                <td class='heading'>Cost</td><td class='heading'>Status</td><td class='heading'>Next Available</td>
                                <td class='heading'>Distance</td><td class='heading'>Action</td></tr>";
							
							while ($row = mysql_fetch_array($result)){
                                print "<form name=\"searchresultsform{$row['ID']}\" action=\"search.php\" method=\"post\">";

                                print "<tr>";
								print "<td>{$row['ID']}</td>";
                                print "<td>{$row['name']}</td>";
                                print "<td>{$row['owner']}</td>";

                                $formatCost = number_format($row['cost_amount'], 2,'.', ',');
                                print "<td> \$$formatCost / {$row['cost_unit']}</td>";

                                // RESOURCE: possible status values are 0 for available, 1 for in use, 2 for in repair
                                if($row['status'] == 0){
                                    print "<td>AVAILABLE</td>";
                                } else if ($row['status'] == 1) {
                                    print "<td>NOT AVAILABLE</td>";
                                } else { // status == 2
                                    print "<td>IN REPAIR</td>";
                                }

                                $currDate = date("Y-m-d");
                                $endDate = $row['end_date'];
			
				                // If it's availble now, let the user know
                                if ($row['status'] == 0) {
                                    print "<td>NOW</td>";
                                } else { // Otherwise tell the user when it will be available
					                print "<td>$endDate</td>";
				                }

                                $roundedDist = round($row['distance'],4);
                                print "<td>{$roundedDist} km</td>";

                                print "<td>";

                                // hidden inputs to be send by POST request
                                print "<input type='hidden' name='resource_id' value='{$row['ID']}'/>";
                                print "<input type='hidden' name='owner' value='{$row['owner']}'/>";
                                print "<input type='hidden' id = 'date{$row['ID']}' name='date' value=''/>";
                                print "<input type='hidden' id = 'status' name='status' value='{$row['status']}'/>";
                                print "<input type='hidden' id = 'end_date' name='end_date' value='{$row['end_date']}'/>";



                                // only show request resource if incident is selected and not being repaired
                                if ($_SESSION['username'] != $row['owner'] AND $row['status'] != 2 AND isset($_SESSION["incident"])) {
                                    print "<input type=\"submit\" name=\"request\" value=\"Request\" onclick=\" var d = window.prompt('Enter return date: ', 'YYYY-MM-DD'); var list = document.getElementById('date{$row['ID']}'); list.value = d; searchresultsform{$row['ID']}.submit();\"/> ";
                                }

                                // only show the deploy button if the user owns the resource and the resource is available
                                if($_SESSION['username'] == $row['owner'] AND $row['status'] == 0 AND isset($_SESSION["incident"])){
                                    print "<input type=\"submit\" name=\"deploy\" value=\"Deploy\" onclick=\" var d = window.prompt('Enter return date: ', 'YYYY-MM-DD'); var list = document.getElementById('date{$row['ID']}'); list.value = d; searchresultsform{$row['ID']}.submit();\"/> ";
                                }

                                //only show the repair button if the user owns the resource and it is not already being repaired
                                if($_SESSION['username'] == $row['owner'] AND $row['status'] != 2) {
                                    print "<input type=\"submit\" name=\"repair\" value=\"Repair\" onclick=\" var d = window.prompt('Enter number of days to be repaired', ''); var list = document.getElementById('date{$row['ID']}'); list.value = d; searchresultsform{$row['ID']}.submit();\"/> ";
                                }

                                print "</tr>";
								print "</form>";
							}
							
							print "</table>";
							print "</div>";
						
						}
						?>
			
					 </div> 
					
				</div> 
				
				<div class="clear"></div> 
			
			</div>    

		
			<div id="footer">                                              
				<div class="right_footer"><a href="http://csstemplatesmarket.com"  target="_blank">http://csstemplatesmarket.com</a></div>       
			</div>
			
		 
		</div>

	</body>
</html>
