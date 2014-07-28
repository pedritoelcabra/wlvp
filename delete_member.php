<?php
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_ADMIN)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}
//<!---PAGE CONTENT WHEN LOGED-->

if(isset($_GET['id'])){
    $member_id = $_GET['id'];
}else{
    header("Location: error.php?err=No id sent");
    exit();
}

if(isset($_GET['action'])){
    $query = "DELETE FROM `$database`.`players` WHERE `wl_id` = $member_id;";
    if(!insert_db($query)){
        header("Location: error.php?err=Could not delete member from database ($query)");
        exit();
    }else {
        echo "This member has been successfully deleted";
    }
}else{
    ?>
    <form name=confirm_delete action="delete_member.php" method=get>
	<b>Are you sure you want to delete this member?</b><br/>
        <input type="hidden"  name="id" value="<?php echo $member_id; ?>">
	<input name=action type=submit value="confirm">
    </form>
    <?php
}


?>

<br /><a href="manage_members.php">Back</a>


<?php include("footer.php");?>