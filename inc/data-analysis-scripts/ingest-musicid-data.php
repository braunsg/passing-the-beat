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

This script was used to inject historical single ranking data for the Billboard Top 100, UK Top 200,
and Japan J-Wave Tokio Hot 100 into a MySQL database for analysis

*/

// Retrieve database connection parameters
include("default-config.php");

$con = mysqli_connect($dbhost,$dbuser,$dbpw,$dbname);

$charts = array("billboard-us" => array("filename" => "Billboard_Top_100_Singles_2-11-16.csv","read-data" => false),
				"top100-uk" => array("filename" => "UK_Top_100_Singles_2-11-16.csv","read-data" => false),
				"jwave" => array("filename" => "Japan_J-Wave-Tokio Hot_100_2-11-16.csv","read-data" => true));

$data_dir = "exported_charts_2-11-16/";			


// Set encoding to UTF8 for Japanese characters
mysqli_query($con,"SET NAMES utf8");

// Loop through each chart's data
foreach($charts as $chart_name => $data) {
	if(!$data["read-data"]) {
		continue;
	}

	print "Reading data from " . $data["filename"] . "...\n";
	sleep(1);
	
	// Determine rankings table name
	switch($chart_name) {
		case "billboard-us":
			$rankings_table_name = "rankings_us";
			break;
		case "top100-uk":
			$rankings_table_name = "rankings_uk";
			break;
		case "jwave":
			$rankings_table_name = "rankings_jp";
			break;
	}
	
	$data_file = fopen($data_dir . $data["filename"],"r");
	
	$line_counter = 0;
	while($line = fgetcsv($data_file)) {
		// Skip column headers line
		if(++$line_counter == 1) {
			continue;
		}

		$single_title = $line[0];
		$single_url = $line[1];
		$single_id = str_replace("http://musicid.academicrightspress.com/releases/","",$single_url);
		$single_artist_name = $line[2];
		$single_artist_url = $line[3];
		$single_artist_id = str_replace("http://musicid.academicrightspress.com/artists/","",$single_artist_url);
		$single_label = $line[4];
		$single_ranking = $line[5];
		$single_ranking_date = $line[6];	


		// Add ranking to table
		$rankings_data = array("single_id" => "'$single_id'",
							   "ranking_date" => "'$single_ranking_date'",
							   "ranking" => $single_ranking,
							   "chart_name" => "'$chart_name'");

		$fields_list = implode(",",array_keys($rankings_data));
		$values_list = implode(",",array_values($rankings_data));
		
		$add_ranking_sql = "INSERT INTO $rankings_table_name (" . $fields_list . ") VALUES (" . $values_list . ")";
		if(!mysqli_query($con,$add_ranking_sql)) {
			print "\t" . $add_ranking_sql . "\n";
			die("ERROR: " . mysqli_error($con) . "\n");
		} else {
		
			print "ADDED RANKING\t" . $chart_name . "\t" . $single_ranking_date . "\t" . $single_ranking . "\t" . $single_title . "\n";
			
			// Determine if single already exists in database
			$exists_single_sql = "SELECT 1 FROM singles WHERE single_id = '$single_id'";
			$result = mysqli_query($con,$exists_single_sql);
			if(mysqli_num_rows($result) == 0) {
			
				$single_data = array("single_id" => "'$single_id'",
									  "single_title" => "'" . mysqli_real_escape_string($con,$single_title) . "'",
									  "artist_id" => "'$single_artist_id'",
									  "single_url" => "'$single_url'",
									  "single_label" => "'" . mysqli_real_escape_string($con,$single_label) . "'");

				$fields_list = implode(",",array_keys($single_data));
				$values_list = implode(",",array_values($single_data));
				
				$add_single_sql = "INSERT INTO singles(" . $fields_list . ") VALUES(" . $values_list . ")";
				if(!mysqli_query($con,$add_single_sql)) {
					print "\t" . $add_single_sql . "\n";
					die("ERROR: " . mysqli_error($con) . "\n");
				} else {
					print "\tADDED SINGLE\t" . $single_id . "\t" . $single_title . "\n";
				}
				
			}


			// Determine if artist already exists in database
			$exists_artist_sql = "SELECT 1 FROM artists WHERE artist_id = '$single_artist_id'";
			$result = mysqli_query($con,$exists_artist_sql);
			if(mysqli_num_rows($result) == 0) {
			
				$artist_data = array("artist_id" => "'$single_artist_id'",
									  "artist_name" => "'" . mysqli_real_escape_string($con,$single_artist_name) . "'",
									  "artist_url" => "'$single_artist_url'");

				$fields_list = implode(",",array_keys($artist_data));
				$values_list = implode(",",array_values($artist_data));
				
				$add_artist_sql = "INSERT INTO artists(" . $fields_list . ") VALUES(" . $values_list . ")";
				if(!mysqli_query($con,$add_artist_sql)) {
					print "\t" . $add_artist_sql . "\n";
					die("ERROR: " . mysqli_error($con) . "\n");
				} else {
					print "\tADDED ARTIST\t" . $single_artist_id . "\t" . $single_artist_name . "\n";
				}
				
			}



		}
							  
	}
	

}

mysqli_close($con);
				
?>