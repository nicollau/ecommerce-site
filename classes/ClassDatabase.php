<?php 

// Superclass Database with related methods

class Database{
    
    protected function Connect(){
        // Connect to the database
        $host = 'localhost';
        $username = 'root';
        $password = 'root';
        $database = 'ecommerce';

        $con = new mysqli($host,$username,$password,$database);

        // Check if the database connection worked
        if(!$con){
            die ("Database connection failed!");
        }

        // Return the connection to use other places
        return $con;
    }
    
    protected function Disconnect($con){
        // Disconnect the database connection
        $con->close();
    }

    public function ReadTable($tableName){
        // This function will read a table from the database with the arguments:
        // tablename = The name of the table in the database
        // sort_by = What the table will be sorted by (if not defined: null)
        // sort_type = What kind of type the table will be sorted by (asscending/desscending) (if not defined: null)

        // Connect to the database and sanitize the input
        $con = $this->Connect();
        $tableName = $this->CleanVar($tableName,$con);

        // A query to get all information from a single table.
        $query = "SELECT * FROM $tableName";
        $result = $this->RunQuery($query,$con);
        

        // Read one row at a time, and input this into a new array.
        $idx = 0;
        while($row=$result->fetch_assoc()){
            $resArray[$idx] = $row;
            $idx++;
        }
        // Disconnect from the database and return the array.
        $this->disconnect($con);
        return $resArray;
    }

    public function ReadOrders($sort_by = null, $sort_type = null){
        // This function will read both the order and the customer table (joined) from the database with the arguments:
        // sort_by = What the final table will be sorted by (if not defined: null)
        // sort_type = What kind of type the final table will be sorted by (ascending/descending) (if not defined: null)

        // Connect to the database and sanitize the input
        $con = $this->Connect();
        
        // Check if sort_by and sort_type has been defined
        if (!is_null($sort_by) && !is_null($sort_type)){
            // If so, run a switch statement
            // If arg. input is 'standard', sort by 'order_id' column
            // If arg. input is 'date_time', sort by 'time' column
            switch ($sort_by){
                case 'standard':
                    $sort_by = 'order_id';
                    break;
                case 'date_time':
                    $sort_by = 'time';
                    break; 
                default: 'order_id';   
            }
            
            // Clean these inputs too, just as a safety measure.
            $sort_by = $this->CleanVar($sort_by,$con);
            $sort_type = $this->CleanVar($sort_type,$con);

            
            // A query for getting the table information but also to sort it.
            $query = 'SELECT * FROM orders INNER JOIN customers ON orders.customer_id=customers.customer_id ORDER BY '. $sort_by . ' ' .$sort_type .';';
        } else {
            // A query to get the joined tables as one table.
            $query = "SELECT * FROM orders INNER JOIN customers ON orders.customer_id=customers.customer_id;";
        }

        // Run the query
        $result = $this->RunQuery($query,$con);
        
        // Read one row at a time, and input this into a new array.
        $idx = 0;
        while($row=$result->fetch_assoc()){
            $resArray[$idx] = $row;
            $idx++;
        }
        // Disconnect from the database and return the array.
        $this->disconnect($con);
        return $resArray;
    }
    
    // Function for sanitizing input
    protected function CleanVar($var, $con){
        $var = htmlentities($var);
        $var = strip_tags($var);
        $var = stripslashes($var);
        $var = mysqli_real_escape_string($con, $var);
    return $var;

    }
    // Function for running a query. A database connection must already be established before running this function.
    protected function RunQuery($query,$con){
        if ($con->connect_error){
            die("Could not connect to the database! " . mysqli_error($con));
        }

        $result = $con->query($query);
        
        if (!$result){
            echo mysqli_error($con);
            return null;
        } else {
            return $result;
        }
    }

}



?>