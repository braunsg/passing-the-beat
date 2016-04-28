<?php

/*

passing-the-beat
https://github.com/braunsg/passing-the-beat
Created by: Steven Braun
Last updated: 2016-04-28

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

Fetches data used to create visualization of individual artist history

*/

// Initialize database connection
include("../config/default-config.php");
$con = mysqli_connect($dbhost,$dbuser,$dbpw,$dbname);

// Retrieve ID of searched artist
$search_id = $_POST["artist_id"];

// Initialize array to hold ranking data
$data = array("billboard-us" => array(), "top100-uk" => array(), "jwave" => array());

// Load artist data from file
$file = fopen($artist_data_path . "artist_" . $search_id . ".csv","r");

// Iterate through data file to grab ranking data
$linecounter = 0;
while($line = fgetcsv($file)) {
	if(++$linecounter == 1) {
		$artist_country = $line[2];
		$artist_name = $line[1];
		$min_date = $line[3];
		$max_date = $line[4];
		continue;
	}

	$chart = $line[0];
	$ranking = $line[1];
	$date = $line[2];
	$msid = $line[3];
	$singletitle = $line[4];

	$data[$chart][] = array("chart" => $chart, "ranking" => $ranking, "date" => $date, "msid" => $msid, "single_title" => $singletitle);
}

fclose($file);
// Return artist history data as JSON
$return_data = array("data" => $data, "artist" => $artist_name, "artist_id" => $search_id, "country" => $artist_country, "min_date" => $min_date, "max_date" => $max_date);
$data = json_encode($return_data,true);
echo $data;
?>
