<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

if (isset($_POST['deleteButton'])) {
  require_once "config.php";
  $sql = "delete from log_user_info where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
$sql = "delete from services where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from districts where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from devices where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from users where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from dataobjects where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from datatypes where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from recordtypes where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from recordvalues where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from branches where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from events where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from messages where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from dataobjects_suggested where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
  $sql = "delete from recordvalues_suggested where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
}
  oci_free_statement($stmt);
$sql = "delete from branches_districts where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
$sql = "delete from branches_services where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
$sql = "delete from datatypes_recordtypes where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
$sql = "delete from events_districts where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
$sql = "delete from events_services where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
$sql = "delete from old_images where deleted = 1";
  if ($stmt = oci_parse($link, $sql)) {
  if (!oci_execute($stmt)) {
    echo "Произошла непредвиденная ошибка";
  }
  oci_free_statement($stmt);
}
}
?>


  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Список активных пользователей</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
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
        <a class="navbar-brand" href="main_page.php">Список активных сотрудников</a>
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

    <form method="post">
                <?php if ($_SESSION["role"] == 2) echo "<input type='submit' class='btn btn-outline-danger' value='Удалить объекты с пометкой на удаление' style='float: left;' name='deleteButton'>"; ?>
            </form>

    <table class="table table-bordered table-hover">
      <?php
        require_once "config.php";
        if ($_SESSION["role"] == 1) {
            $sql = "SELECT distinct u.login, u.fullname, s.name as service_name, d.name as district_name, loginfo.creationdate from (select userid, TO_CHAR(max(log_user_info.creationdate), 'DD.MM.YYYY HH24:MI:SS') as creationdate from log_user_info group by userid) loginfo inner join log_user_info log on log.userid = loginfo.userid join users u on u.iduser =  loginfo.userid join services s on u.service = s.idservice join districts d on u.district = d.iddistrict where log.deleted = '0' and service = :p1 and district = :p2";
            $stmt = oci_parse($link, $sql);
            oci_bind_by_name($stmt, ':p1', $_SESSION["service"]);
            oci_bind_by_name($stmt, ':p2', $_SESSION["district"]);
        }
        if ($_SESSION["role"] == 2) {
            $sql = "SELECT distinct u.login, u.fullname, s.name as service_name, d.name as district_name, loginfo.creationdate from (select userid, TO_CHAR(max(log_user_info.creationdate), 'DD.MM.YYYY HH24:MI:SS') as creationdate from log_user_info group by userid) loginfo inner join log_user_info log on log.userid = loginfo.userid join users u on u.iduser =  loginfo.userid join services s on u.service = s.idservice join districts d on u.district = d.iddistrict where log.deleted = '0'";
            $stmt = oci_parse($link, $sql);
            oci_define_by_name($stmt, 'SERVICE_NAME', $service_name);
            oci_define_by_name($stmt, 'DISTRICT_NAME', $district_name);
        }
        oci_define_by_name($stmt, 'LOGIN', $login);
        oci_define_by_name($stmt, 'FULLNAME', $fullname);
        oci_define_by_name($stmt, 'CREATIONDATE', $creationdate);
        if (oci_execute($stmt)) {
            if (oci_fetch($stmt)) {
            echo "<thead>".
        "<tr class ='active'>".
        "<th>Имя пользователя</th>".
        "<th>ФИО</th>";
        if ($_SESSION["role"] == 2) {
            echo "<th>Служба</th>".
            "<th>Район</th>";
        }
        echo "<th>Дата входа</th>".
        "</tr>".
        "</thead>".
        "<tbody>".
        "<tr>".
        "<td>". $login . "</td>".
        "<td>". $fullname . "</td>";
        if ($_SESSION["role"] == 2) {
            echo "<td>" . $service_name . "</td>".
            "<td>" . $district_name . "</td>";
        }
        echo "<td>" . $creationdate  . "</td>".
        "</tr>";
        while (oci_fetch($stmt)) {
            $creation_date_explode = explode(".", $creationdate);
            echo "<tr>".
        "<td>". $login . "</td>".
        "<td>". $fullname . "</td>";
        if ($_SESSION["role"] == 2) {
            echo "<td>" . $service_name . "</td>".
            "<td>" . $district_name . "</td>";
        }
        echo "<td>" . $creationdate . "</td>".
        "</tr>";
        }
        echo "</tbody>".
        "</table>";
        }
        else {
            echo "<tbody>".
        "<tr>".
        "<td>В настоящее время активные пользователи в системе отсутствуют</td>".
        "</tr>".
        "</tbody>".
        "</table>";
        }
        }
        else {
            echo "Непредвиденная ошибка";
        }
        oci_free_statement($stmt);
        oci_close($link);
        ?>
    </table>
    <script src="js/bootstrap.min.js"></script>
  </body>

  </html>