<?php
// Start the session.
session_start();
// Require all needed files & classes.
require_once "functions.php";
require_once "classes/ClassUser.php";
require_once "classes/ClassCart.php";

// Create an admin if there does not exist one, first.
$admin = new User();
$admin->CreateAdmin();

// Define some variables to be used later.
$createLoginForm = true;
$fieldsError = false;
$loggedIn = false;
$loginFailed = false;

// Check if the login submit button is set.
if (isset($_POST['login'])) {
    // Validate the login form fields to make sure they are not empty.
    if (ValidateArray($_POST)) {
        // If validated, try to login the user with username and password.
        $user = new User($_POST['username'], $_POST['password']);
        $loggedIn = $user->Login();

        if ($loggedIn) {
            // If user could log in, do not display the login form again.
            $createLoginForm = false;
        } else {
            // If user could not be logged in, display the form again but also throw a message.
            // (These variables will be used later in the actual display part. This is to not throw messages at the top of the page, visually.)
            $createLoginForm = true;
            $loginFailed = true;
        }
    } else {
        // Fields not validated, throw a message later.
        $fieldsError = true;
    }
}

// Check if the register submit button is set.
if (isset($_POST['register'])) {
    // Validate the register form fields to make sure they are not empty.
    if (ValidateArray($_POST)) {
        // If validated, try to register the user with username, password and email. Role gets set to 1 by default.
        $user = new User($_POST['username'], $_POST['password'], $_POST['email']);
        // If could register user, do not show the register form.
        // If could not, show the form.
        if ($user->AddUser()) {
            $createRegisterForm = false;
        } else {
            $createRegisterForm = true;
        }
    } else {
        // If not validated, show the form and also throw a message to the user.
        $createRegisterForm = true;
        $fieldsError = true;
    }
}

// Check if already user logged in.
if (
    // Check if 'loggedin' isset in session + that the login button was not pressed + that the session logged in actually returned true.
    isset($_SESSION['loggedin']) &&
    !isset($_POST['login']) &&
    $_SESSION['loggedin']
) {
    // If user logged in, show some text to make the user know they're logged in.
    echo 'Logged in as: ' . $_SESSION['username'];

    $createLoginForm = false; // Do not create the login form.
    $loggedIn = true;
    // Create a new user with the session variables
    $user = new User($_SESSION['username'], $_SESSION['password'], $_SESSION['email'], $_SESSION['role']);
}

// If the GET variable register is set and true (if you click the "Do you want to register?" button),
// show the registration form instead of the login form.
if (isset($_GET['register'])) {
    if ($_GET['register']) {
        $createLoginForm = false;
        $createRegisterForm = true;
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
    <title>E-commerce - Login</title>
</head>

<body>
    <?php
    DisplayNavBar();

    echo '<main>';
    // Check if the site should display a login form or a register form.
    if ($createLoginForm) {
        echo <<<END
        <h2>Login:</h2>
            <form action="login.php" method="post">
                <p>Username:</p>
                <input type="text" name="username" id="username">
                <p>Password:</p>
                <input type="password" name="password" id="password">
                <input type="submit" name="login" id="submit" value="Login">
            </form>
            <p>Not registered? Click <a href="login.php?register=true">here</a></p>
        END;
    }

    if ($createRegisterForm) {
        echo <<<END
        <h2>Register:</h2>
            <form action="login.php" method="post">
                <label for="username">Username</label>
                <input type="text" name="username" id="username">
                <label for="password">Password</label>
                <input type="password" name="password" id="password">
                <label for="email">Email</label>
                <input type="text" name="email" id="email">
                <input type="submit" name="register" id="submit" value="Register">
                <p>Already registered? Click <a href="login.php">here</a></p>
            </form>
        END;
    }
    //  If an error occured earlier in the code, display it here.
    if ($fieldsError) {
        echo "You need to fill in all fields.";
    }

    if ($loginFailed) {
        echo "Error: Your username or password is wrong!";
    }

    // If the user is logged in, show some standard information directing them to the main page and so on.
    // If the user is an admin, show a link to the admin page.
    // Also show a way to log out.

    if ($loggedIn) {
        if ($user->GetRole() == 0) {
            echo '<p>Welcome admin! Go to the <a href="admin.php">admin page</a>.</p>';
            echo '<p>Go to the <a href="index.php">main page</a>.</p>';
            echo '<p>Or do you want to <a href="logout.php?return=login">log out</a>?</p>';
        } else {
            echo '<p>Welcome "' . $_SESSION['username'] . '"! You were logged in successfully! <br> Go back to the <a href="index.php">main page</a>.</p>';
            echo '<p>Or do you want to <a href="logout.php?return=login">log out</a>?</p>';
        }
    }

    echo '</main>';

    ?>



    <?php DisplayFooter(); ?>
</body>

</html>