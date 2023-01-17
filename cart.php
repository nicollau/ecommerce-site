<?php
// Start the session.
session_start();
// Require all needed files & classes.
require_once "functions.php";
require_once "classes/ClassUser.php";
require_once "classes/ClassProduct.php";
require_once "classes/ClassOrder.php";
require_once "classes/ClassCart.php";

// If session suspected hijack, kill it
if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] or $_SESSION['ua'] != $_SERVER['HTTP_USER_AGENT']) {
    KillSession();
}

// Check if logged in
if (isset($_SESSION['loggedin'])) {
    if ($_SESSION['loggedin']) {
        echo 'Logged in as: ' . $_SESSION['username'];
        // Create a new user based on the session variables
        $user = new User($_SESSION['username'], $_SESSION['password'], $_SESSION['email'], $_SESSION['role']);
    }
}

// If the clear button is submitted, clear the cart.
if (isset($_POST['clear'])) {
    Cart::ClearCart();
}

// Set the ordered variable first to be used later.
$ordered = false;

// If place order button is submitted, try to create an order.
if (isset($_POST['placeorder'])) {
    // First validate all the fields.
    if (ValidateArray($_POST)) {
        // First, read the cart and store it in an array.
        $cartArray = Cart::ReadCart();
        // Create a new customer in the database based on the information submitted.
        $customer = new Customer($_POST['firstname'], $_POST['lastname'], $_POST['address'], $_POST['country']);
        // Get the customerID from the newly created customer.
        $customerID = $customer->GetID();
        // Create a new order with the customer ID and the cart array.
        $order = new Order($customerID, $cartArray);

        // If the order went through, clear the cart and set a variable to true for showing a message further down on the site.
        if ($order) {
            Cart::ClearCart();
            $ordered = true;
        }
    } else {
        echo "Error: You need to fill all the fields!";
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
    <title>E-commerce - Cart</title>
</head>

<body>
    <?php
    DisplayNavBar();
    ?>

    <h1>Shopping Cart</h1>

    <?php
    // Check if an order has been placed.
    if ($ordered) {
        // If so, display a thanks and a link back to the main page.
        echo '<p>Thanks for your order!</p>';
        echo '<p>Go back to the <a href="index.php">main page</a>?</p>';
    } else {
        // Check if the cart cookie is set and it's set to 1.
        if (isset($_COOKIE['cart']) && $_COOKIE['cart'] === "1") {


            // Create a new Product to retrieve later.
            $products = new Product();
            // Read the cart data and put it in an array.
            $cartArray = Cart::ReadCart();

            // For each product/item in the cart, retrieve the product information with the ID retrieved from the cart data.
            // + add quantity and fix the price.
            $i = 0;
            foreach ($cartArray as $cartItem) {
                // Retrieve the product information with the cart item ID, and store it in a temp array.
                $tempArray = $products->GetProduct($cartItem['id']);
                // Make a new array with both the product information & the price.
                $cartProduct[$i] = $tempArray[0]; // As this is a database Object, get the first "array" (the table itself).
                $cartProduct[$i]['quantity'] = $cartItem['quantity']; // Apply the new quantity row.
                $cartProduct[$i]['price'] *= $cartItem['quantity']; // Multiply the price with the quantity.
                $i++;
            }

            // Make an empty cart button form to let the user empty their carts.
            echo <<<END
            <form action="" method="post">
            <input type="submit" name="clear" id="clear" value="Empty Cart">
            </form>
            END;

            // Create a table from the earlier made array, and show the table without images. 
            // (to easily see a summary of all the items in the cart without having to scroll through big images etc.)
            CreateTable($cartProduct, false, false, true);

            // Make a form for placing an order
            echo '<h2>Place order:</h2>';
            echo <<<END
                <form action="" method="post">
                <label for="firstname">First name:</label>
                <input type="text" name="firstname" id="firstname">
                <label for="firstname">Last name:</label>
                <input type="text" name="lastname" id="lastname">
                <label for="address">Address:</label>
                <input type="text" name="address" id="address">
                <label for="country">Country:</label>
                END;

            // Make a dropdown in the form for countries.
            // This is retrieved from the "data/countries.json" file.
            // Source: https://gist.github.com/keeguon/2310008#file-countries-json
            echo '<select name="country" id="country">';
            // Open the json file, get it's contents and decode it to PHP.
            $filepath = "data/countries.json";
            $filecontents = file_get_contents($filepath);
            $countries = json_decode($filecontents, true);

            // For each country, display it's name.
            foreach ($countries as $country) {
                echo '<option>' . $country['name'] . '</option>';
            }
            echo '</select>';

            // Show the total price of the order by combining them in a foreach loop.
            foreach ($cartProduct as $item) {
                $total += $item['price'];
            }
            echo "<p><strong>Total price:</strong> $total</p>";
            echo '<input type="submit" name="placeorder" id="placeorder" value="Place Order">';
            echo '</form>';
        } else {
            // If nothing in the cart, show a link so the user can look at the products.
            echo '<p>There is nothing in your cart! Browse <a href="index.php">products</a>.</p>';
        }
    }


    ?>



    <?php
    DisplayFooter();
    ?>
</body>

</html>