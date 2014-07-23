<?php
function condb(){
	include("mysql_config.php");
	$connect=mysql_connect($host,$username,$password);
	$select=@mysql_select_db($database) or die( "Unable to select database");
	return $connect;
	return $select;
}

//query db by table & key/s -> return queried_key value/s, if $single=true returns string  
function query_db($table,$keys,$queried_key,$single){
	$n_keys=1;
	$w_keys="WHERE ";
	foreach($keys as $key=>$value){
		if($n_keys>1){$w_keys.=" AND ";}
		$w_keys.= "`".$key."` = '".$value."'";
		$n_keys++;
	}
//	echo $w_keys."<br/>";
	condb();
	$query="SELECT * FROM `".$table."` ".$w_keys;
//	echo $query."<br/>";
	$result=mysql_query($query);
	$num=mysql_numrows($result);
//	echo $num;
	if(($num!=1)&&($single==TRUE)){
		return "<br/>ALERT! Duplcated ".$key." with value '".$value."'";
	}elseif($single==TRUE){
		$i=0;
		while ($i < $num) {
			$queried=mysql_result($result,$i,$queried_key);
//			echo $queried;
			return $queried;
			$i++;
			}
	}else{
		$i=0;
		$queried=array();
		while ($i < $num) {
			$queri=mysql_result($result,$i,$queried_key);
//			echo $queried;
			$queried[$i]=$queri;
//			echo $queri."<br/>";
			$i++;
		}
//		var_dump($queried);
		return $queried;	
	}
	
	mysql_close();

}
//query db by table & key -> returns true if queried_key value exist
function check_db_entry($table,$key,$value){

	condb();
	$query="SELECT * FROM ".$table." WHERE ".$key." = '".$value."'";
//	echo $query;
	$result=mysql_query($query);
	$num=mysql_numrows($result);
//	echo $num;
	if($num!=0){
		return TRUE;
	}else{
		return FALSE;
	}
	mysql_close();

}
?>
