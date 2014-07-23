<?php

session_start();
//connect to db

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
        $_SESSION["mail"]="";
        $_SESSION["pass"]="";
    }
}

//Check user
$user=$_SESSION["mail"];
$pass=$_SESSION["pass"];
$m_search = array("mail"=>$_SESSION["mail"]);

if($_SESSION["mail"]!=''){
    $loged=FALSE;	

    $db_pwd = query_db('players', $m_search, 'pwd', TRUE);
    $role = query_db('players', $m_search, 'role', TRUE);

    if( ($db_pwd == $pass) && ($role<9) ){
        $loged = TRUE;
    }elseif( ($db_pwd == $pass) && ($role==9) ){
        $confirmed="pending";
    }else{
        $confirmed="wrong";
    }
}

if($loged==FALSE){
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
<?php }
?>
