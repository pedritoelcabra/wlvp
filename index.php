<?php 
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_USER)){
    exit();
}
?>
<!---PAGE CONTENT WHEN LOGED-->
<?php
if($role == ROLE_ADMIN){
    echo "Welcome page admin! "
    . "</br>You can use the links below to confirm new members and add new games or update existing ones."
    . "</br>Please be responsible when adding new games as this consumes a 'significant amount of bandwith', to quote the "
    . "</br>Warlight terms of use.";
    
}
?>
<ul>
    <?php 
    if($role == ROLE_ADMIN){ 
        ?>
        <li><a href="manage_members.php">Members</a></li>
        <li><a href="add_map.php">Maps</a></li>
        <?php 
    } 
    if($role <= ROLE_USER){ 
        ?>
        <li><a href="manage_games.php">Games</a></li>
        <?php 
    } 
    ?>
</ul>
<!---END OF PAGE CONTENT WHEN LOGED-->
<?php
include("footer.php");
