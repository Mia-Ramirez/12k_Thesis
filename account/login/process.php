<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['signIn'])) {
            session_start();
            include('../../utils/common_fx_and_const.php'); // getBaseURL

            $_SESSION['BASE_URL'] = getBaseURL();

            $doc_root = $_SERVER['DOCUMENT_ROOT'];
            if (strpos($_SESSION['BASE_URL'], "pharmanest") != false){
                $doc_root .= "/pharmanest";
            };
            
            $_SESSION['DOC_ROOT'] = $doc_root;
            include($doc_root.'/utils/connect.php');
            
            $email_or_username = mysqli_real_escape_string($conn, $_POST['email_or_username']);
            $password = mysqli_real_escape_string($conn, $_POST['password']);
        
            // Query to check email and password in the database
            $sqlCheck = "SELECT email, role, id, password, username, is_active FROM user WHERE email=\"$email_or_username\" OR username=\"$email_or_username\"";
            $result = mysqli_query($conn, $sqlCheck);
            
            if ($result->num_rows < 1){
                // If email is not yet registered it will display an error message in Login Form
                $_SESSION["login_error"] = "Email is not yet registered!";
                header("Location:index.php");
                exit;
            };

            if ($result->num_rows > 0) {
                $row = mysqli_fetch_array($result);
                $hashedPasswordFromDb = $row["password"]; // Fetch password from your database
                
                if ($row['is_active'] == 0){
                    $_SESSION["login_error"] = "Your Account is disabled/inactive, please contact the Admin";
                    $_SESSION["email_username"] = $email_or_username;
                    header("Location:index.php");

                } else if (password_verify($password, $hashedPasswordFromDb)) {
                    $role = $row['role'];

                    // $_SESSION['user_email'] = $row['email'];
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_name'] = $row['username'];

                    // list($first_name, $creationDate) = explode("_", $row['username']);
                    if ($row['username'] != 'admin'){
                        list($_SESSION['user_first_name'], $_SESSION['user_last_name'], $date_created) = explode("_", $row['username']);
                    } else {
                        $_SESSION['user_first_name'] = $row['username'];
                    };
                    
                    if ($role == 'customer'){
                        header("Location: ../../customer/index.php");
                    } else if (strpos($role, "admin") !== false) {
                        header("Location: ../../admin/index.php");
                    } else {
                        header("Location: ../../pharmacist/index.php");
                    };
                    
                } else {
                    $_SESSION["login_error"] = "Incorrect Password";
                    $_SESSION["email_username"] = $email_or_username;
                    header("Location:index.php");
                };
                exit;
            };
        };
    };
?>