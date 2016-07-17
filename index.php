<?php 
	
	$c = curl_init('http://www.sporcle.com/games/CFralinger/151-original-pokemon-by-picture1');
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt(... other options you want...)

	$html = curl_exec($c);

	preg_match_all('/<img src="(.+)" border="0" alt=".+">/', $html, $images);

	if (curl_error($c))
	    die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	$sortingMethods = [
		'sortByName',
		'sortByCandy',
		'sortByStamina',
		'sortByDefence',
		'sortByAttack',
		'sortByClass',
		'sortByCaptureRate',
		'sortByFleeRate'
	];


	$sort = (isset($_GET['by']) && in_array($_GET['by'], $sortingMethods, true)) ? $_GET['by'] : 'sortByCaptureRate';


	$data = file_get_contents ('./stats.json');
	$json = json_decode($data, true);
	
	$pokeStats = [];

	$start = 79;
	$end = $start + 167;

	for ($i=$start, $t=0, $k = 0; $i < $start + $end; $i++, $t++) { 
		$pokeStats[$t]['Name'] = str_replace('_', ' ', substr($json['Items'][$i]['TemplateId'], 6, strlen($json['Items'][$i]['TemplateId'])));
		if(strpos($pokeStats[$t]['Name'] , 'MOVE') !== false) {
			unset($pokeStats[$t]);
			continue;
		}

		$pokeStats[$t]['Class'] = isset($json['Items'][$i]['Pokemon']['PokemonClass']) ? $json['Items'][$i]['Pokemon']['PokemonClass'] : '';


		$pokeStats[$t]['img'] = $images[1][$k];
		$pokeStats[$t]['Name'] = trim(substr($pokeStats[$t]['Name'], 7, strlen($pokeStats[$t]['Name'])));

		$baseCapture = isset($json['Items'][$i]['Pokemon']['Encounter']['BaseCaptureRate']) ? $json['Items'][$i]['Pokemon']['Encounter']['BaseCaptureRate'] : null;
		$pokeStats[$t]['CaptureRate'] = ( $baseCapture != null) ? $baseCapture * 100 . '%' : '-';

		$baseFlee = isset($json['Items'][$i]['Pokemon']['Encounter']['BaseFleeRate']) ? $json['Items'][$i]['Pokemon']['Encounter']['BaseFleeRate'] : null;
		$pokeStats[$t]['FleeRate'] = ( $baseFlee != null) ? $baseFlee * 100 . '%' : '-';

		$candy = isset($json['Items'][$i]['Pokemon']['CandyToEvolve']) ? $json['Items'][$i]['Pokemon']['CandyToEvolve'] : null;
		$pokeStats[$t]['CandyToEvolve'] = ($candy != null) ? $candy . ' candy' : 'DOES NOT EVOLVE';

		$stamina = isset($json['Items'][$i]['Pokemon']['Stats']['BaseStamina']) ? $json['Items'][$i]['Pokemon']['Stats']['BaseStamina'] : null;
		$pokeStats[$t]['Stamina'] = ($stamina != null) ? $stamina  : '-';

		$attack = isset($json['Items'][$i]['Pokemon']['Stats']['BaseAttack']) ? $json['Items'][$i]['Pokemon']['Stats']['BaseAttack'] : null;
		$pokeStats[$t]['Attack'] = ($attack != null) ? $attack  : '-';

		$defence = isset($json['Items'][$i]['Pokemon']['Stats']['BaseDefense']) ? $json['Items'][$i]['Pokemon']['Stats']['BaseDefense'] : null;
		$pokeStats[$t]['Defence'] = ($defence != null) ? $defence  : '-';

		$k++;
		//echo $pokeStats[$t]['Name'].' - '.$pokeStats[$t]['CaptureRate'] . ' ['. $pokeStats[$t]['FleeRate'] . ']  - ' . $pokeStats[$t]['CandyToEvolve'] . ' -> S'. $pokeStats[$t]['Stamina'] . ' A'. $pokeStats[$t]['Attack'] . ' D'. $pokeStats[$t]['Defence']. '<br>';
	}

	// Sorting according to capture rate
	function sortByName($x, $y) {
	    return strcmp($x['Name'], $y['Name'])  > 0;
	}

	function sortByCaptureRate($x, $y) {
	    return $y['CaptureRate'] - $x['CaptureRate'] ;
	}

	function sortByFleeRate($x, $y) {
	    return $y['FleeRate'] - $x['FleeRate'] ;
	}

	function sortByCandy($x, $y) {
	    return $y['CandyToEvolve'] - $x['CandyToEvolve'] ;
	}

	function sortByStamina($x, $y) {
	    return $y['Stamina'] - $x['Stamina'] ;
	}

	function sortByAttack($x, $y) {
	    return $y['Attack'] - $x['Attack'] ;
	}

	function sortByDefence($x, $y) {
	    return $y['Defence'] - $x['Defence'] ;
	}

	function sortByClass($x, $y) {
	    return strcmp($y['Class'], $x['Class'])  > 0;
	}

	usort($pokeStats, $sort);
		

