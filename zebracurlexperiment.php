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
		$resultfile = "temps/result-zebra-$runtime.csv";

		$input = fopen($inputfile, "r");
		$resultinput = fopen($resultfile, "w");

		$i = 0;
		$urls = array();

		while (($row = fgetcsv($input)) !==false){
			$url = $row[0];
			$urls[$i] = $url;
			$i++;
		}
		fclose($input);

		$curl = new Zebra_cURL();

		$curl->get($urls, 'request_callback');
		
		fwrite($resultinput, "\n\n\"Start Time\",\"$runtime\"\n");
		$endtime = microtime(true);
		fwrite($resultinput, "\"End Time\",\"$endtime\"\n");
		$time = $endtime - $runtime;
		fwrite($resultinput, "\"Run Time\",\"$time\"\n");
		fclose($resultinput);
		echo "end - $time<br><a href='$resultfile'>Download Results</a>";
	} else {
		$time = time();
		echo exec("php zebracurlexperiment.php 1 > temps/zebracurlexperiment-$time.log &");
		echo "<a href='temps/zebracurlexperiment-$time.log'>zebracurlexperiment-$time.log</a>";
	}

	function request_callback($result) {
		global $resultinput;
		$httpcode = $result->info['http_code'];
		$url = $result->info['original_url'];
		fwrite($resultinput, "\"$url\",\"$httpcode\"\n");
	}
?>

</body>
</html>