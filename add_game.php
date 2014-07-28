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
//$game_data = post_request_data($data, 'game', FALSE);
$game_data = json_decode(file_get_contents("test_data.json"), true);

if(!$game_data){
    header("Location: error.php?err=Unable to retrieve game data from Warlight! (game ID: $game_id)");
    exit();
}

// check if we have the map in the database
$map_data = $game_data['map'];
$map_id = $map_data['id'];
if(!check_db_entry("maps", "wl_id", $map_id)){
    header("Location: error.php?err=The map for this game is not in our database.");
    exit();
}

// we create a new game in the database
$turn = 1;
$game_name = $game_data['name'];
$query = "INSERT INTO `$database`.`games` (`game_id`, `turn`, `game_name`, `finished`, `map_id`) "
        . "VALUES ('$game_id', '$turn', '$game_name', '0', '$map_id')";
if(!insert_db($query)){
    header("Location: error.php?err=Could not insert game into database ($query)");
    exit();
}

// create a victory conditions entry
$t_search = array("game_id" => $game_id, "turn" => 1);
if(!query_db("v_conditions",$t_search,'*',FALSE)){
    $query = "INSERT INTO `$database`.`v_conditions` (`game_id`, `turn`) VALUES ('$game_id', '1')";
    if(!insert_db($query)){
        header("Location: error.php?err=Could not insert conditions into database ($query)");
        exit();
    }
}

// we create all the players in the database
$players = $game_data['players'];
foreach ($players as $player){
    $player_id = $player['id'];
    $player_name = $player['name'];
    if(preg_match('/[()\'"]/', $player_name)){
        $player_name = "player" . $player_id;
    }
    $query = "INSERT INTO `$database`.`game_players` (`game_id`, `player_id`, `player_name`) VALUES ('$game_id', '$player_id', '$player_name')";
    if(!insert_db($query)){
        header("Location: error.php?err=Could not insert player into database ($query)");
        exit();
    }
}

echo "The game '$game_name' has been succesfully inserted into the database." ;
?>

<br /><a href="manage_games.php">Back</a>


<?php include("footer.php");?>