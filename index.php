<?php 
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_USER)){
    exit();
}
?>
<!---PAGE CONTENT WHEN LOGED-->
<ul>
<li><a href="">Home</a></li>
<?php if($role < ROLE_ADVANCED){ ?>
<li><a href="add_player.php">Add Player</a></li>
<li><a href="add_map.php">Add Map</a></li>
<?php } ?>
</ul>
<?php if($role == ROLE_ADMIN){ ?>
<form name=add_game action=add_game.php method=get>
	<b>Add new Warlight game</b><br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="Go">
</form>
<form name=delete_game action=delete_game.php method=get>
	<b>Delete a Warlight game from our database</b><br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="Go">
</form>
<?php } ?>
<?php if($role < ROLE_ADVANCED){ ?>
<form name=fetch_turn action=fetch_turn.php method=get>
	<b>Update game from warlight</b><br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="Go">
</form>
<?php } ?>
<form name=watch_game action=game.php method=get>
	<b>Watch game scores</b><br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="Go">
</form>
<!---END OF PAGE CONTENT WHEN LOGED-->
<?php
include("footer.php");
