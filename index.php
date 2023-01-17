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

// Check if user is logged in
if (isset($_SESSION['loggedin'])) {
    if ($_SESSION['loggedin']) {
        echo 'Logged in as: ' . $_SESSION['username'];
        // Create a new user based on the session variables
        $user = new User($_SESSION['username'], $_SESSION['password'], $_SESSION['email'], $_SESSION['role']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset=ss"UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/style.css">
    <title>E-commerce - Main page</title>
</head>

<body>

    <?php
    DisplayNavBar(); // Display the navigation bar

    echo '<h1>E-commerce site</h1>';
    echo '<h2>Our current products:</h2>';
    // Retrieve the products table from the database.
    $products = new Product();
    $table = $products->ReadTable('products');
    if (!empty($table)){
        // If the products table is not empty,
        // create & show the HTML table.
        CreateTable($table, true, true);
    } else {
        echo '<p>There are no products on this site.</p>';
    }

    ?>


    <?php DisplayFooter(); // Display the footer 
    ?>
</body>

</html>