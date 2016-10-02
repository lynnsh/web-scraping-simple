<html>
    <head>
        <link href="styles.css" type="text/css" rel="stylesheet">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <title>Web Scraping Project</title>
    </head> 
    <body>
        <h2>Search the database!</h2>
        <form method="POST" action="" class="well">  
            <div class="top"><input class="btn btn-info" type="submit" 
                        value="Top 10 most gawked submitters!" name="users"/></div>
            <div class="row">
                <div class="col-md-4 lbl"><label for="key">Enter the keyword:</label></div>
                    <div class="col-md-4"><input id="key" type='text' name='key' class="form-control"
                                value="<?php if (isset($_POST['key'])) echo $_POST['key']; ?>"/></div>
            
            <div class="col-md-4 btn-submit"><input class="btn btn-default" type="submit" value="Submit" name="submit"/></div>
            </div>
