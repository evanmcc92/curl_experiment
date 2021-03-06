<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
<?php
	date_default_timezone_set('America/New_York');
	ini_set('error_reporting', E_ALL);
	ini_set("display_errors", 1);
	ini_set('default_socket_timeout', 0);
	ini_set('max_execution_time', 0);
	ini_set('memory_limit', '-1');
	set_time_limit(0);
	require_once 'RollingCurl/RollingCurl.php';
	require_once 'Zebra_cURL.php';


	if (isset($argv[1])) {
		$runtime = microtime(true);
		$inputfile = "temps/input-100.csv";
		$resultfile = "temps/result-rolling-$runtime.csv";

		$input = fopen($inputfile, "r");
		$result = fopen($resultfile, "w");

		$i = 0;
		$urls = array();

		while (($row = fgetcsv($input)) !==false){
			$url = $row[0];
			$urls[$i] = $url;
			$i++;
		}
		fclose($input);

		$rc = new RollingCurl("request_callback");
		$rc->window_size = 20;
		foreach ($urls as $url) {
		    $request = new RollingCurlRequest($url);
		    $rc->add($request);
		}
		$rc->execute();

		
		fwrite($result, "\n\n\"Start Time\",\"$runtime\"\n");
		$endtime = microtime(true);
		fwrite($result, "\"End Time\",\"$endtime\"\n");
		$time = $endtime - $runtime;
		fwrite($result, "\"Run Time\",\"$time\"\n");
		fclose($result);
		echo "end - $time<br><a href='$resultfile'>Download Results</a>";
	} else {
		$time = time();
		echo exec("php rollingcurlexperiment.php 1 > temps/rollingcurlexperiment-$time.log &");
		echo "<a href='temps/rollingcurlexperiment-$time.log'>rollingcurlexperiment-$time.log</a>";
	}

	function request_callback($response, $info, $request) {
		global $result;
	    $request = json_decode(json_encode($request), true);
		$httpcode = $info['http_code'];
		$url = $request['url'];
		fwrite($result, "\"$url\",\"$httpcode\"\n");
	}
?>
</body></html>