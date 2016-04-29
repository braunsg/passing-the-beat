<!--

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

Text and other descriptive content loaded into the visualization page

-->

<div class="content_row">
	<div class="content_section">
		<div class="text_block"><b>The Beatles</b>, <b>Beyonce</b>, <b>Cornelius</b>, and <b>Adele</b>.</div>
		<div class="text_block">What do they have in common? They are all artists who have achieved international success with their music.</div>
		<div class="text_block">Top music charts around the world are not always dominated by artists native to their respective countries. Often, top-ranking music singles jump from one chart to another as the music of artists from one country spreads to others. This animation gives us a way of visually quantifying these movements between three countries to show how music charts are, quite literally, passing the beat across the Atlantic and Pacific.</div>
	</div>

	<div class="content_section">
		<div class="section_header">US, UK, and Japan</div>
		<div class="text_block">
		Historically, crossover artists have been common in the top music charts of three countries in particular &ndash; the United States, the United Kingdom, and Japan. In this visualization, historical single rankings from the <a href="http://www.billboard.com/charts/hot-100" target="_blank">Billboard Top 100</a> (United States), the <a href="http://www.officialcharts.com/charts/singles-chart/" target="_blank">U.K. Top 200</a> (United Kingdom), and <a href="http://www.j-wave.co.jp/original/tokiohot100/" target="_blank">J-Wave Tokio Hot 100</a> (Japan) are aggregated to show the frequency with which artists from these three countries (and around the world) attain success with their music across the Pacific and the Atlantic. For the U.S. and U.K., these data extend back to 1960; for Japan, these data extend back to 1988, coinciding with the first year of the J-Wave Tokio Hot 100.
		</div>
		<div class="text_block">
		Run the animation to see the international dynamics of these music charts from 1960 to 2016. Hover over individual markers to see titles and artists of singles on each chart over time. For information on how to interpret this visualization, <a href="#data">continue reading below</a>.
		</div>

	</div>
</div>
<div class="page_division" id="artist-history">
Artist Data
</div>
<div class="content_row"> <!-- -->
	<div class="content_section">
		<div class="section_header">Individual Artist Single Rankings</div>
		<div class="text_block">
	In addition to following aggregate migrations of foreign artist singles between these charts, we can view these trends for singles by individual artists. In the visualization below, singles by artists that make crossover appearances on the top music charts of other countries are shown. Each circle represents one single making an appearance on the chart and date shown. The relative size of a circle is proportional to its ranking on each given chart &mdash; a large circle means a single is ranking close to the #1 position while a small circle means rankings closer to #100. Any circle that is colored gold represents a single at the #1 position for the music chart indicated. The color of the rest of the circles indicates the artist's home country  (<span style="color:#377eb8;font-weight:600">blue</span> for United States, <span style="color:#4daf4a;font-weight:600">green</span> for the U.K., <span style="color:#e41a1c;font-weight:600">red</span> for Japan, and <span style="font-weight:600">black</span> for all other countries).<br><br>
	Hover over a circle to see international ranking trends for each given single. When one circle is hovered, the rest of the circles corresponding to the same single are highlighted as a group. Try changing the artist name to another one of your own interest to see how their singles have fared across the United States, United Kingdom, and Japan. In some interesting instances, a single that makes an appearance on one of the top music charts may make additional appearances decades after its first release.
		</div>
	</div>
</div>
<div id="artists">
	<div id="artist_select_wrapper">
		<div id="artist_select">
			<input type="text" name="artist" class="input_artist_search" value="Beyonce">
			<div id="autocomplete_container"></div>
			<div class="text_block" style="margin-top:25px;">Enter a specific artist name in the field above. When you select an artist, the chart to the right will automatically regenerate. Some examples include Beyonce, The Beatles, Adele, Justin Bieber, and Lorde.</div>
		</div>
		<div id="artist_svg_wrapper">
		</div>
	</div>
</div>

<div class="page_division" id="data">
Understanding the data
</div>

