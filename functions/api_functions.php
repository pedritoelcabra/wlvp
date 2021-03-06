<?php
function do_post_request($url, $data, $optional_headers = null)
{
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception("Problem with $url, $php_errormsg");
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception("Problem reading data from $url, $php_errormsg");
  }
  return $response;
}

function create_game($data){
//simplify only 2 create games
    include 'api_data.php';
	$url=$apiurl.'CreateGame';
	$auth_email="hostEmail";
	$auth_token="hostAPIToken";
	$api_auth=array(
	   $auth_email => $api_email,
	   $auth_token => $api_token);
	$data=array_merge($api_auth,$data);
//	var_dump($data);
	$jsonString = json_encode($data);

	echo $url;
	$result = do_post_request($url, $jsonString);
	$content = json_decode($result, true);

//	var_dump($content);
	return $content;
}

function post_request_data($data, $action, $get_history){
    include 'api_data.php';

    $postdata = http_build_query(
        array(
            'Email' => $api_email,
            'APIToken' => $api_token,
        )
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);
    $api_url='http://warlight.net/API/';
    if($action=="player"){
        $url=$api_url.'ValidateInviteToken?Token='.$data["Token"];
    }elseif($action=="game"){
        $url=$api_url.'GameFeed?GameID='.$data["GameID"];
        if($get_history){
            $url .= "&GetHistory=true";
        }
    }else if($action == "chat"){
        $url = $api_url . 'GameFeed?GameID=' . $data["GameID"] . "&GetChat=true";
    }


    $result = file_get_contents($url, false, $context);

    if($action == "game"){
        $count=99;
        for($i=0;$count!=0;$i++){
            $pattern = '/GameOrderDeploy":/';
            $order= 'GameOrderDeploy'.$i.'":';
            $replacement = $order;
            $result = preg_replace($pattern, $replacement, $result,1,$count);
        }

        $count=99;
        for($i=0;$count!=0;$i++){
            $pattern = '/GameOrderAttackTransfer":/';
            $order= 'GameOrderAttackTransfer'.$i.'":';
            $replacement = $order;
            $result = preg_replace($pattern, $replacement, $result,1,$count);
        }

        $Cards=cards();

        foreach($Cards as $Card){
            $count=99;
            for($i=0;$count!=0;$i++){
                $pattern = '/'.$Card.'":/';
                $order= $Card.$i.'":';
                $replacement = $order;
                $result = preg_replace($pattern, $replacement, $result,1,$count);
            }
        }
    }

    $game_details = json_decode($result, true);
    
    if(isset($game_details["error"])){
//        echo $game_details["error"]."?";
//        exit();
        return FALSE;
    }else{
        return $game_details;
    }
}
?>
