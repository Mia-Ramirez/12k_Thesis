<?php
    session_start();
    $base_url = $_SESSION["BASE_URL"];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PHARMANEST ESSENTIAL</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="../../styles.css">
        <script src="<?php echo $base_url;?>assets/scripts/common_fx.js"></script>
    </head>
    <body class="body">

        <?php include '../../components/unauth_redirection.php'; ?>

        <?php include '../../components/navbar.php'; ?>  

        <?php
            include('../../../utils/connect.php');
            
            $customer_id = $_SESSION['customer_id'];

            $sqlGetProductLines = "SELECT 
                                        m.name AS medicine_name,
                                        price,
                                        applicable_discounts,
                                        prescription_is_required,
                                        photo,
                                        qty,
                                        selected_discount,
                                        prescription_id
                                    FROM product_line pl
                                    INNER JOIN customer_cart cc ON pl.cart_id=cc.id
                                    INNER JOIN medicine m ON pl.medicine_id=m.id
                                    LEFT JOIN medicine_prescription mp ON pl.medicine_id=mp.medicine_id
                                    WHERE cc.customer_id=$customer_id AND pl.for_checkout=1 AND line_type='cart'
            ";
            
            $product_lines = mysqli_query($conn,$sqlGetProductLines);
            if ($product_lines->num_rows == 0){
                $_SESSION["message_string"] = "Cart is empty!";
                $_SESSION["message_class"] = "danger";
                header("Location:../../home/index.php");
            };

            $selected_discount = NULL;

        ?>
        
        <div class="container">
        <!-- Product Table -->
            <div class="cart-left" style="width: 50%;">
                <div class="card">
                    <h2>Medicine List</h2>
                    <div class="legends">
                        <span> <i class='fas fa-prescription' style='color: red;'></i> - Requires Prescription</span>
                    </div>
                    <table id="productTable">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Price</th>
                                <th>Discounted Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $subtotal = 0;
                                $total_discount = 0;

                                while($data = mysqli_fetch_array($product_lines)){
                                    if ($data['prescription_is_required'] == '1' && is_null($data['prescription_id'])){
                                        header("Location:../prescription/index.php");
                                    };

                                    if ($data['selected_discount']){
                                        $selected_discount = $data['selected_discount'];
                                    };

                                    $line_subtotal = $data['price'] * $data['qty'];

                                    $discount_rate = 0;
                                    if ($data['selected_discount'] && ($data['selected_discount'] == $data['applicable_discounts'] || $data['applicable_discounts'] == 'Both')){
                                        $discount_rate = 0.2;
                                    };

                                    $line_discount = $data['price'] * (1 - $discount_rate);
                                    
                                    $subtotal += $line_subtotal;
                                    $total_discount += ($line_subtotal - ($line_discount * $data['qty']));

                            ?>
                                <tr>
                                <td>
                                    <img src="<?php echo $data['photo'];?>" style="width:50px; height:50px"><br/>
                                    <?php echo $data['medicine_name'];?>
                                    <?php if ($data['prescription_is_required'] == '1') {echo "<i class='button-icon fas fa-prescription' title='Prescription is required' style='color: red !important;'></i>";} ?>
                                </td>
                                <td class="price">₱<?php echo $data['price'];?></td>
                                <td class="discounted-price">₱<?php echo $line_discount;?></td>
                                <td><?php echo $data['qty'];?></td>
                                <td class="total">₱<?php echo $line_subtotal;?></td>
                            </tr>
                            <?php
                                };
                                $total = $subtotal - $total_discount;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary -->
            <div class="cart-right">
                <div class="card">
                    <h2>Summary</h2>
                    <div id="summary">
                        <p>Discount Type:
                            <?php
                                if ($selected_discount){
                                    echo $selected_discount;
                                } else {
                                    echo "None";
                                };
                                $_SESSION['selected_discount'] = $selected_discount;
                            ?>
                        </p>
                        <p>Subtotal: ₱<span id="subtotal"><?php echo $subtotal; ?></span></p>
                        <p>Discount: ₱<span id="discountAmount"><?php echo $total_discount; ?></span></p>
                        <p>Total: ₱<span id="total"><?php echo $total; ?></span></p>
                    </div>

                    <form action="process.php" method="POST">
                        <button class="disabled" type="submit" name="action" value="confirm_order" id="confirm_order" disabled>Confirm</button>
                    </form>
                    
                </div>
            </div>
        </div>


        <script src="../../script.js"></script>
        
        <script>
            window.onload = function() {
                setActivePage("nav_cart");
            };

            const button = document.getElementById('confirm_order');
           
            // Function to check if user has reached the bottom of the page
            function checkScrollPosition() {
                const scrollPosition = window.innerHeight + window.scrollY;
                const pageHeight = document.documentElement.scrollHeight;
                
                // If user has reached the bottom, enable the button
                if (scrollPosition >= pageHeight) {
                    button.disabled = false;
                }
            }

            // Attach the scroll event listener to the window
            window.addEventListener('scroll', checkScrollPosition);
            checkScrollPosition();

        </script>
    </body>
</html>