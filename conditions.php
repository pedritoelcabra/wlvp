<?php
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_USER)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}
//<!---PAGE CONTENT WHEN LOGED-->
// update database if post is set
if( (isset($_POST['turn'])) && (isset($_POST['game_id'])) ){
    $turn = $_POST['turn'];
    $game_id = $_POST['game_id'];
    if( (!is_numeric($turn)) || (!is_numeric($game_id)) ){
        header("Location: error.php?err=Problem with the post data when updating victory conditions");
        exit();
    }
    $and = "";
    if(!isset($_POST['all'])){
        $and = " AND `turn` = $turn";
    }
    if(isset($_POST['armies'])){
        $armies = $_POST['armies'];
        if(is_numeric($armies)){
            $query = "UPDATE `$database`.`v_conditions` SET `army_val` = '$armies' "
                    . "WHERE `game_id` = $game_id$and";
            if(!insert_db($query)){
                header("Location: error.php?err=Could not update victory conditions($query)");
                exit();
            }            
        }
    }
    if(isset($_POST['terrs'])){
        $terrs = $_POST['terrs'];
        if(is_numeric($terrs)){
            $query = "UPDATE `$database`.`v_conditions` SET `terr_val` = '$terrs' "
                    . "WHERE `game_id` = $game_id$and";
            if(!insert_db($query)){
                header("Location: error.php?err=Could not update victory conditions($query)");
                exit();
            }            
        }
    }
    if(isset($_POST['cards'])){
        $cards = $_POST['cards'];
        if(is_numeric($cards)){
            $query = "UPDATE `$database`.`v_conditions` SET `card_val` = '$cards' "
                    . "WHERE `game_id` = $game_id$and";
            if(!insert_db($query)){
                header("Location: error.php?err=Could not update victory conditions($query)");
                exit();
            }            
        }
    }
    if(isset($_POST['what'])){
        $what = $_POST['what'];
        if($what == "special"){
            $new_value = "";
            foreach ($_POST as $key => $val){
                if(is_numeric($key)){
                    if($val != 1){
                        if($new_value != ""){
                            $new_value .= ",";
                        }
                        $new_value .= $key . ":" . $val;
                    }
                }
            }
            $query = "UPDATE `$database`.`v_conditions` SET `key_terrs` = '$new_value' "
                    . "WHERE `game_id` = $game_id$and";
            if(!insert_db($query)){
                header("Location: error.php?err=Could not update victory conditions($query)");
                exit();
            }            
        }
    }
    header("Location: conditions.php?turn=$turn&game_id=$game_id");
    exit();
}

// check if we have game ID
if(isset($_GET["game_id"])){
    $game_id = $_GET["game_id"];
}else{
    if(isset($_POST["game_id"])){
        $game_id = $_POST["game_id"];
    }else{
        header("Location: error.php?err=No game ID for editing conditions");
        exit();
    }
}

// get game data
$g_search = array( "game_id" => $game_id );
$game_data = query_db("games", $g_search, "*", FALSE);
$game_name = $game_data['game_name'];
$map_id = $game_data['map_id'];

// get conditions data
$c_search = array( "game_id" => $game_id );
$conditions_data = query_db("v_conditions", $c_search, "*", FALSE);
if(isset($conditions_data['turn'])){
    $raw = $conditions_data;
    $conditions_data = array();
    $conditions_data[] = $raw;
}

if(isset($_GET["turn"])){
    $turn = $_GET["turn"];
    if($turn == "add"){
        $highest_turn = 0;
        foreach ($conditions_data as $cond_data){
            if($cond_data['turn'] > $highest_turn){
                $highest_turn = $cond_data['turn'];
            }
        }
        $new_turn = $highest_turn + 1;
        $query = "INSERT INTO `$database`.`v_conditions` (`game_id`, `turn`) VALUES ('$game_id', '$new_turn')";
        insert_db($query);
        header("Location: conditions.php?game_id=$game_id&turn=$new_turn");
        exit();
    }
}else{
    if(isset($_POST['turn'])){
        $turn = $_POST["turn"];
    }else{
        $turn = 1;
    }
}

echo    "<h1>$game_name</h1></br>";

