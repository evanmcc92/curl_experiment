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
		$resultfile = "temps/result-single-$runtime.csv";

		$input = fopen($inputfile, "r");
		$result = fopen($resultfile, "w");

		while (($row = fgetcsv($input)) !==false){
			$url = $row[0];
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			$response = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$httpcode = $info['http_code'];

			fwrite($result, "\"$url\",\"$httpcode\"\n");
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
		echo exec("php singlecurlexperiment.php 1 > temps/singlecurlexperiment-$time.log &");
		echo "<a href='temps/singlecurlexperiment-$time.log'>singlecurlexperiment-$time.log</a>";
	}
?>
</body>
</html>