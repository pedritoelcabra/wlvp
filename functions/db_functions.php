<?php

function condb(){
    
    include("mysql_config.php");
    
    $mysqli = new mysqli($host, $username, $password, $database);
    if ($mysqli->connect_errno) {
        printf("Connect failed: %s\n", $mysqli->connect_error);
        return false;
    }
    return $mysqli;
}

//for insert statements
function insert_db($query){
    $mysqli = condb();
    //echo "</br> $query";
    $result = $mysqli->query($query);
    
    $thread = $mysqli->thread_id;
    $mysqli->kill($thread);
    $mysqli->close();
    return $result;
}

//query db by table & key/s -> return queried_key value/s, if $single=true returns string  
function query_db($table, $keys, $queried_key, $single){
    $mysqli = condb();
    $return_val = NULL;
    if($keys != NULL){
	$n_keys=1;
	$w_keys=" WHERE ";
	foreach($keys as $key=>$value){
            if($n_keys>1){$w_keys.=" AND ";}
            $w_keys.= "`".$key."` = '".$value."'";
            $n_keys++;
	}
    }else{
        $w_keys = "";
    }
    
    $query="SELECT $queried_key FROM `".$table."`".$w_keys;
    if($result = $mysqli->query($query)){
        $num = $result->num_rows;
        switch ($num){
            case 0: break;
            case 1:
                if($single){
                    $data = $result->fetch_assoc();
                    $string = $data[$queried_key];
                    $return_val = $string;
                }else{
                    $return_val = $result->fetch_assoc();
                }
                break;
            default :
                if($single){
                    $return_val_arr = $result->fetch_assoc();
                    $return_val = $return_val_arr[0];
                }else{
                    $result_arr = array();
                    while ($row = $result->fetch_assoc()){
                        if($queried_key == "*"){
                            $result_arr[] = $row;
                        }else{
                            $result_arr[] = $row[$queried_key];
                        }
                    }
                    $return_val = $result_arr;
                }
                break;
        }
    }else{
        header("Location: error.php?err=ALERT! Error getting $w_keys with value $value from $table");
        $return_val = NULL;
    }
    
    $result->free();
    $thread = $mysqli->thread_id;
    $mysqli->kill($thread);
    $mysqli->close();
    return $return_val;
}

//query db by table & key -> returns true if queried_key value exist
function check_db_entry($table,$key,$value){
    $mysqli = condb();
    $query="SELECT * FROM ".$table." WHERE ".$key." = '".$value."'";
    $result = $mysqli->query($query);
    $return_val = ($result->num_rows > 0);
 
    $result->free();
    $thread = $mysqli->thread_id;
    $mysqli->kill($thread);
    $mysqli->close();
    return $return_val;
}