// Links to every turn
$turn_data = "";
$counter = 1;
foreach ($conditions_data as $cond_data){
    if($counter == $turn){
        echo "Turn $counter ";
    }else{
        echo "<a href=\"?game_id=$game_id&turn=$counter\">Turn $counter</a> ";
    }
    if($cond_data['turn'] == $turn){
        $turn_data = $cond_data;
    }
    $counter++;
}
if( ($role == ROLE_ADMIN) && ($game_data['finished'] != "1") ){
    echo "</br><a href=\"?game_id=$game_id&turn=add\">Add turn</a></br></br>";
}

echo    "<h2>Turn $turn</h2></br><h2>General conditions</h2>";

if($turn_data == ""){
    echo 'Error getting victory condition data!';
    exit();
}
$army_val = $turn_data['army_val'];
$terr_val = $turn_data['terr_val'];
$card_val = $turn_data['card_val'];

echo "</br></br>Points for each owned army: $army_val";
if($role <= ROLE_ADVANCED){ ?>
    <form name=update action=conditions.php method=post>
        <INPUT TYPE=text name=armies maxlength="4" size="4" value="<?php echo "$army_val"; ?>">
<?php }
echo "</br>Points for each controlled territory: $terr_val</br>";
if($role <= ROLE_ADVANCED){ ?>
        <INPUT TYPE=text name=terrs maxlength="4" size="4" value="<?php echo "$terr_val"; ?>">
<?php }
echo "</br>Points for each owned card: $card_val</br>";
if($role <= ROLE_ADVANCED){ ?>
        <INPUT TYPE=text name=cards maxlength="4" size="4" value="<?php echo "$card_val"; ?>">
        
        <input type="hidden" name="game_id" value="<?php echo "$game_id"; ?>">
        <input type="hidden" name="turn" value="<?php echo "$turn"; ?>"></br>
        <input type="checkbox" name="all" value="1">Apply setting to every turn (Overwrites data)</br>
        <input name=action type=submit value="Set">
    </form>
<?php }

echo    "</br><h2>Territory point values</h2></br>";

// get territories data
$t_search = array( "map_id" => $map_id );
$territory_data = query_db("territories", $t_search, "*", FALSE);
if(isset($territory_data['wl_id'])){
    $raw = $territory_data;
    $territory_data = array();
    $territory_data[] = $raw;
}
$territories = array();
foreach ($territory_data as $terr_data) {
    $territories[$terr_data['wl_id']] = $terr_data['name'];
}
asort($territories);

// load territory values
$territory_values = array_fill_keys(array_keys($territories), 1);
$stored_values = $turn_data['key_terrs'];
if($stored_values != NULL){
    $stored_array = explode(",", $stored_values);
    if(!count($stored_array)){
        $string = $stored_array;
        $stored_array = array();
        $stored_array[] = $string;
    }
    foreach ($stored_array as $stored_value){
        $values = explode(":", $stored_value);
        $s_id = $values[0];
        $s_val = $values[1];
        $territory_values[$s_id] = $s_val;
    }
}
if($role <= ROLE_ADVANCED){ ?>
    <form name=update action=conditions.php method=post>
        <input type="hidden" name="game_id" value="<?php echo "$game_id"; ?>">
        <input type="hidden" name="turn" value="<?php echo "$turn"; ?>">
        <input type="hidden" name="what" value="special">
        <input type="hidden" name="new_value" value="1">
        <input name=action type=submit value="Set values"></br>
        <input type="checkbox" name="all" value="1">Apply setting to every turn (Overwrites data)</br>
<?php }
echo '<table><tr class=\"ca\"><td><b></b></td><td><b>Point value</b></td></tr>';
foreach ($territories as $id => $name){
    echo "<tr class=\"ca\"><td>$name</td>";
    if($role <= ROLE_ADVANCED){ ?>
        <td><INPUT TYPE=text name="<?php echo "$id"; ?>" value="<?php echo "$territory_values[$id]"; ?>" maxlength="4" size="4"></td>
    <?php }else{
        echo "<td>$territory_values[$id]</td>";
    }
    echo "</tr>";
}
echo '</table></br>';
if($role <= ROLE_ADVANCED){ ?>
    <input name=action type=submit value="Set values"></br>
    <input type="checkbox" name="all" value="1">Apply setting to every turn (Overwrites data)</br>
    </form>
<?php }






?>

<br /><br /><br /><a href="manage_games.php">Manage games</a>
<br /><a href="index.php">Index</a>

<?php include("footer.php");