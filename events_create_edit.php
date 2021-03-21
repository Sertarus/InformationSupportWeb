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
$datetime_start = "";
$datetime_end = "";
$description = "";
$services = array();
$districts = array();
if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
  $name = $_GET["name"];
  require_once "config.php";
  $sql = "select idevent, TO_CHAR(timestart, 'DD.MM.YYYY HH24:MI:SS') as timestart, TO_CHAR(timeend, 'DD.MM.YYYY HH24:MI:SS') as timeend, description from events where deleted = '0' and name = :p1";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $name);
    oci_define_by_name($stmt, 'IDEVENT', $id);
    oci_define_by_name($stmt, 'TIMESTART', $current_timestart);
    oci_define_by_name($stmt, 'TIMEEND', $current_timeend);
    oci_define_by_name($stmt, 'DESCRIPTION', $current_description);
    if (oci_execute($stmt)) {
      if (oci_fetch($stmt)) {
        $timestart_arr = explode(" ", $current_timestart);
        $date_arr = explode(".", $timestart_arr[0]);
        $time_arr = explode(":", $timestart_arr[1]);
        $datetime_start = $date_arr[2] . "-" . $date_arr[1] . "-" . $date_arr[0] . "T" . $time_arr[0] . ":" . $time_arr[1];
        $timeend_arr = explode(" ", $current_timeend);
        $date_arr = explode(".", $timeend_arr[0]);
        $time_arr = explode(":", $timeend_arr[1]);
        $datetime_end = $date_arr[2] . "-" . $date_arr[1] . "-" . $date_arr[0] . "T" . $time_arr[0] . ":" . $time_arr[1];
        $description = $current_description;
        $serv_sql = "select service from events_services where event = :p1 and deleted = '0'";
        if ($serv_stmt = oci_parse($link, $serv_sql)) {
          oci_bind_by_name($serv_stmt, ':p1', $id);
          oci_define_by_name($serv_stmt, 'SERVICE', $current_service);
          if (oci_execute($serv_stmt)) {
            while (oci_fetch($serv_stmt)) {
              $services[] = $current_service;
            } 
          }
          else {
            echo "Произошла непредвиденная ошибка";
          }
          oci_free_statement($serv_stmt);
        }
        $dist_sql = "select district from events_districts where event = :p1 and deleted = '0'";
        if ($dist_stmt = oci_parse($link, $dist_sql)) {
          oci_bind_by_name($dist_stmt, ':p1', $id);
          oci_define_by_name($dist_stmt, 'DISTRICT', $current_district);
          if (oci_execute($dist_stmt)) {
            while (oci_fetch($dist_stmt)) {
              $districts[] = $current_district;
            }
          }
          else {
            echo "Произошла непредвиденная ошибка";
          }
          oci_free_statement($dist_stmt);
        }
      }
    } 
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);   
  }
}

