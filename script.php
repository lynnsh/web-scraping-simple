<?php
include 'connect.php';
init();

/**
 * Creates MySQL recipes table.
 * Scrapes foodgawker (first 100 pages) to populate this table.
 */
function init() { 
    $max_pages = 100;
    
    $pdo = createTable();
    $stmt = prepareStmt($pdo);
    if(!isset($stmt))
        echo 'Error when connecting to the database.';
    
    else {
        scrape($stmt, $max_pages);       
        echo 'Pages are saved to the database'.PHP_EOL;
    }
    unset($pdo);
}

/**
 * Creates recipes table.
 * @return the handler to the PDO object.
 */
function createTable() {
    try{
        $sql = "drop table if exists recipes; "
             . "create table recipes (id integer primary key AUTO_INCREMENT, "
             . "title varchar(50) default '' not null, "
             . "reference varchar(500) default '' not null, "
             . "description varchar(500) default '' not null, "
             . "username varchar(50) default '' not null, "
             . "views integer default 0 not null);";
        $stmt = connectToDb(&$pdo, $sql);
        $stmt -> execute();
    }
    catch(PDOException $e) {
        echo $e -> getMessage();
    }
    return $pdo;
}

/**
 * Returns the requested page.
 * @param $ch curl handler.
 * @param $page number of the page to scrape.
 * @return the requested page.
 */
function getPage($ch, $page) {
    curl_setopt($ch, CURLOPT_URL, "https://foodgawker.com/page/$page/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($ch);
}

/**
 * Gets recipes from the provided page and saves them to the database.
 * @global $title title of the recipe.
 * @global $description description of the recipe.
 * @global $ref link to the recipe.
 * @global $user username of the user that posted the recipe.
 * @global $views number of views for the recipe.
 * @param $xpath handler to the XPath object to query the page.
 * @param $stmt handler to the statement object to work with the database.
 */
function getRecipesFromPage($xpath, $stmt) {
    global $title, $description, $ref, $user, $views;
    $objects = $xpath -> query("//div[@class='card front']");
    $i = 0;
    foreach($objects as $obj) {
        $pictures=$xpath->query("//a[@class='picture-link']", $obj) -> item($i);
        $title = $pictures -> getAttribute('title');
        $description = $xpath->query("//section[@class='description']") -> item($i) -> nodeValue;
        $ref = $pictures -> getAttribute('href') ;
        $user = $xpath->query("//a[@class='submitter']") -> item($i) -> nodeValue;
        $views = $xpath->query("//div[@class='gawked']") -> item($i) -> nodeValue;
        saveToDb($stmt);
        $i++;
    }
}

/**
 * Prepares statement and binds the necessary parameters.
 * @global $title title of the recipe.
 * @global $description description of the recipe.
 * @global $ref link to the recipe.
 * @global $user username of the user that posted the recipe.
 * @global $views number of views for the recipe.
 * @param $pdo the handler to the PDO object.
 * @return the handler to the statement object to work with the database.
 */
function prepareStmt($pdo) {
    global $title, $description, $ref, $user, $views;
    if(isset($pdo)) {
        try {
            $sql = "insert into recipes (title, reference, description, "
                    . "username, views) values(?, ?, ?, ?, ?)";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(1, $title);
            $stmt -> bindParam(2, $ref);
            $stmt -> bindParam(3, $description);
            $stmt -> bindParam(4, $user);
            $stmt -> bindParam(5, $views, PDO::PARAM_INT);
        }
        catch(PDOException $e) {
            echo $e -> getMessage();
        }
    }
    return $stmt;
}

/**
 * Saves obtained values to the database.
 * If the record is incomplete, saves it instead to the file.
 * @global $title title of the recipe.
 * @global $description description of the recipe.
 * @global $ref link to the recipe.
 * @global $user username of the user that posted the recipe.
 * @global $views number of views for the recipe.
 * @param $stmt handler to the statement object to work with the database.
 */
function saveToDb($stmt) {
    global $title, $description, $ref, $user, $views;
    //safe to file if the record is incomplete
    if(empty($title) || empty($description) || empty($ref) || 
       empty($user) || empty($views)) {
        $res = fopen('errors.txt', 'a');
        fwrite($res, "title: $title,\r\ndesc: $description,\r\nlink: $ref,\r\n"
                . "username: $user,\r\nviews: $views\r\n\r\n");
        fclose($res);
    }
    //save to db
    else {       
        try {
            $stmt -> execute();
        }
        catch(PDOException $e) {
            echo $e -> getMessage();
        }
    }
}

/**
 * Requestes pages from foodgawker.com
 * @param type $stmt handler to the statement object to work with the database.
 * @param type $max_pages the number of pages to scrape.
 */
function scrape($stmt, $max_pages) {
    $ch = curl_init();      
    $index = 1;
    echo 'Progress: '.PHP_EOL;
    while ($index <= $max_pages) {
        $page = getPage($ch, $index);
        sleep(1);
        if($page) {          
            $html = new DomDocument();
            @$html -> loadHTML($page);
            $xpath = new DOMXpath($html);
            getRecipesFromPage($xpath, $stmt);
        }
        else 
            echo "Error getting page #$index.".PHP_EOL;
        
        if($index % 5 == 0)
            echo "First $index pages scraped.".PHP_EOL;   
        
        $index++;       
    }
    curl_close($ch);
}
