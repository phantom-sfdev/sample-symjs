<?php
function myMicrotime($get_as_float = true) {
	list($msec, $sec) = explode(" ", microtime());
	$time = $sec . substr($msec, 1);
	return $as_float === false ? $time : (float)$time;
}

function timer(&$array, $descrition = '') {
	if( !is_array($array) ) $array = array();
	if( $descrition != '' ) $array['DESC'] = $descrition;
	
	if( !key_exists('START', $array) ) {
		$array['START'] = myMicrotime();
	} else {
		$array['END'] = myMicrotime();
		$array['FULL_TIME'] = $array['END'] - $array['START'];
	}

}

function ___save_debug($data='', $desc=''){
	if( !empty($data) && $data!='' ) {
		if( is_array($data) || is_object($data) ) {
			$data = print_r($data, true);
		}
	}

	$fp = fopen('__dbg', 'a');
	fwrite($fp, "\n$desc: $data\n");
	fclose($fp);
};


function debug_time($finish=false)
{
	static $start_time;
	if(!$finish)
		$start_time = microtime(true);
	else 
		return microtime(true) - $start_time;
}

?>