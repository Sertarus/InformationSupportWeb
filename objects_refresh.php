<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: index.php");
    exit;
}
require_once "config.php";
$sql = "select recordtype, r.name, dataorder, dr.deleted from datatypes_recordtypes dr join recordtypes r on r.idrecordtype = dr.recordtype where datatype in (select datatype from branches where idbranch = :p1 and deleted = '0') and dr.deleted = '0' order by dataorder";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_GET["branchid"]);
	oci_define_by_name($stmt, 'NAME', $name);
	if (oci_execute($stmt)) {
		$counter = 1;
		while (oci_fetch($stmt)) {
			echo "<div class='form-group'><textarea class='form-control' name='" . $name . "' placeholder='" . $name . "'></textarea></div>";
			$counter++;
		}
	}
	else {
		echo "Произошла непредвиденная ошибка";
	}
	oci_free_statement($stmt);
}
oci_close($link);
?>
