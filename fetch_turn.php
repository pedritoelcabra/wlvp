<?php 
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_ADVANCED)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}

//<!---PAGE CONTENT WHEN LOGED-->

if(isset($_GET["game_id"])){
    $game_id=$_GET["game_id"];
}else{
    header("Location: error.php?err=No game ID for fetching turn data");
    exit();
}

$Cards=cards();

$g_id = array("game_id"=>$game_id);
$current_turn_db = query_db("games",$g_id,"turn",TRUE);

if($current_turn_db == NULL){
    // game is not recorded yet in db
    header("Location: error.php?err=This game is not yet recorded in the database");
    exit();
}

//fetch game json
$data=array("GameID"=>$game_id);
//$game_data=post_request_data($data, 'game', TRUE);
include 'test_data.php';
$game_data = $test_data;

//fetch players from db
$game_players = query_db("game_players",$g_id,"player_id",FALSE);
if(!$game_players){
    header("Location: error.php?err=No players from this game are recorded in the db");
    exit();
}

$players = array();
foreach($game_players as $player){
    $players[$player]=$player;
}

// sort out turn data: warlight lags 1 turn behind in their API, so API turn 19 is turn 20 in game
$api_turn = $game_data['numberOfTurns'];
if($current_turn_db >= ($api_turn + 1)){
    header("Location: error.php?err=All turns from this game are recorded");
    exit();
}

$turn = $current_turn_db - 1;
while($turn < $api_turn){
    
    //fetch possitions and record
    $turn_possitions=$game_data["standing".$turn];
    $owning=array();
    $current_turn_db = $turn + 1;
    foreach($turn_possitions as $possition){

        if($possition["ownedBy"]!="Neutral"){
            $p_terr_id=$possition["terrID"];
            $p_ownedBy=$players[$possition["ownedBy"]];
            $p_armies=$possition["armies"];
            $owning[$p_terr_id]=$p_ownedBy;

            $query = "INSERT INTO `$database`.`record_possessions` (`ID`, `game_id`, `turn`, `terr_id`, `ownedBy`, "
                    . "`armies`) VALUES (NULL, '$game_id', '$current_turn_db', '$p_terr_id','$p_ownedBy','$p_armies')";
          
            if(!insert_db($query)){
                header("Location: error.php?err=Could not insert possition ($query)");
                exit();
            }
        }
    }
    
    
    
    //fetch deployments and record
    $turn_deployments = $game_data["turn".$turn];
    record_deploy($turn_deployments, $game_players, $players, $game_id, $turn);
    
    //fetch movements and record
    $turn_moves=$game_data["turn".$turn];
    record_moves($turn_moves, $players, $owning, $game_id, $turn);
    
    //fetch cards and record
    $turn_cards = $game_data["turn".$turn];
    record_cards($Cards, $turn_cards, $players, $turn, $game_id);

    //update turn
    $turn++;
    set_turn($game_id, $turn);
}

function record_deploy($turn_deployments, $game_players, $players, $game_id, $turn){
    include 'mysql_config.php';
    $current_turn_db = $turn + 1;
    $income=array();
    foreach($game_players as $player){
        $income[$player]=0;
    }
    foreach($turn_deployments as $key=>$turn_deployment){
        if(substr($key,0,15)=="GameOrderDeploy"){
        $c_player=$players[$turn_deployment["playerID"]];
        $income[$c_player]+=$turn_deployment["armies"];
        }
    }
    foreach($game_players as $player){
        $p_income=$income[$player];
        $query = "INSERT INTO `$database`.`record_income` (`ID`, `game_id`, `turn`, `player`, `income`) "
                . "VALUES (NULL, '$game_id', '$current_turn_db', '$player','$p_income')";
        if(!insert_db($query)){
            header("Location: error.php?err=Could not insert incomes ($query)");
            exit();
        }
    }
}

function record_moves($turn_moves, $players, $owning, $game_id, $turn){
    include 'mysql_config.php';
    $current_turn_db = $turn + 1;
    foreach($turn_moves as $key=>$turn_move){
        if(substr($key,0,23)=="GameOrderAttackTransfer"){
            $attacker = $players[$turn_move["playerID"]];
            if($owning[$turn_move["attackTo"]]==""){
                $deffender="Neutral";
            }else{
                $deffender=$owning[$turn_move["attackTo"]];
            }
            $killed_att=$turn_move["attackersKilled"];
            $killed_def=$turn_move["defendingArmiesKilled"];
            $isAttack=$turn_move["isAttack"];
            $isSuccessful=$turn_move["isSuccessful"];

            $query = "INSERT INTO `$database`.`record_move` (`ID`, `game_id`, `turn`, `attacker`, `deffender`, "
                    . "`killed_att`, `killed_def`, `isAttack`,`isSuccessful`) VALUES (NULL, '$game_id', '$current_turn_db', "
                    . "'$attacker','$deffender','$killed_att','$killed_def','$isAttack','$isSuccessful')";
            
            if(!insert_db($query)){
                header("Location: error.php?err=Could not insert moves ($query)");
                exit();
            }
        }
    }
}

function record_cards($Cards, $turn_cards, $players, $turn, $game_id){
    include 'mysql_config.php';
    $current_turn_db = $turn + 1;
    foreach($Cards as $C_key => $Card){
        $length = strlen($Card);
        foreach($turn_cards as $key=>$turn_card){
            if(substr($key,0,$length)==$Card){

                $C_type = $C_key;
                $C_from = $players[$turn_card["playerID"]];

                $query = "INSERT INTO `$database`.`record_cards` (`ID`, `game_id`, `turn`, `type`, `from`) VALUES "
                        . "(NULL, '$game_id', '$current_turn_db', '$C_type','$C_from')";
               
                if(!insert_db($query)){
                    header("Location: error.php?err=Could not insert cards ($query)");
                    exit();
                }
            }
        }
    }
}

function set_turn($game_id, $turn){
    include 'mysql_config.php';
    $current_turn_db = $turn + 1;
    $query = "UPDATE `$database`.`games` SET `turn` = '$current_turn_db' WHERE `game_id` = $game_id";
    if(!insert_db($query)){
        header("Location: error.php?err=Could not update turn ($query)");
        exit();
    }
}

//<!---END OF PAGE CONTENT WHEN LOGED-->

include("footer.php");
