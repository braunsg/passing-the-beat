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

This script goes through the database to find duplicate song records and map them to 
single authority IDs -- for example, when singles are released under multiple record labels,
they may have multiple different IDs in the original data. Songs are aggregated based on matches 
between song title and artist.

*/

// Retrieve database connection parameters
include("default-config.php");

$con = mysqli_connect($dbhost,$dbuser,$dbpw,$dbname);
$ids = array();
$sql = "SELECT singles.singles_rid, singles.single_id, singles.single_title, singles.artist_id, artists.artist_name FROM singles INNER JOIN artists ON artists.artist_id = singles.artist_id";
$result = mysqli_query($con,$sql);
while($row = mysqli_fetch_assoc($result)) {
	$single_id = (string) $row['single_id'];
	$ids[$single_id] = array("single_rid" => $row['singles_rid'],
							 "single_title" => $row['single_title'],
							 "artist_id" => $row['artist_id'],
							 "artist_name" => mysqli_real_escape_string($con,$row['artist_name']));
}
$total = count($ids);
$counter = 0;
$duplicate_count = 0;

while(list($single_id,$single_data) = each($ids)) {
	print ++$counter . "/" . count($ids) . " (" . $duplicate_count . " duplicates found)\n";
	$single_rid = $single_data['single_rid'];
	$single_title = $single_data['single_title'];
	$artist_id = (string) $single_data['artist_id'];
	$artist_name = (string) $single_data['artist_name'];
	$subsql = "SELECT singles.single_id, singles.single_title FROM singles INNER JOIN artists ON artists.artist_id = singles.artist_id WHERE singles.single_title LIKE '" . mysqli_real_escape_string($con,$single_title) . "' AND artists.artist_name LIKE '$artist_name' AND singles_rid != '$single_rid'";
	$subresult = mysqli_query($con,$subsql);
	$msid = "ms" . str_pad($counter, 6, "0", STR_PAD_LEFT);

	if($subresult === false) {
		print "\t" . $subsql . "\n";
	}	

	if(mysqli_num_rows($subresult) != 0) {
		$duplicate_count++;
		print $single_id . "\t" . $artist_id . "\t" . $single_title . "\n";
		while($subrow = mysqli_fetch_assoc($subresult)) {
			$sub_single_id = (string) $subrow['single_id'];
			$sub_single_title = $subrow['single_title'];
			print "\t" . $sub_single_id . "\t" . $sub_single_title . "\n";

			// Map this single to $msid and remove from $ids array
			$insert_sql = "INSERT INTO single_mappings(single_id, msid, artist_id) VALUES ('$sub_single_id','$msid','$artist_id')";
			if(!mysqli_query($con,$insert_sql)) {
				die("ERROR: " . mysqli_error($con) . "\n");
			} else {
				unset($ids[$sub_single_id]);
				print "\t" . $sub_single_id . "\tMAPPED TO\t" . $msid . "\n";
			}
			unset($insert_sql);

		}
	}

	// Now map original single ID and remove from array
	$insert_sql = "INSERT INTO single_mappings(single_id, msid, artist_id) VALUES ('$single_id','$msid','$artist_id')";
	if(!mysqli_query($con,$insert_sql)) {
		die("ERROR: " . mysqli_error($con) . "\n");
	} else {
		unset($ids[$single_id]);
		print "\t" . $single_id . "\tMAPPED TO\t" . $msid . "\n";
	}
	mysqli_free_result($subresult);
	
}


?>