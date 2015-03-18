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
		$resultfile = "temps/result-multi-$runtime.csv";

		$input = fopen($inputfile, "r");
		$result = fopen($resultfile, "w");

		$i = 0;
		$mh = curl_multi_init();
		$ch = array();
		$urls = array();

		while (($row = fgetcsv($input)) !==false){
			$url = $row[0];
			$urls[$i] = $url;
			$ch[$i] = curl_init($url);
			curl_multi_add_handle($mh,$ch[$i]); // adds curl to multicurl
			$i++;
		}
		$active = null;

		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) == -1) {
				usleep(1000);
			}
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}

		for ($x=0; $x < count($ch); $x++) { 
			$url = $urls[$x];
			$info = curl_getinfo($ch[$x]);
			$httpcode = $info['http_code'];
			fwrite($result, "\"$url\",\"$httpcode\"\n");
			curl_multi_remove_handle($mh, $ch[$x]);//removes each curl from multi after use
		}

		fclose($input);
		
		fwrite($result, "\n\n\"Start Time\",\"$runtime\"\n");
		$endtime = microtime(true);
		fwrite($result, "\"End Time\",\"$endtime\"\n");
		$time = $endtime - $runtime;
		fwrite($result, "\"Run Time\",\"$time\"\n");
		fclose($result);
		echo "end - $time<br><a href='$resultfile'>Download Results</a>";
	} else {
		$time = time();
		echo exec("php multicurlexperiment.php 1 > temps/multicurlexperiment-$time.log &");
		echo "<a href='temps/multicurlexperiment-$time.log'>multicurlexperiment-$time.log</a>";
	}
?>
</body>
</html>