?>

<!DOCTYPE html>
<html>
<head>
	<title>Pokemon Stats</title>

	<style type="text/css">

		@import url(https://fonts.googleapis.com/css?family=Open+Sans);

		*{
			font-family: 'Open Sans', Arial;
		}

		h1{
			text-align: center;
		}

		table{
			border-collapse: collapse;
			width: 66%;
			margin: 0 auto;
			border: 1px solid #333;
    		border-radius: 6px;
		}

		table thead th{
			background: #346F87;
			padding: 10px;
			color: white;
			text-align: center;
		}

		table thead th a{
			color: white;
			text-decoration: none;
		}

		table tbody tr td{
			text-align: center;
			padding: 10px;
		}		

		table tbody tr{
			border-bottom: 1px solid #333;
		}

		table tbody tr:hover{
			background: #A6273C;
			color: white;
		}

		.pokemon_class_legendary{
			background: #338A96;
			color: white;
		}

		.pokemon_class_legendary:hover{
			background: #E5371F;
			color: white;
		}

		.pokemon_class_mythic{
			background: #1D5057;
			color: white;
		}

		.pokemon_class_mythic:hover{
			background: #E5371F;
			color: white;
		}


	</style>

</head>
<body>

	<h1><?php echo $sort; ?></h1>

	<table>
		<thead>
			<th>Image</th>
			<th><a href="?by=sortByName">Name</a></th>
			<th><a href="?by=sortByCaptureRate">Capture Rate</a></th>
			<th><a href="?by=sortByFleeRate">Flee Rate</a></th>
			<th><a href="?by=sortByCandy">Evolve Candy</a></th>
			<th><a href="?by=sortByStamina">Stamina</a></th>
			<th><a href="?by=sortByAttack">Attack</a></th>
			<th><a href="?by=sortByDefence">Defence</a></th>
			<th><a href="?by=sortByClass">Class</a></th>
		</thead>
		<tbody>
			<?php $i = 0; ?>
			<?php foreach($pokeStats as $pokemon): ?>
				<tr class="<?php echo strtolower($pokemon['Class']); ?>">
					<td>
						<img src="<?php echo $pokemon['img'] ?>" width="70">
					</td>
					<td><?php echo $pokemon['Name']; ?></td>
					<td><?php echo $pokemon['CaptureRate']; ?></td>
					<td><?php echo $pokemon['FleeRate']; ?></td>
					<td><?php echo $pokemon['CandyToEvolve']; ?></td>
					<td><?php echo $pokemon['Stamina']; ?></td>
					<td><?php echo $pokemon['Attack']; ?></td>
					<td><?php echo $pokemon['Defence']; ?></td>
					<td><?php echo $pokemon['Class']; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

</body>
</html>


