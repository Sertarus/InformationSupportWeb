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
if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
  $name = $_GET["name"];
}

if (isset($_POST['addButton'])) {
          $name = $_POST['name'];
          if (strlen($_POST['name']) >= 1 && strlen($_POST['name']) <= 40) {
            require_once "config.php";
            $sql = "select * from services where name = :p1 and deleted = '0'";
            if ($stmt = oci_parse($link, $sql)) {
              oci_bind_by_name($stmt, ':p1', $name);
              if (oci_execute($stmt)) {
                if (oci_fetch($stmt)) {
                  $name_err = "Служба с таким именем уже существует";
                  oci_free_statement($stmt);
                  oci_close($link);
                }
                else {
                  if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
                    $sql = "update services set name = :p1, changedby = :p2, changeddate = SYSTIMESTAMP where name = :p3";
                    if ($stmt = oci_parse($link, $sql)) {
                      oci_bind_by_name($stmt, ':p1', $name);
                      oci_bind_by_name($stmt, ':p2', $_SESSION["iduser"]);
                      oci_bind_by_name($stmt, ':p3', $_GET["name"]);
                      if (!oci_execute($stmt)) {
                        echo "Произошла непредвиденная ошибка";
                      }
                      oci_free_statement($stmt);
                    }
                  }
                  else {
                    $sql = "insert into services (name, createdby, creationdate) values (:p1, :p2, SYSTIMESTAMP)";
                    if ($stmt = oci_parse($link, $sql)) {
                      oci_bind_by_name($stmt, ':p1', $name);
                      oci_bind_by_name($stmt, ':p2', $_SESSION["iduser"]);
                      if (!oci_execute($stmt)) {
                        echo "Произошла непредвиденная ошибка";
                      }
                      oci_free_statement($stmt);
                    }
                  }
                  oci_close($link);
                  header("location: services.php");
                }
              }
              else {
                echo "Произошла непредвиденная ошибка";
              }
            }
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
    <title>Службы</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <script src="js/jquery-3.6.0.min.js"></script>
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
        <a class="navbar-brand" href="services.php">Назад</a>
        <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarsExample01" aria-controls="navbarsExample01" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExample01">
          <ul class="navbar-nav me-auto mb-2">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="main_page.php">Список активных сотрудников</a>
            </li>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Изменение информации в системе</a>
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
    if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
      echo "<form method='post'><div class='form-group w-25 mx-auto m-4'>
        <input type='text' class='form-control' name='name' placeholder='Название' value='". $name . "'>
        <span class='help-block'>". $name_err . "</span>
      </div>

                <input type='submit' class='btn btn-primary' value='Изменить службу' name='addButton'>
            </form>";
    }
    else {
      echo "<form method='post'><div class='form-group w-25 mx-auto m-4'>
        <input type='text' class='form-control' name='name' placeholder='Название' value='". $name . "'>
        <span class='help-block'>" . $name_err . "</span>
      </div>

                <input type='submit' class='btn btn-primary' value='Создать службу' name='addButton'>
            </form>";
    }
    ?>
    <script src="js/bootstrap.min.js"></script>
  </body>

  </html>