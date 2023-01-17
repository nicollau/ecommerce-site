<?php
// Class: User
require_once("ClassDatabase.php");

class User extends Database
{
    protected $username;
    protected $password;
    protected $email;
    protected $role;

    // Constructor function for creating a user. Takes in username, password, email and a role
    // Default values are empty, and the role to 1 (user role).
    public function __construct($username ="", $password="", $email="", $role=1)
    {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->role = $role;
    }

    // Function for creating an admin user
    public function CreateAdmin()
    {
        // Check if an admin account does not exist. If it does not exist, create it
        $con = $this->Connect();
        $query = "SELECT * FROM users WHERE username = 'admin' AND role = '0'";
        $result = $this->RunQuery($query, $con);

        if (!$result->num_rows > 0) {
            // Could not find an admin user, create one with standard values.
            $pass = password_hash("0admin0", PASSWORD_DEFAULT); // Hash the password.
            $query = "INSERT INTO users (username,password,email,role) VALUES ('admin','$pass','admin@ecommerce.com','0')";
            $result = $this->RunQuery($query, $con);
        } else {
            // Admin exists, move on..
        }

        $this->Disconnect($con);
    }

    // Function for logging an user in
    public function Login()
    {
        // Connect to the database and clean the username variable gotten.
        $con = $this->Connect();
        $username = $this->CleanVar($this->username, $con);

        // Run a query to check if user actually exists, and set if the user can log in or not.
        $query = "SELECT username FROM users WHERE username = '$username'";
        $result = $con->query($query);

        if ($result->num_rows > 0) {
            $canLogin = true;
        } else {
            $canLogin = false;
        }


        if ($canLogin) {
            // User found!
            // Run a query to find the hashed password to check if it matches with the input gotten.
            $query = "SELECT password FROM users WHERE username = '$username'";
            $result = $con->query($query);
            if ($result) {
                // Password was found, now check it against the one input.
                $hash = $result->fetch_assoc();
                $validate = password_verify($this->password, $hash['password']);
            } else {
                // Force validating to be false if there returns an error in the database.
                echo mysqli_error($con);
                $validate = false;
            }

            // If the password is valid, log in!
            if ($validate) {
                // Run a query to retrieve all the user information.
                $query = "SELECT username, email, role FROM users WHERE username='$username'";
                $result = $con->query($query);
                if ($result) {
                    // If could retrieve the query, fetch an associative array from it.
                    $userArray = $result->fetch_assoc();
                    // Regenerate the session ID for safety.
                    session_regenerate_id();
                    // Store the information in the class based on the associative array.
                    $this->username = $userArray['username'];
                    $this->email = $userArray['email'];
                    $this->role = $userArray['role'];

                    // Then retrieve the information from the class, and input it into the session variable.
                    $_SESSION['username'] = $this->username;
                    $_SESSION['email'] = $this->email;
                    $_SESSION['role'] = $this->role;

                    // Set the session variable "loggedin" to true.
                    $_SESSION['loggedin'] = true;

                    // Store IP and user agent
                    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
                } else {
                    echo "An error occured when trying to login, please try again.";
                    echo mysqli_error($con);
                    $_SESSION['loggedin'] = false;
                }
            }
        }

        // Disconnect from the DB.
        $this->Disconnect($con);

        // If login was successful, return true, if not return false.
        if ($_SESSION['loggedin']) {
            return true;
        } else {
            return false;
        }
    }

    // Function for adding a user to the database (register)
    public function AddUser()
    {

        // Connect to database, clean username & email
        $con = $this->Connect();
        $this->username = $this->CleanVar($this->username, $con);
        $this->email = $this->CleanVar($this->email, $con);

        // Check if user already exists in the database, if so - do not allow registration.
        $query = "SELECT username FROM users WHERE username = '$this->username'";
        $result = $con->query($query);
        if ($result->num_rows > 0) {
            $canRegister = false;
            $userCreated = false;
        } else {
            $canRegister = true;
        }

        // If user does not exist and can be added:
        if ($canRegister) {
            // Hash & salt the password.
            // as the password can include special characters and so, do not clean it.
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);

            // Query for adding the user.
            $query = "INSERT INTO users (username,password,email,role) VALUES('$this->username','$this->password','$this->email','1')";
            $result = $this->RunQuery($query, $con);

            if (!is_null($result)) {
                echo "User created!";
                $userCreated = true;
            } else {
                echo "Could not create user!";
                $userCreated = false;
            }
        } else {
            echo "Username already taken!";
            $userCreated = false;
        }

        // Disconnect and return true if could create user, false if not.
        $this->Disconnect($con);
        if ($userCreated) {
            return true;
        } else {
            return false;
        }
    }

    // A function for getting the role of the user.
    public function GetRole()
    {
        return $this->role;
    }
}
