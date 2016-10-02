<?php
/**
 * Connects to the database and prepares the statement.
 * @param $pdo the handler to the PDO object.
 * @param $sql the query to prepare.
 * @return the handler to the statement object to work with the database.
 */
function connectToDb(&$pdo, $sql) {
    $dbname = 'homestead';//'cs1242395';
    $dbuser = 'homestead';//'CS1242395';
    $dbpassword = 'secret';//'harsioco';
    $host = 'localhost';//'waldo2.dawsoncollege.qc.ca';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpassword);
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo -> prepare($sql);
    
    return $stmt;
}
