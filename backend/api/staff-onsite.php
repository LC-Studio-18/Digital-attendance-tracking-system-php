<?php
    header("Content-Type: application/json");     // Letting the browser know teh response will be in JSON format
    require_once__DIR__ . "/../config/database.php";

    try{

        $pdo = getDatabaseConnection();   // Setting up PDO Connection so that PHP can communicate with the MySQL database

    } catch(PDOException $exception) { 

        http_response_code(500);     
        echo json_encode ([
            "error" => "Unable to retrieve staff records"
        ]);           // Return the http code 500 with error message "Unable to retrieve staff records" 


        exit;   // Used so that PHP doesnt continue executing the rest of the endpoint
    }