<?php
require_once "config.php";
$sql = "update dataobjects set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where name = :p2";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["name"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
}
$sql = "update recordvalues set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where dataobject in (select iddataobject from dataobjects where name = :p2)";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["name"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
}
$sql = "select name from recordtypes where deleted = '0' and idrecordtype not in (select recordtype from datatypes_recordtypes where deleted = '0') and idrecordtype not in (select recordtype from recordvalues where deleted = 0)";
if ($stmt = oci_parse($link, $sql)) {
      oci_define_by_name($stmt, 'NAME', $rec_name);
      if (oci_execute($stmt)) {
        while (oci_fetch($stmt)) {
          $rec_upd_sql = "update recordtypes set deleted = '1', changedby = :p1, changeddate = SYSTIMESTAMP where name = :p2";
          if ($rec_upd_stmt = oci_parse($link, $rec_upd_sql)) {
            oci_bind_by_name($rec_upd_stmt, ':p1', $_SESSION['iduser']);
            oci_bind_by_name($rec_upd_stmt, ':p2', $rec_name);
            if (!oci_execute($rec_upd_stmt)) {
              echo "Произошла непредвиденная ошибка";
            }
          }
        }
      }
      else {
        echo "Произошла непредвиденная ошибка";
      }
    }
oci_free_statement($stmt);
oci_close($link);
header("location: objects.php");
exit;
?>