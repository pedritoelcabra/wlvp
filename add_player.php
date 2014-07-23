<?php 
include("header.php");

if($loged==TRUE){
    ?>
    <!---PAGE CONTENT WHEN LOGED-->
    <p>Add player</p>
    <form action="add_player.php" method="post">
    Your Email: <input type="text" name="mail"><br>
    Warlight ID: <input type="text" name="wl_id"><br>
    Password: <input type="text" name="pwd"><br>
    <input type="Submit" name="action" value="add_player">
    </form>

    <?php 
    if(isset($_POST["action"])){
        $action = $_POST["action"];
    }else{
        $action = "";
    }
    if(($action=="add_player")&&($_POST["wl_id"]!='')&&($_POST["pwd"]!='')&&($_POST["mail"]!='')) {
        $wl_id=$_POST["wl_id"];
        //	echo $wl_id;
        $pwd=md5($_POST["pwd"]);
        $mail=$_POST["mail"];
        $data=array("Token"=>$wl_id);
        $player_data=post_request_data($data, 'player');
        if($player_data==FALSE){
            echo "Incorrect Warlight ID";
	}else{

            //	var_dump($player_data);
            $name=htmlspecialchars($player_data["name"]);
            $member=$player_data["isMember"];
            $color=$player_data["color"];
            $headline=htmlspecialchars($player_data["tagline"], ENT_QUOTES);
            $clan=htmlspecialchars($player_data["clan"]);

            if(!check_db_entry("players","wl_id",$wl_id)){
                $query = "INSERT INTO `Sql571710_2`.`players` (`ID`, `mail`, `wl_id`, `pwd`, `role`, `name`, `member`, `color`, `headline`, `clan`, `profile_pic`) VALUES (NULL, '".$mail."','".$wl_id."', '".$pwd."', '9', '".$name."', '".$member."', '".$color."', '".$headline."', '".$clan."', '')";
                //echo $query;
                $resultado = insert_db($query);
                echo $player_data["name"]." has been added as user. Not validated yet";
            }else{
                echo $player_data["name"]." already is a user.";
            }
        }
    }
//<!---END OF PAGE CONTENT WHEN LOGED-->
}
?>
<?php include("footer.php");?>
