<?php
include 'form.html.php';
include 'connect.php';
init();

/**
 * Displays the appropriate information depending on user queries.
 */
function init() {
    $display = 10;
    if($_SERVER['REQUEST_METHOD'] == 'POST') { 
        if(isset($_POST['users']))
            showUsers();
        elseif (isset($_POST['key'])) {
            unset($_GET['t']);
            unset($_GET['p']);
            unset($_GET['q']);           
            $key = validKey($_POST['key']);
            showRecipes($key, $display); 
        }
     }
     elseif(isset($_GET['q'])) {
        $key = validKey($_GET['q']);
        showRecipes($key, $display); 
     }
}

/**
 * Works with the appropriate methods to display recipes based on the keyword.
 * @param $key the value to be searched for in the database.
 * @param $display the number of recipes to display on one page.
 */
function showRecipes($key, $display) {
    if($key) {
        if((isset($_GET['t']) && is_numeric($_GET['t'])))
            $pages = htmlentities($_GET['t']);
        else
            $pages = ceil(getNumPages($key)/$display);
        
        if((isset($_GET['p']) && is_numeric($_GET['p'])))
            $current = htmlentities($_GET['p']);
        else
            $current = 0;
        
        getRecipes($key, $current*$display, $display);  
        displayPages($current, $pages, $key);
    }
}
 
 /**
  * Displays results retrieved from the database.
  * @param $stmt handler to the statement object to work with the database.
  */
 function displayResults($stmt) {
     if($stmt -> rowCount() == 0)
            echo "<p>No recipes found for the requested keyword.</p>";
     else {
        echo "<h3>Results:</h3>";
        while($row = $stmt -> fetch()) {
            $title = $row['title'];
            $ref = $row['reference'];
            $description = $row['description'];
            $user = $row['username'];
            $views = $row['views'];
            echo '<div class="box">';
            echo "<p><a href='$ref' class='green title'>"
                ."<b>$title</b></a> by <b>$user</b></p>"
                ."<p>$description</p><p><i>Gawked</i>: "
                . "<span class='green'>$views</span></p></div>";
        }
     }
 }
 
 /**
  * Displays links to other pages if ones are present.
  * @param $current the current page number.
  * @param $pages the total number of pages.
  * @param $key the value to be searched for in the database.
  */
 function displayPages($current, $pages, $key) {
     if($pages > 1) {
         echo '<div class="page"><ul class="pagination">';
         if($current != 0)
             echo "<li><a href='index.php?t=$pages&p=".($current-1)
                 ."&q=$key'><</a></li>";
         
         for ($i = 0; $i < $pages; $i++) {
            if($current == $i)
                echo "<li class='active'><a>".($i+1)."</a></li>";
            else
                echo "<li><a href='index.php?t=$pages&p=$i&q=$key'>".($i+1)
                    ."</a></li>";
         }
         
         if($current != ($pages-1))
             echo "<li><a href='index.php?t=$pages&p=".($current+1)
                 ."&q=$key'>></a></li>";          
     }
 }
 
 /**
  * Returns the total number of pages for the current query.
  * @param $key the value to be searched for in the database.
  * @return the total number of pages for the current query.
  */
 function getNumPages($key) {
     try {
         $sql="select count(*) as number from recipes where description like ?";
         $stmt = connectToDb($pdo, $sql);
         $stmt -> bindValue(1, '%'.$key.'%');
         $stmt -> execute();
         $row = $stmt -> fetch();   
         $pages = $row['number'];
         return $pages;
     }
     catch(PDOException $e) {
        $error = $e -> getMessage();
        echo "<p class='error'>Error: $error</p>";
    }
    finally {
        unset($pdo);
    }
 }

 
 /**
  * Queries database for the requested keyword.
  * @param $key the value to be searched for in the database.
  * @param $start the start index to query in the database.
  * @param $display the number of rows to get from the database.
  */
 function getRecipes($key, $start, $display) {
    try{
        $query = "select title, reference, description, username, views "
                . "from recipes where description like ? "
                . "order by views desc limit ?, ?";
        $stmt = connectToDb($pdo, $query);
        $stmt -> bindValue(1, '%'.$key.'%');
        $stmt -> bindValue(2, $start, PDO::PARAM_INT);
        $stmt -> bindValue(3, $display, PDO::PARAM_INT);
        $stmt -> execute();
        displayResults($stmt);    
    }
    catch(PDOException $e) {
        $error = $e -> getMessage();
        echo "<p class='error'>Error: $error</p>";
    }
    finally {
        unset($pdo);
    }
 }
 
 /**
  * Displays top 10 users (the ones with the most views for
  * their submitted recipes).
  */
  function showUsers() {
    try{
        $query = "select username, sum(views) as views from recipes "
                . "group by username order by sum(views) desc limit 10";
        $stmt = connectToDb($pdo, $query);
        $stmt -> execute();
        echo "<h3>Results:</h3>";
        $index = 1;
        while($row = $stmt -> fetch()) {           
            $user = $row['username'];
            $views = $row['views'];
            echo "<div class='users'>$index. <span class='blue title'>"
               . "<b>$user</b></span> with views: "
               . "<i class='blue'>$views</i></div>";
            $index++;
        }
    }
    catch(PDOException $e) {
        $error = $e -> getMessage();
        echo "<p class='error'>Error: $error</p>";
    }
    finally {
        unset($pdo);
    }
 }
 
 
 /**
  * Validates the key value submitted by the user.
  * @return the key.
  */
 function validKey($value) { 
    $key = false;     
    if(empty($value))
        echo "<p class='error'>Invalid keyword</p>";
    else {
        $key = htmlentities($value);
    }
    return $key;
}

?>

<hr>
</div>
</form>
<footer class="white">All data © FoodGawker, 2016</footer>
</body>
</html>
