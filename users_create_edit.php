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
$pass = "";
$pass_err = "";
$full_name = "";
$full_name_err = "";
$email = "";
$email_err = "";
$phone_number = "";
$phone_number_err = "";
$role = "";
$service = "";
$district = "";
$device = "";
$device_err = "";
$blocked = 0;
if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
{
    $name = $_GET["name"];
    require_once "config.php";
    $sql = "select password, fullname, email, phonenumber, role, service, district, device, blocked from users where login = :p1 and deleted = '0'";
    if ($stmt = oci_parse($link, $sql))
    {
        oci_bind_by_name($stmt, ':p1', $name);
        oci_define_by_name($stmt, 'PASSWORD', $current_pass);
        oci_define_by_name($stmt, 'FULLNAME', $current_fullname);
        oci_define_by_name($stmt, 'EMAIL', $current_email);
        oci_define_by_name($stmt, 'PHONENUMBER', $current_number);
        oci_define_by_name($stmt, 'ROLE', $current_role);
        oci_define_by_name($stmt, 'SERVICE', $current_service);
        oci_define_by_name($stmt, 'DISTRICT', $current_district);
        oci_define_by_name($stmt, 'DEVICE', $current_device);
        oci_define_by_name($stmt, 'BLOCKED', $current_blocked);
        if (oci_execute($stmt))
        {
            oci_fetch($stmt);
            $pass = $current_pass;
            $full_name = $current_fullname;
            $email = $current_email;
            $phone_number = $current_number;
            $role = $current_role;
            $service = $current_service;
            $district = $current_district;
            $device = $current_device;
            $blocked = $current_blocked;
        }
        else
        {
            echo "Произошла непредвиденная ошибка";
        }
        oci_free_statement($stmt);
    }
}

