<!--

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

Index page initializing all visualizations and content

-->

<!DOCTYPE html>
<head>

	<title>Crossover artists</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<!-- Initialize default site variables -->
	<?php	
		include("inc/config/default-config.php");
	?>
	
	<!-- Load jQuery, D3 libraries -->
	<script src="inc/lib/jquery-1.11.2.min.js"></script>
	<script src="inc/lib/d3.v3.min.js"></script>
	</script>
	<link rel="stylesheet" type="text/css" href="inc/config/default-style.css">


<script src="inc/lib/default-functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<div id="header">
			<div id="title_block">
				<div class="title">Passing<span style="color:steelblue;">the</span><span style="font-weight:600; color: steelblue;">beat</span>
				</div>
				<div class="subtitle">Crossover Artists in the U.S., U.K., and Japan
				</div>
			</div>
			<div id="navigation_block">
				<div class="navigation_item"><a href="#countries">Countries</a></div>
				<div class="navigation_item"><a href="#artist-history">Artists</a></div>
				<div class="navigation_item"><a href="#data">The Data</a></div>
				<div class="navigation_item"><a href="#about">About</a></div>
			</div>
		</div>
		<div id="countries"></div> <!-- end #viz -->
			<!-- 	content div -->

			<?php  include("inc/content/content.php"); ?>

			<!-- end content div -->
	</div> <!-- end #wrapper -->
</body>
</html>