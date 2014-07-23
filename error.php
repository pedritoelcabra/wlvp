<?php
$error = filter_input(INPUT_GET, 'err', $filter = FILTER_SANITIZE_STRING);
 
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
        <br /><a href="index.php">Back</a>
    </body>
</html>