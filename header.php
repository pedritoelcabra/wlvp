<?php
/*
 * To be included on every page
 * Checks if users are logged in and records it in $loged == TRUE
 * Checks user roles:
 *      9 not yet confirmed
 *      8 or less can watch scores
 *      4 or less can also add maps and players
 *      1 can also add new games
 */
define('ROLE_UNCONFIRMED', 9);
define('ROLE_USER', 8);
define('ROLE_ADVANCED', 4);
define('ROLE_ADMIN', 1);

session_start();

include("functions/game_functions.php");
include("functions/db_functions.php");
include("functions/api_functions.php");

// check if there are POST variables set
if(isset($_POST["action"])){

    //VALIDATE SESSION
    $action=$_POST["action"];
    if($action=="Login") {
        $user=$_POST["mail"];
        $pass=md5($_POST["pass"]);
        if($user!="") {
            //	session_save_path('/web/htdocs/www.veintegenarios.net/home/wl/sessions');

            $_SESSION["mail"]=$user;
            $_SESSION["pass"]=$pass;
        }
    }elseif($action=="Logout") {
        session_destroy();
        $loged = FALSE;
        $confirmed = "";
        $role = 10;
        header("Location: index.php");
        exit();
    }
}


if(isset($_SESSION["mail"])){
    $loged=FALSE;	
    
    //Check user
    $user=$_SESSION["mail"];
    $pass=$_SESSION["pass"];
    $m_search = array("mail"=>$_SESSION["mail"]);

    $db_pwd = query_db('players', $m_search, 'pwd', TRUE);
    $role = query_db('players', $m_search, 'role', TRUE);
    
    if($db_pwd == NULL){
        session_destroy();
        header("Location: error.php?err=No such user");
        exit();
    }
    
    if( ($db_pwd == $pass) && ($role < ROLE_UNCONFIRMED) ){
        $loged = TRUE;
    }elseif( ($db_pwd == $pass) && ($role == ROLE_UNCONFIRMED) ){
        $confirmed="pending";
    }else{
        $confirmed="wrong";
    }
} else {
    $loged=FALSE;	
    $confirmed = "";
}

if($loged == FALSE){
    if($confirmed=="pending"){

            echo "Welcome, ".query_db("players",$m_search,"name",TRUE)."<br/>";
            echo "your login is valid, but your account has not been validated yet.";?>
            <form name=login action=index.php method=post>
                    <input name=action type=submit value="Logout">
            </form>
    <?php }else{
            if($confirmed=="wrong"){
                echo "Login incorrect<br/>";		
            }
    echo "You must log in";
    include("intro.php");
    }
}else{
    echo "<a href='http://www.veintegenarios.net/wl/'>Home</a><br/>";
    echo "Welcome, ".query_db("players",$m_search,"name",TRUE);
    ?>
    <form name=login action=index.php method=post>
            <input name=action type=submit value="Logout">
    </form>
    <?php 
}