if (isset($_POST['addButton'])) {
          $name = $_POST['name'];
          $datetime_start = $_POST['timestart'];
          $datetime_end = $_POST['timeend'];
          $description = $_POST['description'];
          $services = $_POST['services'];
          $districts = $_POST['districts'];
          if (mb_strlen($name) >= 1 && mb_strlen($name) <= 20) {
            if (mb_strlen($datetime_start) > 0) {
              if (mb_strlen($datetime_end) > 0) {
                if (mb_strlen($description) >= 1 && mb_strlen($description) <= 200) {
                  if (!empty($services)) {
                    if (!empty($districts)) {
                      require_once "config.php";
                      if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
                    {
                        $sql = "select * from events where name = :p1 and name != :p2 and deleted = '0'";
                    }
                    else
                    {
                        $sql = "select * from events where name = :p1 and deleted = '0'";
                    }
                      if ($stmt = oci_parse($link, $sql)) {
                        oci_bind_by_name($stmt, ':p1', $name);
                        if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
                          oci_bind_by_name($stmt, ':p2', $_GET['name']);
                        }
                        if (oci_execute($stmt)) {
                          if (!oci_fetch($stmt)) {
                            $database_timestart = str_replace("T", " ", $datetime_start) . ":00.000000000";
                            $database_timeend = str_replace("T", " ", $datetime_end) . ":00.000000000";
                            if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
                              $event_id = "";
                              $sql = "select idevent from events where deleted = '0' and name = :p1";
                              oci_free_statement($stmt);
                              if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $_GET['name']);
                                oci_define_by_name($stmt, 'IDEVENT', $id);
                                if (oci_execute($stmt)) {
                                  if (oci_fetch($stmt)) {
                                    $event_id = $id;
                                  }
                                }
                                else {
                                  echo "Произошла непредвиденная ошибка";
                                }
                              }
                              $sql = "update events set name = :p1, description = :p2, timestart = TO_TIMESTAMP(:p3, 'YYYY-MM-DD HH24:MI:SS:FF'), timeend = TO_TIMESTAMP(:p4, 'YYYY-MM-DD HH24:MI:SS:FF'), changedby = :p5, changeddate = SYSTIMESTAMP where idevent = :p6";
                              if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $name);
                                oci_bind_by_name($stmt, ':p2', $description);
                                oci_bind_by_name($stmt, ':p3', $database_timestart);
                                oci_bind_by_name($stmt, ':p4', $database_timeend);
                                oci_bind_by_name($stmt, ':p5', $_SESSION['iduser']);
                                oci_bind_by_name($stmt, ':p6', $event_id);
                                if (!oci_execute($stmt)) {
                                  echo "Произошла непредвиденная ошибка";
                                }
                                oci_free_statement($stmt);
                              }
                              $sql = "update events_services set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = '1' where event = :p2 and ";
                              $counter = 1;
                              foreach ($services as $key => $value) {
                                if ($counter == 1) {
                                  $sql = $sql . "service != " . $value;
                                }
                                else {
                                  $sql = $sql . " and service != " . $value;
                                }
                                $counter++;
                              }
                              if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $_SESSION['iduser']);
                                oci_bind_by_name($stmt, ':p2', $event_id);
                                if (!oci_execute($stmt)) {
                                  echo "Произошла непредвиденная ошибка";
                                }
                                oci_free_statement($stmt);
                              }
                              $sql = "update events_districts set changedby = :p1, changeddate = SYSTIMESTAMP, deleted = '1' where event = :p2 and ";
                              $counter = 1;
                              foreach ($districts as $key => $value) {
                                if ($counter == 1) {
                                  $sql = $sql . "district != " . $value;
                                }
                                else {
                                  $sql = $sql . " and district != " . $value;
                                }
                                $counter++;
                              }
                              if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $_SESSION['iduser']);
                                oci_bind_by_name($stmt, ':p2', $event_id);
                                if (!oci_execute($stmt)) {
                                  echo "Произошла непредвиденная ошибка";
                                }
                                oci_free_statement($stmt);
                              }
                              foreach ($services as $key => $value) {
                                $sql = "select * from events_services where event = :p1 and service = :p2 and deleted = '0'";
                                if ($stmt = oci_parse($link, $sql)) {
                                  oci_bind_by_name($stmt, ':p1', $event_id);
                                  oci_bind_by_name($stmt, ':p2', $value);
                                  if (oci_execute($stmt)) {
                                    if (!oci_fetch($stmt)) {
                                      $insert_serv_sql = "insert into events_services (event, service, createdby, creationdate) values (:p1, :p2, :p3, SYSTIMESTAMP)";
                                      if ($insert_stmt = oci_parse($link, $insert_serv_sql)) {
                                        oci_bind_by_name($insert_stmt, ':p1', $event_id);
                                        oci_bind_by_name($insert_stmt, ':p2', $value);
                                        oci_bind_by_name($insert_stmt, ':p3', $_SESSION['iduser']);
                                        if (!oci_execute($insert_stmt)) {
                                          echo "Произошла непредвиденная ошибка";
                                        }
                                        oci_free_statement($insert_stmt);
                                      }
                                    }
                                  }
                                  else {
                                    echo "Произошла непредвиденная ошибка";
                                  }
                                  oci_free_statement($stmt);
                                }
                              }
                              foreach ($districts as $key => $value) {
                                $sql = "select * from events_districts where event = :p1 and district = :p2 and deleted = '0'";
                                if ($stmt = oci_parse($link, $sql)) {
                                  oci_bind_by_name($stmt, ':p1', $event_id);
                                  oci_bind_by_name($stmt, ':p2', $value);
                                  if (oci_execute($stmt)) {
                                    if (!oci_fetch($stmt)) {
                                      $insert_dist_sql = "insert into events_districts (event, district, createdby, creationdate) values (:p1, :p2, :p3, SYSTIMESTAMP)";
                                      if ($insert_stmt = oci_parse($link, $insert_dist_sql)) {
                                        oci_bind_by_name($insert_stmt, ':p1', $event_id);
                                        oci_bind_by_name($insert_stmt, ':p2', $value);
                                        oci_bind_by_name($insert_stmt, ':p3', $_SESSION['iduser']);
                                        if (!oci_execute($insert_stmt)) {
                                          echo "Произошла непредвиденная ошибка";
                                        }
                                        oci_free_statement($insert_stmt);
                                      }
                                    }
                                  }
                                  else {
                                    echo "Произошла непредвиденная ошибка";
                                  }
                                  oci_free_statement($stmt);
                                }
                              }
                            }
                            else {
                              $sql = "insert into events (name, description, timestart, timeend, createdby, creationdate) values (:p1, :p2, TO_TIMESTAMP(:p3, 'YYYY-MM-DD HH24:MI:SS:FF'), TO_TIMESTAMP(:p4, 'YYYY-MM-DD HH24:MI:SS:FF'), :p5, SYSTIMESTAMP)";
                              if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $name);
                                oci_bind_by_name($stmt, ':p2', $description);
                                oci_bind_by_name($stmt, ':p3', $database_timestart);
                                oci_bind_by_name($stmt, ':p4', $database_timeend);
                                oci_bind_by_name($stmt, ':p5', $_SESSION['iduser']);
                                if (!oci_execute($stmt)) {
                                  echo "Произошла непредвиденная ошибка";
                                }
                                oci_free_statement($stmt);
                              }
                              $event_id = "";
                              $sql = "select idevent from events where deleted = '0' and name = :p1";
                              if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $name);
                                oci_define_by_name($stmt, 'IDEVENT', $id);
                                if (oci_execute($stmt)) {
                                  if (oci_fetch($stmt)) {
                                    $event_id = $id;
                                  }
                                }
                                else {
                                  echo "Произошла непредвиденная ошибка";
                                }
                                oci_free_statement($stmt);
                              }
                              foreach ($services as $key => $value) {
                                $sql = "insert into events_services (event, service, createdby, creationdate) values (:p1, :p2, :p3, SYSTIMESTAMP)";
                                if ($insert_stmt = oci_parse($link, $sql)) {
                                  oci_bind_by_name($insert_stmt, ':p1', $event_id);
                                  oci_bind_by_name($insert_stmt, ':p2', $value);
                                  oci_bind_by_name($insert_stmt, ':p3', $_SESSION['iduser']);
                                  if (!oci_execute($insert_stmt)) {
                                    echo "Произошла непредвиденная ошибка";
                                  }
                                  oci_free_statement($insert_stmt);
                                }
                              }
                              foreach ($districts as $key => $value) {
                                $sql = "insert into events_districts (event, district, createdby, creationdate) values (:p1, :p2, :p3, SYSTIMESTAMP)";
                                if ($insert_stmt = oci_parse($link, $sql)) {
                                  oci_bind_by_name($insert_stmt, ':p1', $event_id);
                                  oci_bind_by_name($insert_stmt, ':p2', $value);
                                  oci_bind_by_name($insert_stmt, ':p3', $_SESSION['iduser']);
                                  if (!oci_execute($insert_stmt)) {
                                    echo "Произошла непредвиденная ошибка";
                                  }
                                  oci_free_statement($insert_stmt);
                                }
                              }
                            }
                            header("location: events.php");
                          }
                          else {
                            $name_err = "Мероприятие с таким названием уже существует";
                          }
                        }
                        else {
                          echo "Произошла непредвиденная ошибка";
                        }
                      }
                    }
                    else {
                      $name_err = "Не выбрано ни одного района";
                    }
                  }
                  else {
                    $name_err = "Не выбрано ни одной службы";
                  }
                }
                else {
                  $name_err = "Длина описания должна быть от 1 до 200 символов";
                }
              }
              else {
                $name_err = "Дата окончания не установлена";
              }
            } 
            else {
              $name_err = "Дата начала не установлена";
            }
          }
          else {
            $name_err = "Длина названия должна быть от 1 до 20 символов";
          }
        }
