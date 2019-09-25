<?php
include 'ChromePhp.php';

//ini_set('display_errors', 1);

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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /* return a resource */
    if (!empty($_POST['return_resource'])) {

        $id = $_POST['return_resource'];
        $query = "UPDATE resource SET `status` = 0 WHERE ID = $id";

        $result = mysql_query($query);

        if (!$result) {
           print '<p class="error">Error: ' . mysql_error() . '</p>';
            exit();
        }

        header("Location: status.php");
        unset($result);
    }


    /* cancel a resource request */
    if (!empty($_POST['cancel_request']) && !empty($_POST['incident'])) {

        $id = $_POST['cancel_request'];
        $username = $_SESSION['username'];
        $incidentID = $_POST['incident'];

        $query = "DELETE FROM Request WHERE requester_id = '$username' AND resource_id = $id AND incident_id = $incidentID;";

        $result = mysql_query($query);

        if (!$result) {
            print '<p class="error">Error: ' . mysql_error() . '</p>';
            exit();
        }

        header("Location: status.php");
        unset($result);
    }

    /* deploy a resource (accept request) */
    if (!empty($_POST['resource']) && !empty($_POST['incident']) && !empty($_POST['requester']) && $_POST['deploy']) {

        $id = $_POST['resource'];
        $username = $_SESSION['username'];
        $incidentID = $_POST['incident'];
        $requester = $_POST['requester'];

        $query1 = "UPDATE Resource SET `status` = 1 WHERE Resource.ID='$id' AND `owner`='$username';";
        $result1 = mysql_query($query1);

        $query2 = "DELETE FROM Request WHERE requester_id = '$requester' AND resource_id = '$id' AND incident_id = $incidentID;";
        $result2 = mysql_query($query2);

        if (!$result1 || !$result2) {
            print '<p class="error">Error: ' . mysql_error() . '</p>';
            exit();
        }

        header("Location: status.php");
        unset($result);
    }

    /* reject a request */
    if (!empty($_POST['resource']) && !empty($_POST['incident']) && !empty($_POST['requester']) && $_POST['reject']) {

        $id = $_POST['resource'];
        $username = $_SESSION['username'];
        $incidentID = $_POST['incident'];
        $requester = $_POST['requester'];

        $query = "DELETE FROM Request WHERE requester_id = '$requester' AND resource_id = '$id' AND Incident_id = $incidentID;";

        $result = mysql_query($query);

        if (!$result) {
        print '<p class="error">Error: ' . mysql_error() . '</p>';
            exit();
        }
        header("Location: status.php");
        unset($result);
    }

    /* cancel a repair */
    if (!empty($_POST['cancel_repair'])) {

        $id = $_POST['cancel_repair'];
        $username = $_SESSION['username'];

        $query = "DELETE FROM Request WHERE requester_id='$username' AND resource_id=$id AND `type` = 0;";

        $result = mysql_query($query);

        if (!$result) {
            print '<p class="error">Error: ' . mysql_error() . '</p>';
            exit();
        }
        header("Location: status.php");
        unset($result);
    }}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Resource Status</title>
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
            <li><a href="add_incident.php">add incident</a></li>
            <li><a href="search.php">search resources</a></li>
            <li class="selected"><a href="status.php">resource status</a></li>
            <li><a href="report.php">resource report</a></li>
            <li><a href="logout.php">log out</a></li>
        </ul>
    </div>

    <div class="center_content">

        <div class="center_left">
            <div class="title_name">Resource Status</div>

            <!----------------------- RESOURCES IN USE TABLE ---------------------->
            <h4>Resources In Use</h4>

            <table width="100%">
                <tr>
                    <td class="heading">ID</td>
                    <td class="heading">Resource Name</td>
                    <td class="heading">Incident</td>
                    <td class="heading">Owner</td>
                    <td class="heading">Start Date</td>
                    <td class="heading">Return By</td>
                    <td class="heading">Action</td>

                </tr>


                <?php

                $query1 = "SELECT Resource.ID, Resource.name, Incident.description, Resource.owner, Request.start_date, 
                  Request.end_date FROM ((Incident JOIN Request ON Incident.ID=Request.Incident_id) JOIN Resource 
                  ON Request.Resource_id=Resource.ID) WHERE Incident.Owner='{$_SESSION['username']}' 
                  AND Request.type = 2 AND Resource.status = 1";

                $result1 = mysql_query($query1);
                if (!$result1) {
                    print "<p class='error'>Error: " . mysql_error() . "</p>";
                    exit();
                }

                while ($row = mysql_fetch_array($result1)) {

                    print "<form name=\"inuseform{$row['ID']}\" action=\"status.php\" method=\"post\">";

                    print "<tr>";
                    print "<td>{$row['ID']}</td>";
                    print "<td>{$row['name']}</td>";
                    print "<td>{$row['description']}</td>";
                    print "<td>{$row['owner']}</td>";

                    $formatted_start_date = date('Y-m-d', strtotime($row['start_date']));
                    $formatted_end_date = date('Y-m-d', strtotime($row['end_date']));

                    print "<td>{$formatted_start_date}</td>";
                    print "<td>{$formatted_end_date}</td>";

                    print "<input type='hidden' name='return_resource' value='{$row['ID']}'/>";
                    print "<td><input type=\"submit\" name=\"return\" value=\"Return\" onclick=\"inuseform{$row['ID']}.submit();\" />";

                    print "</tr>";
                    print "</form>";

                }
                ?>

            </table>

            <!----------------------- RESOURCES REQUESTED BY ME TABLE ---------------------->
            <h4>Resources Requested By Me</h4>


            <table width="100%">
                <tr>
                    <td class="heading">ID</td>
                    <td class="heading">Resource Name</td>
                    <td class="heading">Incident</td>
                    <td class="heading">Owner</td>
                    <td class="heading">Return By</td>
                    <td class="heading">Action</td>

                </tr>

                <?php

                 $query1 = "SELECT Incident.ID AS incident_id, Resource.ID AS resource_id, Resource.name, Incident.description, Resource.owner, Request.end_date
                  FROM ((Request JOIN Incident ON Request.incident_id=Incident.ID) JOIN Resource 
                  ON Request.resource_id=Resource.ID) WHERE Request.requester_id='{$_SESSION['username']}'
                  AND Incident.owner='{$_SESSION['username']}' AND '{$_SESSION['username']}' != Request.owner AND Request.type = 1";

                $result1 = mysql_query($query1);


                if (!$result1) {
                    print "<p class='error'>Error: " . mysql_error() . "</p>";
                    exit();
                }

                while ($row = mysql_fetch_array($result1)) {

                    print "<form name=\"requestedbymeform{$row['resource_id']}\" action=\"status.php\" method=\"post\">";
                    print "<tr>";
                    print "<td>{$row['resource_id']}</td>";
                    print "<td>{$row['name']}</td>";
                    print "<td>{$row['description']}</td>";
                    print "<td>{$row['owner']}</td>";

                    $formatted_end_date = date('Y-m-d', strtotime($row['end_date']));
                    print "<td>{$formatted_end_date}</td>";

                    print "<input type='hidden' name='cancel_request' value='{$row['resource_id']}'/>";
                    print "<input type='hidden' name='incident' value='{$row['incident_id']}'/>";
                    print "<td><input type=\"submit\" name=\"cancel\" value=\"Cancel\" onclick=\"requestedbymeform{$row['ID']}.submit();\" />";

                    print "</tr>";
                    print "</form>";
                }

                ?>



            </table>

            <!----------------------- RESOURCE REQUESTS RECEIVED BY ME TABLE ---------------------->
            <h4>Resource Requests Received By Me</h4>

            <table width="100%">
                <tr>
                    <td class="heading">ID</td>
                    <td class="heading">Resource Name</td>
                    <td class="heading">Incident</td>
                    <td class="heading">Requested By</td>
                    <td class="heading">Return By</td>
                    <td class="heading">Action</td>

                </tr>


                <?php

                $query1 = "SELECT Incident.ID AS incident_id, Resource.ID AS resource_id, Resource.name, Incident.description, Request.requester_id, 
                Request.end_date FROM ((Resource JOIN Request ON Resource.ID=Request.resource_id) JOIN Incident 
                ON Incident.ID=Request.incident_id) WHERE Resource.Owner='{$_SESSION['username']}'";

                $result1 = mysql_query($query1);
                if (!$result1) {
                    print "<p class='error'>Error: " . mysql_error() . "</p>";
                    exit();
                }

                while ($row = mysql_fetch_array($result1)) {
                    print "<form name=\"requestsreceivedform{$row['resource_id']}\" action=\"status.php\" method=\"post\">";

                    print "<tr>";
                    print "<td>{$row['resource_id']}</td>";
                    print "<td>{$row['name']}</td>";
                    print "<td>{$row['description']}</td>";
                    print "<td>{$row['requester_id']}</td>";

                    $formatted_end_date = date('Y-m-d', strtotime($row['end_date']));
                    print "<td>{$formatted_end_date}</td>";

                    print "<input type='hidden' name='resource' value='{$row['resource_id']}'/>";
                    print "<input type='hidden' name='requester' value='{$row['requester_id']}'/>";
                    print "<input type='hidden' name='incident' value='{$row['incident_id']}'/>";

                    print "<td><input type=\"submit\" name=\"deploy\" value=\"Deploy\" onclick=\"requestsreceivedform{$row['resource_id']}.submit();\" />";
                    print "<input type=\"submit\" name=\"reject\" value=\"Reject\" onclick=\"requestsreceivedform{$row['resource_id']}.submit();\" /> </td>";

                    print "</tr>";

                    print "</form>";

                }

                ?>

            </table>


            <!----------------------- REPAIRS SCHEDULED/ IN PROGRESS TABLE ---------------------->
            <h4>Repairs Scheduled/In-progress</h4>

            <table width="100%">
                <tr>
                    <td class="heading">ID</td>
                    <td class="heading">Resource Name</td>
                    <td class="heading">Start On</td>
                    <td class="heading">Ready By</td>
                    <td class="heading">Action</td>

                </tr>


                <?php

                $query1 = "SELECT Request.Resource_id, Resource.name, Request.start_date, Request.end_date 
                  FROM Request JOIN Resource ON Request.Resource_id= Resource.ID WHERE Request.Type=0 AND 
                  Request.owner = '{$_SESSION['username']}' GROUP BY Request.Resource_id";

                $result1 = mysql_query($query1);
                if (!$result1) {
                    print "<p class='error'>Error: " . mysql_error() . "</p>";
                    exit();
                }

                while ($row = mysql_fetch_array($result1)) {
                    print "<form name=\"cancelrepair{$row['Resource_id']}\" action=\"status.php\" method=\"post\">";

                    print "<tr>";
                    print "<td>{$row['Resource_id']}</td>";
                    print "<td>{$row['name']}</td>";

                    $formatted_start_date = date('Y-m-d', strtotime($row['start_date']));
                    $formatted_end_date = date('Y-m-d', strtotime($row['end_date']));

                    print "<td>{$formatted_start_date}</td>";
                    print "<td>{$formatted_end_date}</td>";

                    print "<input type='hidden' name='cancel_repair' value='{$row['Resource_id']}'/>";
                    print "<td><input type=\"submit\" name=\"cancel\" value=\"Cancel\" onclick=\"cancelrepair{$row['Resource_id']}.submit();\" />";

                    print "</tr>";
                    print "</form>";

                }

                ?>

            </table>


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