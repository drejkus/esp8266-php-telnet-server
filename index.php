<?php

if (!$_POST) { 
	include "client.php"; 
	exit;
}



error_reporting(E_ALL);

//echo '<pre>';

$port = $_POST['port'];
$address = $_POST['ip'];
$timeout = 5; //timeout to wait for response
$files_dir = "./files/";

$msg="OK";

//print_r($_POST);die();
function socket_read_timeout($socket, $timeout=5, $data_timeout=0.5) {
	$output = false;
	$tstart = microtime(true);
	while (microtime(true)-$tstart<=$timeout) {
		usleep(10000); //avoid busy waiting
		while ($out = socket_read($socket, 2048)) {
			$output.=$out;
			//restart with data timeout
			$tstart=microtime(true);
			$timeout=$data_timeout;
		}
	}
	return $output;
}

function slow_write_read($socket, $lines, $timeout=2) {
	if (!is_array($lines)) {
		$lines = explode("\n",$lines);
	}
//	$lines = array($script,"\n","\n");
	$output="";
	foreach ($lines as $l) {
		if ($l!="") {
			$l.="\n";
			socket_write($socket, $l, strlen($l));
			$o = socket_read_timeout($socket, $timeout);
			if (trim($o)!=">>") {
				$output.=$o;
			}
		}
	}
	return $output;
}


function parse_files_list($output, &$output_array) {
		
//	$output_array['files']=	
	$files = array();
	$files0 = explode("\n",trim($output,"> \n\r"));
	foreach ($files0 as $f) {
		list($file,$size)=explode(':',$f);
		$files[]=array('file'=>$file,'size'=>$size);
	}
	$output_array['files']=$files;
	return $output_array;
}

function save_local($output, &$output_array) {
	global $files_dir;
	$s1 = "-- File content start\n";
	$s2 = "-- File content end";
	$p1 = strpos($output, $s1 );		
	$p2 = strpos($output, $s2 );		
	if ( ($p1!==false)&&($p2!==false) ) {
		$fn = $files_dir.$_POST['file'];
		$start = $p1+strlen($s1);
		file_put_contents($fn, substr($output, $start , $p2-$start) );
		$output_array['response']='';
	}

	return $output_array;
}


/*
possible commands :
	- llist: list local files
	- rlist: list files from ESP8266
	- get: get file from ESP8266
	- put: store files to ESP8266
	- run: run commands ESP8266
*/

//SPECIAL CASE, no remote connection, handle here
if ($_POST['cmd']=='llist') {
	$files0 = glob($files_dir.'*.lua');
	$files = array();
	foreach ($files0 as $f) {
		$files[]=array("file"=>$f,'size'=>filesize($f));
	}
	header('Content-Type: application/json');
	echo json_encode( array( "action"=>$_POST['cmd'], "result"=>"OK", "response"=>"", "files"=>$files ) );
	exit;
}



$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    $msg="socket_create() failed: reason: " . socket_strerror(socket_last_error());
} else {
	$t1 = microtime(true);
	$result = socket_connect($socket, $address, $port);

	if ($result === false) {
    	$msg="socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket));
	} else {

		socket_set_nonblock($socket);

		$cmd = "";
		$output0 = socket_read_timeout($socket, 1);	

		$process = false;
		//$msg = "=node.heap()\n";
		switch ($_POST['cmd']) {
			case "run": 
				$cmd = $_POST['code'];
				break;
			case "do": 
				$file = $_POST['file'];
				$cmd = "dofile(\"$file\")\n";
				break;
			case "rlist" : 
				//$msg = $_POST['msg'];
				$cmd="for n,s in pairs(file.list()) do print(n..\":\"..s) end";
				$process = "parse_files_list";
			break;
			case "get":
				$file = $_POST['file'];
				$cmd =""
				."if file.open(\"$file\",\"r\") then do print(\"-- File content start\")\n"
				."repeat _line = file.readline() if (_line~=nil) then print(string.sub(_line,1,-2)) end until _line == nil\n"
				."file.close() _line=nil collectgarbage() print(\"-- File content end\") end else print(\"-- Can't open file $file\") end\n\n"
				;
//				$process = 
			break;
			case "down":
				$file = $_POST['file'];
				$cmd =""
				."if file.open(\"$file\",\"r\") then do print(\"-- File content start\")\n"
				."repeat _line = file.readline() if (_line~=nil) then print(string.sub(_line,1,-2)) end until _line == nil\n"
				."file.close() _line=nil collectgarbage() print(\"-- File content end\") end else print(\"-- Can't open file $file\") end\n\n"
				;
				$process = "save_local";
			break;
			case "put": 
				$cmd=array();
				$file = $_POST['file'];
				if ($content = file_get_contents($file)) {
					$filename=pathinfo($file, PATHINFO_BASENAME);
					$cmd[] = "FILE=\"{$filename}\" file.remove(FILE) file.open(FILE,\"w+\")"; 
					$e = explode("\n",$content);
					if ($e[count($e)-1]=='') {
						unset($e[count($e)-1]); //fix last \n
					}
					foreach ($e as $l) {
						$cmd[]="file.writeline([[".$l."]])";
					}
					$cmd[]="file.close()\n";
				}
			break;
			
			default: 
				$output = "Unknown command!";
			
		}

		if ( ($cmd!='')||(is_array($cmd)) ) {
			//socket_write($socket, $cmd, strlen($cmd));
			//$output = socket_read_timeout($socket, $timeout);	
			$output = slow_write_read($socket, $cmd);
		}

		$t2 = microtime(true);

		$output_array = array( 
			"action"=>$_POST['cmd'],
			"result"=>$msg,
			"hello"=>$output0,
			"cmd"=>$cmd,
			"response"=>$output,
			"time"=>number_format($t2-$t1,2,'.',''),
		);
		
		if ($process!==false) {
			call_user_func_array($process, array($output, &$output_array) );
		}

	}

}


socket_close($socket);

header('Content-Type: application/json');
echo json_encode( $output_array	);


