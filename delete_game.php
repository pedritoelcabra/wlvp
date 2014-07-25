<?php
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_ADMIN)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}
//<!---PAGE CONTENT WHEN LOGED-->

// check if we have game ID
if(isset($_GET["game_id"])){
    $game_id=$_GET["game_id"];
}else{
    header("Location: error.php?err=No game ID for deleting");
    exit();
}

$querya = "DELETE FROM `$database`.`games` WHERE game_id = $game_id;";
$result = insert_db($querya);

$queryb = "DELETE FROM `$database`.`game_players` WHERE game_id = $game_id;";
insert_db($queryb);

$queryc = "DELETE FROM `$database`.`record_cards` WHERE game_id = $game_id;";
insert_db($queryc);

$queryd = "DELETE FROM `$database`.`record_income` WHERE game_id = $game_id;";
insert_db($queryd);

$querye = "DELETE FROM `$database`.`record_move` WHERE game_id = $game_id;";
insert_db($querye);

$queryf = "DELETE FROM `$database`.`record_possessions` WHERE game_id = $game_id;";
insert_db($queryf);

$queryg = "DELETE FROM `$database`.`game_settings` WHERE game_id = $game_id;";
insert_db($queryg);

echo "Game number $game_id has been deleted.";
?>
<br /><a href="index.php">Back</a>