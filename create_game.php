<?php
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_USER)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}

//<!---PAGE CONTENT WHEN LOGED-->
if(!isset($_POST['mail'])){
    $data = json_decode(file_get_contents("api.json"), true);   
    $players = $data['players'];
}else{
    $data['hostEmail'] = $_POST['mail'];
    $data['hostAPIToken'] = $_POST['token'];
    $data['templateID'] = $_POST['template'];
    $data['gameName'] = $_POST['game_name'];
    $data['personalMessage'] = $_POST['description'];
    $player_names = array();
    $players = array();
    foreach ($_POST as $key => $val){
        if(substr($key, 0, 6) == "player"){
            $id = substr($key, 6, 999);
            $player = array();
            $player['token'] = $val;
            $players[$id] = $player;
        }
    }
    foreach ($_POST as $key => $val){
        if(substr($key, 0, 6) == "plteam"){
            $id = substr($key, 6, 999);
            $players[$id]['team'] = $val;
        }
    }
    foreach ($_POST as $key => $val){
        if(substr($key, 0, 6) == "plname"){
            $id = substr($key, 6, 999);
            $token = $players[$id]['token'];
            $player_names[$token] = $val;
        }
    }
    $data['players'] = $players;
    $action = substr($_POST['action'], 0, 5);
    switch ($action){
        case "Add p": 
            $count = $_POST['count'];
            if((count($players) + $count) > 40){
                $count = 40 - count($players);
            }
            $new = array( "token" => "token", "team" => "None");
            for($i = 0; $i < $count; $i++){
                $players[] = $new;
            }
            break;
        case "Remov":
            $rid = substr($_POST['action'], 13) - 1;
            $players_old = $players;
            $players = array();
            foreach ($players_old as $pid => $player) {
                if($pid == $rid){
                    continue;
                }
                $players[] = $player;
            }
            break;
        case "Confi":
            foreach ($players as $player){
                $token = $player['token'];
                if($player_names[$token] != "unconfirmed"){
                    echo "skipped $token";
                    continue;
                }
                $name = "";
                $data_token = array("Token" => $token);
                $player_data = post_request_data($data_token, 'player', FALSE);
                if($player_data == FALSE){
                    $name = "Incorrect token";
                }else{
                    $name = $player_data["name"];
                }
                $player_names[$token] = $name;
            }
            break;
        case "Creat":
            $json_data = json_encode($data);
            $result = json_decode(send_data($json_data));
            if(isset( $result->gameID )){
                $game_id = $result->gameID;
                echo "Game created successfully! Game ID: $game_id";
            }elseif ( isset($result->error )) {
                $err = $result->error;
                echo "Game creation failed! Error: $err";
            }else{
                echo 'Unknown error!</br></br>';
                echo $json_data;                
            }
            echo "</br>";
            ?>

            <br /><br /><br /><a href="manage_games.php">Games</a>
            <br /><a href="index.php">Index</a>
            <?php

            include("footer.php");
            exit();
            break;
        default : break;
    }

}

if(!isset($player_names)){
    $player_names = array();
}
foreach ($players as $player){
    if(!isset($player_names[$player['token']])){
        $player_names[$player['token']] = "unconfirmed";
    }
}

echo "On this page you can create a new game through the Warlight Create Game API. This is currently the only option to</br>"
. "create games in a way that we can later access their data for the scoring system.</br>"
. "<b>Games created in this way cannot be modified later!</b></br>"
. "Make sure all the player you invite are going to accept the game as you won't be able to remove them once the game is created.</br></br>"
. "Note: Only Warlight members can make use of this API.</br></br>"
. "For more info on the API check the "
. "<a href=\"http://wiki.warlight.net/index.php/Create_game_API\">Warlight Wiki</a> page</br></br>";

?>
<form name=create_game action=create_game.php method=post>
    Your e-mail:</br> 
    <INPUT TYPE=text name=mail value="<?php echo "{$data['hostEmail']}"; ?>"></br></br>
    Your API token:(<a href="http://warlight.net/API/GetAPIToken">Can be found here:</a>)</br> 
    <INPUT TYPE=text name=token value="<?php echo "{$data['hostAPIToken']}"; ?>"></br></br>
    The template ID - using a template is highly recommended!:</br> 
    <INPUT TYPE=text name=template value="<?php echo "{$data['templateID']}"; ?>"></br></br>
    Game name:</br> 
    <INPUT TYPE=text name=game_name value="<?php echo "{$data['gameName']}"; ?>"></br></br>
    Game description (max 1024 characters):</br> 
    <textarea name="description" style="width:550px;height:150px;"><?php echo "{$data['personalMessage']}"; ?></textarea></br></br>
    <?php
    echo "Player tokens are the number in the player profile link on Warlight</br>";
    echo "Keep in mind that every feature and setting from the template must be usable by all invited players.</br>";
    echo "For no teams use 'None' in each case. For teams use '0','1','2' etc.</br></br>";
    $counter = 0;
    ?>
    </br><?php echo count($players); ?> players <INPUT TYPE=text name=count style="width: 50px;" value="1">
    <input name=action type=submit value="Add players"></br></br>
    <?php
    foreach ($players as $player){
        $name = $player_names[$player['token']];
        echo "Player: $name "; ?>
        <INPUT TYPE=text name="<?php echo "player" . $counter; ?>" style="width: 150px;" 
               value="<?php echo "{$player['token']}"; ?>"> Team 
        <INPUT TYPE=text name="<?php echo "plteam" . $counter; ?>" style="width: 50px;" 
               value="<?php echo "{$player['team']}"; ?>">
        <INPUT TYPE=hidden name="<?php echo "plname" . $counter; ?>" value="<?php echo "$name"; ?>">
        <input name=action type=submit value="Remove player <?php echo $counter + 1; ?>"></br>
        <?php
        $counter++;
    }
    ?>
    </br><input name=action type=submit value="Confirm players"></br></br>
    <h2>Make sure all information is correct and all players are confirmed before sending the information!</h2></br>
    </br><input name=action type=submit value="Create Game"></br></br>
</br></br></form>

    
<?php
function send_data($data){
    $ch = curl_init('http://warlight.net/API/CreateGame');                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data))                                                                       
    );                                                                                                                   

    $result = curl_exec($ch);
    return $result;
}

//<!---END OF PAGE CONTENT WHEN LOGED-->
?>

<br /><br /><br /><a href="manage_games.php">Games</a>
<br /><a href="index.php">Index</a>
<?php

include("footer.php");