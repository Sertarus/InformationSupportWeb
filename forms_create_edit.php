<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}
$name = "";
$name_err = "";
$is_human = "";
$rec_arr = array();
if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
  $name = $_GET["name"];
  require_once "config.php";
  $sql = "select d.name as datatype, r.name as record, d.ishuman, dataorder from datatypes_recordtypes dr join datatypes d on d.iddatatype = dr.datatype join recordtypes r on r.idrecordtype = dr.recordtype where d.name = :p1 and dr.deleted = '0' and r.deleted = '0' order by dataorder";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["name"]);
    oci_define_by_name($stmt, 'RECORD', $rec_name);
    oci_define_by_name($stmt, 'ISHUMAN', $current_is_human);
    if (oci_execute($stmt)) {
      while (oci_fetch($stmt)) {
        if (strcmp($is_human, "") == 0) {
          $is_human = $current_is_human;
        }
        $rec_arr[] = $rec_name;
      }
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
  }
}

if (isset($_POST['addButton'])) {
  $name = $_POST['name'];
  if (!filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
    if (isset($_POST['isHuman'])) {
      $is_human = 1;
    }
    else {
      $is_human = 0;
    }
  }
  if (strlen($name) >= 1 && strlen($name) <= 40) {
    require_once "config.php";
    if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
      $sql = "select * from datatypes where name = :p1 and name != :p2 and deleted = '0'";
    }
    else {
      $sql = "select * from datatypes where name = :p1 and deleted = '0'";
    }
    
    if ($stmt = oci_parse($link, $sql)) {
      oci_bind_by_name($stmt, ':p1', $name);
      if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
        oci_bind_by_name($stmt, ':p2', $_GET['name']);
      }
      if (oci_execute($stmt)) {
        if (oci_fetch($stmt)) {
          $name_err = "Форма заполнения с таким именем уже существует";
        }
        else {
        $is_rec_err = false;
         $rec_arr = array();
        if ($is_human == 1) {
            $rec_arr[] = "ФИО";
            $rec_arr[] = "Дата рождения";
            $rec_arr[] = "Описание";
          }
        foreach ($_POST as $key => $value) {
          
      if (strcmp($key, "name") != 0 && strcmp($key, "isHuman") != 0 && strcmp($key, "addButton") != 0) {
        $rec_arr[] = $value;
        if (strlen($value) < 1 || strlen($value) > 20) {
        $is_rec_err = true;
        $name_err = "Длина названия каждого из реквизитов должна быть от 1 до 20 символов";
      }
      }
}
$array_new = array_count_values($rec_arr);
        foreach ($array_new as $key => $value) {
          if ($value > 1) {
            $is_rec_err = true;
            $name_err = "В форме не должно быть одинаковых реквизитов";
            break;
          }
        }
if (!$is_rec_err) {
  if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
    $sql = "update datatypes set name = :p1, changedby = :p2, changeddate = SYSTIMESTAMP where name = :p3";
    if ($stmt = oci_parse($link, $sql)) {
      oci_bind_by_name($stmt, ':p1', $name);
      oci_bind_by_name($stmt, ':p2', $_SESSION['iduser']);
      oci_bind_by_name($stmt, ':p3', $_GET['name']);
      if (!oci_execute($stmt)) {
        echo "Произошла непредвиденная ошибка";
      }
    }
    $sql = "select name from recordtypes where deleted = '0' and idrecordtype in (select recordtype from datatypes_recordtypes where deleted = '0' and datatype in (select iddatatype from datatypes where name = :p1 and deleted = '0'))";
    if ($stmt = oci_parse($link, $sql)) {
      oci_bind_by_name($stmt, ':p1', $name);
      oci_define_by_name($stmt, 'NAME', $rec_name);
      if (oci_execute($stmt)) {
        while (oci_fetch($stmt)) {
          if (!in_array($rec_name, $rec_arr)) {
            $rec_upd_sql = "update datatypes_recordtypes set deleted = '1', changedby = :p1, changeddate = SYSTIMESTAMP where recordtype in (select idrecordtype from recordtypes where name = :p2 and deleted = '0') and datatype in (select iddatatype from datatypes where name = :p3 and deleted = 0)";
            if ($rec_upd_stmt = oci_parse($link, $rec_upd_sql)) {
              oci_bind_by_name($rec_upd_stmt, ':p1', $_SESSION['iduser']);
              oci_bind_by_name($rec_upd_stmt, ':p2', $rec_name);
              oci_bind_by_name($rec_upd_stmt, ':p3', $name);
              if (!oci_execute($rec_upd_stmt)) {
                echo "Произошла непредвиденная ошибка";
              }
            }
          }
        }
      }
      else {
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
    foreach ($rec_arr as $key => $value) {
      $sql = "select * from recordtypes where name = :p1 and deleted = '0'";
      if ($stmt = oci_parse($link, $sql)) {
        oci_bind_by_name($stmt, ':p1', $value);
        if (oci_execute($stmt)) {
          if (!oci_fetch($stmt)) {
            $rec_add_sql = "insert into recordtypes (name, createdby, creationdate) values (:p1, :p2, SYSTIMESTAMP)";
            if ($rec_add_stmt = oci_parse($link, $rec_add_sql)) {
              oci_bind_by_name($rec_add_stmt, ':p1', $value);
              oci_bind_by_name($rec_add_stmt, ':p2', $_SESSION['iduser']);
              if (!oci_execute($rec_add_stmt)) {
                echo "Произошла непредвиденная ошибка";
              }
            }
          }
        }
        else {
          echo "Произошла непредвиденная ошибка";
        }
      }
      $sql = "select * from datatypes_recordtypes where deleted = '0' and datatype in (select iddatatype from datatypes where deleted = '0' and name = :p1) and recordtype in (select idrecordtype from recordtypes where deleted = '0' and name = :p2)";
      if ($stmt = oci_parse($link, $sql)) {
        oci_bind_by_name($stmt, ':p1', $name);
        oci_bind_by_name($stmt, ':p2', $value);
        if (oci_execute($stmt)) {
          if (!oci_fetch($stmt)) {
            $rec_dat_add_sql = "insert into datatypes_recordtypes (datatype, recordtype, dataorder, createdby, creationdate) values (:p1, :p2, :p3, :p4, SYSTIMESTAMP)";
            if ($rec_dat_add_stmt = oci_parse($link, $rec_dat_add_sql)) {
              $datatype_id = "";
              $recordtype_id = "";
              $datatype_id_sql = "select iddatatype from datatypes where deleted = '0' and name = :p1";
              if ($datatype_id_stmt = oci_parse($link, $datatype_id_sql)) {
                oci_bind_by_name($datatype_id_stmt, ':p1', $name);
                oci_define_by_name($datatype_id_stmt, 'IDDATATYPE', $current_datatype_id);
                if (oci_execute($datatype_id_stmt)) {
                  while (oci_fetch($datatype_id_stmt)) {
                    $datatype_id = $current_datatype_id;
                  }
                }
                else {
                  echo "Произошла непредвиденная ошибка";
                }
              }
              $recordtype_id_sql = "select idrecordtype from recordtypes where deleted = '0' and name = :p1";
              if ($recordtype_id_stmt = oci_parse($link, $recordtype_id_sql)) {
                oci_bind_by_name($recordtype_id_stmt, ':p1', $value);
                oci_define_by_name($recordtype_id_stmt, 'IDRECORDTYPE', $current_recordtype_id);
                if (oci_execute($recordtype_id_stmt)) {
                  while (oci_fetch($recordtype_id_stmt)) {
                    $recordtype_id = $current_recordtype_id;
                  }
                }
                else {
                  echo "Произошла непредвиденная ошибка";
                }
              }
              oci_bind_by_name($rec_dat_add_stmt, ':p1', $datatype_id);
              oci_bind_by_name($rec_dat_add_stmt, ':p2', $recordtype_id);
              $order = $key + 1;
              oci_bind_by_name($rec_dat_add_stmt, ':p3', $order);
              oci_bind_by_name($rec_dat_add_stmt, ':p4', $_SESSION['iduser']);
              if (!oci_execute($rec_dat_add_stmt)) {
                echo "Произошла непредвиденная ошибка";
              }
            }
          }
          else {
            $rec_dat_add_sql = "update datatypes_recordtypes set dataorder = :p1, changedby = :p2, creationdate = SYSTIMESTAMP where deleted = '0' and datatype = :p3 and recordtype = :p4";
            if ($rec_dat_add_stmt = oci_parse($link, $rec_dat_add_sql)) {
              $datatype_id = "";
              $recordtype_id = "";
              $datatype_id_sql = "select iddatatype from datatypes where deleted = '0' and name = :p1";
              if ($datatype_id_stmt = oci_parse($link, $datatype_id_sql)) {
                oci_bind_by_name($datatype_id_stmt, ':p1', $name);
                oci_define_by_name($datatype_id_stmt, 'IDDATATYPE', $current_datatype_id);
                if (oci_execute($datatype_id_stmt)) {
                  while (oci_fetch($datatype_id_stmt)) {
                    $datatype_id = $current_datatype_id;
                  }
                }
                else {
                  echo "Произошла непредвиденная ошибка";
                }
              }
              $recordtype_id_sql = "select idrecordtype from recordtypes where deleted = '0' and name = :p1";
              if ($recordtype_id_stmt = oci_parse($link, $recordtype_id_sql)) {
                oci_bind_by_name($recordtype_id_stmt, ':p1', $value);
                oci_define_by_name($recordtype_id_stmt, 'IDRECORDTYPE', $current_recordtype_id);
                if (oci_execute($recordtype_id_stmt)) {
                  while (oci_fetch($recordtype_id_stmt)) {
                    $recordtype_id = $current_recordtype_id;
                  }
                }
                else {
                  echo "Произошла непредвиденная ошибка";
                }
              }
              $order = $key + 1;
              oci_bind_by_name($rec_dat_add_stmt, ':p1', $order);
              oci_bind_by_name($rec_dat_add_stmt, ':p2', $_SESSION['iduser']);
              oci_bind_by_name($rec_dat_add_stmt, ':p3', $datatype_id);
              oci_bind_by_name($rec_dat_add_stmt, ':p4', $recordtype_id);
              if (!oci_execute($rec_dat_add_stmt)) {
                echo "Произошла непредвиденная ошибка";
              }
            }
          }
        }
        else {
          echo "Произошла непредвиденная ошибка";
        }
      }
    }
  }
  else {
    $sql = "insert into datatypes (name, ishuman, createdby, creationdate) values (:p1, :p3, :p2, SYSTIMESTAMP)";
    if ($stmt = oci_parse($link, $sql)) {
      oci_bind_by_name($stmt, ':p1', $name);
      oci_bind_by_name($stmt, ':p2', $_SESSION['iduser']);
      oci_bind_by_name($stmt, ':p3', $is_human);
      if (!oci_execute($stmt)) {
        echo "Произошла непредвиденная ошибка";
      }
    }
    foreach ($rec_arr as $key => $value) {
      $sql = "select * from recordtypes where name = :p1 and deleted = '0'";
      if ($stmt = oci_parse($link, $sql)) {
        oci_bind_by_name($stmt, ':p1', $value);
        if (oci_execute($stmt)) {
          if (!oci_fetch($stmt)) {
            $rec_add_sql = "insert into recordtypes (name, createdby, creationdate) values (:p1, :p2, SYSTIMESTAMP)";
            if ($rec_add_stmt = oci_parse($link, $rec_add_sql)) {
              oci_bind_by_name($rec_add_stmt, ':p1', $value);
              oci_bind_by_name($rec_add_stmt, ':p2', $_SESSION['iduser']);
              if (!oci_execute($rec_add_stmt)) {
                echo "Произошла непредвиденная ошибка";
              }
            }
          }
        }
        else {
          echo "Произошла непредвиденная ошибка";
        }
      }
      $rec_dat_add_sql = "insert into datatypes_recordtypes (datatype, recordtype, dataorder, createdby, creationdate) values (:p1, :p2, :p3, :p4, SYSTIMESTAMP)";
            if ($rec_dat_add_stmt = oci_parse($link, $rec_dat_add_sql)) {
              $datatype_id = "";
              $recordtype_id = "";
              $datatype_id_sql = "select iddatatype from datatypes where deleted = '0' and name = :p1";
              if ($datatype_id_stmt = oci_parse($link, $datatype_id_sql)) {
                oci_bind_by_name($datatype_id_stmt, ':p1', $name);
                oci_define_by_name($datatype_id_stmt, 'IDDATATYPE', $current_datatype_id);
                if (oci_execute($datatype_id_stmt)) {
                  while (oci_fetch($datatype_id_stmt)) {
                    $datatype_id = $current_datatype_id;
                  }
                }
                else {
                  echo "Произошла непредвиденная ошибка";
                }
              }
              $recordtype_id_sql = "select idrecordtype from recordtypes where deleted = '0' and name = :p1";
              if ($recordtype_id_stmt = oci_parse($link, $recordtype_id_sql)) {
                oci_bind_by_name($recordtype_id_stmt, ':p1', $value);
                oci_define_by_name($recordtype_id_stmt, 'IDRECORDTYPE', $current_recordtype_id);
                if (oci_execute($recordtype_id_stmt)) {
                  while (oci_fetch($recordtype_id_stmt)) {
                    $recordtype_id = $current_recordtype_id;
                  }
                }
                else {
                  echo "Произошла непредвиденная ошибка";
                }
              }
              $order = $key + 1;
              oci_bind_by_name($rec_dat_add_stmt, ':p1', $datatype_id);
              oci_bind_by_name($rec_dat_add_stmt, ':p2', $recordtype_id);
              oci_bind_by_name($rec_dat_add_stmt, ':p3', $order);
              oci_bind_by_name($rec_dat_add_stmt, ':p4', $_SESSION['iduser']);
              if (!oci_execute($rec_dat_add_stmt)) {
                echo "Произошла непредвиденная ошибка";
              }
            }
    }
  }
  header("location: forms.php");
}
  }
      }
      else {
        echo "Произошла непредвиденная ошибка";
      }
      oci_free_statement($stmt);
    }
    oci_close($link);
  }
  else {
    $name_err = "Длина названия должна быть от 1 до 40 символов";
      }
}
?>
 <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Формы</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <link rel="stylesheet" href="css/inputForms.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/forms_create.js?2"></script>
    <style type="text/css">
      body {
        font: 14px sans-serif;
        text-align: center;
      }
    </style>
  </head>

  <body>
    <nav class="navbar navbar-dark bg-dark" aria-label="First navbar example">
      <div class="container-fluid">
        <a class="navbar-brand" href="forms.php">Назад</a>
        <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarsExample01" aria-controls="navbarsExample01" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExample01">
          <ul class="navbar-nav me-auto mb-2">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="main_page.php">Список активных сотрудников</a>
            </li>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Изменение информации о системе</a>
              <ul class="dropdown-menu text-center" aria-labelledby="dropdown01">
              <li><a class="dropdown-item" href="services.php">Службы</a></li>
              <li><a class="dropdown-item" href="districts.php">Районы</a></li>
              <li><a class="dropdown-item" href="devices.php">Устройства</a></li>
              <li><a class="dropdown-item" href="users.php">Пользователи</a></li>
              <li><a class="dropdown-item" href="branches.php">Ветки</a></li>
              <li><a class="dropdown-item" href="forms.php">Формы заполнения</a></li>
              <li><a class="dropdown-item" href="objects.php">Объекты данных</a></li>
              <li><a class="dropdown-item" href="events.php">Мероприятия</a></li>
            </ul>
          </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Выход</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <?php 
      echo "<div class='container'><form method='post'><div id = 'inputContainer' class='form-group w-50  mx-auto m-4'>
        <div class='form-group'><input type='text' class='form-control' name='name' placeholder='Название' value='". $name . "'>
        <span class='help-block'>". $name_err . "</span>
      </div>";
      $buttonNameString = "Изменить форму";
      if (!filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
        $buttonNameString = "Добавить форму";
        echo "<div class='form-group'>Форма содержит информацию о человеке: <input type='checkbox' id = 'isHuman' name='isHuman' onclick = 'showWarning()'/></div>
      <p id='isHumanText' style='display:none'>К данной форме будут добавлены реквизиты 'ФИО', 'Дата рождения', 'Описание'.</p>";
      }
      echo "<br><b>Реквизиты:</b><br><br>";
      foreach ($rec_arr as $key => $value) {
        if (strcmp($value, "ФИО") != 0 && strcmp($value, "Дата рождения") != 0 && strcmp($value, "Описание") != 0) {
          $num = $key + 1;
        echo "<div class = 'form-group'><div class='input-group'><input type='text' class='form-control' name = 'rec" . $num . "' placeholder = 'Реквизит " . $num . "' value = '" . $value . "'><span class='input-group-btn'><button id = 'del" . $num . "' type='button' class='btn btn-danger'><i class='fa fa-trash'></i></button></span></div></div>";
        }
        
      }
      echo "</div></div><div class='form-group'><a id = 'addRec' href='#'>Добавить реквизит</a></div><input type='submit' class='btn btn-primary' value='" . $buttonNameString . "' name='addButton'>
      </form>";
    ?>
    <script src="js/bootstrap.min.js"></script>
  </body>

  </html>