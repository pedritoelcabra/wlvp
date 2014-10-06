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
    header("Location: error.php?err=No game ID for fetching turn data");
    exit();
}

//check if the game is not already in DB
if(check_db_entry("games","game_id",$game_id)){
    header("Location: error.php?err=This game is already in our database");
    exit();
}

// get game data from Warlight
$data = array("GameID" => $game_id);
$game_data = post_request_data($data, 'game', TRUE);
file_put_contents("test_data.json", json_encode($game_data));
//$game_data = json_decode(file_get_contents("test_data.json"), true);

if(!$game_data){
    header("Location: error.php?err=no_game_data");
    exit();
}

// check if we have the map in the database
if(isset($game_data['map'])){
    $map_data = $game_data['map'];
    $map_id = $map_data['id'];
}else{
    if(isset($_GET["map_id"])){
        $map_id = $_GET["map_id"];
    }else{
        echo "This game has no map data, maybe because it's not finished or because it wasn't created through the API."
        . "</br></br>You can insert the map ID manually or leave the field at 0 to add the game without map data</br></br>"
        . "<b>It has to be the ID shown in the map list in the VPS not the map ID from the Warlight map page</b></br></br>";
        ?>
        <form name=add_game action=add_game.php method=get>
                Map ID:
                <INPUT TYPE=text name=map_id value="0">
                <INPUT TYPE=hidden name=game_id value="<?php echo $game_id; ?>">
                <input name=action type=submit value="Add">
        </form>
        <?php

        echo "<br /><br /><a href=\"manage_games.php\">Back</a>";

        include("footer.php");
        exit();
    }
}
if($map_id != 0){
    if(!check_db_entry("maps", "wl_id", $map_id)){
        header("Location: error.php?err=The map for this game is not in our database.");
        exit();
    }
}

// we create a new game in the database
$turn = 1;
$game_name = $game_data['name'];
$query = "INSERT INTO `$database`.`games` (`game_id`, `turn`, `game_name`, `finished`, `map_id`) "
        . "VALUES ('$game_id', '$turn', '$game_name', '0', '$map_id')";
if(!insert_db($query)){
    if(!DEBUG){$query = "";}
    header("Location: error.php?err=Could not insert game into database ($query)");
    exit();
}

// create a victory conditions entry
$t_search = array("game_id" => $game_id, "turn" => 1);
if(!query_db("v_conditions",$t_search,'*',FALSE)){
    $query = "INSERT INTO `$database`.`v_conditions` (`game_id`, `turn`) VALUES ('$game_id', '1')";
    if(!insert_db($query)){
        if(!DEBUG){$query = "";}
        header("Location: error.php?err=Could not insert conditions into database ($query)");
        exit();
    }
}

// we create all the players in the database
$players = $game_data['players'];
foreach ($players as $player){
    $player_id = $player['id'];
    $player_name = escape_str($player['name']);
    $query = "INSERT INTO `$database`.`game_players` (`game_id`, `player_id`, `player_name`) VALUES ('$game_id', '$player_id', '$player_name')";
    if(!insert_db($query)){
        if(!DEBUG){$query = "";}
        header("Location: error.php?err=Could not insert player into database ($query)");
        exit();
    }
}

echo "The game '$game_name' has been succesfully inserted into the database." ;
?>

<br /><a href="manage_games.php">Back</a>


<?php include("footer.php");?>