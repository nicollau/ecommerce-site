<?php
// Simple site for logging the user out of the site.

// Start the session.
session_start();

// Require all needed files.
require_once "functions.php";

// Kill the session (log out)
KillSession();

// If directed to logout.php with a GET variable in the link, return to that GET variable (document).
// If not, return to index.
if (isset($_GET['return'])) {
    $return = $_GET['return'];
} else {
    $return = "index";
}
// Redirect the user out of the logout page.
header("Location: " . $return . ".php");
