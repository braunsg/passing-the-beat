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

This script generates CSV files for each crossover single to be used in visualization

*/


// Retrieve database connection parameters
include("default-config.php");
$con = mysqli_connect($dbhost,$dbuser,$dbpw,$dbname);

$singleids = array();

$sql = "SELECT mapped_crossovers.msid, mapped_crossovers.artist_id, single_mappings.single_id, singles.single_title FROM mapped_crossovers INNER JOIN singles ON singles.single_id = mapped_crossovers.single_id INNER JOIN single_mappings ON single_mappings.msid = mapped_crossovers.msid";
$result = mysqli_query($con,$sql);
while($row = mysqli_fetch_assoc($result)) {
	$msid = $row['msid'];
	$single_id = $row['single_id'];
	if(!array_key_exists($msid,$singleids)) {
		$singleids[$msid] = array("single_ids" => array("'$single_id'"), "artist_id" => $row['artist_id'], "title" => $row['single_title']);
	} else {
		if(!array_key_exists($single_id,$singleids[$msid]["single_ids"])) {
			$singleids[$msid]["single_ids"][] = "'$single_id'";
		}
	}
}
mysqli_free_result($result);

$tables = array("rankings_us","rankings_uk","rankings_jp");

$count = 0;
foreach($singleids as $msid => $single_data) {
	$mapped_single_ids = implode(",",$single_data["single_ids"]);

	$artistid = $single_data["artist_id"];
	$single_title = $single_data["title"];
	
	// Get artist locations and genres
	$sql = "SELECT artist_locations.artist_country, artists.artist_name FROM artists LEFT JOIN artist_locations ON artists.artist_id = artist_locations.artist_id WHERE artists.artist_id = '$artistid'";
	print $sql . "\n";
	$result = mysqli_query($con,$sql);
	$obj = mysqli_fetch_object($result);
	
	
	$single_country = $obj->artist_country;
	$artist_name = $obj->artist_name;

	print $single_country . "\t" . $artist_name . "\n";
	$min_date = null;
	$data = array();

	print $msid . "\t" . ++$count . "/" . count($singleids) . "\n";

	foreach($tables as $table) {
		$sql = "SELECT ranking_date,ranking, chart_name FROM $table WHERE single_id IN($mapped_single_ids)";
		$result = mysqli_query($con,$sql);
		while($row = mysqli_fetch_assoc($result)) {
			$date = $row['ranking_date'];
			$ranking = $row['ranking'];
			$chart = $row['chart_name'];
			if(is_null($min_date)) {
				$min_date = $date;
			} 
			if($date < $min_date) {
				$min_date = $date;
			}
			$data[] = array($date, $ranking, $chart);
	
		}
		mysqli_free_result($result);
	}
	$file = fopen("singles-data/" . $min_date . "_" . $msid . ".csv","a");
	fputcsv($file, array($msid,$single_country,$artist_name,$artistid,$single_title));
	foreach($data as $line) {
		fputcsv($file, $line);
	}
	fclose($file);
}
print "Process complete.\n";

mysqli_close($con);

?>