<!DOCTYPE html>
<?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        session_start();
        $doc_root = $_SESSION["DOC_ROOT"];
        if (!isset($_SESSION["customer_id"])){
            header("Location:../list/index.php");
            exit;
        };

        if (isset($_POST["action"])) {
            if ($_POST['action'] === 'yes') {
                include($doc_root.'/utils/connect.php');
                $user_id = $_SESSION['customer_user_id'];
                $customer_id = $_SESSION['customer_id'];

                if ($user_id != ''){
                    $sqlUpdateUser = "UPDATE user SET is_active='0' WHERE id='$user_id'";
                    if(!mysqli_query($conn,$sqlUpdateUser)){
                        die("Something went wrong");
                    };
                };

                $sqlUpdateCustomer = "UPDATE customer SET is_active='0' WHERE id='$customer_id'";
                if(!mysqli_query($conn,$sqlUpdateCustomer)){
                    die("Something went wrong");
                };
                $_SESSION["message_string"] = "Customer archived successfully!";
                $_SESSION["message_class"] = "info";
            };
            unset($_SESSION['customer_id']);
            unset($_SESSION['customer_user_id']);
            header("Location:../list/index.php");
            exit;
        };
    };
?>