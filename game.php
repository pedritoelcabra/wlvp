<?php 
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_USER)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}

//<!---PAGE CONTENT WHEN LOGED-->

if(isset($_GET["game_id"])){
    $game_id=$_GET["game_id"];
}else{
    header("Location: error.php?err=No game ID for displaying score data");
    exit();
}

// get game data
$g_search = array( "game_id" => $game_id );
$game_data = query_db("games", $g_search, "*", FALSE);
$game_name = $game_data['game_name'];
$map_id = $game_data['map_id'];
$last_turn = $game_data['turn'];

if(isset($_GET["turn"])){
    $turn = $_GET["turn"];
}else{
    $turn = $last_turn;
}

if($last_turn < 1){
    header("Location: error.php?err=No turns recorded for this game yet");
    exit();
}

// get conditions data
$c_search = array( "game_id" => $game_id );
$conditions_data = query_db("v_conditions", $c_search, "*", FALSE);
if(isset($conditions_data['turn'])){
    $raw = $conditions_data;
    $conditions_data = array();
    $conditions_data[] = $raw;
}

// extract conditions
$card_values = array();
$territory_values = array();
$army_values = array();
$special_terr_values = array();
foreach ($conditions_data as $cond_data){
    $this_turn = $cond_data['turn'];
    $army_values[$this_turn] = $cond_data['army_val'];
    $territory_values[$this_turn] = $cond_data['terr_val'];
    $card_values[$this_turn] = $cond_data['card_val'];
    $key_terrs = array();
    if($cond_data['key_terrs'] != NULL){
        $key_terrs_arr = explode(",", $cond_data['key_terrs']);
        if(!count($key_terrs_arr)){
            $string = $key_terrs_arr;
            $key_terrs_arr = array();
            $key_terrs_arr[] = $string;
        }
        foreach ($key_terrs_arr as $stored_value){
            $values = explode(":", $stored_value);
            $s_id = $values[0];
            $s_val = $values[1];
            $key_terrs[$s_id] = $s_val;
        }
    }
    $special_terr_values[$this_turn] = $key_terrs;
}

// get territory and army data
$o_search = array( "game_id" => $game_id );
$o_data = query_db("record_possessions", $o_search, "*", FALSE);
if(isset($o_data['turn'])){
    $raw = $o_data;
    $o_data = array();
    $o_data[] = $raw;
}
//process data
$owned_data = array();
for($i = 1; $i <= $last_turn; $i++){
    $owned_data[$i] = array();
}
foreach ($o_data as $turn_o){
    $t = $turn_o['turn'];
    $owned_data[$t][] = $turn_o;
}

// get discarded cards data
$d_search = array( "game_id" => $game_id, "type" => "Discard" );
$card_data = query_db("record_cards", $d_search, "*", FALSE);
if(isset($card_data['turn'])){
    $raw = $card_data;
    $card_data = array();
    $card_data[] = $raw;
}

// get player data
$p_search = array( "game_id" => $game_id );
$player_data = query_db("game_players", $p_search, "*", FALSE);
if(isset($player_data['player_id'])){
    $raw = $player_data;
    $player_data = array();
    $player_data[] = $raw;
}

// calculate scores
$a_factor = 1;
$t_factor = 1;
$c_factor = 1;
$total_scores = array();
$turn_scores = array();
$army_scores = array();
$terr_scores = array();
$card_scores = array();
$special_scores = array();
$special_count = array();
for($i = 1; $i <= $turn; $i++){
    $turn_scores = array();
    $army_scores = array();
    $terr_scores = array();
    $card_scores = array();
    $special_scores = array();
    $special_count = array();
    $a_factor = $army_values[$i];
    $t_factor = $territory_values[$i];
    $c_factor = $card_values[$i];
    foreach ($player_data as $player){
        $player_id = $player['player_id'];
        $a = 0;
        $t = 0;
        $s = 0;
        $sn = 0;
        $c = 0;
        
        // army score and territory/special score
        foreach ($owned_data[$i] as $possession) {
            //var_dump($possession);
            if($possession['ownedBy'] == $player_id){
                $a += $possession['armies'];
                $t ++;
                if(in_array($possession['terr_id'], $special_terr_values[$i])){
                    $s += $special_terr_values[$i][$possession['terr_id']];
                    $sn++;
                }
            }
        }
        $army_scores[$player_id] = $a * $a_factor;
        $terr_scores[$player_id] = $t * $t_factor;
        $special_scores[$player_id] = $s;
        $special_count[$player_id] = $sn;
        
        // card score
        foreach ($card_data as $card){
            if( ($card['turn'] == $i) && ($card['from'] == $player_id) ){
                $c++;
            }
        }
        $card_scores[$player_id] = $c * $c_factor;
        
        $turn_scores[$player_id] = $army_scores[$player_id] + $terr_scores[$player_id] 
                + $special_scores[$player_id] + $card_scores[$player_id];
        if(isset($total_scores[$player_id])){
            $total_scores[$player_id] += $turn_scores[$player_id];
        }else{
            $total_scores[$player_id] = $turn_scores[$player_id];
        }
    }
}

echo "<table><tr>";
for($i=1; $i<=$last_turn; $i++){
    if($i == $turn){
        echo "<td>Turn $i</td>";
    }else{
        echo "<td><a href=\"?game_id=$game_id&turn=$i\">Turn $i</a></td>";
    }
    if(!($i%10)){
        echo "</tr><tr>";
    }
}
echo "</tr></table>";
echo "</br><h2>Standings and scores for game:</br>$game_name</br>Turn $turn</h2></br>";

echo "<table>";
echo "<tr><td>Player</td><td>Territory score</td><td>Army score</td><td>Card score</td>"
. "<td>Special territory score</td><td>Turn score</td><td>Total score</td></tr>";
echo "<tr><td></td><td>$t_factor point(s)</td><td>$a_factor point(s)</td><td>$c_factor point(s)</td>"
        . "<td>(count)</td><td></td><td></td></tr>";

foreach($player_data as $player){
    $player_name = $player['player_name'];
    $player_id = $player['player_id'];
    $t = $terr_scores[$player_id];
    $a = $army_scores[$player_id];
    $c = $card_scores[$player_id];
    $st = $special_scores[$player_id];
    $sc = $special_count[$player_id];
    $turn_s = $turn_scores[$player_id];
    $total_s = $total_scores[$player_id];
    
    echo "<tr><td>$player_name</td><td>$t</td><td>$a</td><td>$c</td><td>$st ($sc)</td>"
            . "<td>$turn_s</td><td>$total_s</td></tr>";
}
echo "</table>";

//<!---END OF PAGE CONTENT WHEN LOGED-->
?>

<br /><br /><br /><a href="manage_games.php">Games</a>
<br /><a href="index.php">Index</a>
<?php

include("footer.php");