<div class="content_row">
	<div class="content_section">
		<div class="section_header">How to Read</div>
		<div class="text_block">
	In the animation above, each horizontal axis corresponds to the top music charts of the United States (Billboard Top 100 Singles), the United Kingdom (UK Top 200 Singles), and Japan (J-Wave Tokio Hot 100), shown in order from top to bottom. When a circle appears on one of these axes, this means a single by an artist from another country has made an appearance on the chart represented by the axis. The color of the circle indicates the artist's country of origin (<span style="color:#377eb8;font-weight:600">blue</span> for United States, <span style="color:#4daf4a;font-weight:600">green</span> for the U.K., <span style="color:#e41a1c;font-weight:600">red</span> for Japan, and <span style="font-weight:600">black</span> for all other countries), and a marker's movement indicates that the single has jumped from one other country's chart to the one indicated.
	<br><br>The horizontal position of each marker indicates the single's rank position in the given chart, with position 1 at the far left and position 100 at the far right.
	<br><br>As the animation progresses, it keeps a tally of how many singles by foreign artists have held each given rank position on each chart over time. As this grows, a horizontally arranged bar chart is created on each axis; the gold bars represent the peak chart position reached by a given single, and the dark gray bars indicate the position into which a foreign single first makes its appearance on the given chart. As the width of these rectangles grows in proportion to the number of singles obtained at each position, it becomes possible to see, for instance, the proportion of foreign artists' singles that enter a country's music charts at the #1 position.	
		</div>
	</div>
	<div class="content_section">
		<div class="text_block">
			<img class="key_image" src="inc/img/key_figure-indiv_bars-annotated.png">
			<img class="key_image" src="inc/img/key_figure-total_bars-annotated.png">
		</div>
	</div>
	
</div>
<div class="content_row">
	<div class="content_section">
		<div class="section_header">What to look for</div>
		<div class="section_subheader">The British Invasion</div>
		<div class="text_block" style="padding-left:50px; padding-right:150px;">
		In the middle of the 1960s, several British artists and groups exploded in popularity in the United States. During this so-called <a target="_blank" href="https://en.wikipedia.org/wiki/British_Invasion">British Invasion</a>, music by groups like The Beatles was highly successful, and this is reflected in the animation above between 1960 and 1970.<br><br>
		British artists are represented in the visualization with green circles, and several hits make their way into the Billboard Top 100 during this time. Pause the animation and hover over individual markers to see examples of popular songs by these groups.
		</div>
		<div class="section_subheader">The U.K. Top 200 comes of age</div>
		<div class="text_block" style="padding-left:50px; padding-right:150px;">
			The U.K. equivalent of the Billboard Top 100 did not officially exist until 1969, and for many years after, it did not include a full ranking of 200 singles (explaining the truncated horizontal axis for the U.K. Top 200 in the animation). Today, the official chart features 200 top hits, although often only the top 100 are presented in publication. For purposes of comparison here, only the top 100 singles for each year of the U.K. Top 200 are presented.
		</div>
		<div class="section_subheader">The J-Wave Tokio Hot 100 makes it debut</div>
		<div class="text_block" style="padding-left:50px; padding-right:150px;">The J-Wave Tokio Hot 100 is the youngest list among the 3 shown here, making its debut on October 2, 1988. Since its inception, many notable U.S. and U.K. artists have dominated its rankings, and hit singles by these artists have even occasionally made their way into the J-Wave list before making an appearance on their home country charts.</div>
		<div class="section_subheader">Country differences</div>
		<div class="text_block" style="padding-left:50px; padding-right:150px;"></div>
		<div class="text_block" style="padding-left:50px; padding-right:150px;">
			On average, the U.K. Top 200 Singles has featured hits by foreign artists more often since 1960 than the Billboard Top 100. Although the J-Wave Tokio Hot 100 (year of inception 1988) is much younger than the Billboard Top 100 (year of inception 1956), a visual comparison of the total number of foreign artist appearances on both the Billboard and J-Wave charts shows that J-Wave competes with the Billboard for a high proportion of foreign artists. Empirically, this is unsurprising; a review of J-Wave rankings shows that many U.S. and U.K. artists consistently attain high success relative to Japanese artists on the same chart.
		</div>
		<div class="section_subheader">Japanese singles make fewer international appearances</div>
		<div class="text_block" style="padding-left:50px; padding-right:150px;">
		Only a small handful of Japanese artists have released singles making appearances outside of Japan, and only on the U.K. Top 200. Examples of these artists include recording artist and producer Cornelius (whose single "Drop" found its way into the U.K. in 2002) and band Mad Capsule Markets (whose single "Fly High" made the U.K. charts in 2003).
		</div>
		<div class="section_subheader">Changing dynamics in crossovers</div>
		<div class="text_block" style="padding-left:50px; padding-right:150px;">
			In the early decades of the music charts presented here, it was common for foreign artist singles to make an appearance on a country's chart at a middle or lower ranking and then gradually rise up the chart, peaking at its top ranking position over time. In the past decade in particular, however, this dynamic has changed &mdash; instead of a gradual movement up the ranks, more foreign artist singles now make a first appearance on foreign charts at their peak position, thereafter falling through the ranks quickly.<br><br>
			Observe the gray and gold bars for each chart to see this in action. For each rank position on a chart, the gold bar represents the relative number of foreign artist singles peaking at that position while the gray represents the relative number of singles making a first appearance at that position. For the U.K. Top 200 in particular, a large number of foreign artist singles both make a first appearance on the chart as well as peak at the #1 spot.
		</div>
	</div>
