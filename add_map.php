<?php include("header.php");?>
<?php if($loged==TRUE){?>
<!---PAGE CONTENT WHEN LOGED-->
<p>Add map</p>
<form action="add_map.php" method="post">
Game ID: <input type="text" name="game_id"><br>
(you must add a finnished game from a tournment with the desired map)
<input type="Submit" name="action" value="add_map">
</form>
<?php 
  $action=$_POST["action"];
  if(($action=="add_map")&&($_POST["game_id"]!='')) {
	$game_id=$_POST["game_id"];
	$data=array("GameID"=>$game_id);
	$game_data=post_request_data($data, 'game');
      if($game_data==FALSE){
	echo "Incorrect game ID";
      }else{
	$map_details=$game_data["map"];
	$wl_id= $map_details["id"];
	$name = $map_details["name"];
	$territories=$map_details["territories"];
	if(!check_db_entry("maps","wl_id",$wl_id)){
		$n_territories=0;
	    foreach($territories as $territory){
		$t_name=$territory["name"];
		$t_id=$territory["id"];
		condb();
		$query = "INSERT INTO `Sql571710_2`.`territories` (`ID`, `wl_id`, `name`, `map_id`) VALUES (NULL, '".$t_id."', '".$t_name."', '".$wl_id."')";
		//echo $query;
		$resultado=mysql_query($query);
		mysql_close();
		$n_territories++;	
	    }


		condb();
		$query = "INSERT INTO `Sql571710_2`.`maps` (`ID`, `wl_id`, `name`, `n_territories`, `pic`) VALUES (NULL, '".$wl_id."', '".$name."', '".$n_territories."', '')";
		//echo $query;
		$resultado=mysql_query($query);
		mysql_close();
		echo "The map ".$name." is now available." ;
	}else{
		echo "The map ".$name." was already available.";
	}



      }

  }
?>
<!---END OF PAGE CONTENT WHEN LOGED-->
<?php }?>
<?php include("footer.php");?>
