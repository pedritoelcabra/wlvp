<?php
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_USER)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}
//<!---PAGE CONTENT WHEN LOGED-->

$show_ooc = TRUE;
$filter_player = 1;

if(isset($_GET["game_id"])){
    $game_id = $_GET["game_id"];

    // get chat data from warlight
    $data = array("GameID" => $game_id);
    $game_data = post_request_data($data, 'chat', FALSE);
    
    $chat_edited = array();
    foreach ($game_data['chat'] as $entry) {
        $new_entry = $entry;
        $found = true;
        while ($found){
            $pos = strpos($new_entry['message'], "<a");
            if($pos !== false){
                $length = strpos($new_entry['message'], ">", $pos) - $pos;
                $old_entry = substr($new_entry['message'], $pos, $length + 1);
                $type = substr($new_entry['message'], $pos + 3, 1);
                $type = ($type == "b" ? "bonus" : "territory");
                $value = substr($new_entry['message'], $pos + 5, $length - 5);
                $replace = $type . " " . $value;
                $new_entry['message'] = str_replace($old_entry, $replace, $new_entry['message']);
            }else{
                $found = false;
            }
        }
        $chat_edited[] = $new_entry;
    }
    $game_data['chat'] = $chat_edited;
    
    $pass_on_data = htmlspecialchars(serialize($game_data));
}else{
    if(isset($_POST["game_id"])){
        $game_id = $_POST["game_id"];
        $game_data = unserialize($_POST["game_data"]);
        $pass_on_data = htmlspecialchars(serialize($game_data));
        if(isset($_POST["OOC"])){
            $show_ooc = FALSE;
        }
        if(isset($_POST["show_chat"])){
            $filter_player = $_POST["show_chat"];
        }
    }else{
        header("Location: error.php?err=No game ID for fetching chat data");
        exit();
    }
}

if(!$game_data){
    header("Location: error.php?err=Unable to retrieve chat data from Warlight! (game ID: $game_id)");
    exit();
}

$chat_data = $game_data['chat'];
// remove OOC
if(!$show_ooc){
    $chat_edited = array();
    foreach ($chat_data as $chat_entry) {
        $msg = $chat_entry['message'];
        $has_ooc = strpos($msg, "[{");
        if($has_ooc === FALSE){
            $has_ooc = strpos($msg, "((");
        }
        if($has_ooc !== FALSE){
            continue;
        }
        $chat_edited[] = $chat_entry;
    }
    $chat_data = $chat_edited;
}

$player_data = $game_data['players'];
$player_names = array();
foreach($player_data as $player){
    $player_short = substr(substr($player['id'], 2, 99), 0, -2);
    $player_names[$player_short] = $player['name'];
}
?>
<br /><a href="manage_games.php">Back to Games</a><br /><br />
<form name=update action=chat.php method=post>
    <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
    <input type="hidden" name="game_data" value="<?php echo $pass_on_data; ?>">
    Hide OOC chat? (out of character)
    <input type="checkbox" name="OOC" value="show_ooc" <?php if(!$show_ooc)echo "checked"; ?>></br>
    Show chat messages from:
    <select name="show_chat"> 
        <option value="1">Everyone</option>
        <?php
        foreach ($player_data as $player){
            if($player['state'] == "Playing"){
                $id = $player['id'];
                $name = $player['name'];
                $selected = "";
                if($filter_player == $id){
                    $selected = "selected=\"selected\"";
                }
                echo "<option $selected value=\"$id\">$name</option>";
            }
        }
        ?>
    </select>
    <input name=action type=submit value="Update">
</form>
<?php
echo "<table>";
$turn = "";
foreach ($chat_data as $chat_line){
    $id = $chat_line['playerID'];
    if($filter_player > 1){
        $filter = substr(substr($filter_player, 2, 99), 0, -2);
        if ($id != $filter){
            continue;
        }
    }
    $name = $player_names[$id];
    echo "<tr class=\"iv\">";
    $date = $chat_line['date'];
    if($turn != $chat_line['turn']){
        $turn = $chat_line['turn'];
        echo "</tr><tr class=\"iv\"><td></td><td class=\"iv\">"
        . "</td><td class=\"iv\"><h2>Turn $turn</h2></td></tr><tr class=\"iv\">";
    }
    $msg = $chat_line['message'];
    echo "<td>$name</td><td class=\"iv\">$date</td><td class=\"iv\">$msg</td>";
    echo "</tr>";
}
echo "</table>";
?>

<br /><br /><br /><a href="manage_games.php">Back to Games</a>
<br /><a href="index.php">Index</a>

<?php 
include("footer.php");