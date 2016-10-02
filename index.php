<?php
include 'form.html.php';
include 'connect.php';
init();

/**
 * Validates user input and queries the database.
 */
function init() {
    if($_SERVER['REQUEST_METHOD'] == 'POST') { 
        if(isset($_POST['users']))
            showUsers();
        else {
            $key = validate();
            if($key)
                showRecipes($key);  
            }
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
            echo "<p><a href='$ref' class='green title'><b >$title</b></a> by <b>$user</b></p>"
                ."<p>$description</p><p><i>Gawked</i>: <span class='green'>$views</span></p></div>";
        }
     }
 }

 
 /**
  * Queries database for the requested keyword.
  * @param $key the key to be compared with a recipe description.
  */
 function showRecipes($key) {
    try{
        $query = "select title, reference, description, username, views "
                . "from recipes where description like ? order by views desc";
        $stmt = connectToDb($pdo, $query);
        $stmt -> bindValue(1, '%'.$key.'%');
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
 
  function showUsers() {
    try{
        $query = "select username, sum(views) as views from recipes group by username "
                . "order by sum(views) desc limit 10";
        $stmt = connectToDb($pdo, $query);
        $stmt -> execute();
        echo "<h3>Results:</h3>";
        $index = 1;
        while($row = $stmt -> fetch()) {           
            $user = $row['username'];
            $views = $row['views'];
            echo "<div class='users'>$index. <span class='blue title'>"
                    . "<b>$user</b></span> with views: <i class='blue'>$views</i></div>";
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
 function validate() { 
    $key = false;     
    if(empty($_POST['key']))
        echo "<p class='error'>Invalid keyword</p>";
    else {
        $key = htmlentities($_POST['key']);
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

