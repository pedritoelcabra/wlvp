<?php include("header.php");?>
<?php if($loged==TRUE){?>
<!---PAGE CONTENT WHEN LOGED-->
<?php
$game_id=$_GET["game_id"];
$g_id=array("game_id"=>$game_id);
$last_turn=query_db("games",$g_id,"turn",TRUE)-1;
//db 1 -> API 0
echo "Last turn played was ".(($last_turn)+1)."<br/>";
//db turn 1
//fetch game json
$data=array("GameID"=>$game_id);
$game_data=post_request_data($data, 'game');
//var_dump($game_data);
//fetch players from db
$game_players=query_db("game_players",$g_id,"player_id",FALSE);
$players=array();
foreach($game_players as $player){
	$p=substr(substr($player,2), 0, -2);
	$players[$p]=$player;
}

//check turn
//echo "<br/>".$last_turn."<br/>"; var_dump($game_data["turn".($last_turn)]); echo "<br/>";
if(($game_data["turn".(($last_turn))]=='')){
//db 1 -> API 0
	echo "All turns from this game are recorded<br/>";
//anula el resto -> }elseif($hhh==99){
}else{
	$p_turn=$last_turn+1;
//fetch possitions and record
	$turn_possitions=$game_data["standing".($last_turn+1)];
	$owning=array();
	foreach($turn_possitions as $possition){

			$p_terr_id=$possition["terrID"];
			$p_ownedBy=$players[$possition["ownedBy"]];
			$p_armies=$possition["armies"];
			$owning[$p_terr_id]=$p_ownedBy;
		if($possition["ownedBy"]!="Neutral"){
			condb();
			//game_id	turn	terr_id armies
			$query = "INSERT INTO `Sql571710_2`.`record_possessions` (`ID`, `game_id`, `turn`, `terr_id`, `ownedBy`, `armies`) VALUES (NULL, '".$game_id."', '".$p_turn."', '".$p_terr_id."','".$p_ownedBy."','".$p_armies."')";
			//echo $query;
			$resultado=mysql_query($query);
			mysql_close();
		}
	}
//fetch deployments and record
	$turn_deployments=$game_data["turn".($last_turn)];
//	print_r($turn_deployments);
//	var_dump($players);
	$income=array();
	foreach($game_players as $player){
		$income[$player]=0;
//		echo $player;
	}
//	var_dump($turn_deployments);
	foreach($turn_deployments as $key=>$turn_deployment){
		if(substr($key,0,15)=="GameOrderDeploy"){
		$c_player=$players[$turn_deployment["playerID"]];
//		echo $c_player;
//		echo $turn_deployment["armies"]."<br/>";
		$income[$c_player]+=$turn_deployment["armies"];
		}
	}
//	var_dump($income);
	foreach($game_players as $player){
		$p_income=$income[$player];
//		echo $player."->".$p_income."<br/>";
		condb();
		$query = "INSERT INTO `Sql571710_2`.`record_income` (`ID`, `game_id`, `turn`, `player`, `income`) VALUES (NULL, '".$game_id."', '".$p_turn."', '".$player."','".$p_income."')";
		//echo $query;
		$resultado=mysql_query($query);
//		echo $resultado;
		mysql_close();
	}
	

//fetch movements and record
	$turn_moves=$game_data["turn".($last_turn)];
	foreach($turn_moves as $key=>$turn_move){
	   if(substr($key,0,23)=="GameOrderAttackTransfer"){
		$attacker=$players[$turn_move["playerID"]];
		if($owning[$turn_move["attackTo"]]==""){
		$deffender="Neutral";
		}else{
		$deffender=$owning[$turn_move["attackTo"]];
		}
		//echo $deffender."<br/>";
		$killed_att=$turn_move["attackersKilled"];
		$killed_def=$turn_move["defendingArmiesKilled"];
		$isAttack=$turn_move["isAttack"];
		$isSuccessful=$turn_move["isSuccessful"];
			condb();
			$query = "INSERT INTO `Sql571710_2`.`record_move` (`ID`, `game_id`, `turn`, `attacker`, `deffender`, `killed_att`, `killed_def`, `isAttack`,`isSuccessful`) VALUES (NULL, '".$game_id."', '".$p_turn."', '".$attacker."','".$deffender."','".$killed_att."','".$killed_def."','".$isAttack."','".$isSuccessful."')";
			//echo $query."<br/>";
			$resultado=mysql_query($query);
			//echo $resultado;
			mysql_close();		
	   }
	}

//fetch cards and record

	$Cards=cards();

	foreach($Cards as $C_key => $Card){
		$length=strlen($Card);
		$turn_cards=$game_data["turn".($last_turn)];
		foreach($turn_cards as $key=>$turn_card){
//			echo $length."<br/>";
			if(substr($key,0,$length)==$Card){
				$C_type=$C_key;
				$C_from=$players[$turn_card["playerID"]];
				condb();
				$query = "INSERT INTO `Sql571710_2`.`record_cards` (`ID`, `game_id`, `turn`, `type`, `from`) VALUES (NULL, '".$game_id."', '".$p_turn."', '".$C_type."','".$C_from."')";
				//echo $query."<br/>";
				$resultado=mysql_query($query);
				//echo $resultado;
				mysql_close();

				
			}
		}
	}




//update turn
$current_turn=$last_turn+2;
	condb();
	$query = "UPDATE `Sql571710_2`.`games` SET `turn` = '".$current_turn."' WHERE `games`.`game_id` =".$game_id;
	$resultado=mysql_query($query);
	//echo $query;
	mysql_close();
	echo "Game advanced to turn ".($current_turn)."<br/>";
}


?>
<!---END OF PAGE CONTENT WHEN LOGED-->
<?php }?>
<?php include("footer.php");?>
