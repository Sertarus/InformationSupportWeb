<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}
?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title><?php echo $_GET["id"];?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel='stylesheet' href='css/watch.css' />
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
        <a class="navbar-brand" href="suggestions.php">Назад</a>
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
              <li><a class="dropdown-item" href="suggestions.php">Запросы на изменение данных</a></li>
            </ul>
          </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Выход</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <script src="js/bootstrap.min.js"></script>
  </body>
  <?php
  echo "<div class='parent'><ul>".
  "<br><li>Предлагаемые изменения:</li></div>";
  require_once "config.php";
  $sql = "select d.image, dataobject, do.branch as branch from dataobjects_suggested d left join dataobjects do on do.iddataobject = d.dataobject where d.idsuggested = :p1 and d.deleted = '0'";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["id"]);
    oci_define_by_name($stmt, 'IMAGE', $image);
    oci_define_by_name($stmt, 'BRANCH', $branch);
    oci_define_by_name($stmt, 'DATAOBJECT', $dataobject);
    if (oci_execute($stmt)) {
      oci_fetch($stmt);
      if (!is_null($image)) {
        echo "<div class='row m-2'><div class='col-sm-auto m-2'>";
        echo '<img src="data:image/jpeg;base64,'.base64_encode($image -> load()).'" width ="300" height="300"/>';
        echo "</div>";
      }
      echo "<div class = 'col'>".
      "<div class='parent'><ul>";
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);
  }
  $datatype_id = "";
  $sql = "select datatype from branches where deleted = '0' and idbranch = :p1";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $branch);
    oci_define_by_name($stmt, 'DATATYPE', $datatype);
    if (oci_execute($stmt)) {
      if (oci_fetch($stmt)) {
        $datatype_id = $datatype;
      }
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);
  }
  $sql = "select recordtype, r.name, dataorder, dr.deleted from datatypes_recordtypes dr join recordtypes r on r.idrecordtype = dr.recordtype where datatype = :p1 and dr.deleted = '0' order by dataorder";
  $records = array();
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $datatype_id);
    oci_define_by_name($stmt, 'RECORDTYPE', $recordtype);
    oci_define_by_name($stmt, 'NAME', $recordtype_name);
    if (oci_execute($stmt)) {
      while (oci_fetch($stmt)) {
        $rec_sql = "select value from recordvalues_suggested where recordtype = :p1 and suggesteddataobject = :p2 and deleted = '0'";
        if ($rec_stmt = oci_parse($link, $rec_sql)) {
          oci_bind_by_name($rec_stmt, ':p1', $recordtype);
          oci_bind_by_name($rec_stmt, ':p2', $_GET["id"]);
          oci_define_by_name($rec_stmt, 'VALUE', $value);
          if (oci_execute($rec_stmt)) {
            while (oci_fetch($rec_stmt)) {
              $records[$recordtype] = $recordtype_name;
              echo "<li><b>" . $recordtype_name . ": </b>" . $value . "</li>";
            }
          }
          else {
            echo "Произошла непредвиденная ошибка";
          }
          oci_free_statement($rec_stmt);
        }
      }
      echo "</ul></div></div></div>";
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);
  }
  echo "<div class='parent'><ul>".
  "<br><li>Текущее состояние:</li></div>";
  $sql = "select d.image from dataobjects d where d.iddataobject = :p1 and d.deleted = '0'";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $dataobject);
    oci_define_by_name($stmt, 'IMAGE', $old_image);
    if (oci_execute($stmt)) {
      oci_fetch($stmt);
      if (!is_null($old_image) && !is_null($image)) {
        echo "<div class='row m-2'><div class='col-sm-auto m-2'>";
        echo '<img src="data:image/jpeg;base64,'.base64_encode($old_image -> load()).'" width ="300" height="300"/>';
        echo "</div>";
      }
      echo "<div class = 'col'>".
      "<div class='parent'><ul>";
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);
  }
  foreach ($records as $key => $value) {
    $rec_sql = "select value from recordvalues where recordtype = :p1 and dataobject = :p2 and deleted = '0'";
        if ($rec_stmt = oci_parse($link, $rec_sql)) {
          oci_bind_by_name($rec_stmt, ':p1', $key);
          oci_bind_by_name($rec_stmt, ':p2', $dataobject);
          oci_define_by_name($rec_stmt, 'VALUE', $value2);
          if (oci_execute($rec_stmt)) {
            while (oci_fetch($rec_stmt)) {
              echo "<li><b>" . $value . ": </b>" . $value2 . "</li>";
            }
          }
          else {
            echo "Произошла непредвиденная ошибка";
          }
          oci_free_statement($rec_stmt);
        }
  }
  oci_close($link);
  ?>
  </html>