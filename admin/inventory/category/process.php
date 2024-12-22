<!DOCTYPE html>
<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        session_start();
        $categoryName = $_POST['category_name'];
        include('../../../utils/connect.php');
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $id = $_POST['id'];

            $sqlCheck="SELECT id FROM category WHERE name='$categoryName' AND id!=$id";
            $result=$conn->query($sqlCheck);
            
            if ($result->num_rows != 0){
                $_SESSION["message_string"] = "This Category Name already exists !";
                $_SESSION["message_class"] = "danger";
                header("Location:index.php");
                exit;
            };

            // Edit category
            
            $sql = "UPDATE category SET name = '$categoryName' WHERE id = $id";
            $message_string = "Category successfully updated!";

        } else {

            $sqlCheck="SELECT id FROM category WHERE name='$categoryName'";
            $result=$conn->query($sqlCheck);
            
            if ($result->num_rows != 0){
                $_SESSION["message_string"] = "This Category Name already exists !";
                $_SESSION["message_class"] = "danger";
                header("Location:index.php");
                exit;
            };

            // Add new category
            $sql = "INSERT INTO category (name) VALUES ('$categoryName')";
            $message_string = "Category successfully added!";
        }

        if(!mysqli_query($conn,$sql)){
            die("Something went wrong");
        };

        $_SESSION["message_string"] = $message_string;
        $_SESSION["message_class"] = "success";
        header("Location:index.php");
    };

?>