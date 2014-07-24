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
<form action="add_map.php" method="post">
Game ID: <input type="text" name="game_id"><br>
(you must add a finished game from a tournament with the desired map) <br/>
<input type="Submit" name="action" value="add_map">
</form>
<?php 
if(isset($_POST["action"])){
    $action=$_POST["action"];
}else{
    $action = "";
}

if(($action=="add_map")&&($_POST["game_id"]!='')) {

    $game_id=$_POST["game_id"];
    $data=array("GameID"=>$game_id);
    $game_data=post_request_data($data, 'game', FALSE);
    if($game_data==FALSE){
        echo "Incorrect game ID";
    }else{
        $map_details = $game_data["map"];
        $wl_id = $map_details["id"];
        $name = $map_details["name"];
        $territories = $map_details["territories"];
        if(!check_db_entry("maps","wl_id",$wl_id)){
            $n_territories=0;
            foreach($territories as $territory){
                $t_name=$territory["name"];
                $t_id=$territory["id"];

                $query = "INSERT INTO `$database`.`territories` (`ID`, `wl_id`, `name`, `map_id`) VALUES (NULL, '".$t_id."', '".$t_name."', '".$wl_id."')";
                if (insert_db($query)){
                    $n_territories++;
                }
            }
            if($n_territories != count($territories)){
                echo "An error occurred while inserting territories into the database";
            }else{
                $query = "INSERT INTO `$database`.`maps` (`ID`, `wl_id`, `name`, `n_territories`, `pic`) VALUES (NULL, '".$wl_id."', '".$name."', '".$n_territories."', '')";

                if(insert_db($query)){
                    echo "The map $name is now available." ;
                }else{
                    echo "An error occurred while inserting the map into the database";
                }
            }
        }else{
            echo "The map $name was already available.";
        }
    }
}
//<!---END OF PAGE CONTENT WHEN LOGED-->
include("footer.php");?>
