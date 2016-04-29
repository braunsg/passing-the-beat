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

This script retrieves data about artist location/country of origin using 
the EchoNest API

*/


// Retrieve database connection parameters
include("default-config.php");
$con = mysqli_connect($dbhost,$dbuser,$dbpw,$dbname);

$logfile = fopen("logfile.txt","a");

function do_log($string,$logfile) {
	fwrite($logfile,$string);
	print $string;
}

// Retrieve artists to analyze from file
do_log("Retrieving artists to analyze from file...\n",$logfile);

$artists = array();

// Open file of artist IDs
$ids_file = fopen("artists_id_files.txt","r");

while($line = fgets($ids_file)) {
	$data = explode("\t",trim($line));
	$artists[]  = array("artist_id" => $data[0], "artist_name" => $data[1]);
}

// print_r($single_ids); exit;
$total_artist_count = count($artists);

do_log("Preparing to loop through " . $total_artist_count . " artists...\n",$logfile);
sleep(1);
// exit;

// Get Echonest data
$api_key = "ECHONEST_API_KEY";
$consumer_key = "ECHONEST_CONSUMER_KEY";
$shared_secret = "ECHONEST_SHARED_SECRET";

$url_base = "http://developer.echonest.com/api/v4/artist/profile?api_key=" . $api_key . "&format=json&bucket=artist_location&bucket=terms";

$openCurl = curl_init();

foreach($artists as $i => $artist_data) {

	// Rate limit: 20 calls per minute
	sleep(3);
	
	$artist_id = $artist_data["artist_id"];
	$artist_name = $artist_data["artist_name"];
	do_log("ARTIST\t" . $i . " of " . $total_artist_count . "\t" . $artist_name . "\n",$logfile);
	curl_setopt_array($openCurl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => $url_base . "&name=" . urlencode($artist_name)

	));

	$result = curl_exec($openCurl);
	$json = json_decode($result,true);

	// Initialize term_strings array here so that if no artist is returned,
	// no terms are incorrectly attributed to the artist

	$term_strings = array();
	$location_find = 0;
	if(count($json["response"]["artist"]) == 0) {
		do_log(print_r($json["response"]["status"]),$logfile);
		$artist_echonest_id = "NULL";
	} else {
		$artist_echonest_id = "'" . $json["response"]["artist"]["id"] . "'";
		
		$location_data = $json["response"]["artist"]["artist_location"];
		if($location_data) {
			$location_find = 1;
		}
		$location_fields = array("artist_country" => "country",
								 "artist_city" => "city",
								 "artist_region" => "region",
								 "artist_location" => "location");
		foreach($location_fields as $var=>$field) {
			$value = trim($location_data[$field]);
			if(strlen($value) == 0) {
				$$var = "NULL";
			} else {
				$$var = "'" . mysqli_real_escape_string($con,$value) . "'";
			}
		}
	
		$term_list = "";

		// Specify an arbitrary term weight threshold --
		// Decide which terms to include in database
		$term_wt_threshold = 0.40;	
		$terms = $json["response"]["artist"]["terms"];
	
		foreach($terms as $j=>$term) {
			$term_weight = (float) $term["weight"];
			if($term_weight >= $term_wt_threshold) {
				$term_strings[] = array("name" => $term["name"],
										"frequency" => $term["frequency"],
										"weight" => $term_weight);
				$term_list .= ($term["name"] . ",");	
			}
		}
	}
	
// Insert Echonest ID
	$check = mysqli_query($con,"SELECT artist_external_id FROM artist_ids WHERE artist_id = '$artist_id' AND artist_external_id IS NULL");
	if(mysqli_num_rows($check) == 1) {
		$id_sql = "UPDATE artist_ids SET artist_external_id = '$artist_echonest_id' WHERE artist_id = '$artist_id'";
		if(!mysqli_query($con,$id_sql)) {
			do_log("\t\tNOTICE: " . mysqli_error($con) . "\n",$logfile);
		} else {
			do_log("\tRECORDED ECHONEST ID\t" . $artist_echonest_id . "\n",$logfile);
		}
	} else {
		$id_sql = "INSERT IGNORE INTO artist_ids (artist_id,artist_external_id_type,artist_external_id) VALUES('$artist_id','echonest'," . $artist_echonest_id . ")";
		if(!mysqli_query($con,$id_sql)) {
			do_log("\t\tNOTICE: " . mysqli_error($con) . "\n",$logfile);
		} else {
			do_log("\tRECORDED ECHONEST ID\t" . $artist_echonest_id . "\n",$logfile);
		}
	
	}

// Insert location
	if($location_find == 1) {
		$check = mysqli_query($con,"SELECT artist_country FROM artist_locations WHERE artist_id = '$artist_id' AND artist_country IS NULL");
		if(mysqli_num_rows($check) == 1) {

			$location_sql = "UPDATE artist_locations SET artist_city = $artist_city, artist_country = $artist_country, artist_region = $artist_region, artist_location = $artist_location WHERE artist_id = '$artist_id'";
			if(!mysqli_query($con,$location_sql)) {
				do_log("\t\tNOTICE: " . mysqli_error($con) . "\n",$logfile);
			} else {
				do_log("\tRECORDED ARTIST LOCATION\t" . $artist_country . "\n",$logfile);
			}
		} else {
			$location_sql = "INSERT IGNORE INTO artist_locations (artist_id,artist_city,artist_country,artist_region,artist_location) VALUES('$artist_id',$artist_city,$artist_country,$artist_region,$artist_location)";
			if(!mysqli_query($con,$location_sql)) {
				do_log("\t\tNOTICE: " . mysqli_error($con) . "\n",$logfile);
			} else {
				do_log("\tRECORDED ARTIST LOCATION\t" . $artist_country . "\n",$logfile);
			}		
		}
	} else {
		do_log("\tNO ARTIST RESULT RETURNED",$logfile);
	}

// Insert terms/genres
	foreach($term_strings as $j=>$term) {
		$term_name = mysqli_real_escape_string($con,$term["name"]);
		$term_weight = $term["weight"];
		$term_freq = $term["frequency"];
		
		$genres_sql = "INSERT INTO artist_genres (artist_id,artist_term,artist_term_weight,artist_term_freq) VALUES('$artist_id','$term_name',$term_weight,$term_freq)";
		if(!mysqli_query($con,$genres_sql)) {
			do_log("\t\tNOTICE: " . mysqli_error($con) . "\n",$logfile);
			$term_error = 1;
		} else {
			$term_error = 0;
		}
	}
	if($term_error == 0) {
		do_log("\tRECORDED ARTIST GENRES\t" . substr($term_list,0,strlen($term_list)-1) . "\n",$logfile);
	} else {
		do_log("GENRE ERROR: see above\n",$logfile);
	}
}

do_log("Process complete.\n",$logfile);


?>