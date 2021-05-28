<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: index.php");
    exit;
}
require_once "config.php";
$sql = "select image, dataobject from dataobjects_suggested where idsuggested = :p1 and deleted = '0'";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["id"]);
    oci_define_by_name($stmt, 'IMAGE', $image);
    oci_define_by_name($stmt, 'DATAOBJECT', $dataobject);
    if (oci_execute($stmt)) {
      oci_fetch($stmt);
      if (!is_null($image)) {
        $image_sql = "update dataobjects set image = EMPTY_BLOB(), changedby = :p2, changeddate = SYSTIMESTAMP where iddataobject = :p3 and deleted = 0 returning image into :p1";
        if ($image_stmt = oci_parse($link, $image_sql)) {
        	$lob = oci_new_descriptor($link, OCI_D_LOB);
            oci_bind_by_name($image_stmt, ':p1', $lob, -1, OCI_B_BLOB);
        	oci_bind_by_name($image_stmt, ':p2', $_SESSION["iduser"]);
        	oci_bind_by_name($image_stmt, ':p3', $dataobject);
        	if (!oci_execute($image_stmt, OCI_DEFAULT)) {
        		echo "Произошла непредвиденная ошибка";
        	}
        	if (!$lob -> append($image)) {
                oci_rollback($link);
            }
            else {
                oci_commit($link);
            }
            $lob->free();
        }
        else {
        	echo "Произошла непредвиденная ошибка";
        }
        oci_free_statement($image_stmt);
      }
      else {
      	$do_sql = "update dataobjects set changedby = :p1, changeddate = SYSTIMESTAMP where iddataobject = :p2 and deleted = 0";
      	if ($do_stmt = oci_parse($link, $do_sql)) {
      		oci_bind_by_name($do_stmt, ':p1', $_SESSION["iduser"]);
      		oci_bind_by_name($do_stmt, ':p2', $dataobject);
      		if(!oci_execute($do_stmt)) {
      			echo "Произошла непредвиденная ошибка";
      		}
      		oci_free_statement($do_stmt);
      	}
      }
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
  }
  oci_free_statement($stmt);
$sql = "select value, recordtype from recordvalues_suggested where suggesteddataobject = :p1 and deleted = 0";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_GET["id"]);
	oci_define_by_name($stmt, 'VALUE', $value);
	oci_define_by_name($stmt, 'RECORDTYPE', $recordtype);
	if (oci_execute($stmt)) {
		while (oci_fetch($stmt)) {
			$change_sql = "update recordvalues set value = :p1, changedby = :p2, changeddate = SYSTIMESTAMP where dataobject = :p3 and recordtype = :p4 and deleted = 0";
			if ($change_stmt = oci_parse($link, $change_sql)) {
				oci_bind_by_name($change_stmt, ':p1', $value);
				oci_bind_by_name($change_stmt, ':p2', $_SESSION["iduser"]);
				oci_bind_by_name($change_stmt, ':p3', $dataobject);
				oci_bind_by_name($change_stmt, ':p4', $recordtype);
				if (!oci_execute($change_stmt)) {
					echo "Произошла непредвиденная ошибка";
				}
			}
			oci_free_statement($change_stmt);
		}
	}
	else {
		echo "Произошла непредвиденная ошибка";
	}
}
oci_free_statement($stmt);
$sql = "update recordvalues_suggested set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where deleted = 0 and suggesteddataobject = :p2";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["id"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
}
oci_free_statement($stmt);
$sql = "update dataobjects_suggested set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = 1 where deleted = 0 and idsuggested = :p2";
if ($stmt = oci_parse($link, $sql)) {
	oci_bind_by_name($stmt, ':p1', $_SESSION["iduser"]);
	oci_bind_by_name($stmt, ':p2', $_GET["id"]);
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
}
oci_free_statement($stmt);
oci_close($link);
header("location: suggestions.php");
exit;
?>