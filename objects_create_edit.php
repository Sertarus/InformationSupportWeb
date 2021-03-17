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
$image = null;
$start_image_copy = null;
$branch = "";
$form_data = array();
$form_data_edit = array();
if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
{
    $name = $_GET["name"];
    require_once "config.php";
    $sql = "select branch, image from dataobjects d where d.deleted = '0' and d.name = :p1";
    if ($stmt = oci_parse($link, $sql))
    {
        oci_bind_by_name($stmt, ':p1', $name);
        oci_define_by_name($stmt, 'BRANCH', $current_branch);
        oci_define_by_name($stmt, 'IMAGE', $current_image);
        if (oci_execute($stmt))
        {
            if (oci_fetch($stmt))
            {
                $branch = $current_branch;
                $start_image_copy = $current_image;
            }
        }
        else
        {
            echo "Произошла непредвиденная ошибка";
        }
    }

    $sql = "select recordtype, r.name, dataorder, dr.deleted from datatypes_recordtypes dr join recordtypes r on r.idrecordtype = dr.recordtype where datatype in (select datatype from branches where idbranch = :p1 and deleted = '0') and dr.deleted = '0' order by dataorder";
    if ($stmt = oci_parse($link, $sql))
    {
        oci_bind_by_name($stmt, ':p1', $branch);
        oci_define_by_name($stmt, 'RECORDTYPE', $recordtype_id);
        oci_define_by_name($stmt, 'NAME', $req_name);
        if (oci_execute($stmt))
        {
            while (oci_fetch($stmt))
            {
                $req_val_sql = "select value from recordvalues where deleted = '0' and dataobject in (select iddataobject from dataobjects where name = :p1 and deleted = '0') and recordtype = :p2";
                if ($req_val_stmt = oci_parse($link, $req_val_sql))
                {
                    oci_bind_by_name($req_val_stmt, ':p1', $_GET["name"]);
                    oci_bind_by_name($req_val_stmt, ':p2', $recordtype_id);
                    oci_define_by_name($req_val_stmt, 'VALUE', $value);
                    if (oci_execute($req_val_stmt))
                    {
                        if (oci_fetch($req_val_stmt))
                        {
                            $form_data[$req_name] = $value;
                            $form_data_edit[$req_name] = $value;
                        }
                    }
                    else
                    {
                        echo "Произошла непредвиденная ошибка";
                    }
                }
            }
        }
        else
        {
            echo "Произошла непредвиденная ошибка";
        }
    }
    oci_free_statement($stmt);
}

