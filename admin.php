<?php
// Start the session.
session_start();
// Require all needed files & classes.
require_once "functions.php";
require_once "classes/ClassProduct.php";
require_once "classes/ClassUser.php";
require_once "classes/ClassCart.php";

// If session suspected hijack, kill it
if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] or $_SESSION['ua'] != $_SERVER['HTTP_USER_AGENT']) {
    KillSession();
}

// Check if logged in via the session, and check if the user is admin.
if (isset($_SESSION['loggedin'])) {
    if ($_SESSION['loggedin']) {
        echo 'Logged in as: ' . $_SESSION['username'];
        // Create a new user based on the session variables
        $user = new User($_SESSION['username'], $_SESSION['password'], $_SESSION['email'], $_SESSION['role']);

        if ($user->GetRole() == 0) {
            // If user's role is 0, user is admin
            $admin = true;
        }
    } else {
        $admin = false;
    }
} else {
    $admin = false;
}

// Send the user to main page if not admin, they have no access here.
if (!$admin) {
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/style.css">
    <title>E-commerce - Admin Page</title>
</head>

<body>

    <?php
    DisplayNavBar();

    // As a failsafe, check again if the role is 0 before displaying any admin related information.
    if ($user->GetRole() == 0) {
        // To remove:)
        if ($debug) {
            echo "<pre>";
            echo "FILES:<br>";
            print_r($_FILES);
            echo "</pre>";
            echo "<pre>";
            echo "IMG_UPLOAD:";
            print_r($_FILES['img_upload']);
            echo "</pre>";
        }

        // If the product add 'submit' button was pressed, try to add the product into the database.
        if (isset($_POST['submit'])) {

            // Define some variables first to be used later.
            $fieldsFilled = false;
            $overwrite = false;
            $uploadDone = false;
            $canUpload = true;

            // First, check if the fields are filled out.
            if (ValidateArray($_POST)) {
                // This will check all the input fields to make sure they are not null, and not empty.
                // Create variables from the POST variables to be used later. (also for easy typing)
                $product_name = $_POST['product_name'];
                $product_desc = $_POST['product_desc'];
                $product_price = $_POST['product_price'];

                // If fields are filled, set the variable to true - otherwise false and throw a message to the user.
                $fieldsFilled = true;
            } else {
                echo "Error: All fields needs to be filled!";
                echo "<br>";
                $fieldsFilled = false;
            }
            // If overwrite is checked in the form, set overwrite var to true.
            // This variable will be used in the upload.php, which gets included under here.
            if ($_POST['overwrite_check']) {
                $overwrite = true;
            }

            // Upload the image file by including the upload script here.
            include("upload.php");


            // Make sure fields are filled and the upload is done before adding the product to the database.
            if ($fieldsFilled && $uploadDone) {
                // If fields are filled and upload is complete, add the product to the database!
                $product = new Product($product_img, $product_name, $product_desc, $product_price);
                $product->AddProduct();
            } else { // Else throw a message to the user.
                echo "Error: Could not add product.";
                echo "<br>";
            }
        }

        // If the delete product button is submitted, try to delete the product with the id gotten in POST from the datbaase.
        if (isset($_POST['delete'])) {
            $product = new Product();
            $product->DeleteProduct($_POST['product_id']);
        }
    }
    ?>

    <!-- Form for creating a new product-->
    <h2>Create a new product:</h2>
    <form enctype="multipart/form-data" method="POST" id="createform" action="#products">
        <label for id="img_upload">Upload an image (JPG only!): </label>
        <input type="file" name="img_upload" id="img_upload">
        <label for id="overwrite_check">Overwrite file?</label>
        <input type="checkbox" name="overwrite_check" id="overwrite_check">
        <label for id="product_name">Name: </label>
        <input type="text" name="product_name" id="product_name">
        <label for id="product_desc">Description: </label>
        <textarea rows="3" cols="50" name="product_desc" id="product_desc">Description goes here...</textarea>
        <label for id="product_price">Price: </label>
        <input type="number" name="product_price" id="product_price">
        <input type="submit" name="submit" value="Add Product">
    </form>

    <hr>

    <?php
    // Check again if the user is admin. As there are multiple php steps in this document, I make sure to include this every time.
    if ($user->GetRole() == 0) {
        // Products Table
        // Create a new product to retrieve the products table from.
        $products = new Product();
        $table = $products->ReadTable('products');


        // Only display the products table if there are products present in the database.

        echo '<h1 id="#products">Current products:</h1>';

        if (!empty($table)) {
            // Create the HTML table using the associative array which has been returned.
            CreateTable($table);

            echo '<h2>Delete a product:</h2>';

            // Form for deleting products. Will only display if there are products present in the database.
            echo <<<END
                <form action="" method="post" id="deleteform" action="#deleteform">
                <label for="product_id">Item:</label>
                <select name="product_id" id="product_id">
            END;

            // For each item in the product table, show the id and name in the option html tag to make it so the user
            // can choose what product to delete from a dropdown menu.

            foreach ($table as $item) {
                echo '<option value="' . $item['product_id'] . '">(' . $item['product_id'] . ') Name: ' . $item['product_name'] . '</option>';
            }

            echo <<<END
                </select>
                <input type="submit" name="delete" value="Delete">
                </form>
            END;
        } else {
            echo '<p>There are no products in the database.</p>';
        }
    }

    // Orders Table
    echo '<h2>Orders:</h2>';

    // Check again if the user is admin..
    if ($user->GetRole() == 0) {
        // If the get variable 'sort' is retrieved, read the database table orders, but sort it by the values gotten in GET.
        // If not, read the table as normal.
        // Then create the HTML table using the retrieved array.
        if (isset($_GET['sort'])) {
            $table = $products->ReadOrders($_GET['sort_by'], $_GET['sort_type']);
        } else {
            $table = $products->ReadOrders();
        }

        if (!empty($table)) {
            // Only show the form and create the table if there are any orders are present in the database.
            // Sort Form
            // I've added some hashtags here so that the user gets scrolled straight to this part of the page when submitting the sort form.

            CreateTable($table);

            echo <<<END
                <form action="#sortform" method="get" id="sortform">
                <label for="sort_by">Sort by:</label>
                <select name="sort_by" id="sort_type">
                <option value="standard">Order ID</option>
                <option value="date_time">Date / Time</option>
                </select>
                <label for="type_1">Ascending</label>
                <input type="radio" name="sort_type" id="type_1" value="ASC" checked="checked">
                <label for="type_2">Descending</label>
                <input type="radio" name="sort_type" id="type_2" value="DESC">
                <input type="submit" name="sort" id="sort" value="Sort">
                </form>
            END;
        } else {
            echo '<p>There are no orders in the database.</p>';
        }
    }
    ?>

    <?php DisplayFooter(); ?>

</body>

</html>