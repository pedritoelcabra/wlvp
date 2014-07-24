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

if(isset($_GET["turn"])){
    $turn = $_GET["turn"];
}else{
    $turn = "";
}

$g_id=array("game_id"=>$game_id);
$last_turn = (query_db("games",$g_id,"turn",TRUE)-1);
if($turn==""){$turn=$last_turn;}

if($last_turn < 0){
    header("Location: error.php?err=No turns recorded for this game yet");
    exit();
}

echo "<table><tr>";
for($i=1; $i<=$last_turn; $i++){
    ?>
    <td><a href="?game_id=<?php echo $game_id;?>&turn=<?php echo $i;?>">Turn <?php echo $i;?></a></td>
    <?php 
} ?>
</tr></table>
<table>
<tr><td>Player</td><td>Territories</td><td>Armies</td></tr>
<?php
$game_players=query_db("game_players",$g_id,"player_id",FALSE);
foreach($game_players as $player){
    $p_key=array('player_id'=>$player);
    $player_name = query_db("game_players",$p_key,"player_name",FALSE);
    $player_name = $player_name[0];
    $t_search=array(
                    "game_id"=>$game_id,
                    "turn" => $turn,
                    "ownedBy"=> $player
                    );
    //	var_dump($t_search);
    $territories = query_db("record_possessions",$t_search,'armies',FALSE);
    //	var_dump($territories);
    $t=0;
    $a=0;
    foreach($territories as $armies){
    //		echo $armies;
        $t++;
        $a+=$armies;
    }
    echo "<tr><td>".$player_name."</td><td>".$t."</td><td>".$a."</td></tr>";
}
echo "</table>";

//<!---END OF PAGE CONTENT WHEN LOGED-->

include("footer.php");

