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

function validateDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/* if form was submitted, then save new data */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errorMsg = "";

    /* validate form */
    if (empty($_POST['date'])) {
        $errorMsg .= "Error: You must provide an incident date.  <br>\n ";
    }

    if (!empty($_POST['date']) AND !validateDate($_POST['date'])) {
        $errorMsg .= "Error: You must provide a valid date.  <br>\n ";
    }

    if (($_POST['date']) > date ("Y-m-d")) {
        $errorMsg .= "Error: You must provide a valid date.  <br>\n ";
    }

    if (empty($_POST['description'])) {
        $errorMsg .= "Error: You must briefly describe the incident.  <br>\n ";
    }

    if (empty($_POST['latitude'])) {
        $errorMsg .= "Error: You must provide a latitude value.  <br>\n ";
    }

    if (!empty($_POST['latitude']) AND (($_POST['latitude'])<=-90 OR ($_POST['latitude'])>=90)) {
        $errorMsg .= "Error: Enter a latitude value between -90 degrees and 90 degrees.  <br>\n ";
    }

    if (empty($_POST['longitude'])) {
        $errorMsg .= "Error: You must provide a longitude value.  <br>\n ";
    }

    if (!empty($_POST['longitude']) AND (($_POST['longitude'])<=-180 OR ($_POST['longitude'])>=180)) {
        $errorMsg .= "Error: Enter a longitude value between -180 degrees and 180 degrees.  <br>\n ";
    }

    if(empty($errorMsg)) {

        /* Insert incident */
        $date = mysql_real_escape_string($_POST['date']);
        $description = mysql_real_escape_string($_POST['description']);
        $latitude = mysql_real_escape_string($_POST['latitude']);
        $longitude = mysql_real_escape_string($_POST['longitude']);
        $username = $_SESSION['username'];

        $query = "INSERT INTO Incident (`date`, description, latitude, longitude, `owner`)" .
            "VALUES ('$date', '$description', '$latitude', '$longitude', '$username')";
        $result = mysql_query($query);
        if (!$result) {
            print '<p class="error">Error: ' . mysql_error() . '</p>';
            exit();
        }

        /* redirect to home page */
        header("Location: add_incident.php");

        unset($result);
    }
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>New Incident</title>
        <link rel="stylesheet" type="text/css" href="style.css"/>
    </head>

    <body>

    <div id="main_container">

        <div id="header">
            <div class="logo"><img src="images/erms_logo.png" border="0" alt="" title=""/></div>
        </div>

        <div class="menu">
            <ul>
                <li><a href="home.php">home</a></li>
                <li><a href="add_resource.php">add resource</a></li>
				<li class="selected"><a href="add_incident.php">add incident</a></li>
                <li><a href="search.php">search resources</a></li>
                <li><a href="status.php">resource status</a></li>
                <li><a href="report.php">resource report</a></li>
                <li><a href="logout.php">log out</a></li>
            </ul>
        </div>

        <div class="center_content">

            <div class="center_left">
                <!-- <div class="title_name"> <?php print $row['name']; ?></div>-->

                <h2>New Incident Info</h2>

                <?php
                // error message display
                if (!empty($errorMsg)) {
                    print "<div class='profile_section' style='color:red'>$errorMsg</div>";
                }
                ?>

                <form name="addincidentform" action="add_incident.php" method="post">

                <table width="80%">
                    <tr>
                        <td class="item_label">Incident ID</td>
                        <?php

                        $query = "SELECT `ID` FROM `incident` " .
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
                        <td class="item_label">Date</td>
                        <td><input type="date" name="date" /></td>
                    </tr>

                    <tr>
                        <td class="item_label">Description</td>
                        <td><input type="text" name="description" /></td>
                    </tr>

                    <tr>
                        <td class="item_label">Latitude</td>
                        <td><input type="number" name="latitude" /></td>
                    </tr>
                    <tr>
                        <td class="item_label">Longitude</td>
                        <td><input type="number" name="longitude" /></td>
                    </tr>

                </table>
                </form>
                </br>
                <a href="javascript:addincidentform.submit();" class="fancy_button">Submit</a>


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
