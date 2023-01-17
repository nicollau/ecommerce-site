<?php 
    // Class: Cart
    // For cart, I have used static functions to have these in the project as well.
    // I have not used these on most of the other functions, as i've gone the object oriented route instead.

    require_once("ClassDatabase.php");

    class Cart extends Database {

        protected $id;
        protected $quantity;

        // Construct function for creating the cart 
        // Collects id and quantity, and runs the AddToCart function.
        public function __construct($id,$quantity){
            $this->id = $id;
            $this->quantity = $quantity;
            $this->AddToCart();
        }

       protected function AddToCart(){
           // AddToCart will add a product to the JSON file for later reading & use.
           // To prepare the JSON input, first we will add both the id and quantity into an object called data.

            $data = [
                'id' => $this->id,
                'quantity' => $this->quantity
            ];
            
            // Set the filepath
            $filepath = "data/cart.json";
        
            // If the file exists, and if the contents of the file is not empty
            if (file_exists($filepath) && file_get_contents($filepath)!=NULL){
                // Read the file and decode it for PHP
                $filecontents = file_get_contents($filepath);
                $cartTemp = json_decode($filecontents,true);
                
                // Make sure only items with different IDs gets pushed as individual items,
                // and those who have the same gets their quantity up.

                $doPush = true;
                foreach ($cartTemp as $key => $item){
                    if ($data['id'] == $item['id']){
                        // If the id is the same, plus the quantity
                        // I have to retrieve the key here to actually modify the $cartTemp array, as if I would have usen
                        // "$item", it would not push anything - as it's more a temporary thing for the foreach loop.

                        $cartTemp[$key]['quantity'] += $data['quantity'];
        
                        if ($cartTemp[$key]['quantity'] > 100) {
                            // If quantity is over 100, set it back to 100 and notify the user.
                            $cartTemp[$key]['quantity'] = 100;
                            $maxNotify = true;
                        }
                        // Don't push the array as we've already fixed the quantity
                        $doPush = false;
                    }
                }
                if ($doPush){
                    // If dopush is true (the id in the cart and the json file does not match), push the data array.
                    array_push($cartTemp, $data);
                }
        
                if ($maxNotify){
                    // Notify the user if they have passed the max amount.
                    echo "Error: You have added a maximum quantity of this item in the cart already!";
                }
        
                // Now re-encode to json and apply to file
                $dataJSON = json_encode($cartTemp);

                // Here we open, write and close the file which I find more suitable then just using file_put_contents.
                $file = fopen($filepath, 'w');
                fwrite($file, $dataJSON);
                fclose($file);

            } else {
                // Now if the file does not exist, just push the data into a temporary array,
                // encode it and send it to a new JSON file.
                $arr = array();
                array_push($arr,$data);
                
                $dataJSON = json_encode($arr);
                $file = fopen($filepath, 'w');
                fwrite($file, $dataJSON);
                fclose($file);
            }
            // Set a cookie for the cart to last for one week.
            // This cookie only tells the site that there is something in the cart.
            setcookie("cart", "1", time() + (86400 * 7));
        }
        
        protected function GetCart(){
            // This function will read the data from the json file, decode it to PHP, and return the whole array.
            $filepath = "data/cart.json";
            $filecontents = file_get_contents($filepath);
            $cartArray = json_decode($filecontents,true);
            return $cartArray;
        }
 
        protected function DeleteFromCart(){
            
        }
        
        protected function UnlinkJSONAndCookie(){
            // This function will remove the json file, and unset the cookie.
            unlink("data/cart.json");
            setcookie("cart", "0", time() -10000);
        }

        public static function ReadCart(){
            // Public function for returning the cart
            return self::GetCart();
        }

        public static function ClearCart(){
            // Public function for cleaning the cart
            self::UnlinkJSONAndCookie();
        }

    }