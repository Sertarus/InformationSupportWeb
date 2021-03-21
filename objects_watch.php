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
        <a class="navbar-brand" href="objects.php">Назад</a>
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
    <script src="js/bootstrap.min.js"></script>
  </body>
  <?php
  require_once "config.php";
  $sql = "select d.iddataobject, d.image, d.name, d.branch as branchid, b.name as branch, d.createdby, TO_CHAR(d.creationdate, 'DD.MM.YYYY HH24:MI:SS') as creationdate, d.changedby, TO_CHAR(d.changeddate, 'DD.MM.YYYY HH24:MI:SS') as changeddate, u.login as createdbyname, us.login as changedbyname from dataobjects d left join users u on u.iduser = d.createdby left join users us on us.iduser = d.changedby left join branches b on b.idbranch = d.branch where d.name = :p1 and d.deleted = '0'";
  $dataobject_id = "";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["name"]);
    oci_define_by_name($stmt, 'IDDATAOBJECT', $id);
    oci_define_by_name($stmt, 'NAME', $name);
    oci_define_by_name($stmt, 'IMAGE', $image);
    oci_define_by_name($stmt, 'BRANCH', $branch);
    oci_define_by_name($stmt, 'BRANCHID', $branch_id);
    oci_define_by_name($stmt, 'CREATEDBYNAME', $createdby);
    oci_define_by_name($stmt, 'CREATIONDATE', $creationdate);
    oci_define_by_name($stmt, 'CHANGEDBYNAME', $changedby);
    oci_define_by_name($stmt, 'CHANGEDDATE', $changeddate);
    if (oci_execute($stmt)) {
      oci_fetch($stmt);
      $dataobject_id = $id;
      if (!is_null($image)) {
        echo "<div class='row m-2'><div class='col-sm-auto m-2'>";
        echo '<img src="data:image/jpeg;base64,'.base64_encode($image -> load()).'" width ="300" height="300"/>';
        echo "</div>";
      }
      echo "<div class = 'col'>";
      echo "<div class='parent'><ul>".
       "<li><b>Название: </b>" . $name. "</li>" . 
       "<li><b>Находится в ветке: </b>" . $branch. "</li>" .
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
  $datatype_id = "";
  $sql = "select datatype from branches where deleted = '0' and idbranch = :p1";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $branch_id);
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
  echo "<br><li>Форма объекта:</li>";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $datatype_id);
    oci_define_by_name($stmt, 'RECORDTYPE', $recordtype);
    oci_define_by_name($stmt, 'NAME', $recordtype_name);
    if (oci_execute($stmt)) {
      while (oci_fetch($stmt)) {
        $rec_sql = "select value from recordvalues where recordtype = :p1 and dataobject = :p2 and deleted = '0'";
        if ($rec_stmt = oci_parse($link, $rec_sql)) {
          oci_bind_by_name($rec_stmt, ':p1', $recordtype);
          oci_bind_by_name($rec_stmt, ':p2', $dataobject_id);
          oci_define_by_name($rec_stmt, 'VALUE', $value);
          if (oci_execute($rec_stmt)) {
            if (oci_fetch($rec_stmt)) {
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
  oci_close($link);
  ?>
  </html>