?>
 <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Мероприятия</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap-select.min.css">
    <script src="js/bootstrap-select.min.js"></script>
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
        <a class="navbar-brand" href="events.php">Назад</a>
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
    $additional_datetime_string = "onfocus=(this.type='datetime-local') onblur=(if (this.value == '') {this.type='text'})";
    $type = "text";
    if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN) || isset($_POST['addButton'])) {
      $additional_datetime_string = "";
      $type = "datetime-local";
    } 
      echo "<form method='post'><div class='form-group w-25 mx-auto m-4'>
      <div class='form-group'>
        <input type='text' class='form-control' name='name' placeholder='Название' value='". $name . "'>
        <span class='help-block'>". $name_err . "</span>
        </div>
        <div class='form-group'><input type='". $type . "' class='form-control' " . $additional_datetime_string . " name='timestart' placeholder='Время начала' value='". $datetime_start . "'></div>
        <div class='form-group'><input type='" . $type . "' class='form-control' " . $additional_datetime_string . " name='timeend' placeholder='Время окончания' value='". $datetime_end . "'></div>
        <div class='form-group'><textarea class='form-control' name='description' placeholder='Описание'>" . $description . "</textarea></div>";
        echo "<div id='txthint'><div class='form-group'><select id='services' name='services[]' class='selectpicker' title='Ничего не выбрано' multiple>";
        require_once "config.php";
        $sql = "select idservice, name from services where deleted = '0'";
            if ($stmt = oci_parse($link, $sql))
            {
                oci_define_by_name($stmt, 'IDSERVICE', $id);
                oci_define_by_name($stmt, 'NAME', $drop_service);
                if (oci_execute($stmt))
                {
                    while (oci_fetch($stmt))
                    {
                        $selected = "";
                        if (in_array($id, $services))
                        {
                            $selected = " selected";
                        }
                        echo "<option value='" . $id . "'" . $selected . ">" . $drop_service . "</option>";
                    }
                }
                else
                {
                    echo "Произошла непредвиденная ошибка";
                }
                oci_free_statement($stmt);
            }
            echo "</select></div>";

            echo "<div class='form-group'><select id='districts' name='districts[]' class='selectpicker' title='Ничего не выбрано' multiple>";
        $sql = "select iddistrict, name from districts where deleted = '0'";
            if ($stmt = oci_parse($link, $sql))
            {
                if (strcmp($branch, "") != 0) {
                    oci_bind_by_name($stmt, ':p1', $branch);
                }
                oci_define_by_name($stmt, 'IDDISTRICT', $id);
                oci_define_by_name($stmt, 'NAME', $drop_district);
                if (oci_execute($stmt))
                {
                    while (oci_fetch($stmt))
                    {
                        $selected = "";
                        if (in_array($id, $districts))
                        {
                            $selected = " selected";
                        }
                        echo "<option value='" . $id . "'" . $selected . ">" . $drop_district . "</option>";
                    }
                }
                else
                {
                    echo "Произошла непредвиденная ошибка";
                }
                oci_free_statement($stmt);
            }
            echo "</select></div></div>";
          
            $addButton_name = "Создать мероприятие";
           if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
            $addButton_name = "Изменить мероприятие";
           } 
      echo "</div>
                <input type='submit' class='btn btn-primary' value='" . $addButton_name . "' name='addButton'>
            </form>";
            oci_close($link);
    ?>
    <script src="js/bootstrap.min.js"></script>
  </body>

  </html>