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

This script generates csv files holding individual artist histories

*/


// Retrieve database connection parameters
include("default-config.php");
$con = mysqli_connect($dbhost,$dbuser,$dbpw,$dbname);


print "Getting artists...\n";
$artists = array();
$sql = "SELECT DISTINCT artist_id FROM mapped_crossovers";
$result = mysqli_query($con,$sql);
$artist_count = mysqli_num_rows($result);
$artist_counter = 0;
while($row = mysqli_fetch_assoc($result)) {
	print "ARTIST\t" . ++$artist_counter . "/" . $artist_count . "\n";
	$artist_id = (string)$row['artist_id'];
	if($artist_id !== "91663") { continue; }
	$artists[$artist_id] = array("name" => null,
								 "country" => null,
								 "min_date" => null,
								 "max_date" => null);
								 
	$subsql = "SELECT artists.artist_name, artist_locations.artist_country FROM artists INNER JOIN artist_locations ON artists.artist_id = artist_locations.artist_id WHERE artists.artist_id = '$artist_id' ORDER BY -artist_locations.artist_country DESC LIMIT 1";
	print $subsql . "\n";
	$subresult = mysqli_query($con,$subsql);
	$obj = mysqli_fetch_object($subresult);
	$country = $obj->artist_country;
	$name = $obj->artist_name;
	$artists[$artist_id]["name"] = $name;
	$artists[$artist_id]["country"] = $country;
}
print "Done.\n";

$filenames = array();
$filename_exceptions = array(".DS_Store");

foreach (new DirectoryIterator('singles-data/') as $fileInfo) {
	$filename = $fileInfo->getFilename();
	if($fileInfo->isDot() || in_array($filename,$filename_exceptions)) continue;
	$filenames[] = $fileInfo->getFilename();
}	


$total = count($artists);
$counter = 0;
foreach($artists as $this_artist_id => $artist_data) {
	print ++$counter . "/" . $total . "\n";
	$min_date = null;
	$max_date = null;
	$this_artist_id = (string)$this_artist_id;
	if($this_artist_id !== "91663") {
		continue;
	}
	$artist_name = $artist_data["name"];
	$artist_country = $artist_data["country"];

	$output = fopen("TESTING/artist_" . $this_artist_id . ".csv","a");
	$data = array("billboard-us" => array(), "top100-uk" => array(), "jwave" => array());
	foreach($filenames as $filename) {
		$singletitle = null;
		$file = fopen("../data/mapped-all_2016-04-25/" . $filename,"r");
		$linecounter = 0;
		while($line = fgetcsv($file)) {
			if(++$linecounter == 1) {
				$msid = $line[0];
				$singletitle = $line[4];
				$artist_id = (string)$line[3];
				if($artist_id !== $this_artist_id) {
					continue 2;
				}
				continue;
			}

			$chart = $line[2];
			$ranking = $line[1];
		
			// If ranking position is greater than 100 (e.g., UK chart),
			// disregard so can normalize comparisons across US, UK and Japan charts
			if($ranking > 100) {
				continue;
			}
			$date = $line[0];
			if(is_null($min_date)) {
				$min_date = $date;
			}
			if(is_null($max_date)) {
				$max_date = $date;
			}
			if($date < $min_date) {
				$min_date = $date;
			}
			if($date > $max_date) {
				$max_date = $date;
			}

			$data[$chart][] = array("chart" => $chart, "ranking" => $ranking, "date" => $date, "msid" => $msid, "single_title" => $singletitle);
		}
	}
	fputcsv($output,array($this_artist_id,$artist_name,$artist_country,$min_date,$max_date));
	
	foreach($data as $chart => $subdata) {
		foreach($subdata as $i => $outputdata) {
			fputcsv($output,$outputdata);
		}
	}
}

?>
