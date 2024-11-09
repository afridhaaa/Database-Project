<?php
require __DIR__ . '/../vendor/autoload.php';
// Include Composer's autoload file

// MongoDB Atlas connection string
$host = "mongodb+srv://root:root@formulavault.860xw.mongodb.net/?retryWrites=true&w=majority&appName=FormulaVault";
 

try {
    // Create a MongoDB client
    $client = new MongoDB\Client($host);
    
    // Select the database
    $db = $client->FormulaVault;

    //echo "Connected to MongoDB Atlas successfully!";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
