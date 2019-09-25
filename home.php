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

$query = "SELECT name,type FROM User WHERE username = '{$_SESSION['username']}';";


$result = mysql_query($query);
if (!$result) {
	print "<p class='error'>Error: " . mysql_error() . "</p>";
	exit();
}

$row = mysql_fetch_array($result);

if (!$row) {
	print "<p>Error: No data returned from database.  Administrator login NOT supported.</p>";
	print "<a href='logout.php'>Logout</a>";
	exit();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>ERMS Main Menu</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	
	<body>

		<div id="main_container">
			
			<div id="header">
				<div class="logo"><img src="images/erms_logo.png" border="0" alt="" title="" /></div>       
			</div>
			
			<div class="menu">
				<ul>                                                                         
					<li class="selected"><a href="home.php">home</a></li>
					<li><a href="add_resource.php">add resource</a></li>
					<li><a href="add_incident.php">add incident</a></li>
					<li><a href="search.php">search resources</a></li>
					<li><a href="status.php">resource status</a></li>
					<li><a href="report.php">resource report</a></li>
					<li><a href="logout.php">log out</a></li>
				</ul>
			</div>
			
			<div class="center_content">
			
				<div class="center_left">

                    <div class="title">ERMS</div>

                    <div>
                        <table>
                                <?php 
                                    $type = $row['type'];
                                    if ($type == 'M') {
                                        print "<tr>";
                                        print "<td>" . "Municipality: " . $row['name'] . "</td>";
                                        print "</tr>";

                                        $query = "SELECT pop_size " .
                                                 "FROM Municipality " .
                                                 "WHERE username = '" . $_SESSION['username'] . "' ";
                                        $result = mysql_query($query);
                                        $row2 = mysql_fetch_array($result); 
                                        print "<tr>";
                                        print "<td>" . "Population: " . $row2['pop_size'] . "</td>";
                                        print "</td>";
                                    }
                                    elseif ($type == 'I') {
                                        print "<tr>";
                                        print "<td>" . $row['name'] . "</td>";
                                        print "</tr>";

                                        $query = "SELECT job_title, hire_date " .
                                                 "FROM Individual " .
                                                 "WHERE username = '" . $_SESSION['username'] . "' ";
                                        $result = mysql_query($query);
                                        $row2 = mysql_fetch_array($result); 
                                        print "<tr>";
                                        print "<td>"  . "Job Title: " . $row2['job_title'] . "</td>";
                                        print "</tr>";
                                        print "<tr>";
                                        print "<td>"  . "Hire Date: " . $row2['hire_date'] . "</td>";
                                        print "</tr>";
                                    }
                                    elseif ($type == 'G') {
                                        print "<tr>";
                                        print "<td>" . $row['name'] . "</td>";
                                        print "</tr>";
                                        
                                        $query = "SELECT jurisdiction " .
                                                 "FROM Government_Agency " .
                                                 "WHERE username = '" . $_SESSION['username'] . "' ";
                                        $result = mysql_query($query);
                                        $row2 = mysql_fetch_array($result); 
                                        print "<tr>";
                                        print "<td>"  . "Jurisdiction: " . $row2['jurisdiction'] . "</td>";
                                        print "</tr>";
                                    }
                                    elseif ($type == 'C') {
                                        print "<tr>";
                                        print "<td>" . $row['name'] . "</td>";
                                        print "</tr>";
                                        
                                        $query = "SELECT HQ_location " .
                                                 "FROM Company " .
                                                 "WHERE username = '" . $_SESSION['username'] . "' ";
                                        $result = mysql_query($query);
                                        $row2 = mysql_fetch_array($result); 
                                        print "<tr>";
                                        print "<td>"  . "Headquartered: " . $row2['HQ_location'] . "</td>";
                                        print "</tr>";
                                    }
                                ?>
                        </table>
                    </div>
                            
														
				<div class="clear"></div> 
			
			</div>    

		
			<div id="footer">                                              
				<div class="right_footer"><a href="http://csstemplatesmarket.com"  target="_blank">http://csstemplatesmarket.com</a></div>       
			</div>
			
		 
		</div>

	</body>
</html>
