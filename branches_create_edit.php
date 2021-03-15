<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: index.php");
    exit;
}
$name = "";
$name_err = "";
$form = "";
$branch = "";
$services = array();
$districts = array();
if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
{
    $name = $_GET["name"];
    require_once "config.php";
    $sql = "select d.name as datatype from branches b join datatypes d on d.iddatatype = b.datatype where b.deleted = '0' and b.name = :p1";
    if ($stmt = oci_parse($link, $sql)) {
        oci_bind_by_name($stmt, ':p1', $name);
        oci_define_by_name($stmt, 'DATATYPE', $current_form);
        if (oci_execute($stmt)) {
            if (oci_fetch($stmt)) {
                $form = $current_form;
            }
        }
        else {
            echo "Произошла непредвиденная ошибка";
        }
    }
    oci_free_statement($stmt);
}

if (isset($_POST['addButton']))
{
$name = $_POST['name'];
$form = $_POST['forms'];
$branch = $_POST['branches'];
$services = $_POST['services'];
$districts = $_POST['districts'];

if (strlen($name) >= 1 && strlen($name) <= 30) {
    if (!empty($services) || filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
        if (!empty($districts) || filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
            require_once "config.php";
            if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
                $sql = "select * from branches where name = :p1 and name != :p2 and deleted = '0'";
            }
            else {
                $sql = "select * from branches where name = :p1 and deleted = '0'";
            }
            if ($stmt = oci_parse($link, $sql)) {
                oci_bind_by_name($stmt, ':p1', $name);
                if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
                    oci_bind_by_name($stmt, ':p2', $_GET['name']);
                }
                if (oci_execute($stmt)) {
                    if (!oci_fetch($stmt)) {
                        if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
                            $sql = "update branches set name = :p1, datatype = :p2, changedby = :p3, changeddate = SYSTIMESTAMP where name = :p4";
                            if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $name);
                                oci_bind_by_name($stmt, ':p2', $form);
                                oci_bind_by_name($stmt, ':p3', $_SESSION['iduser']);
                                oci_bind_by_name($stmt, ':p4', $_GET['name']);
                                if (!oci_execute($stmt)) {
                                    echo "Произошла непредвиденная ошибка";
                                }
                            }
                        }
                        else {
                            $sql = "insert into branches (name, datatype, higherbranch, createdby, creationdate) values (:p1, :p2, :p3, :p4, SYSTIMESTAMP)";
                            if ($stmt = oci_parse($link, $sql)) {
                                oci_bind_by_name($stmt, ':p1', $name);
                                oci_bind_by_name($stmt, ':p2', $form);
                                if (strcmp($branch, "") == 0) {
                                    $branch_string = null;
                                }
                                else {
                                    $branch_string = $branch;
                                }
                                oci_bind_by_name($stmt, ':p3', $branch_string);
                                oci_bind_by_name($stmt, ':p4', $_SESSION['iduser']);
                                if (!oci_execute($stmt)) {
                                    echo "Произошла непредвиденная ошибка";
                                }
                                else {
                                    $sql = "select idbranch from branches where name = :p1 and deleted = '0'";
                                    if ($stmt = oci_parse($link, $sql)) {
                                        oci_bind_by_name($stmt, ':p1', $name);
                                        oci_define_by_name($stmt, 'IDBRANCH', $current_branch);
                                        if (oci_execute($stmt)) {
                                            if (oci_fetch($stmt)) {
                                                foreach ($services as $key => $value) {
                                                    $add_serv_sql = "insert into branches_services (branch, service, createdby, creationdate) values (:p1, :p2, :p3, SYSTIMESTAMP)";
                                                    if ($add_serv_stmt = oci_parse($link, $add_serv_sql)) {
                                                        oci_bind_by_name($add_serv_stmt, ':p1', $current_branch);
                                                        oci_bind_by_name($add_serv_stmt, ':p2', $value);
                                                        oci_bind_by_name($add_serv_stmt, ':p3', $_SESSION['iduser']);
                                                        if (!oci_execute($add_serv_stmt)) {
                                                            echo "Произошла непредвиденная ошибка";
                                                        }
                                                    }
                                                }
                                                foreach ($districts as $key => $value) {
                                                    $add_dist_sql = "insert into branches_districts (branch, district, createdby, creationdate) values (:p1, :p2, :p3, SYSTIMESTAMP)";
                                                    if ($add_dist_stmt = oci_parse($link, $add_dist_sql)) {
                                                        oci_bind_by_name($add_dist_stmt, ':p1', $current_branch);
                                                        oci_bind_by_name($add_dist_stmt, ':p2', $value);
                                                        oci_bind_by_name($add_dist_stmt, ':p3', $_SESSION['iduser']);
                                                        if (!oci_execute($add_dist_stmt)) {
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
                                }
                            }
                        }
                        header("location: branches.php");
                    }
                    else {
                        $name_err = "Ветка с таким именем уже существует";
                    }
                    oci_close($link);
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
    $name_err = "Длина названия должна быть от 1 до 30 символов";
}
}

?>
 <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Ветки</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <link rel="stylesheet" href="css/inputForms.css">
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
        <a class="navbar-brand" href="branches.php">Назад</a>
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
    <?php
        echo "<div class='container'><form method='post'><div class='form-group w-25  mx-auto m-4'>
        <div class='form-group'><input type='text' class='form-control' name='name' placeholder='Название' value='" . $name . "'>
        <span class='help-block'>" . $name_err . "</span></div>";
        
        require_once "config.php";
            echo "<div class='form-group'><select id='forms' name='forms'>";
            $sql = "select iddatatype, name from datatypes where deleted = '0'";
            if ($stmt = oci_parse($link, $sql))
            {
                oci_define_by_name($stmt, 'IDDATATYPE', $id);
                oci_define_by_name($stmt, 'NAME', $drop_form);
                if (oci_execute($stmt))
                {
                    while (oci_fetch($stmt))
                    {
                        $selected = "";
                        if ($form == $id)
                        {
                            $selected = " selected";
                        }
                        echo "<option value='" . $id . "'" . $selected . ">" . $drop_form . "</option>";
                    }
                }
                else
                {
                    echo "Произошла непредвиденная ошибка";
                }
            }
            echo "</select></div>";
            if (!filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
            echo "<div class='form-group'><select  id='branches' name='branches'>
            <option value = '' selected>-</option>";
            $sql = "select idbranch, name from branches where deleted = '0'";
            if ($stmt = oci_parse($link, $sql))
            {
                oci_define_by_name($stmt, 'IDBRANCH', $id);
                oci_define_by_name($stmt, 'NAME', $drop_branch);
                if (oci_execute($stmt))
                {
                    while (oci_fetch($stmt))
                    {
                        $selected = "";
                        if ($branch == $id)
                        {
                            $selected = " selected";
                        }
                        echo "<option value='" . $id . "'" . $selected . ">" . $drop_branch . "</option>";
                    }
                }
                else
                {
                    echo "Произошла непредвиденная ошибка";
                }
            }
            echo "</select></div>";

        echo "<div id='txthint'><div class='form-group'><select id='services' name='services[]' class='selectpicker' title='Ничего не выбрано' multiple>";
        $additional_sql = "";
        if (strcmp($branch, "") != 0) {
            $additional_sql = " and idservice in (select service from branches_services where deleted = '0' and branch = :p1)";
        }
        $sql = "select idservice, name from services where deleted = '0'" . $additional_sql;
            if ($stmt = oci_parse($link, $sql))
            {
                if (strcmp($branch, "") != 0) {
                    oci_bind_by_name($stmt, ':p1', $branch);
                }
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
            }
            echo "</select></div>";

            echo "<div class='form-group'><select id='districts' name='districts[]' class='selectpicker' title='Ничего не выбрано' multiple>";
            $additional_sql = "";
        if (strcmp($branch, "") != 0) {
            $additional_sql = " and iddistrict in (select district from branches_districts where deleted = '0' and branch = :p1)";
        }
        $sql = "select iddistrict, name from districts where deleted = '0'" . $additional_sql;
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
            }
            echo "</select></div></div>";
}
            
        $button_name = "Создать ветку";
        if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
          $button_name = "Изменить ветку";
        }
        echo "</div></div><input type='submit' class='btn btn-primary' value='" . $button_name . "' name='addButton'></form>";
?>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/branches_create.js"></script>
  </body>

  </html>
