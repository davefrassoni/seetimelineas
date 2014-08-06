<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
$followers = file_get_contents('http://localhost:8080/seetimelineas/develop/twitter.php?url=friends&screenName='.$_REQUEST['screenName']);
$fl  = json_decode($followers);
$wall = array();

if(!empty($fl->errors)) {
	die($fl->errors[0]->message);
}

$elem = array();

if (is_array($fl)) {
	foreach($fl as $id) {
		var_dump ($id);
		if(!empty($id)) {
			$el = file_get_contents('http://localhost:8080/seetimelineas/develop/twitter.php?url=timeline&user_id='.$id.'&count=20&include_entities=true&include_rts=false&exclude_replies=true');
			$e = json_decode($el);

			var_dump($e);

			foreach($e as $it){
				if(!empty($it->errors)){
					die($it->errors[0]->message);
				}

				if(!empty($it->text) && $it->text != 'null'  && $it->text != 'null,'  && $it->text != null){
					$string = utf8_encode($it->text);
					$new_string = preg_replace("/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/","<a href=\"\\0\" target=\"_blank\">\\0</a>", $string);

					if(strtotime(date('Y-m-d H:i:s', strtotime('-5 hour'))) < strtotime($it->created_at.' -4 hour')) {
						$elem[] = array(
							'link' => 'https://www.twitter.com/'.$it->user->screen_name.'/status/'.$it->id_str,
							'author' => $it->user->screen_name,
							'name' => $it->user->name,
							'id' => $it->id_str,
							'feed'=>'twitter',
							'publishedDate' => $it->created_at,
							'profileImage' => $it->user->profile_image_url,
							'timestamp' => strtotime($it->created_at),
							'content' => $new_string,
							'niceTime' => niceTime(strtotime($it->created_at)));
					}
				}
			}
		}
	}
}

var_dump($elem);

function niceTime($time){
	$b = (strtotime(date("Y-m-d H:i:s")));
	$a = $time;
	$diff = abs($b - $a);

	$years = floor($diff / (365*60*60*24));
	$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	$dcont = '';
	if($days>0){
		if($days != 1){
			$dcont = 'Hace '.$days.' dias';
		}else{
			$dcont = 'Hace '.$days.' dia';
		}
	}else{
		$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24) / (60*60));
		if($hours>0) {
			if($hours != 1) {
				$dcont = 'Hace '.$hours.' horas';
			} else {
				$dcont = 'Hace '.$hours.' hora';
			}
		} else {
			$minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60));
			if($minutes>0) {
				if($minutes != 1) {
					$dcont = 'Hace '.$minutes.' minutos';
				} else {
					$dcont = 'Hace '.$minutes.' minuto';
				}
			} else {
				$segundos = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24));
				$dcont = 'Hace '.$segundos.' segundos';
			}
		}
	}

	return $dcont;
}