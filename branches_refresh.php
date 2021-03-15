<?php
require_once "config.php";
$sql_add = "";
if (strcmp($_GET['branchid'], "") != 0 ) {
	$sql_add = " and branch = :p1";
}
$sql = "select idservice, name from services where deleted = '0' and idservice in (select service from branches_services where deleted = '0'" . $sql_add . ")";
if ($stmt = oci_parse($link, $sql)) {
	if (strcmp($_GET['branchid'], "") != 0 ) {
		oci_bind_by_name($stmt, ':p1', $_GET['branchid']);
	}
	oci_define_by_name($stmt, 'IDSERVICE', $id);
	oci_define_by_name($stmt, 'NAME', $service);
	if (oci_execute($stmt)) {
		if (oci_fetch($stmt)) {
			echo $id . ".". $service;
		}
		while (oci_fetch($stmt)) {
			echo "," . $id . ".". $service;
		}
		echo "?";
	}
	else {
		echo "Произошла непредвиденная ошибка";
	}
}
$sql = "select iddistrict, name from districts where deleted = '0' and iddistrict in (select district from branches_districts where deleted = '0'" . $sql_add . ")";
if ($stmt = oci_parse($link, $sql)) {
	if (strcmp($_GET['branchid'], "") != 0 ) {
		oci_bind_by_name($stmt, ':p1', $_GET['branchid']);
	}
	oci_define_by_name($stmt, 'IDDISTRICT', $id);
	oci_define_by_name($stmt, 'NAME', $district);
	if (oci_execute($stmt)) {
		if (oci_fetch($stmt)) {
			echo $id . "." . $district;
					}
		while (oci_fetch($stmt)) {
			echo "," . $id . ".". $district;
		}
	}
	else {
		echo "Произошла непредвиденная ошибка";
	}
}
?>
