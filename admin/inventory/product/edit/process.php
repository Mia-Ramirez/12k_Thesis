<!DOCTYPE html>
<?php
    $maxFileSize = 5 * 1024 * 1024;  // 5MB in bytes

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        session_start();
        $doc_root = $_SESSION["DOC_ROOT"];
        if (!isset($_SESSION["product_id"])){
            header("Location:../index.php");
            exit;
        };

        if (isset($_POST["action"])) {
            include($doc_root.'/utils/connect.php');
            include($doc_root.'/utils/common_fx_and_const.php'); // getBaseURL
            if ($_POST['action'] === 'update_product') {
                $product_id = $_SESSION['product_id'];
                
                $category_names = mysqli_real_escape_string($conn, $_POST["category_names"]);
                $product_name = mysqli_real_escape_string($conn, $_POST["product_name"]);
                // $price = mysqli_real_escape_string($conn, $_POST["price"]);
                $valid_discounts = mysqli_real_escape_string($conn, $_POST["valid_discounts"]);
                $required_prescription = mysqli_real_escape_string($conn, $_POST["required_prescription"]);
                $rack_location = mysqli_real_escape_string($conn, $_POST["rack_location"]);
                $maintaining_quantity = mysqli_real_escape_string($conn, $_POST["maintaining_quantity"]);

                $imagePath = $_SESSION['photo_url'];
                
                $category_names = str_replace(", ", ",", $category_names);
                $category_names = "'".str_replace(",", "','", $category_names)."'";
                $getCategoryIDs="SELECT id FROM category WHERE name IN ($category_names)";
                $categoryIDs_result=$conn->query($getCategoryIDs);
                $category_ids = "";
                while($data = mysqli_fetch_array($categoryIDs_result)){
                    if ($category_ids == ""){
                        $category_ids = $data['id'];
                    } else {
                        $category_ids .= ",".$data['id'];
                    };
                };

                if ($required_prescription == "Yes"){
                    $required_prescription = 1;
                } else {
                    $required_prescription = 0;
                };

                // Input Validation for Name
                $checkProduct="SELECT id FROM product WHERE name='$product_name' AND id!=$product_id";
                $product_result=$conn->query($checkProduct);

                if ($product_result->num_rows != 0){
                    $_SESSION["message_string"] = "This Product Name already exists !";
                    $_SESSION["message_class"] = "danger";
                    header("Location:index.php?product_id=".$product_id);
                    exit;
                };

                // Input Validation for Photo

                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    // Get image details
                    $imageTmpName = $_FILES['image']['tmp_name'];
                    $imageName = $_FILES['image']['name'];
                    $imageSize = $_FILES['image']['size'];
                    $imageType = $_FILES['image']['type'];
            
                    // Define allowed image types (optional)
                    $allowedTypes = ['image/jpeg','image/png'];
                    
                    // Validate image type
                    if ($imageSize > $maxFileSize) {
                        $_SESSION["message_string"] = "File size exceeds the maximum limit of 5MB.";
                        $_SESSION["message_class"] = "danger";
                        header("Location:index.php?product_id=".$product_id);
                        exit;
                    };
                    
                    if (in_array($imageType, $allowedTypes)) {
                        // Generate a unique name for the image to prevent overwriting
                        $newImageName = uniqid() . '-' . str_replace([" "," "], "_", $imageName);
                        $uploadDir = '../../../../assets/photos/';  // The folder where the image will be stored
                        
                        // Check if the upload directory exists, if not create it
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        };

                        $imagePath = getBaseURL() . str_replace("../", "", $uploadDir) . $newImageName;
                        $old_file_path = str_replace(getBaseURL(), "../../../../", $_SESSION['photo_url']);
                        
                        if ($_SESSION['photo_url'] != $imagePath) {
                            if (file_exists($old_file_path)) {
                                // Delete the old file
                                if (unlink($old_file_path)) {
                                    error_log("File (".$old_file_path.") deleted.");
                                } else {
                                    error_log("Failed to delete the file.");
                                };
                                
                                // Move the uploaded file to the desired location
                                if (!move_uploaded_file($imageTmpName, $uploadDir . $newImageName)) {
                                    $_SESSION["message_string"] = "Error uploading image.";
                                    $_SESSION["message_class"] = "danger";
                                    header("Location:index.php?product_id=".$product_id);
                                    exit;
                                };

                            } else {
                                error_log("File (".$old_file_path.") does not exist.");
                            };
                        };

                    } else {
                        $_SESSION["message_string"] = "Invalid image type. Only JPG, and PNG are allowed.";
                        $_SESSION["message_class"] = "danger";
                        header("Location:index.php?product_id=".$product_id);
                        exit;
                    };

                };
                
                // $sqlUpdateProduct = "UPDATE product SET name='$product_name', price='$price', applicable_discounts='$valid_discounts', prescription_is_required='$required_prescription', photo='$imagePath', rack_location='$rack_location', maintaining_quantity='$maintaining_quantity' WHERE id=$product_id";
                $sqlUpdateProduct = "UPDATE product SET name='$product_name', applicable_discounts='$valid_discounts', prescription_is_required='$required_prescription', photo='$imagePath', rack_location='$rack_location', maintaining_quantity='$maintaining_quantity' WHERE id=$product_id";
                if(!mysqli_query($conn,$sqlUpdateProduct)){
                    die("Something went wrong");
                };
                
                $sqlUpdateProductCategory = "UPDATE product_categories SET category_ids='$category_ids' WHERE product_id=$product_id";
                if(!mysqli_query($conn,$sqlUpdateProductCategory)){
                    die("Something went wrong");
                };

                $_SESSION["message_string"] = "Product details updated successfully!";
                $_SESSION["message_class"] = "info";
                header("Location:index.php?product_id=".$product_id);
                exit;
            };
        };
    };
?>