<!DOCTYPE html>
<?php
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header("Location:index.php");
        exit;
    };
        
    if (!isset($_GET['medicine_id']) || !isset($_GET['action']) ) {
        header("Location:index.php");
        exit;
    };

    session_start();
    include('../../utils/connect.php');
    $medicine_id = $_GET['medicine_id'];
    $action = $_GET['action'];

    $user_id = $_SESSION['user_id'];

    $sqlCheckMedicine = "SELECT name FROM medicine WHERE id=$medicine_id"; 
    $result = mysqli_query($conn, $sqlCheckMedicine);
    
    if ($result->num_rows < 1){
        // If email is not yet registered it will display an error message in Login Form
        $_SESSION["message_string"] = "Medicine not found!";
        $_SESSION["message_class"] = "error";
        header("Location:index.php");
        exit;
    };

    $sqlGetCustomerCartID = "SELECT cc.id AS customer_cart_id, c.id AS customer_id FROM customer_cart cc RIGHT JOIN customer c ON cc.customer_id=c.id WHERE c.user_id=$user_id";
                
    $result = mysqli_query($conn,$sqlGetCustomerCartID);
    $row = mysqli_fetch_array($result);
    $customer_id = $row['customer_id'];
    
    if ($row['customer_cart_id']){
        $customer_cart_id = $row['customer_cart_id'];
    } else {
        $sqlInsertCustomerCart = "INSERT INTO customer_cart(customer_id, product_lines) VALUES ('$customer_id', NULL)";
        if(!mysqli_query($conn,$sqlInsertCustomerCart)){
            die("Something went wrong");
        };
        $customer_cart_id = mysqli_insert_id($conn);  
    };
    
    if ($action == "buy_now"){
        $for_checkout = 1;
    } else {
        $for_checkout = 0;
    };

    $sqlGetProductLine = "SELECT id, qty FROM product_line WHERE cart_id=$customer_cart_id AND medicine_id=$medicine_id";
    $result = mysqli_query($conn,$sqlGetProductLine);
    if ($result->num_rows != 0){
        $row = mysqli_fetch_array($result);
        $qty = $row['qty'] + 1;
        $line_id = $row['id'];
        $sqlUpdateProductLine = "UPDATE product_line SET qty='$qty', for_checkout='$for_checkout' WHERE id=$line_id";
        if(!mysqli_query($conn,$sqlUpdateProductLine)){
            die("Something went wrong");
        };

    } else {
        $sqlInsertProductLine = "INSERT INTO product_line(cart_id, medicine_id, qty, for_checkout) VALUES ('$customer_cart_id', '$medicine_id', '1', '$for_checkout')";
        if(!mysqli_query($conn,$sqlInsertProductLine)){
            die("Something went wrong");
        };
    };

    $row = mysqli_fetch_array($result);
    if ($action == "buy_now"){
        header("Location:../cart/checkout/index.php");
    } else {
        $_SESSION["message_string"] = "Medicine added to cart!";
        $_SESSION["message_class"] = "info";
        header("Location:index.php");
    };
    exit;
?>