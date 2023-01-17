<?php
// Start the session.
session_start();
// Require all needed files & classes.
require_once "functions.php";
require_once "classes/ClassProduct.php";
require_once "classes/ClassUser.php";
require_once "classes/ClassCart.php";

// If the id variable is set in GET, save it into another variable and pass a boolean that the id is set.
// The GET variable is retrieved from the product link itself to display the correct product.
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $idSet = true;
} else {
    $idSet = false;
}

if ($idSet) {
    // If idset is true, retrieve the product from the database.
    $productArray = new Product();
    $product = $productArray->GetProduct($id);
}

// If session suspected hijack, kill it
if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] or $_SESSION['ua'] != $_SERVER['HTTP_USER_AGENT']) {
    KillSession();
}

// Check if logged in
if (isset($_SESSION['loggedin'])) {
    if ($_SESSION['loggedin']) {
        echo 'Logged in as: ' . $_SESSION['username'];
        
        $user = new User($_SESSION['username'],$_SESSION['password'],$_SESSION['email'],$_SESSION['role']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/style.css">
    <?php
    if ($idSet) {
        // If idset is true, set the title of the page to include the product name
        echo '<title>E-commerce - ' . $product['0']['product_name'] . '</title>';
    } else {
        echo '<title>E-commerce - ?</title>';
    }
    ?>
</head>

<body>
    <?php
    DisplayNavBar();

    if (isset($_POST['submit'])) {
        if ($_POST['quantity'] > 0) {
            // If submit button is pressed and the quantity set is over 0,
            // add a new product to the cart.
            $quantity = $_POST['quantity'];
            $cart = new Cart($id,$quantity);
        }
    }

    if ($idSet) {
        // Create a table from the single product
        CreateTable($product);
        // Create a form for adding the product in a cart
        echo <<<END
        <form action="" method="post">     
        <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" max="100" value="1">
            <input type="submit" name="submit" value="Add to cart">
        </form>
        END;
    }

    DisplayFooter();
    ?>
</body>

</html>