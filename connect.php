<?php
/**
 * Connects to the database and prepares the statement.
 * @param $pdo the handler to the PDO object.
 * @param $sql the query to prepare.
 * @return the handler to the statement object to work with the database.
 */
function connectToDb(&$pdo, $sql) {
    $dbname = 'cs1242395'; //'homestead';//
    $dbuser = 'CS1242395'; //'homestead';//
    $dbpassword = 'harsioco'; //'secret';//
    $host = 'waldo2.dawsoncollege.qc.ca'; //'localhost';//
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpassword);
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo -> prepare($sql);
    
    return $stmt;
}
