<?php
$error = filter_input(INPUT_GET, 'err', $filter = FILTER_SANITIZE_STRING);
 
switch ($error){
    case 'no_game_data': $error = "Unable to retrieve game data from Warlight!</br> Remember that only games from"
            . " tournaments, from the ladder, or games that have been created through the API can be accessed."; break;
    default : break;
}

if (! $error) {
    $error = 'Oops! An unknown error happened.';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Error</title>
        <link rel="stylesheet" type="text/css" href="default.css" />
    </head>
    <body>
        <p class="error"><?php echo $error; ?></p>  
        <br /><a href="index.php">Back to index</a>
    </body>
</html>