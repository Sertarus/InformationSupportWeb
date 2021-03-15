<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: main_page.php");
    exit;
}
 
// Include config file
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["login"]))){
        $username_err = "Введите логин пользователя";
    } else{
        $username = trim($_POST["login"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Введите пароль пользователя";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
    	require_once "config.php";
        // Prepare a select statement
        $sql = "SELECT iduser, login, password, role, service, district FROM users WHERE login = :p1";
        
        if($stmt = oci_parse($link, $sql)){
            // Bind variables to the prepared statement as parameters
            oci_bind_by_name($stmt, ':p1', $username);
            oci_define_by_name($stmt, 'IDUSER', $iduser);
			oci_define_by_name($stmt, 'PASSWORD', $pass);
			oci_define_by_name($stmt, 'ROLE', $role);
			oci_define_by_name($stmt, 'SERVICE', $service);
			oci_define_by_name($stmt, 'DISTRICT', $district);
            // Attempt to execute the prepared statement
            if(oci_execute($stmt)){
                // Store result
                
                // Check if username exists, if yes then verify password
                if(oci_fetch($stmt)){             
                        if(strcmp($password, $pass) == 0 && $role != 0){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            $_SESSION["service"] = $service;
                            $_SESSION["district"] = $district;
                            $_SESSION["iduser"] = $iduser;                        
                            
                            // Redirect user to welcome page
                            header("location: main_page.php");
                        } else{
                            // Display an error message if password is not valid
                            $password_err = "Неправильные данные для входа";
                        }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "Неправильное имя пользователя";
                }
            } else{
                echo "Непредвиденная ошибка";
            }

            // Close statement
            oci_free_statement($stmt);
        }
        oci_close($link);
    }
    
  
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Bootstrap test</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/bootstrap.min.css" >
	<link rel='stylesheet' href='css/style.css' />

</head>
<body>
	<div class="wrapper fadeInDown">
		<div id="formContent">
    		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Имя пользователя</label>
                <input type="text" name="login" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Авторизоваться">
            </div>
        </form>
		</div>
	</div>
	<script src="js/bootstrap.min.js"></script>
</body>
</html>