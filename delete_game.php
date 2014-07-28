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
    $game_id = $_GET["game_id"];
}else{
    header("Location: error.php?err=No game ID for deleting");
    exit();
}

if(!isset($_GET['delete'])){
    ?>
    <form name=confirm_delete action="delete_game.php" method=get>
	<b>Are you sure you want to delete this game?</b><br/>
        <input type="checkbox" name="conditions" value="delete">Also delete victory conditions</br>
        <input type="hidden"  name="game_id" value="<?php echo $game_id; ?>">
	<input name=delete type=submit value="confirm">
    </form>
    <?php
}else{

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

    if(isset($_GET['conditions'])){
        if($_GET['conditions'] == "delete"){
            $queryh = "DELETE FROM `$database`.`v_conditions` WHERE game_id = $game_id;";
            insert_db($queryh);
        }
    }

    echo "Game number $game_id has been deleted.";
    
}
?>
<br /><a href="manage_games.php">Back</a>

<?php include("footer.php");?>