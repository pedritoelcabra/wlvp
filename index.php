<?php include("header.php");?>
<?php if($loged==TRUE){?>
<!---PAGE CONTENT WHEN LOGED-->
<ul>
<li><a href="">Home</a></li>
<li><a href="add_player.php">Add Player</a></li>
<li><a href="add_map.php">Add Map</a></li>
</ul>
<form name=fetch_turn action=fetch_turn.php method=get>
	<b>Update game from warlight</b><br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="fetch">
</form>
<form name=watch_game action=game.php method=get>
	<b>Watch game scores</b><br/>
	Game ID:
	<INPUT TYPE=text name=game_id>
	<input name=action type=submit value="watch">
</form>
<!---END OF PAGE CONTENT WHEN LOGED-->
<?php }?>
<?php include("footer.php");?>
