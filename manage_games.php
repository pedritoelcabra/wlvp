<?php
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_USER)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}
//<!---PAGE CONTENT WHEN LOGED-->
if($role <= ROLE_USER){
    echo "<b><a href=\"create_game.php\">Create a new game through the Warlight API</a></b></br></br>";
}

if($role == ROLE_ADMIN){ ?>
<form name=add_game action=add_game.php method=get>
	<b>Add game from Warlight</b><br/> (Adding and updating games consumes a lot of Warlight bandwith - use responsibly!)<br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="Add">
</form>
<form name=delete_game action=delete_game.php method=get>
	<b>Delete a Warlight game from our database</b><br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="Delete">
</form>
<form name=fetch_turn action=fetch_turn.php method=get>
	<b>Update game from Warlight </b> <br/>(Adding and updating games consumes a lot of Warlight bandwith - use responsibly!)<br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="Update">
</form>
<?php } ?>

<?php 

echo '<table>';
echo '<tr><td>Game name</td><td>Game ID</td><td>Last recorded turn</td></tr>';
$games = query_db("games", NULL, "*", FALSE);
if(isset($games['game_name'])){
    $games_raw = $games;
    $games = array();
    $games[] = $games_raw;
}
if($games){
    foreach ($games as $game){
        $name = $game['game_name'];
        $wl_id = $game['game_id'];
        $turn = $game['turn'];
        $finished = $game['finished'];
        if(!$finished){
            echo "<tr><td>$name</td><td>$wl_id</td><td>$turn</td>";
        }else{
            echo "<tr><td><a href=\"game.php?game_id=$wl_id\">$name</a></td><td>$wl_id</td><td>$turn</td>";
        }
        if($role == ROLE_ADMIN){
            echo "<td><a href=\"delete_game.php?game_id=$wl_id&action=Delete\">Delete</a></td>";
            if(!$finished){
                echo "<td><a href=\"fetch_turn.php?game_id=$wl_id&action=Update\">Update</a></td>";
            }else{
                echo "<td>Finished</td>";
            }
            echo "<td><a href=\"permissions.php?game_id=$wl_id\">Set permissions</a></td>";
        }
        echo "<td><a href=\"conditions.php?game_id=$wl_id\">Victory conditions</a></td>";
        echo "</tr>";

    }
}
echo '</table>';

?>

<br /><a href="index.php">Back</a>

<?php include("footer.php");?>