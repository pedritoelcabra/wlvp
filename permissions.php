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
    // get game data
    $p_search = array( "game_id" => $game_id );
    $game_data = query_db("games", $p_search, "*", FALSE);
    $game_name = $game_data['game_name'];
    $access = $game_data['access'];
    if($access == NULL){
        $authorized = array();
    }else{
        $authorized = explode(":", $access);
    }
    if(isset($_GET['action'])){
        $action = substr($_GET['action'], 0, 3);
        switch ($action){
            case "Add":
                if(isset($_GET["player_id"])){
                    $add_id = $_GET["player_id"];
                    if(is_numeric($add_id)){
                        if(in_array($add_id, $authorized)){
                            echo "This player is already authorized.";
                        }else{
                            $player_search = array( "wl_id" => $add_id );
                            $player_role = query_db("players", $player_search, "role", TRUE);
                            if(!$player_role){
                                echo "This ID is not registered in our system.";
                            }
                            if($player_role == 1){
                                echo "Admins are always authorized.";
                            }else if($player_role > 1){
                                $save_data_arr = $authorized;
                                $save_data_arr[] = $add_id;
                                $access = implode(":", $save_data_arr);
                                $query = "UPDATE `$database`.`games` SET `access` = '$access' WHERE `game_id` = $game_id";
                                if(!insert_db($query)){
                                    echo "Error while inserting new authorization.";
                                }else{
                                    $authorized[] = $add_id;
                                }
                            }
                        }
                    }else{
                        echo "Invalid Player ID";
                    }
                }
                break;
            case "Rem":
                $rem_id = substr($_GET['action'], 7, 999);
                if(is_numeric($rem_id)){
                    if(in_array($rem_id, $authorized)){
                        $save_data_arr = array();
                        foreach ($authorized as $auth){
                            if($auth != $rem_id){
                                $save_data_arr[] = $auth;
                            }
                        }
                        $access = implode(":", $save_data_arr);
                        $query = "UPDATE `$database`.`games` SET `access` = '$access' WHERE `game_id` = $game_id";
                        if(!insert_db($query)){
                            echo "Error while removing authorization.";
                        }else{
                            $authorized = $save_data_arr;
                        }
                    }
                }else{
                    echo "Invalid Player ID";
                }
                break;
            default :
                break;
        }
    }
}else{
    if(isset($_POST["game_id"])){
        $game_id = $_POST["game_id"];
    }else{
        header("Location: error.php?err=No game ID for setting permissions");
        exit();
    }
}

echo    "<h1>$game_name</h1></br>";
echo    "<u>Users authorized to modify victory conditions:</u></br></br>";

if($access == NULL){
    echo "Only page admins</br></br>";
}else{
    echo "<form name=remove action=permissions.php method=get>";
    foreach ($authorized as $player_id){
        if($player_id == ""){
            continue;
        }
        $player_search = array( "wl_id" => $player_id );
        $player_name = query_db("players", $player_search, "name", TRUE);
        echo "$player_name <input name=action type=submit value=\"Remove $player_id\"></br>";
    }
    echo "<INPUT TYPE=hidden name=\"game_id\" value=\"$game_id\">";
    echo "</form>";
}
?>
<form name=add action=permissions.php method=get>
	<b>Authorize a new user</b><br/>
	Warlight ID:
	<INPUT TYPE=text name=player_id>
        <INPUT TYPE=hidden name="game_id" value="<?php echo $game_id; ?>">
	<input name=action type=submit value="Add">
</form>


<br /><br /><br /><a href="manage_games.php">Games</a>
<br /><a href="index.php">Index</a>

<?php include("footer.php");
