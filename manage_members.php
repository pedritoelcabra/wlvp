<?php
include("header.php");
include 'mysql_config.php';
if((!$loged) || ($role > ROLE_ADMIN)){
    header("Location: error.php?err=You're not authorized to access this page");
    exit();
}
//<!---PAGE CONTENT WHEN LOGED-->
if(isset($_GET['id']) && isset($_GET['promote'])){
    $member_id = $_GET['id'];
    $lvl = $_GET['promote'];
    $query = "UPDATE `$database`.`players` SET `role` = $lvl WHERE `wl_id` = $member_id";
    insert_db($query);
    echo "Promoted user</br></br>";
}
echo "Member role definitions (provisory)"
. "<ul>"
. "<li><b>Unconfirmed</b>: Has created account, waiting for admin confirmation.</li>"
. "<li><b>User</b>: Can watch game scores.</li>"
. "<li><b>Advanced User</b>: Can also modify victory conditions.</li>"
. "<li><b>Admin</b>: All access, can edit members and games.</li>"
. "</ul></br>";

echo '<table>';
echo '<tr><td>Name</td><td>WL ID</td><td>Role</td><td></td></tr>';
$members = query_db("players", NULL, "*", FALSE);
if(isset($members['name'])){
    $raw = $members;
    $members = array();
    $members[] = $raw;
}
if($members){
    foreach ($members as $member){
        $name = $member['name'];
        $wl_id = $member['wl_id'];
        $role = $member['role'];
        switch ($role) {
            case 8: 
                $role_name = "User";
                $promote_text = "Promote";
                $promote_lvl = 4;
                break;
            case 4: 
                $role_name = "Advanced User";
                break;
            case 1: 
                $role_name = "Admin";
                break;
            default:
                $role_name = "Unconfirmed";
                $promote_text = "Confirm";
                $promote_lvl = 8;
                break;
        }
        echo "<tr><td>$name</td><td>$wl_id</td><td>$role_name</td><td>";
        if($role > 1){
            echo "<a href=delete_member.php?id=$wl_id>Delete member";
        }
        echo "</a></td>";
        if(($role_name == "Unconfirmed") || ($role_name == "User") ){
            echo "<td><a href=manage_members.php?id=$wl_id&promote=$promote_lvl>$promote_text</a></td>";
        }

        echo "</tr>";
    }
}
echo '</table>';

?>


<br /><a href="index.php">Back</a>


<?php include("footer.php");?>