if (isset($_POST['addButton']))
{
    $name = $_POST['name'];
    if (!filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
    {
        $branch = $_POST['branches'];
    }
    if ($_FILES['uploadfile']['size'] != 0)
    {
        $image = $_FILES['uploadfile']['tmp_name'];
    }
    foreach ($_POST as $key => $value)
    {
        if (strcmp($key, "name") != 0 && strcmp($key, "branches") != 0 && strcmp($key, "addButton") != 0)
        {
            $replaced_key = str_replace("_", " ", $key);
            $form_data[htmlspecialchars($replaced_key)] = htmlspecialchars($value);
        }
    }
    if (strlen($name) >= 1 && strlen($name) <= 30)
    {
        $allowed = array(
            "image/jpeg",
            "image/png"
        );
        if (is_null($image) || in_array($_FILES['uploadfile']['type'], $allowed))
        {
            if (!is_null($image))
            {
                $info = getimagesize($image);

                if ($info['mime'] == 'image/jpeg') {
                    $new_image = imagecreatefromjpeg($image);
                    imagejpeg($new_image, "tmpimage.jpg", 50);
                } 

                elseif ($info['mime'] == 'image/png') {
                    $new_image = imagecreatefrompng($image);
                    $width = imagesx($new_image);
$height = imagesy($new_image);
$output = imagecreatetruecolor($width, $height);
$white = imagecolorallocate($output,  255, 255, 255);
imagefilledrectangle($output, 0, 0, $width, $height, $white);
imagecopy($output, $new_image, 0, 0, 0, 0, $width, $height);
imagejpeg($output, "tmpimage.jpg", 50);
                }  
                
                $image = "tmpimage.jpg";
            }
            if (is_null($image) || filesize($image) <= 5000000)
            {
                $is_error_form = false;
                foreach ($_POST as $key => $value)
                {
                    if (strcmp($key, "name") != 0 && strcmp($key, "branches") != 0 && strcmp($key, "addButton") != 0)
                    {
                        if (strlen(htmlspecialchars($value)) < 1 && strlen(htmlspecialchars($value)) > 150)
                        {
                            $is_error_form = true;
                        }
                    }
                }
                if (!$is_error_form)
                {
                    require_once "config.php";
                    if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
                    {
                        $sql = "select * from dataobjects where name = :p1 and name != :p2 and deleted = '0'";
                    }
                    else
                    {
                        $sql = "select * from dataobjects where name = :p1 and deleted = '0'";
                    }
                    if ($stmt = oci_parse($link, $sql))
                    {
                        oci_bind_by_name($stmt, ':p1', $name);
                        if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
                        {
                            oci_bind_by_name($stmt, ':p2', $_GET['name']);
                        }
                        if (oci_execute($stmt))
                        {
                            if (!oci_fetch($stmt))
                            {
                                if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
                                {
                                    if (is_null($image)) {
                                        $sql = "update dataobjects set name = :p1, changedby = :p3, changeddate = SYSTIMESTAMP where name = :p4";
                                    }
                                    else {
                                        $sql = "update dataobjects set name = :p1, image = EMPTY_BLOB(), changedby = :p3, changeddate = SYSTIMESTAMP where name = :p4 returning image into :p2";
                                    }
                                    if ($stmt = oci_parse($link, $sql))
                                        
                                    {
                                        oci_bind_by_name($stmt, ':p1', $name);
                                        if (!is_null($image)) {
                                            $lob = oci_new_descriptor($link, OCI_D_LOB);
                                            oci_bind_by_name($stmt, ':p2', $lob, -1, OCI_B_BLOB);
                                            $image_input = file_get_contents("tmpimage.jpg");
                                        }
                                        
                                        oci_bind_by_name($stmt, ':p3', $_SESSION['iduser']);
                                        oci_bind_by_name($stmt, ':p4', $_GET['name']);
                                        if (!oci_execute($stmt, OCI_DEFAULT))
                                        {
                                            echo "Произошла непредвиденная ошибка";
                                        }
                                        if (!is_null($image)) {
                                            if (!$lob ->save($image_input)) {
                                            oci_rollback($link);
                                        }
                                        else {
                                            oci_commit($link);
                                        }
                                        $lob->free();
                                        }
                                    }
                                    foreach ($_POST as $key => $value)
                                    {
                                        if (strcmp($key, "name") != 0 && strcmp($key, "branches") != 0 && strcmp($key, "addButton") != 0)
                                        {
                                            if (strcmp(htmlspecialchars($value), $form_data_edit[$key]) != 0)
                                            {
                                                $sql = "update recordvalues set value = :p1, changedby = :p2, changeddate = SYSTIMESTAMP where deleted = '0' and recordtype in (select idrecordtype from recordtypes where name = :p3 and deleted = '0') and dataobject in (select iddataobject from dataobjects where deleted = '0' and name = :p4)";
                                                if ($stmt = oci_parse($link, $sql))
                                                {
                                                    oci_bind_by_name($stmt, ':p1', htmlspecialchars($value));
                                                    oci_bind_by_name($stmt, ':p2', $_SESSION['iduser']);
                                                    oci_bind_by_name($stmt, ':p3', $key);
                                                    oci_bind_by_name($stmt, ':p4', $name);
                                                    if (!oci_execute($stmt))
                                                    {
                                                        echo "Произошла непредвиденная ошибка";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    if (!is_null($image)) {
                                        $sql = "insert into dataobjects (name, branch, image, createdby, creationdate) values (:p1, :p2, EMPTY_BLOB(), :p4, SYSTIMESTAMP) returning image into :p3";
                                    }
                                    else {
                                        $sql = "insert into dataobjects (name, branch, image, createdby, creationdate) values (:p1, :p2, :p3, :p4, SYSTIMESTAMP)";
                                    }
                                    
                                    if ($stmt = oci_parse($link, $sql))
                                    {
                                        
                                        oci_bind_by_name($stmt, ':p1', $name);
                                        if (is_null($image))
                                        {
                                            $image_input = null;
                                            oci_bind_by_name($stmt, ':p3', $image_input);
                                        }
                                        else
                                        {
                                            $image_input = file_get_contents("tmpimage.jpg");
                                            $lob = oci_new_descriptor($link, OCI_D_LOB);
                                            oci_bind_by_name($stmt, ':p3', $lob, -1, OCI_B_BLOB);
                                        }
                                        oci_bind_by_name($stmt, ':p2', $branch);
                                        
                                        oci_bind_by_name($stmt, ':p4', $_SESSION['iduser']);
                                        if (!oci_execute($stmt, OCI_DEFAULT))
                                        {
                                            echo "Произошла непредвиденная ошибка";
                                        }
                                        if (!is_null($image)) {
                                            if (!$lob ->save($image_input)) {
                                            oci_rollback($link);
                                        }
                                        else {
                                            oci_commit($link);
                                        }
                                        $lob->free();   
                                        }
                                    }
                                    $dataobject_id = "";
                                    $data_sql = "select iddataobject from dataobjects where name = :p1 and deleted = '0'";
                                    if ($data_stmt = oci_parse($link, $data_sql))
                                    {
                                        oci_bind_by_name($data_stmt, ':p1', $name);
                                        oci_define_by_name($data_stmt, 'IDDATAOBJECT', $id);
                                        if (oci_execute($data_stmt))
                                        {
                                            if (oci_fetch($data_stmt))
                                            {
                                                $dataobject_id = $id;
                                            }
                                        }
                                        else
                                        {
                                            echo "Произошла непредвиденная ошибка";
                                        }
                                    }
                                    foreach ($form_data as $key => $value)
                                    {
                                        $recordtype_id = "";
                                        $record_sql = "select idrecordtype from recordtypes where name = :p1 and deleted = '0'";
                                        if ($record_stmt = oci_parse($link, $record_sql))
                                        {
                                            oci_bind_by_name($record_stmt, ':p1', $key);
                                            oci_define_by_name($record_stmt, 'IDRECORDTYPE', $id);
                                            if (oci_execute($record_stmt))
                                            {
                                                if (oci_fetch($record_stmt))
                                                {
                                                    $recordtype_id = $id;
                                                }
                                            }
                                            else
                                            {
                                                echo "Произошла непредвиденная ошибка";
                                            }
                                        }
                                        $sql = "insert into recordvalues (value, recordtype, dataobject, createdby, creationdate) values (:p1, :p2, :p3, :p4, SYSTIMESTAMP)";
                                        if ($stmt = oci_parse($link, $sql))
                                        {
                                            oci_bind_by_name($stmt, ':p1', $value);
                                            oci_bind_by_name($stmt, ':p2', $recordtype_id);
                                            oci_bind_by_name($stmt, ':p3', $dataobject_id);
                                            oci_bind_by_name($stmt, ':p4', $_SESSION['iduser']);
                                            if (!oci_execute($stmt))
                                            {
                                                echo "Произошла непредвиденная ошибка";
                                            }
                                        }
                                    }
                                }
                            }

                            else
                            {
                                "Объект данных с таким именем уже существует";
                            }
                            header("location: objects.php");
                        }
                        else
                        {
                            echo "Произошла непредвиденная ошибка";
                        }
                    }
                    oci_close($link);
                }
                else
                {
                    $name_err = "Длина значения каждого реквизита должна быть от 1 до 150 символов";
                }
                
            }
            else
            {
                $name_err = "Размер загружаемого изображения слишком большой";
            }
        }
        else
        {
            $name_err = "Загружаемые изображения должны иметь один из следующих форматов: jpeg, png";
        }
    }
    if (!is_null($image))
    {
        unlink("tmpimage.jpg");
    }
    else
    {
        $name_err = "Длина названия должна быть от 1 до 30 символов";
    }
}

?>
 <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Объекты данных</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.css">
    <link rel="stylesheet" href="css/inputForms.css">
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/objects_create.js?2"></script>
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
    <?php
echo "<div class='container'><form method='post' enctype='multipart/form-data'><div class='form-group w-50  mx-auto m-4'>
        <div class='form-group'><input type='text' class='form-control' name='name' placeholder='Название' value='" . $name . "'>
        <span class='help-block'>" . $name_err . "</span></div>";
echo

"<div class='form-group'><div class='row'><div class='col-sm-auto'><img id='preview'";
if (!is_null($start_image_copy))
{
    echo " src = 'data:image/jpeg;base64," . base64_encode($start_image_copy->load()) . "' ";
}

echo "width = '200' height = '200'/></div><div class='col align-self-center'>
<div class='col'><input type='file' id = 'input' name='uploadfile' value=''/></div></div></div></div>";

require_once "config.php";
if (!filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
{
    echo "Ветка для размещения:";
    echo "<div class='form-group'><select  id='branches' name='branches'>";
    $sql = "select idbranch, name from branches where deleted = '0'";
    if ($stmt = oci_parse($link, $sql))
    {
        oci_define_by_name($stmt, 'IDBRANCH', $id);
        oci_define_by_name($stmt, 'NAME', $drop_branch);
        if (oci_execute($stmt))
        {
            while (oci_fetch($stmt))
            {
                $is_service_in = false;
                $is_district_in = false;
                $serv_sql = "select * from branches_services where branch = :p1 and service = :p2 and deleted = '0'";
                $serv_stmt = oci_parse($link, $serv_sql);
                oci_bind_by_name($serv_stmt, ':p1', $id);
                oci_bind_by_name($serv_stmt, ':p2', $_SESSION["service"]);
                if (oci_execute($serv_stmt))
                {
                    if (oci_fetch($serv_stmt))
                    {
                        $is_service_in = true;
                    }
                }
                else
                {
                    echo "Произошла непредвиденная ошибка";
                }
                $dist_sql = "select * from branches_districts where branch = :p1 and district = :p2 and deleted = '0'";
                $dist_stmt = oci_parse($link, $dist_sql);
                oci_bind_by_name($dist_stmt, ':p1', $id);
                oci_bind_by_name($dist_stmt, ':p2', $_SESSION["district"]);
                if (oci_execute($dist_stmt))
                {
                    if (oci_fetch($dist_stmt))
                    {
                        $is_district_in = true;
                    }
                }
                else
                {
                    echo "Произошла непредвиденная ошибка";
                }
                if (($is_service_in && $is_district_in) || $_SESSION["role"] == 2)
                {
                    $selected = "";
                    if ($branch == $id)
                    {
                        $selected = " selected";
                    }
                    echo "<option value='" . $id . "'" . $selected . ">" . $drop_branch . "</option>";
                }
            }
        }
        else
        {
            echo "Произошла непредвиденная ошибка";
        }
    }
    echo "</select></div>";
}

echo "<div id='txthint'>";
if (!empty($form_data))
{
    echo "<div id='not_update'></div>";
    foreach ($form_data as $key => $value)
    {
        echo "<div class='form-group'><textarea class='form-control' name='" . $key . "' placeholder='" . $key . "'>" . htmlspecialchars($value) . "</textarea></div>";
    }
}
echo "</div>";
$button_name = "Создать объект";
if (filter_var($_GET["isEdit"], FILTER_VALIDATE_BOOLEAN))
{
    $button_name = "Изменить объект";
}
echo "</div></div><input type='submit' class='btn btn-primary' value='" . $button_name . "' name='addButton'></form>";
?>
    <script src="js/bootstrap.min.js"></script>
  </body>

  </html>
