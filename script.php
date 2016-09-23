<?php
init();

function init() {
    $index = 1;
    $ch = curl_init();
    while ($index <= 10) {
        $page = curlSetup($ch, $index);
        sleep(1);

        $html = new DomDocument();
        @$html->loadHTML($page);
        $xpath = new DOMXpath($html);

        getValues($xpath);

        $index++;
    }
    curl_close($ch);
}

function curlSetup($ch, $index) {
    curl_setopt($ch, CURLOPT_URL, "https://foodgawker.com/page/$index/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($ch);
}

function getValues($xpath) {
    $objects = $xpath->query("//div[@class='card front']");
    $i = 0;
    foreach($objects as $obj) {
        $pictures = $xpath->query("//a[@class='picture-link']", $obj) ->item($i);
        $title = $pictures -> getAttribute('title');
        $description = $xpath->query("//section[@class='description']") -> item($i) -> nodeValue;
        $ref = $pictures -> getAttribute('href') ;
        $user = $xpath->query("//a[@class='submitter']") -> item($i) -> nodeValue;
        $views = $xpath->query("//div[@class='gawked']") -> item($i) -> nodeValue;
        //saveToDb($title, $description, $ref, $user, $views);
        echo "<p>$title   $ref    $description    $user    $views</p>";
        $i++;
    }
}

function saveToDb($title, $description, $ref, $user, $views) {
    if(empty($title) || empty($description) || empty($ref) || empty($user) || empty($views)) {
        $res = fopen('errors.txt', 'a');
        fwrite($res, "title: $title, desc: $description, link: $ref, username: $user, views: $views\r\n");
        fclose($res);
    }
    else {
        //save to db
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=test', 'local', 'compsci');
            $sql = "insert into recipes (title, reference, description, username, views)"
                    . " values(?, ?, ?, ?, ?)";
            
        }
        catch(PDOException $e) {
            echo $e -> getMessage();
        }
        finally{
            unset($pdo);
        }
    }
}

