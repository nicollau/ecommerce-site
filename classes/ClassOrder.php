<?php

// Class: Order, requires Customer as well as Database.

require_once "ClassDatabase.php";
require_once "ClassCustomer.php";

class Order extends Database
{
    // Properties
    protected array $orders;
    protected $customer_id;

    // Construct function for creating an order. Takes both a customer id, and an array with the order information.
    public function __construct($customerID, array $orders){
        $this->customer_id = $customerID;
        $this->orders = $orders;

        // A return variable to return info after creating the order.
        $toReturn = $this->AddOrderToDB();
        return $toReturn;
    }

    protected function AddOrderToDB(){
        // Connect to the database, clean the customer_id as a safety measure.
        $con = $this->Connect();
        $customer_id = $this->CleanVar($this->customer_id, $con);

        // Create variables first to be input later, incase something goes wrong when retrieving them.
        $product_id = null;
        $quantity = null;
        $date = null;

        foreach ($this->orders as $order){
            // For each item in the orders array, retrieve id and quantity.
            $product_id = $order['id'];
            $quantity = $order['quantity'];

            // Get the date right now to mark when the order was created.
            $date = date("Y-m-d H:m:s");

            // Query for inserting the order.
            $query = "INSERT INTO orders (customer_id,product_id,time,quantity) VALUES('$customer_id','$product_id','$date','$quantity')";
            $result = $this->RunQuery($query, $con);

            // Disconnect before returning
            $this->Disconnect($con);
    
            if (!is_null($result)) {
                // If the query went through, return true.
                return true;
            } else {
                // If not, throw and error and return false.
                echo "Error: Could not add order to database!" + mysqli_error($con);
                return false;
            }

        }

       
    }

}