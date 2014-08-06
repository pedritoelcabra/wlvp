<?php 
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_ADVANCED)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}

?>
<!---PAGE CONTENT WHEN LOGED-->
<p>Add map</p>
<form action="add_map.php" method="get">
Game ID: <input type="text" name="game_id"><br>
(You must enter the ID of a finished game from a tournament with the desired map) <br/>
<b>Loading the map may take a few seconds!!</b> <br/>
<input type="Submit" name="action" value="add">
</form>
<?php 
if(isset($_GET["action"])){
    $action=$_GET["action"];
}else{
    $action = "";
}

if(isset($_GET["game_id"])) {
    $game_id = $_GET["game_id"];
    if($action == "add"){
        $data = array("GameID"=>$game_id);
        $game_data = post_request_data($data, 'game', TRUE);
        if($game_data==FALSE){
            header("Location: error.php?err=no_game_data");
            exit();
        }else{
            if(!isset($game_data["map"])){
                header("Location: error.php?err=Game data contains no map data! Maybe this game is not finished?");
                exit();
            }
            $map_details = $game_data["map"];
            $wl_id = $map_details["id"];
            $name = escape_str($map_details["name"]);
            $territories = $map_details["territories"];
            $bonuses = $map_details["bonuses"];
            if(!check_db_entry("maps","wl_id",$wl_id)){
                $n_territories = 0;
                foreach($territories as $territory){
                    $t_name = escape_str($territory["name"]);
                    $t_id = $territory["id"];

                    $query = "INSERT INTO `$database`.`territories` (`ID`, `wl_id`, `name`, `map_id`) VALUES "
                            . "(NULL, '$t_id', '$t_name', '$wl_id')";
                    if (insert_db($query)){
                        $n_territories++;
                    }else{
                        if(!DEBUG){$query = "";}
                        header("Location: error.php?err=An error occurred while inserting territories into the database! "
                                . "($query)");
                        exit();
                    }
                }
                foreach($bonuses as $bonus){
                    $b_name = escape_str($bonus["name"]);
                    $b_id = $bonus["id"];

                    $query = "INSERT INTO `$database`.`bonuses` (`ID`, `wl_id`, `name`, `map_id`) VALUES "
                            . "(NULL, '$b_id', '$b_name', '$wl_id')";
                    if (!insert_db($query)){
                        if(!DEBUG){$query = "";}
                        header("Location: error.php?err=An error occurred while inserting bonuses into the database! "
                                . "($query)");
                        exit();
                    }
                }
                
                $query = "INSERT INTO `$database`.`maps` (`ID`, `wl_id`, `name`, `n_territories`, `pic`) VALUES "
                        . "(NULL, '$wl_id', '$name', '$n_territories', '')";

                if(insert_db($query)){
                    echo "The map $name is now available." ;
                }else{
                    if(!DEBUG){$query = "";}
                    header("Location: error.php?err=An error occurred while inserting the map into the database! "
                            . "($query)");
                    exit();
                }
            }else{
                echo "The map $name was already available.";
            }
        }
    }else if($action == "delete"){
        $querya = "DELETE FROM `$database`.`maps` WHERE wl_id = $game_id;";
        $result = insert_db($querya);
        
        $queryb = "DELETE FROM `$database`.`territories` WHERE map_id = $game_id;";
        $result = insert_db($queryb);
        
        $queryc = "DELETE FROM `$database`.`bonuses` WHERE map_id = $game_id;";
        $result = insert_db($queryc);
        
        echo "</br></br>Map deleted!</br>";
    }
}

echo '<table>';
echo '<tr><td><b>Map name</b></td><td><b>Map ID</b></td><td><b>Territories</b></td><td></td></tr>';
$maps = query_db("maps", NULL, "*", FALSE);
if(isset($maps['name'])){
    $raw = $maps;
    $maps = array();
    $maps[] = $raw;
}
if($maps){
    foreach ($maps as $map){
        $name = $map['name'];
        $wl_id = $map['wl_id'];
        $territories = $map['n_territories'];
        echo "<tr><td>$name</td><td>$wl_id</td><td>$territories</td>";
        if($role == ROLE_ADMIN){
            echo "<td><a href=\"add_map.php?game_id=$wl_id&action=delete\">Delete</a></td>";
        }
        echo "</tr>";

    }
}
echo '</table>';

//<!---END OF PAGE CONTENT WHEN LOGED-->
?>

<br /><a href="index.php">Back</a>

<?php include("footer.php");?>