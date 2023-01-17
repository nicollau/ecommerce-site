<?php
// Functions document.


// Function for displaying the navigation bar.
function DisplayNavBar()
{
    echo "<nav>";
    echo '<hr>';
    echo "<a href='index.php'>Home</a>  ";

    // If the cart cookie is set and is set to 1, show the number next to the "Shopping Cart" link, with the quantity of the cart item contents.
    if (isset($_COOKIE['cart']) && $_COOKIE['cart'] === "1") {
        // First, read the cart and store it in an array.
        $array = Cart::ReadCart();

        // For each item, add up the quantity number to show.
        $quantity = 0;
        foreach ($array as $item) {
            $quantity++;
        }
        echo "<a href='cart.php'>Shopping Cart ($quantity)</a>  ";
    } else {
        echo "<a href='cart.php'>Shopping Cart</a>  ";
    }

    // Check if the user is logged in.
    if (isset($_SESSION['loggedin'])) {
        // Create a new user based on the session variables.
        $user = new User($_SESSION['username'], $_SESSION['password'], $_SESSION['email'], $_SESSION['role']);
        // If the user is admin, show the admin page link.
        if ($user->GetRole() == 0) {
            echo "<a href='admin.php'>Admin Page</a> ";
        }
        // If logged in, show a logout link.
        echo "<a href='logout.php'>Logout</a>";
    } else {
        // If not logged in, show a link to the login/registration page.
        echo "<a href='login.php'>Login / Register</a>";
    }
    echo '<hr>';
    echo "</nav>";
}

// Function for displaying a simple footer.
function DisplayFooter()
{
    echo "<hr>";
    echo "<footer>";
    echo "<p>Site by Nicolas Laukemann 2022</p>";
    echo "</footer>";
}

// Function for validating an array.
function ValidateArray($array)
{
    foreach ($array as $item) {
        // Check if item is empty or returns null.
        // Returns false if so.
        if (empty($item) || is_null($item)) {
            return false;
        }
    }
    // If all items in the array has a value, return true.
    return true;
}



// Take an associative array as input and creates a table from it. 
// Has three arguments which is set to false by standard.
// First one is nameAsLink, which if there is a product name in the table to create, will turn it into a link.
// Second one is onlyessentials, which will only show product name and image (if present).
// Third one is noimage, which will not include an image if there is one present.
function CreateTable($resArray, $nameAsLink = false, $onlyEssentials = false, $noImage = false)
{
    echo '<table>';
    // Define variable for later.
    $isFirstRow = false;

    // For each item in the associative array:
    foreach ($resArray as $item) {
        // Check if not first row.
        if (!$isFirstRow) {
            echo "<tr>";
            // Check the keys in the array, and based on that
            // "convert" the keys to more readable text.
            foreach ($item as $key => $value) {
                switch ($key) {
                    case 'product_id':
                        $fixed_key = "Product ID";
                        break;
                    case 'order_id':
                        $fixed_key = "Order ID";
                        break;
                    case 'time':
                        $fixed_key = "Date / Time";
                        break;
                    case 'customer_id':
                        $fixed_key = "Customer ID";
                        break;
                    case 'product_name':
                        $fixed_key = "Product";
                        break;
                    case 'image_name':
                        $fixed_key = "Image";
                        break;
                    case 'description':
                        $fixed_key = "Description";
                        break;
                    case 'price':
                        $fixed_key = "Price";
                        break;
                    case 'quantity':
                        $fixed_key = "Quantity";
                        break;
                    case 'firstname':
                        $fixed_key = "First Name";
                        break;
                    case 'lastname':
                        $fixed_key = "Last Name";
                        break;
                    case 'address':
                        $fixed_key = "Address";
                        break;
                    case 'country':
                        $fixed_key = "Country";
                        break;
                    default:
                        "?"; // Incase a key is not in this list, supply a question mark as a default value.
                }
                // If "onlyEssentials" is true, only show the product_name and image_name
                if ($onlyEssentials) {
                    // If the key of the item is either product_name or image_name, show the item.
                    if ($key === "product_name" | $key === "image_name") {
                        if ($noImage && $key === "image_name") {
                            // If noImage is true as well, skip showing the image header.
                        } else {
                            // Display the fixed key of the correct item as a header.
                            echo "<th> $fixed_key </th>";
                        }
                    }
                } else {
                    // If onlyEssentials is false, show the entire table.
                    if ($noImage && $key === "image_name") {
                        // If noImage is true and the key is the image_name, do not display the image header text.
                    } else {
                        // Display the fixed key of the correct item as a header.
                        echo "<th> $fixed_key </th>";
                    }
                }
            }
            echo "</tr>";

            // Headers (first row) is done, move on to creating the rest of the rows.
            $createRow = true;
            $isFirstRow = TRUE;
        } else {
            $createRow = true;
        }


        // If can create a normal row:
        if ($createRow) {
            echo "<tr>";
            // For each item in the array,
            foreach ($item as $key => $value) {
                // First, check that noImage is false, if so - display the image
                if (!$noImage && $key === "image_name") {
                    // Instead of displaying the image filename itself, we will use that name instead to
                    // create an actual visible image. I have also included the product name in the alt text if the
                    // image should not exist or not show by error.
                    echo '<td><img width="125" height="125" alt="' . $item['product_name'] . '" src="uploads/' . $value . '"></td>'; // This includes the image filename in the src html tag, which will direct to the correct image file.
                } else if ($noImage && $key === "image_name") {
                    // Do nothing.
                } else if ($key === "product_name") {
                    if ($nameAsLink) {
                        // If the key is product name, and nameAsLink is valid - show the product name as a link to the correct product.
                        // This also makes a link based on the ID itself, so you can go to the correct product page.
                        $link = "product.php?id=" . $item['product_id'];
                        echo '<td><a href="' . $link . '">' . $value . '</td>'; // Include both the link as a href, and the value (the product name) to display.
                    } else {
                        // For all other keys, display the value in the table.
                        echo "<td> $value </td>";
                    }
                } else {
                    if (!$onlyEssentials) {
                        // If onlyEssentials is false, display the rest of the items too. (not only image & name)
                        echo "<td> $value </td>";
                    }
                }
            }
            echo "</tr>";
            $createRow = false;
        }
    }
    echo "</table>";
}



// Function for killing the session, either by logout or suspected session hijack.
function KillSession()
{
    // Take the current session array and empty it.
    $_SESSION = array();
    
    // Delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    // Finally, destroy the session.
    session_destroy();
}
