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
    <style type="text/css">
      body {
        font: 20px sans-serif;
        text-align: center;
      }
    </style>
  </head>

  <body>
    <nav class="navbar navbar-dark bg-dark" aria-label="First navbar example">
      <div class="container-fluid">
        <a class="navbar-brand" href="users.php">Назад</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample01" aria-controls="navbarsExample01" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExample01">
          <ul class="navbar-nav me-auto mb-2">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="main_page.php">Список активных сотрудников</a>
            </li>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-bs-toggle="dropdown" aria-expanded="false">Изменение информации в системе</a>
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
  $sql = "select u.login, u.fullname, u.role, u.email, u.phonenumber, s.name as service, d.name as district, dev.name as device, u.blocked, u.createdby, TO_CHAR(u.creationdate, 'DD.MM.YYYY HH24:MI:SS') as creationdate, u.changedby, TO_CHAR(u.changeddate, 'DD.MM.YYYY HH24:MI:SS') as changeddate, us.login as createdbyname, use.login as changedbyname from users u left join users us on us.iduser = u.createdby left join users use on use.iduser = u.changedby join services s on s.idservice = u.service join districts d on d.iddistrict = u.district join devices dev on dev.iddevice = u.device where u.login = :p1";
  if ($stmt = oci_parse($link, $sql)) {
    oci_bind_by_name($stmt, ':p1', $_GET["name"]);
    oci_define_by_name($stmt, 'LOGIN', $name);
    oci_define_by_name($stmt, 'FULLNAME', $fullname);
    oci_define_by_name($stmt, 'ROLE', $role);
    oci_define_by_name($stmt, 'EMAIL', $email);
    oci_define_by_name($stmt, 'PHONENUMBER', $phone_number);
    oci_define_by_name($stmt, 'SERVICE', $service);
    oci_define_by_name($stmt, 'DISTRICT', $district);
    oci_define_by_name($stmt, 'DEVICE', $device);
    oci_define_by_name($stmt, 'BLOCKED', $blocked);
    oci_define_by_name($stmt, 'CREATEDBYNAME', $createdby);
    oci_define_by_name($stmt, 'CREATIONDATE', $creationdate);
    oci_define_by_name($stmt, 'CHANGEDBYNAME', $changedby);
    oci_define_by_name($stmt, 'CHANGEDDATE', $changeddate);
    if (oci_execute($stmt)) {
      oci_fetch($stmt);
      if ($role == 0) {
      $role_string = "Пользователь";
    }
    if ($role == 1) {
      $role_string = "Локальный администратор";
    }
    if ($role == 2) {
      $role_string = "Администратор";
    }
    if ($blocked == 0) {
      $blocked_string = "Не заблокирован";
    }
    else {
      $blocked_string = "Заблокирован";
    }
      echo "<div class='parent'><ul>".
       "<li><b>Имя пользователя: </b>" . $name. "</li>" .
        "<li><b>ФИО: </b>" . $fullname. "</li>" .
        "<li><b>Роль: </b>" . $role_string. "</li>" .
        "<li><b>Электронная почта: </b>" . $email. "</li>" .
        "<li><b>Номер телефона: </b>" . $phone_number. "</li>" .
        "<li><b>Служба: </b>" . $service. "</li>" .
        "<li><b>Район: </b>" . $district. "</li>" .
        "<li><b>Устройство: </b>" . $device. "</li>" .
        "<li><b>Статус блокировки: </b>" . $blocked_string . "</li>" .
      "<li><b>Создавший пользователь: </b>" . $createdby. "</li>" .
      "<li><b>Дата создания: </b>" . $creationdate . "</li>";
      if (!is_null($changedby) && !is_null($changeddate)) {
        echo "<li><b>Последний изменивший пользователь: </b>" . $changedby. "</li>" .
        "<li><b>Дата последнего изменения: </b>" . $changeddate . "</li>";
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