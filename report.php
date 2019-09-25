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

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Resource Report</title>
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
            <li><a href="status.php">resource status</a></li>
            <li class="selected"><a href="report.php">resource report</a></li>
            <li><a href="logout.php">log out</a></li>
        </ul>
    </div>

    <div class="center_content">

        <div class="center_left">
            <!-- <div class="title_name"> <?php print $row['name']; ?></div>-->

                    <h2>Resource Report by Primary Emergency Support Function</h2>

                    <table width="100%">
                        <tr>
                            <td class="heading">#</td>
                            <td class="heading">Primary Emergency Support Function</td>
                            <td class="heading">Total Resources</td>
                            <td class="heading">Resources in Use</td>

                        </tr>


                        <?php

                        $query1 = "SELECT total.totalID, total.description, total.totalCount, used.usedCount 
                          FROM (SELECT ESF.ID totalID, ESF.description, COUNT(Resource.primary_esf) totalCount 
                          FROM ESF LEFT JOIN Resource ON Resource.owner = '{$_SESSION['username']}' AND Resource.primary_esf=ESF.ID 
                          GROUP BY ESF.ID) AS total LEFT JOIN (SELECT ESF.ID usedID, ESF.description, 
                          COUNT(Resource.status) usedCount FROM ESF LEFT JOIN Resource ON Resource.owner = '{$_SESSION['username']}' 
                          AND Resource.primary_esf=ESF.ID AND Resource.status = 1 GROUP BY ESF.ID) AS used ON 
                          used.usedID = total.totalID";

                        $result1 = mysql_query($query1);
                        if (!$result1) {
                            print "<p class='error'>Error: " . mysql_error() . "</p>";
                            exit();
                        }

                        while ($row = mysql_fetch_array($result1)) {
                            print "<tr>";
                            print "<td>{$row['totalID']}</td>";
                            print "<td>{$row['description']}</td>";
                            print "<td>{$row['totalCount']}</td>";
                            print "<td>{$row['usedCount']}</td>";
                            print "</tr>";
                        }

                        $query2 = "SELECT SUM(tempTable.totalCount) AS totalCountSum, SUM(tempTable.usedCount) AS usedCountSum 
                        FROM ( SELECT total.totalID, total.description, total.totalCount, used.usedCount FROM 
                        (SELECT ESF.ID totalID, ESF.description, COUNT(Resource.primary_esf) totalCount FROM ESF 
                        LEFT JOIN Resource ON Resource.owner = '{$_SESSION['username']}' AND Resource.primary_esf=ESF.ID GROUP BY ESF.ID) 
                        AS total LEFT JOIN (SELECT ESF.ID usedID, ESF.description, COUNT(Resource.status) usedCount 
                        FROM ESF LEFT JOIN Resource ON Resource.owner = '{$_SESSION['username']}' AND Resource.primary_esf=ESF.ID AND 
                        Resource.status = 1 GROUP BY ESF.ID) AS used ON used.usedID = total.totalID ) tempTable";

                        $result2 = mysql_query($query2);
                        if (!$result2) {
                            print "<p class='error'>Error: " . mysql_error() . "</p>";
                            exit();
                        }

                        $row = mysql_fetch_array($result2);

                        print "<tr>";
                        print "<td></td>";
                        print "<td>TOTALS:</td>";
                        print "<td>{$row['totalCountSum']}</td>";
                        print "<td>{$row['usedCountSum']}</td>";
                        print "</tr>";


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