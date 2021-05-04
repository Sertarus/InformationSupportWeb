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
    <title><?php echo $_GET["name"];?></title>
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
        <a class="navbar-brand" href="branches.php">Назад</a>
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
  require_once "config.php";
  $sql = "select b.name, d.name as datatype, br.name as higherbranch, b.createdby, TO_CHAR(b.creationdate, 'DD.MM.YYYY HH24:MI:SS') as creationdate, b.changedby, TO_CHAR(b.changeddate, 'DD.MM.YYYY HH24:MI:SS') as changeddate, u.login as createdbyname, us.login as changedbyname from branches b left join users u on u.iduser = b.createdby left join users us on us.iduser = b.changedby left join datatypes d on d.iddatatype = b.datatype left join branches br on br.idbranch = b.higherbranch where b.name = :p1 and b.deleted = '0'";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["name"]);
    oci_define_by_name($stmt, 'NAME', $name);
    oci_define_by_name($stmt, 'DATATYPE', $datatype);
    oci_define_by_name($stmt, 'HIGHERBRANCH', $higherbranch);
    oci_define_by_name($stmt, 'CREATEDBYNAME', $createdby);
    oci_define_by_name($stmt, 'CREATIONDATE', $creationdate);
    oci_define_by_name($stmt, 'CHANGEDBYNAME', $changedby);
    oci_define_by_name($stmt, 'CHANGEDDATE', $changeddate);
    if (oci_execute($stmt)) {
      oci_fetch($stmt);
      if (is_null($higherbranch)) {
          $branch_string = "-";
        }
        else {
          $branch_string = $higherbranch;
        }
      echo "<div class='parent'><ul>".
       "<li><b>Название: </b>" . $name. "</li>" . 
       "<li><b>Находится в ветке: </b>" . $branch_string. "</li>" .
       "<li><b>Привязанная форма заполнения: </b>" . $datatype. "</li>" . 
      "<li><b>Создавший пользователь: </b>" . $createdby. "</li>" .
      "<li><b>Дата создания: </b>" . $creationdate . "</li>";
      if (!is_null($changedby) && !is_null($changeddate)) {
        echo "<li><b>Последний изменивший пользователь: </b>" . $changedby. "</li>" .
        "<li><b>Дата последнего изменения: </b>" . $changeddate . "</li>";
      }
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);
  }
  $sql = "select s.name as service from branches_services bs left join services s on s.idservice = bs.service where bs.deleted = '0' and branch in (select idbranch from branches where deleted = '0' and name =:p1) order by s.name";
  echo "<br><li>Привязанные службы:</li>";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["name"]);
    oci_define_by_name($stmt, 'SERVICE', $service);
    if (oci_execute($stmt)) {
      $counter = 1;
      while (oci_fetch($stmt)) {
        echo "<li><b>Служба ". $counter . ": </b>" . $service . "</li>";
        $counter++;
      }
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);
  }
  $sql = "select d.name as district from branches_districts bd left join districts d on d.iddistrict = bd.district where bd.deleted = '0' and branch in (select idbranch from branches where deleted = '0' and name =:p1) order by d.name";
  echo "<br><li>Привязанные Районы:</li>";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["name"]);
    oci_define_by_name($stmt, 'DISTRICT', $district);
    if (oci_execute($stmt)) {
      $counter = 1;
      while (oci_fetch($stmt)) {
        echo "<li><b>Район ". $counter . ": </b>" . $district . "</li>";
        $counter++;
      }
      echo "</ul></div>";
    }
    else {
      echo "Произошла непредвиденная ошибка";
    }
    oci_free_statement($stmt);
  }
  oci_close($link);
  ?>
  </html>