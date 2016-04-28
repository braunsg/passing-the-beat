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

Defines global site variables and parameters

*/

// Define default PHP initialization parameters
ini_set("auto_detect_line_endings",true);
date_default_timezone_set("America/New_York");

// Define database parameters
$dbhost = "DATABASE_HOST";
$dbuser = "DATABASE_USER";
$dbpw = "DATABASE_PASSWORD";
$dbname = "DATABASE_NAME";

// Define file paths
$artist_data_path = "../data/artist-data/";
$singles_data_path = "../data/singles-data/";
?>