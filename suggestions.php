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
	$sql = "delete from dataobjects_suggested where deleted = 1";
	if ($stmt = oci_parse($link, $sql)) {
	if (!oci_execute($stmt)) {
		echo "Произошла непредвиденная ошибка";
	}
  oci_free_statement($stmt);
}
$sql = "delete from recordvalues_suggested where deleted = 1";
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
    <title>Запросы на изменение информации</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/suggestions.js?2"></script>
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
        <a class="navbar-brand" href="suggestions.php">Запросы на изменение информации</a>
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
    <table id="suggestions_table" class="table table-bordered table-hover">
      <?php
        require_once "config.php";
        $sql = "select idsuggested, dataobject, u.login as createdby, TO_CHAR(d.creationdate, 'DD.MM.YYYY HH24:MI:SS') as creationdate, do.name as name, do.branch as branch from dataobjects_suggested d join users u on u.iduser = d.createdby left join dataobjects do on do.iddataobject = d.dataobject left join users u on u.iduser = d.createdby where d.deleted = '0' order by d.creationdate";
        $stmt = oci_parse($link, $sql);
        oci_define_by_name($stmt, 'IDSUGGESTED', $id);
        oci_define_by_name($stmt, 'NAME', $name);
        oci_define_by_name($stmt, 'CREATEDBY', $creator);
        oci_define_by_name($stmt, 'CREATIONDATE', $creationdate);
        oci_define_by_name($stmt, 'BRANCH', $branch);
        if (oci_execute($stmt)) {
            if (oci_fetch($stmt)) {
              $is_service_in = false;
              $is_district_in = false;
              $serv_sql = "select * from branches_services where branch = :p1 and service = :p2 and deleted = '0'";
              $serv_stmt = oci_parse($link, $serv_sql);
              oci_bind_by_name($serv_stmt, ':p1', $branch);
              oci_bind_by_name($serv_stmt, ':p2', $_SESSION["service"]);
              if (oci_execute($serv_stmt)) {
                if (oci_fetch($serv_stmt)) {
                  $is_service_in = true;
                }
              }
              else {
                echo "Произошла непредвиденная ошибка";
              }
              oci_free_statement($serv_stmt);
              $dist_sql = "select * from branches_districts where branch = :p1 and district = :p2 and deleted = '0'";
              $dist_stmt = oci_parse($link, $dist_sql);
              oci_bind_by_name($dist_stmt, ':p1', $branch);
              oci_bind_by_name($dist_stmt, ':p2', $_SESSION["district"]);
              if (oci_execute($dist_stmt)) {
                if (oci_fetch($dist_stmt)) {
                  $is_district_in = true;
                }
              }
              else {
                echo "Произошла непредвиденная ошибка";
              }
              oci_free_statement($dist_stmt);
            echo "<thead>".
        "<tr class ='active'>".
        "<th>Идентификатор</th>".
        "<th>Изменяемый объект</th>".
        "<th>Создавший пользователь</th>".
        "<th>Дата создания</th>".
        "<th>Действия</th>".
        "</tr>".
        "</thead>".
        "<tbody>".
        "<tr>".
        "<td class='id'>". $id . "</td>".
        "<td>" . $name . "</td>".
        "<td>" . $creator . "</td>".
        "<td>" . $creationdate  . "</td>".
        "<td><button type='button' class='btn btn-primary'><i class='far fa-eye'></i></button>    ";
        if ($_SESSION["role"] == 2 || ($is_service_in && $is_district_in)){
          echo "<button type='button' class='btn btn-success'><i class='fas fa-check'></i></button>  ".
        "<button type='button' class='btn btn-danger'><i class='fas fa-times'></i></button></td>";
        }
        echo "</tr>";
        while (oci_fetch($stmt)) {
          $is_service_in = false;
              $is_district_in = false;
              $serv_sql = "select * from branches_services where branch = :p1 and service = :p2 and deleted = '0'";
              $serv_stmt = oci_parse($link, $serv_sql);
              oci_bind_by_name($serv_stmt, ':p1', $branch);
              oci_bind_by_name($serv_stmt, ':p2', $_SESSION["service"]);
              if (oci_execute($serv_stmt)) {
                if (oci_fetch($serv_stmt)) {
                  $is_service_in = true;
                }
              }
              else {
                echo "Произошла непредвиденная ошибка";
              }
              oci_free_statement($serv_stmt);
              $dist_sql = "select * from branches_districts where branch = :p1 and district = :p2 and deleted = '0'";
              $dist_stmt = oci_parse($link, $dist_sql);
              oci_bind_by_name($dist_stmt, ':p1', $branch);
              oci_bind_by_name($dist_stmt, ':p2', $_SESSION["district"]);
              if (oci_execute($dist_stmt)) {
                if (oci_fetch($dist_stmt)) {
                  $is_district_in = true;
                }
              }
              else {
                echo "Произошла непредвиденная ошибка";
              }
              oci_free_statement($dist_stmt);
            echo "<tr>".
        "<td class='id'>". $id . "</td>".
        "<td>" . $name . "</td>".
        "<td>". $creator . "</td>".
        "<td>" . $creationdate . "</td>".
        "<td><button type='button' class='btn btn-primary'><i class='far fa-eye'></i></button>    ";
        if ($_SESSION["role"] == 2 || ($is_service_in && $is_district_in)){
          echo "<button type='button' class='btn btn-success'><i class='fas fa-check'></i></button>  ".
        "<button type='button' class='btn btn-danger'><i class='fas fa-times'></i></button></td>";
        }
        echo "</tr>";
        }
        echo "</tbody>".
        "</table>";
        }
        else {
            echo "<tbody>".
        "<tr>".
        "<td>На данный момент не создано ни одного запроса</td>".
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