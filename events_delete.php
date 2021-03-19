<?php
require_once "config.php";
$sql = "update events set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where name = :p2";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["name"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
}
$sql = "update events_services set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where event in (select idevent from events where name = :p2)";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["name"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
}
$sql = "update events_districts set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where event in (select idevent from events where name = :p2)";
if ($stmt = oci_parse($link, $sql)) {
  oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
  oci_bind_by_name($stmt, ':p2', $_GET["name"]);
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
oci_free_statement($stmt);
oci_close($link);
header("location: events.php");
exit;
?>