<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: index.php");
    exit;
}
require_once "config.php";
$sql = "update branches set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where name = :p2 and deleted = 0";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["name"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
	oci_free_statement($stmt);
}
$sql = "update branches_services set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where deleted = 0 and branch in (select idbranch from branches where name = :p2)";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["name"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
	oci_free_statement($stmt);
}
$sql = "update branches_districts set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where deleted = 0 and branch in (select idbranch from branches where name = :p2)";
if ($stmt = oci_parse($link, $sql)) {
  oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
  oci_bind_by_name($stmt, ':p2', $_GET["name"]);
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
oci_close($link);
header("location: branches.php");
exit;
?>