if (isset($_POST['addButton']))
{
    $name = $_POST['name'];
    $pass = $_POST['pass'];
    $full_name = $_POST['fullname'];
    $email = $_POST['email'];
    $phone_number = $_POST['phonenumber'];
    $role = $_POST['roles'];
    if ($_SESSION["role"] == 1)
    {
        $service = $_SESSION["service"];
        $district = $_SESSION["district"];
    }
    else
    {
        $service = $_POST["services"];
        $district = $_POST["districts"];
    }
    $device = $_POST["devices"];
    $blocked = $_POST["blocked"];
    if (is_null($blocked)) {
      $blocked = 0;
    }
    else {
      $blocked = 1;
    }
    if (mb_strlen($_POST['name']) >= 1 && mb_strlen($_POST['name']) <= 20 || filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
    {
      if (mb_strlen($_POST['pass']) >= 1 && mb_strlen($_POST['pass']) <= 20)
        {
          if (mb_strlen($_POST['fullname']) >= 1 && mb_strlen($_POST['fullname']) <= 60)
            {
              if (mb_strlen($_POST['email']) >= 1 && mb_strlen($_POST['email']) <= 20)
                {
                  if (mb_strlen($_POST['phonenumber']) >= 1 && mb_strlen($_POST['phonenumber']) <= 16)
                    {
                      if (!is_null($device)) {
                      require_once "config.php";
                      $sql = "select * from users where login = :p1 and deleted = '0'";
                        if ($stmt = oci_parse($link, $sql))
                        {
                            oci_bind_by_name($stmt, ':p1', $name);
                            if (oci_execute($stmt))
                            {
                                if (oci_fetch($stmt))
                                {
                                    $name_err = "Пользователь с таким именем уже существует";
                                    oci_free_statement($stmt);
                                }
                                else
                                {
                                    if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
                                    {
                                        $sql = "update users set password = :p1, fullname = :p2, email = :p3, phonenumber = :p4, role = :p5, service = :p6, district = :p7, device = :p8, changedby = :p9, changeddate = SYSTIMESTAMP, blocked = :p10 where login = :p11";
                                        oci_free_statement($stmt);
                                        if ($stmt = oci_parse($link, $sql))
                                        {
                                            oci_bind_by_name($stmt, ':p1', $pass);
                                            oci_bind_by_name($stmt, ':p2', $full_name);
                                            oci_bind_by_name($stmt, ':p3', $email);
                                            oci_bind_by_name($stmt, ':p4', $phone_number);
                                            oci_bind_by_name($stmt, ':p5', $role);
                                            oci_bind_by_name($stmt, ':p6', $service);
                                            oci_bind_by_name($stmt, ':p7', $district);
                                            oci_bind_by_name($stmt, ':p8', $device);
                                            oci_bind_by_name($stmt, ':p9', $_SESSION["iduser"]);
                                            oci_bind_by_name($stmt, ':p10', $blocked);
                                            oci_bind_by_name($stmt, ':p11', $_GET["name"]);
                                            if (!oci_execute($stmt))
                                            {
                                                echo "Произошла непредвиденная ошибка";
                                            }
                                            oci_free_statement($stmt);
                                        }
                                    }
                                    else
                                    {
                                        $sql = "insert into users (login, password, fullname, role, email, phonenumber, service, district,device, createdby, creationdate, blocked) values (:p1, :p2, :p3, :p4, :p5, :p6, :p7, :p8, :p9, :p10, SYSTIMESTAMP, '0')";
                                        if ($stmt = oci_parse($link, $sql))
                                        {
                                            oci_bind_by_name($stmt, ':p1', $name);
                                            oci_bind_by_name($stmt, ':p2', $pass);
                                            oci_bind_by_name($stmt, ':p3', $full_name);
                                            oci_bind_by_name($stmt, ':p5', $email);
                                            oci_bind_by_name($stmt, ':p6', $phone_number);
                                            oci_bind_by_name($stmt, ':p4', $role);
                                            oci_bind_by_name($stmt, ':p7', $service);
                                            oci_bind_by_name($stmt, ':p8', $district);
                                            oci_bind_by_name($stmt, ':p9', $device);
                                            oci_bind_by_name($stmt, ':p10', $_SESSION["iduser"]);
                                            if (!oci_execute($stmt))
                                            {
                                                echo "Произошла непредвиденная ошибка";
                                            }
                                            oci_free_statement($stmt);
                                        }
                                    }
                                    oci_close($link);
                                    header("location: users.php");
                                }
                            }
                            else
                            {
                                echo "Произошла непредвиденная ошибка";
                            }
                    }
                  }
                  else {
                    $device_err = "  В системе нет свободных устройств";
                  }
                }
                else {
                      $phone_number_err = "Длина номера телефона должна составлять от 1 до 16 символов";
                    }
            }
            else {
                  $email_err = "Длина электронной почты должна составлять от 1 до 20 символов";
                }
        }
        else {
              $fullname_err = "Длина ФИО должна составлять от 1 до 60 символов";
            }
    }
  else {
          $pass_err = "Длина пароля должна составлять от 1 до 20 символов";
        }
  }
  else {
          $name_err = "Длина логина должна составлять от 1 до 20 символов";
    }
}

?>
 <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Пользователи</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <link rel="stylesheet" href="css/inputForms.css">
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
        <a class="navbar-brand" href="users.php">Назад</a>
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
    <?php
        echo "<div class='container'><form method='post'><div class='form-group w-25  mx-auto m-4'>";
        if (!filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
        {
            echo "<div class='form-group'><input type='text' class='form-control' name='name' placeholder='Имя пользователя' value='" . $name . "'>
        <span class='help-block'>" . $name_err . "</span></div>";
        }

        echo "<div class='form-group'><input type='password' class='form-control' name='pass' placeholder='Пароль' value='" . $pass . "'>
        <span class='help-block'>" . $pass_err . "</span></div>
        <div class='form-group'><input type='text' class='form-control' name='fullname' placeholder='ФИО' value='" . $full_name . "'>
        <span class='help-block'>" . $fullname_err . "</span></div>
        <div class='form-group'><input type='text' class='form-control' name='email' placeholder='Электронная почта' value='" . $email . "'>
        <span class='help-block'>" . $email_err . "</span></div>
        <div class='form-group'><input type='text' class='form-control' name='phonenumber' placeholder='Номер телефона' value='" . $phone_number . "'>
        <span class='help-block'>" . $phone_number_err . "</span></div>
        <div class='form-group'><select id='roles' name='roles'>
          <option value='0' ";
        if ($role == 0) echo "selected";
        echo ">Пользователь</option>
          <option value='1' ";
        if ($role == 1) echo "selected";
        echo ">Локальный администратор</option>";
        if ($_SESSION["role"] == 2)
        {
            echo "<option value='2' ";
            if ($role == 2) echo "selected";
            echo ">Администратор</option>";
        }
        echo "</select></div>";
        require_once "config.php";
        if ($_SESSION["role"] == 2)
        {
            echo "<div class='form-group'><select id='services' name='services'>";
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
                        if ($service == $id)
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
            echo "<div class='form-group'><select id='districts' name='districts'>";
            $sql = "select iddistrict, name from districts where deleted = '0'";
            if ($stmt = oci_parse($link, $sql))
            {
                oci_define_by_name($stmt, 'IDDISTRICT', $id);
                oci_define_by_name($stmt, 'NAME', $drop_district);
                if (oci_execute($stmt))
                {
                    while (oci_fetch($stmt))
                    {
                        $selected = "";
                        if ($district == $id)
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
            echo "</select></div>";
        }
        echo "<div class='form-group'><select id='devices' name='devices'>";
        $sql = "select iddevice, name from devices d join users u on u.iduser = d.createdby where (iddevice not in (select device from users where deleted = '0') or iddevice = :p1) and d.deleted = '0'";
        if ($_SESSION["role"] == 1) {
            $sql = $sql . " and u.service = :p2 and u.district = :p3";
        }
        if ($stmt = oci_parse($link, $sql))
        {
            oci_bind_by_name($stmt, ':p1', $device);
            if ($_SESSION["role"] == 1) {
            oci_bind_by_name($stmt, ':p2', $_SESSION["service"]);
            oci_bind_by_name($stmt, ':p3', $_SESSION["district"]);
        }
            
            oci_define_by_name($stmt, 'IDDEVICE', $id);
            oci_define_by_name($stmt, 'NAME', $drop_device);
            if (oci_execute($stmt))
            {
                while (oci_fetch($stmt))
                {
                    $selected = "";
                    if ($device == $id)
                    {
                        $selected = "selected";
                    }
                    echo "<option value='" . $id . "' " . $selected . ">" . $drop_device . "</option>";
                }
            }
            else
            {
                echo "Произошла непредвиденная ошибка";
            }
            oci_free_statement($stmt);
        }
        echo "</select><span class='help-block'>" . $device_err . "</span></div>";
        if ($blocked == 1) {
          $checked_string = "checked = 'checked'";
        }
        if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
          echo "<div class='form-group'>Пользователь заблокирован: <input type='checkbox' name='blocked' " . $checked_string . "/></div>";
        }
        $button_name = "Создать пользователя";
        if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN)) {
          $button_name = "Изменить пользователя";
        }
        echo "</div></div><input type='submit' class='btn btn-primary' value='" . $button_name . "' name='addButton'></form>";
        oci_close($link);
?>
    <script src="js/bootstrap.min.js"></script>
  </body>

  </html>
