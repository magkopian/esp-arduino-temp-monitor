<?php
/*
* Copyright (c) 2017 Manolis Agkopian
* See the file LICENSE for copying permission.
*/

define('MYSQL_USER', '[username]');
define('MYSQL_PASSWD', '[password]');
define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_DATABASE', 'sensors');

if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {

	$client_id = 0;
	if ( isset($_GET['client_id']) ) {
		$client_id = (int) $_GET['client_id'];
	}

	$time_to = time();
	$time_from = time() - 60 * 60 * 24;
	$to = date('Y-m-d H:i:s', $time_to);
	$from = date('Y-m-d H:i:s', $time_from);

	$data = fetch_data($client_id, $to, $from);

	$day_hours = ['12:00 AM', '1:00 AM', '2:00 AM', '3:00 AM', '4:00 AM', '5:00 AM', '6:00 AM', '7:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM', '6:00 PM', '7:00 PM', '8:00 PM', '9:00 PM', '10:00 PM', '11:00 PM', '12:00 PM'];
	$current_hour = date('G', $time_from);
	$temperature_data = [];
	$humidity_data = [];
	$labels = [];

	$data_period = date('d/m/Y', $time_from) . ' - ' . date('d/m/Y', $time_to);

	for ( $i = 0; $i < 24; ++$i ) {

		if ( !empty($data['temperature']) ) {
			$temperature_data[] = $data['temperature'][$current_hour]['avg_value'];
		}

		if ( !empty($data['humidity']) ) {
			$humidity_data[] = $data['humidity'][$current_hour]['avg_value'];
		}

		$labels[] = $day_hours[$current_hour];

		if ( ++$current_hour > 23 ) {
			$current_hour = 0;		
		}
	}

}

/**
* Fetches the temperature and humidity from the database
*/
function fetch_data ($client_id, $to, $from) {

	try {
		$dbh = new PDO('mysql:dbname=' . MYSQL_DATABASE . ';host=' . MYSQL_HOST, MYSQL_USER, MYSQL_PASSWD);
		$sth1 = $dbh->prepare('SELECT AVG(`value`) AS `avg_value`, HOUR(`created_at`) AS `hour`, `created_at` FROM `temperature` WHERE `client_id` = :client_id AND `created_at` >= :from AND `created_at` <= :to GROUP BY `hour`');
		$sth2 = $dbh->prepare('SELECT AVG(`value`) AS `avg_value`, HOUR(`created_at`) AS `hour`,`created_at` FROM `humidity` WHERE `client_id` = :client_id  AND `created_at` >= :from AND `created_at` <= :to GROUP BY `hour`');
		$sth1->execute([':client_id' => $client_id, ':to' => $to, ':from' => $from]);
		$sth2->execute([':client_id' => $client_id, ':to' => $to, ':from' => $from]);
		$temperature_data = $sth1->fetchAll(PDO::FETCH_ASSOC);
		$humidity_data = $sth2->fetchAll(PDO::FETCH_ASSOC);
	}
	catch ( PDOException $e ) {
		echo 'Connection failed: ' . $e->getMessage();
	}

	return ['temperature' => $temperature_data, 'humidity' => $humidity_data];

}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Temperature and humidity for period <?php echo $data_period; ?></title>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
	<style>
		canvas {
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
		}
		.chart {
			width:70%;
			margin: 0 auto;
		}
	</style>
</head>
<body>
	<?php if ( empty($temperature_data) && empty($humidity_data) ): ?>
		<div>No data from client <?php echo $client_id; ?> during the period <?php echo $data_period; ?>.</div>
	<?php else: ?>
	<div class="chart">
		<canvas id="canvas"></canvas>
	</div>
	<script>
		var config = {
			type: 'line',
			data: {
				labels: [<?php echo '"', implode('", "', $labels), '"'; ?>],
				datasets: [{
					label: "Temperature",
					fill: false,
					backgroundColor: "#FF0000",
					borderColor: "#FF0000",
					data: [<?php echo implode(', ', $temperature_data); ?>],
				},
				{
					label: "Humidity",
					fill: false,
					backgroundColor: "#0000FF",
					borderColor: "#0000FF",
					data: [<?php echo implode(', ', $humidity_data); ?>],
				}]
			},
			options: {
				responsive: true,
				title:{
					display:true,
					text: "Temperature and humidity for period <?php echo $data_period; ?>"
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Hours'
						}
					}],
					yAxes: [{
						display: true,
						ticks: {
							suggestedMin: 10,
							suggestedMax: 40,
						}
					}]
				}
			}
		};

		window.onload = function() {
			var ctx = document.getElementById("canvas").getContext("2d");
			window.myLine = new Chart(ctx, config);
		};
	</script>
	<?php endif; ?>
</body>
</html>
