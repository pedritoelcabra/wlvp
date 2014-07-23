<?php
	session_start();
//connect to db

include("functions/game_functions.php");
include("functions/db_functions.php");
include("functions/api_functions.php");

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

//Check user

    $user=$_SESSION["mail"];
    $pass=$_SESSION["pass"];
    if($_SESSION["mail"]!=''){
	$loged=FALSE;	
	condb();
	$query="SELECT * FROM players";
	$result=mysql_query($query);
	$num=mysql_numrows($result);

	$i=0;
	while ($i < $num) {
	
	$mail=mysql_result($result,$i,"mail");
	$pwd=mysql_result($result,$i,"pwd");
	$role=mysql_result($result,$i,"role");
		if(($mail==$user)&&($pwd==$pass)&&($role<9)){
		$loged = TRUE;
		$i=$num+1;
		}elseif(($mail==$user)&&($pwd==$pass)&&($role==9)){
		$confirmed="pending";
		}else{
		$confirmed="wrong";
		}
	$i++;
	}
	mysql_close();
    }
$m_search=array("mail"=>$_SESSION["mail"]);
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
