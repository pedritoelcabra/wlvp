<?php 
include("header.php");
include 'mysql_config.php';
// <!---PAGE CONTENT WHEN LOGED-->

if(isset($_POST["action"])){
    $action = $_POST["action"];
}else{
    $action = "";
    ?>
    <p>Create user account</p>
    <form action="add_player.php" method="post">
    Email: <input type="text" name="mail"><br>
    Warlight ID: <input type="text" name="wl_id"><br>
    Password: <input type="text" name="pwd"><br>
    <input type="Submit" name="action" value="Create">
    </form>
    <?php 
}
if(($action=="Create")&&($_POST["wl_id"]!='')&&($_POST["pwd"]!='')&&($_POST["mail"]!='')) {
    $wl_id=$_POST["wl_id"];
    //	echo $wl_id;
    $pwd = md5($_POST["pwd"]);
    $mail = $_POST["mail"];
    $data = array("Token"=>$wl_id);
    $player_data = post_request_data($data, 'player', FALSE);
    if($player_data == FALSE){
        echo "Incorrect Warlight ID";
    }else{

        //	var_dump($player_data);
        $name = htmlspecialchars($player_data["name"]);
        $member = $player_data["isMember"];
        $color = $player_data["color"];
        $headline = htmlspecialchars($player_data["tagline"], ENT_QUOTES);
        $clan = htmlspecialchars($player_data["clan"]);

        if( (!check_db_entry("players","wl_id",$wl_id)) && (!check_db_entry("players","mail",$mail)) ){
            include 'mysql_config.php';
            $query = "INSERT INTO `$database`.`players` (`ID`, `mail`, `wl_id`, `pwd`, `role`, `name`, `member`, `color`, `headline`, `clan`, `profile_pic`) VALUES (NULL, '".$mail."','".$wl_id."', '".$pwd."', '8', '".$name."', '".$member."', '".$color."', '".$headline."', '".$clan."', '')";
            
            if(insert_db($query)){
                echo $player_data["name"]." has been added as user. Your account is not validated yet, you need to contact an admin of {rp} before you can access the system.";
            }else{
                echo "Error while inserting player into the database.";
            }                
        }else{
            echo "There is already a user with this ID or Email.";
        }
    }
}
//<!---END OF PAGE CONTENT WHEN LOGED-->
include("footer.php");
