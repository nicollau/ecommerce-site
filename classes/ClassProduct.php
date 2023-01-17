<?php

// Class: Product

require_once "ClassDatabase.php";

class Product extends Database
{
    protected $product_img;
    protected $product_name;
    protected $product_desc;
    protected $product_price;

    // Construct method for creating a product, takes a image name, name, description and a price.
    // Standard values is empty to be able to create 'empty' products just for using one of the functions.
    public function __construct($img = "", $name = "", $desc = "", $price = 0){
        $this->product_img = $img;
        $this->product_name = $name;
        $this->product_desc = $desc;
        $this->product_price = $price;
    }

    // Function for adding a product to the database.
    public function AddProduct()
    {
        // First, connect to the database, then clean the variables to be input
        $con = $this->Connect();
        $product_img = $this->CleanVar($this->product_img, $con);
        $product_name = $this->CleanVar($this->product_name, $con);
        $product_desc = $this->CleanVar($this->product_desc, $con);
        $product_price = $this->CleanVar($this->product_price, $con);

        // Query for inserting products
        $query = "INSERT INTO products (product_name,image_name,description,price) VALUES('$product_name','$product_img','$product_desc',$product_price)";
        $result = $this->RunQuery($query, $con);

        // Print a statement to the user if the product was added or not.
        if (!is_null($result)) {
            echo "Product added!";
        } else {
            echo "Could not add product to database!";
        }

        $this->Disconnect($con);
    }


    // Function for deleting a product, takes an id.
    public function DeleteProduct($id)
    {
        // First, define some variables to be used later.
        $canDelete = false;
        $productFound = false;

        // Set the directory where to look for the uploaded images.
        $directory = "uploads/";

        // Connect to the database and clean the id variable.
        $con = $this->Connect();
        $id = $this->CleanVar($id, $con);

        // First, check if the item actually exists (failsafe)
        $query = "SELECT * FROM products WHERE product_id='$id'";
        $result = $this->RunQuery($query, $con);
        // If the result's rows are over 0, the product has been found.
        if ($result->num_rows > 0) {
            $productFound = true;
        } else {
            echo "Error: Product not found!";
        }

        // If the product is found:
        if ($productFound) {
            // Retrieve the image name from the database.
            // Run a query to the database, and then fetch a associtive array from this, 
            // where we will look for the column 'image_name'.
            $query = "SELECT image_name FROM products WHERE product_id='$id'";
            $image_result = $this->RunQuery($query, $con);
            $result_assoc = $image_result->fetch_assoc();
            $img_file_name = $result_assoc['image_name'];

            // If the result returns something else than null,
            // delete the file.
            if (!is_null($image_result)) {
                // Set the filepath
                $file = $directory . $img_file_name;
                // Delete the file
                $status = unlink($file);
                // Check if file could be deleted or not, and return some information to the user.
                if ($status) {
                    $canDelete = true;
                } else {
                    echo "Warning: Image file was found, but could not be deleted.";
                    $canDelete = true;
                }
            } else {
                // If for some reason the image could not be found, throw a message to the user too.
                echo "Error: Could not find an image connected to the product?";
                $canDelete = false;
            }

            // Make sure the product can only be deleted if the image could be found & deleted.
            // This is to make sure the product is fully gone when first removed.
            if ($canDelete) {
                // Run a delete query in the database.
                $query = "DELETE FROM products WHERE product_id='$id'";
                $result = $this->RunQuery($query, $con);

                // Return information if the product was deleted or not.
                if (!is_null($result)) {
                    echo "Product deleted!";
                }

            } else {
                // If can't delete, return info to user.
                echo "Error: Product NOT deleted!";
            }
        }
        $this->Disconnect($con);
    }

    
    // Function for retrieving product from the database.
    public function GetProduct($id){
        // Run a query to get the relevant information from the database, where the id is what is given as an argument.
        $con = $this->connect();
        $query = "SELECT product_name, image_name, description, price FROM products WHERE product_id='$id'";
        $result = $this->RunQuery($query,$con);

        // Read 1 row at a time, and input this information into an array.
        $idx = 0;
        while($row=$result->fetch_assoc()){
            $resArray[$idx] = $row;
            $idx++;
        }
        // Disconnect before returning the array.
        $this->disconnect($con);
        return $resArray;

    }
}
