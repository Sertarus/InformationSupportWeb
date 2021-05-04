<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}
if (isset($_POST['addButton'])) {
        	session_start();
        	header("location: services_create_edit.php?isEdit=0");
        }
if (isset($_POST['deleteButton'])) {
	require_once "config.php";
	$sql = "delete from services where deleted = 1";
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
    <title>Службы</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/services.js?2"></script>
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
        <a class="navbar-brand" href="services.php">Службы</a>
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
                <?php if ($_SESSION["role"] == 2) echo "<input type='submit' class='btn btn-outline-primary' value='Добавить службу' style='float: right;' name='addButton'><input type='submit' class='btn btn-outline-danger' value='Удалить объекты с пометкой на удаление' style='float: left;' name='deleteButton'>"; ?>
            </form>
    <table id="services_table" class="table table-bordered table-hover">
      <?php
        require_once "config.php";
        $sql = "select name, s.createdby, TO_CHAR(s.creationdate, 'DD.MM.YYYY HH24:MI:SS') as creationdate, u.login as creator_login from services s join users u on u.iduser = s.createdby where s.deleted = '0'";
        $stmt = oci_parse($link, $sql);
        oci_define_by_name($stmt, 'NAME', $name);
        oci_define_by_name($stmt, 'CREATOR_LOGIN', $creator);
        oci_define_by_name($stmt, 'CREATIONDATE', $creationdate);
        if (oci_execute($stmt)) {
            if (oci_fetch($stmt)) {
            echo "<thead>".
        "<tr class ='active'>".
        "<th>Название</th>".
        "<th>Создавший пользователь</th>".
        "<th>Дата создания</th>".
        "<th>Действия</th>".
        "</tr>".
        "</thead>".
        "<tbody>".
        "<tr>".
        "<td class='name'>". $name . "</td>".
        "<td>". $creator . "</td>".
        "<td>" . $creationdate  . "</td>".
        "<td><button type='button' class='btn btn-primary'><i class='far fa-eye'></i></button>		";
        if ($_SESSION["role"] == 2) {
        	echo "<button type='button' class='btn btn-success'><i class='fas fa-edit'></i></button>	".
        	"<button type='button' class='btn btn-danger'><i class='fa fa-trash'></i></button></td>";
        }
        echo "</tr>";
        while (oci_fetch($stmt)) {
            echo "<tr>".
        "<td class='name'>". $name . "</td>".
        "<td>". $creator . "</td>".
        "<td>" . $creationdate . "</td>".
        "<td><button type='button' class='btn btn-primary'><i class='far fa-eye'></i></button>		";
        if ($_SESSION["role"] == 2) {
        	echo "<button type='button' class='btn btn-success'><i class='fas fa-edit'></i></button>	".
        	"<button type='button' class='btn btn-danger'><i class='fa fa-trash'></i></button></td>";
        }
        echo "</tr>";
        }
        echo "</tbody>".
        "</table>";
        }
        else {
            echo "<tbody>".
        "<tr>".
        "<td>На данный момент не создано ни одной службы</td>".
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