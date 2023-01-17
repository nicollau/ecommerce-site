<?php

// Class: Customer

require_once "ClassDatabase.php";

class Customer extends Database 
{
    protected $id;
    protected $firstname;
    protected $lastname;
    protected $address;
    protected $country;

    // Constructor, takes in customer information and adds this to the database.
    public function __construct($firstname,$lastname,$address,$country){
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->address = $address;
        $this->country = $country;

        $result = $this->AddCustomerToDB();

        // After the customer is inserted into the database, the $result will determine the customer ID of the user,
        // and will add the information to the object itself so it can be read later.
        // If the database function, for some reason, did not work - it will return null.
        if (!is_null($result)){
            $this->id = $result;
        } else {
            $this->id = null;
        }
    }
    

    // A public function for returning an ID from a created customer
    public function GetID(){
        return $this->id;
    }

    protected function AddCustomerToDB(){
        // Connect to the database and sanitize the input
        $con = $this->Connect();
        $firstname = $this->CleanVar($this->firstname, $con);
        $lastname = $this->CleanVar($this->lastname, $con);
        $address = $this->CleanVar($this->address, $con);
        $country = $this->CleanVar($this->country, $con);
        $customerExists = false;

        // Run a query to find if the customer already exists
        $query = "SELECT * FROM customers WHERE firstname = '$firstname' AND lastname = '$lastname' AND address = '$address' AND country = '$country'";
        $result = $this->RunQuery($query,$con);

        if ($result->num_rows>0){
            // If more than 0 rows are in the result, the customer exists.
            $customerExists=true;
        }

        if ($customerExists){
            $this->Disconnect($con);
            // I made sure to always disconnect before returning so the connection would not retain.

            // Fetch an associative array from the result.
            $assoc = $result->fetch_assoc();
            // Check the array for the customer_id key and return it, if possible.

            if (!is_null($assoc['customer_id'])){
                return $assoc['customer_id'];
            } else {
                return null;
            }
        } else {
            // If customer does not exist, insert the information into the database and create one.
            $query = "INSERT INTO customers (firstname,lastname,address,country) VALUES ('$firstname','$lastname','$address','$country')";

            $result = $this->RunQuery($query,$con);
    
            if (!is_null($result)) {
                // Check if the result is OK, if so retrieve the INSERT_ID from the connection, as this will be the customer id.
                // Then disconnect & return that.
                $last_id = $con->insert_id;
                $this->Disconnect($con);
                return $last_id;
            } else {
                // Throw an error if could not add customer to database.
                echo "Error: Could not create customer! " . mysqli_error($con);
                $this->Disconnect($con);
                return null;
            }
        }
        
    }
}