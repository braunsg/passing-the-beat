<?php

/*

passing-the-beat
https://github.com/braunsg/passing-the-beat
Created by: Steven Braun
Last updated: 2016-04-29

Open source code for "Passing the Beat," an interactive visualization of Billboard Top 100, 
U.K. Top 200, and J-Wave Tokio Hot 100 crossover artists built with D3.js.

This work is provided under the MIT License (MIT)
COPYRIGHT (C) 2016 Steven Braun

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

A full copy of the license is included in LICENSE.md.

//////////////////////////////////////////////////////////////////////////////////////////
// About this file

This script goes through the database to identify crossover singles -- songs by artists of 
one country making appearances on the top music charts of countries not of their own

*/

// Retrieve database connection parameters
include("default-config.php");

$con = mysqli_connect($dbhost,$dbuser,$dbpw,$dbname);

// Retrieve single IDs to analyze from file
print "Retrieving single IDs to analyze\n";

$single_ids = array();
$sql = "SELECT single_id, msid, artist_id FROM single_mappings";
$result = mysqli_query($con,$sql);
while($row = mysqli_fetch_assoc($result)) {
	$single_id = (string) $row['single_id'];
	$msid = (string) $row['msid'];
	$artist_id = $row['artist_id'];
	if(!array_key_exists($msid,$single_ids)) {
		$single_ids[$msid] = array();
	}
	$single_ids[$msid][$single_id] = "'$artist_id'";

}
mysqli_free_result($result);

$total_single_count = count($single_ids);

print "Preparing to loop through " . $total_single_count . " singles...\n";
sleep(1);

// Going to iterate through all singles in table, so
// limit size of each chunk for memory allocation
// $count_limit = 1000;
// $offset = 0;

// Specify tables to search
$table_names = array("rankings_us","rankings_uk","rankings_jp");


$counter = 0;

foreach($single_ids as $msid => $ms_data) {
	$mapped_single_ids = array_keys($ms_data);
	$mapped_sids_string = implode(",",$mapped_single_ids);
	$counter++;
	// Pick up where left off but FIRST MAKE SURE NO DUPLICATES!
	if($counter < 28929) {
		continue;
	}

	// Now do analysis
	print "Analyzing single " . $counter . " of " . $total_single_count . "...\n";
	
	$ranking_data = array();

	foreach($table_names as $table_name) {
		$sql = "SELECT ranking, ranking_date, single_id FROM " . $table_name . " WHERE single_id IN ($mapped_sids_string) ORDER BY ranking_date ASC";
		$result = mysqli_query($con,$sql);
		while($row = mysqli_fetch_assoc($result)) {
			$ranking_date = $row["ranking_date"];
			$single_id = $row["single_id"];
			if(!array_key_exists($ranking_date,$ranking_data)) {
				$ranking_data[$ranking_date] = array();
			}
			$ranking_data[$ranking_date][] = array("date" => $ranking_date,
												"ranking" => $row["ranking"],
												"chart" => $table_name,
												"single_id" => $single_id);
		}
		mysqli_free_result($result);
	}
	
	if(count($ranking_data) > 0) {
	
		// Order array by ranking date, from earliest in time to most recent
		ksort($ranking_data);
	
		$crossover_data = array();
		$charts = array();
		$origin_chart = null;
			
		// Now sort through sequentially to identify any crossovers
		foreach($ranking_data as $date => $data) {
							
			foreach($data as $i => $subdata) {
				if($i == 0 && is_null($origin_chart)) {
					// For first ranking, get starting chart
					$origin_chart = $subdata["chart"];
					$charts[] = $origin_chart;
				}
			
				$compare_chart = $subdata["chart"];
				if($compare_chart !== $origin_chart) {
					// Only add crossover if previous crossover hasn't been noted already
					if(!in_array($compare_chart,$charts)) {
						$single_id = $subdata["single_id"];
						$charts[] = $compare_chart;
						$crossover_data[] = array("single_id" => "'$single_id'",
												  "msid" => "'$msid'",
												  "artist_id" => "$ms_data[$single_id]",
												  "crossover_date" => "'$date'",
												  "ranking_from" => $ranking_from,
												  "ranking_to" => $subdata["ranking"],
												  "chartname_from" => "'$origin_chart'",
												  "chartname_to" => "'$compare_chart'");
					}
				}

				// Grab starting ranking from each relative ranking reference/start point
				// Ranking from prior array entry is compared against that of the subsequent entry
				$ranking_from = $subdata["ranking"];

			}

		}

		// Inject crossovers into table, if any
		if(count($crossover_data) > 0) {
			foreach($crossover_data as $j => $crossover) {
				$fields_list = implode(",",array_keys($crossover));
				$values_list = implode(",",array_values($crossover));
				$crossover_sql = "INSERT INTO mapped_crossovers(" . $fields_list . ") VALUES(" . $values_list . ")";
				if(!mysqli_query($con,$crossover_sql)) {
					print "\t" . $crossover_sql . "\n";
					die("ERROR: " . mysqli_error($con) . "\n");
				} else {
						print "\tADDED CROSSOVER " . $crossover["crossover_date"] . "\t" . $crossover["single_id"] . "\t" . $crossover["chartname_from"] . "\t" . $crossover["chartname_to"] . "\n";
				}
			}
		}

	}

}

mysqli_close($con);


?>