</div>
<div class="page_division" id="about">
Data Analysis
</div>
<div class="content_row">
	<div class="content_section">
		<div class="section_header">About the Data and Analysis</div>
		<div class="text_block">
This visualization is built upon historical data for all single rankings on each chart (<a href="http://www.billboard.com/charts/hot-100" target="_blank">Billboard Top 100 Singles</a>, <a href="http://www.officialcharts.com/charts/singles-chart/" target="_blank">UK Top 200 Singles</a>, and <a href="http://www.j-wave.co.jp/original/tokiohot100/" target="_blank">J-Wave Tokio Hot 100</a>) over time between 1960 and 2016. For each chart, these data include song title, artist, record label, chart ranking, and ranking date. From these aggregated data, this visualization depicts <i>only</i> data about singles (and their artists) that have made appearances on <i>charts outside the home country of their artists</i>. Additionally, data for the U.K. Top 200 have been truncated to include only the top 100 ranking singles for each complete year.<br><br>
Due to inconsistencies in some publishing standards in the music industry, some data may be excluded unintentionally from analysis. For example, data about singles by the group <i>Rolling Stones</i> and <i>The Rolling Stones</i> are aggregated separately in the overall analysis due to differences in how these bands (and others) may be signified on different record labels. For similar reasons, there may also be some data about artist location (country) missing or seemingly inaccurate, for example when deciding whether an artist's country of origin should be defined as the country in which they live/were born versus where they actively produce their music. For the purpose of this visualization, these errors are relatively negligible.<br><br>
All music ranking data for these charts were provided by <a href="http://www.academicrightspress.com/entertainment/music" target="_blank">Music Industry Data</a> (Music ID) from Academic Rights Press. Data about artist locations (<i>i.e.,</i> artist country of origin) were retrieved via the <a target="_blank" href="http://developer.echonest.com/docs/v4">EchoNest API</a>. The visualizations were created with <a target="_blank" href="http://d3js.org">D3.js</a>.<br><br>
The full source code for this visualization is available <a target="_blank" href="https://github.com/braunsg/passing-the-beat">on GitHub</a>.
		</div>
	</div>
</div>
<div class="content_row">
	<div class="content_section">
		<div class="section_header">Acknowledgments</div>
		<div class="text_block">
		This visualization was created by <a target="_blank" href="http://www.stevengbraun.com/">Steven Braun</a> (&copy; 2016) using data provided by <a href="http://www.academicrightspress.com/entertainment/music" target="_blank">Music ID</a> from Academic Rights Press. Steven Braun is the Data Analytics and Visualization Specialist in the Northeastern University Libraries <a target="_blank" href="http://dsg.neu.edu">Digital Scholarship Group</a> and may be contacted via email at <i>braunsg[at]gmail.com</i>.
		</div>
	</div>
</div>