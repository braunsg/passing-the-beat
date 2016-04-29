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

This script aggregates all of the singles data into a single CSV file 
for faster loading in the visualization

*/

// Generate array of filenames
$filenames = array();
$filename_exceptions = array(".DS_Store");
$filecounter = 0;

// The singles data was stored in CSV files in a separate directory for this analysis,
// so referring to those here
foreach (new DirectoryIterator("singles-data/") as $fileInfo) {

		$filename = $fileInfo->getFilename();
		if($fileInfo->isDot() || in_array($filename,$filename_exceptions)) continue;
		$filenames[] = $filename;

}	

$data = array();
$dates = array();
$filecounter = 0;
$totalcount = count($filenames);
$null_count = 0;

// Iterate through files holding data about individual singles
foreach($filenames as $filename) {
	$filecounter++;
	
	$country = null;
	$artist = null;
	$singletitle = null;
	$min_date = null;
	$max_date = null;

	$file = fopen("singles-data/" . $filename,"r");
	$linecounter = 0;

	// Instantiate array holding data about peak chart rankings
	$max_rankings = array("billboard-us" => array("rank" => 100, "date" => null), "top100-uk" => array("rank" => 200, "date" => null), "jwave" => array("rank" => 100, "date" => null));

	// Iterate through each line of CSV file
	while($line = fgetcsv($file)) {
		if(++$linecounter == 1) {
			$msid = $line[0];
			$country = $line[1];
			$artist = $line[2];
			$artist_id = $line[3];
			$singletitle = $line[4];
			$singletitle = str_replace("\"","'",$singletitle);
			$filedata = array("country" => $country,
							  "artist" => $artist,
							  "artist_id" => $artist_id,
							  "singletitle" => $singletitle,
							  "msid" => $msid,
							  "data" => array());

			continue;
		}
		$chart = $line[2];
		$ranking = (int) $line[1];
		$date = $line[0];
		$date_components = explode("-",$date);
		$date_year = $date_components[0];
		
		// Limit returned data to only those since 1960
		if($date_year < 1960) { 
			continue;
		}
		
		// Only store data if ranking is less than or equal to 100
		// to normalize comparisons across charts (e.g., UK Top 200 vs Billboard Top 100)
		if($ranking <= 100) {
			if(!array_key_exists($date,$dates)) {
				$dates[$date] = 1;
			}
			if($ranking < $max_rankings[$chart]["rank"]) {
				$max_rankings[$chart]["rank"] = $ranking;
				$max_rankings[$chart]["date"] = $date;
			}
			$filedata["data"][] = array("chart" => $chart, "ranking" => $ranking, "date" => $date);
		}
	}

	// Define function to sort $filedata by ranking date
	$cmp = function($a,$b) {
		
		// Just doing a string comparison since all dates are formatted as YYYY-MM-DD	
		return strcmp($a["date"],$b["date"]);
		
	};
	
	usort($filedata["data"],$cmp);
	
	// Iterate through each ranking data item
	$position_counts = array("billboard-us" => 0, "top100-uk" => 0, "jwave" => 0);
	foreach($filedata["data"] as $i => $this_data) {
			$date = $this_data["date"];
			$chart = $this_data["chart"];
			$ranking = $this_data["ranking"];
			
			if($i == 0) {
				$chartname_from = $chart;
			}
			
			// Start determining whether or not this item should be stored in final data array
			$store = true;
			switch($chart) {
			
				// If the ranking datum is for the chart corresponding to the artist's
				// country of origin, do not store
				case "billboard-us":
					if($country === "United States") {
						$store = false;
					}
					break;
				case "top100-uk":
					if($country === "United Kingdom") {
						$store = false;
					}
					break;
				case "jwave":
					if($country === "Japan") {
						$store = false;
					}
					break;
			}
			
			if(!array_key_exists($chart,$max_rankings)) {
				// If $chart key has been removed from $max_rankings,
				// then the top ranking has been stored already for this chart
				// and we do not need to store
				$store = false;
			}
			
			if($store === true) {
				// If pass first storage test (foreign country chart),
				// only include FIRST appearance on foreign chart and then TOP position
				// on chart
				
				if($position_counts[$chart] > 0) {
					if($ranking != $max_rankings[$chart]["rank"]) {
						$store = false;
					} else {
						$max_ranking = $max_rankings[$chart]["rank"];
						$max_ranking_date = $max_rankings[$chart]["date"];
						unset($max_rankings[$chart]);
					}
				} else {
					$max_ranking = $max_rankings[$chart]["rank"];
					$max_ranking_date = $max_rankings[$chart]["date"];							
					$position_counts[$chart]++;
				}
			}
			
			if($store === true) {

				// Add date key to $data, if necessary
				if(!array_key_exists($date,$data)) {
					$data[$date] = array();
				}

				$data[$date][] = array("msid" => $msid,
									   "artist" => $artist,
									   "artist_id" => $artist_id,
									   "country_name" => $country,
									   "chart_from" => $chartname_from,
									   "chart" => $chart,
									   "ranking" => $ranking,
									   "single_title" => $singletitle,
									   "peak_position" => $max_ranking,
									   "peak_date" => $max_ranking_date,
									   "date" => $date);

			}
			
			if($chart !== $chartname_from) {
				$chartname_from = $chart;
			}

		
		
	}
	
	fclose($file);
}

// Sort $dates and $data arrays
ksort($dates);
ksort($data);

// Output file
$output = fopen("singles-data-output.csv","a");
fputcsv($output,array("msid","artist","artist_id","country_name","chart_from","chart","ranking","single_title","peak_position","peak_date","date"));

foreach($data as $date => $singles) {
	foreach($singles as $i => $singledata) {
		fputcsv($output,$singledata);
	}
}

fclose($output);

// Also generate a CSV file holding all chart date pointers
$output = fopen("dates.csv","a");
fputcsv($output,array_keys($dates));
fclose($output);

print "Process complete.\n";

?>