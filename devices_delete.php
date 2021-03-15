<?php
require_once "config.php";
$sql = "update devices set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where name = :p2";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["name"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
	oci_free_statement($stmt);
}
oci_close($link);
header("location: devices.php");
exit;
?>