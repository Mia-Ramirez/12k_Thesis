<?php
    session_start();
    $base_url = $_SESSION["BASE_URL"];
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $base_url;?>assets/styles/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="../../styles.css">
        <link rel="stylesheet" type="text/css" href="styles.css">
        <script src="<?php echo $base_url;?>assets/scripts/common_fx.js"></script>
        <?php include '../../components/title.php'; ?>
    </head>

    <body>
        <?php include '../../components/unauth_redirection.php'; ?>
        
        <?php include '../../components/side_nav.php'; ?>
        
        <?php
            $current_page_title = "list of products";
            include '../../components/top_nav.php';
        ?>

        <?php
            include('../../../utils/connect.php');
            $sqlGetProducts = "SELECT * FROM product";
            $filter_str = "";
            $query = NULL;
            if (isset($_GET['query'])){
                $query = $_GET['query'];
                $filter_str .= " WHERE name LIKE '%$query%'";
            };

            $sqlGetProducts .= $filter_str." ORDER BY id DESC";
            $result = mysqli_query($conn,$sqlGetProducts);
        ?>

        <div class="search">
            <form method="GET" action="">
                <input type="text" value="<?php echo $query; ?>" name="query" placeholder="Search anything...">
                <button class="btns" type="submit">Search</button>
                <button type="button" class="btns" onclick="redirectToPage('add')">Add Product</button>  
            </form>
        </div>

        <div class="table">
            <?php
                if (isset($_SESSION["message_string"])) {
                    ?>
                        <div class="alert alert-<?php echo $_SESSION["message_class"] ?>">
                            <?php 
                            echo $_SESSION["message_string"];
                            ?>
                        </div>
                    <?php
                    unset($_SESSION["message_string"]);
                    unset($_SESSION["message_class"]);
                }
            ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Rack Location</th>
                        <th>Current Number of Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    while($data = mysqli_fetch_array($result)){
                    ?>
                    <tr>
                        
                        <td><?php echo $data["name"];?></td>
                        <td>₱<?php echo $data["price"];?></td>
                        <td><?php echo $data["rack_location"];?></td>
                        <td><?php echo $data["current_quantity"];?></td>
                        <td>
                            <a href="./edit/index.php?product_id=<?php echo $data["id"]; ?>"><i class="button-icon fas fa-pen-to-square" title="Edit"></i></a>
                            <a href="../stock/add/index.php?product_id=<?php echo $data["id"]; ?>"><i class="button-icon fas fa-plus" title="Stock In"></i></a>
                            <a href="../stock/batch/index.php?product_id=<?php echo $data["id"]; ?>"><i class="button-icon fas fa-minus" title="Stock Out"></i></a>
                            <a href="../history/index.php?product_id=<?php echo $data["id"]; ?>"><i class="button-icon fas fa-clock-rotate-left" title="View History"></i></a>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>

            </table>
        </div>

        <script>
            function redirectToPage(page) {
                window.location.href = './'+page+'/index.php';
            };

            window.onload = function() {
                setActivePage("nav_inventory");
            };
        </script>
    </body>
</html>