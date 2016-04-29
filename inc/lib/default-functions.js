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

JavaScript functions used to create all visualizations and load content

*/


// Initialize upon page load
$(document).ready(function() {

	//////// Declare some global variables
	var data = {}, dates = [], artist_ids = {};	// objects holding data and dates for main visualization
	var artist_svg;		// variable reference to SVG in artist history module
	var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
	var tracker_paused, manual_pause, date_pointer, svg;
	
	var timer;
	var animation_speed = 1;
	var animation_completed = false;		// For debugging and testing purposes
	var artist_retrieved_name;
	var autocomplete_top = $("input[name='artist']").position().top + $("input[name='artist']").outerHeight() + "px";
	var autocomplete_left = $("input[name='artist']").position().left + "px";
	
	// Create placeholder object that will store information about when a foreign artist single
	// appears on a country's chart
	var markers = {};
	
	var distr_uk, distr_us,distr_jp, distr_scale,uk_distr_bars,uk_distr_bars_entry,us_distr_bars,us_distr_bars_entry,jp_distr_bars,jp_distr_bars_entry;

	//////// Bind page events to elements

	// Bind onclick for artist history search
	$("input[name='artist']").on("click", function() {
		$(this).val("");
		$("#autocomplete_container").css("visibility","hidden");
	});
	
	
	//////// Define events on page initialization	

	// At page initialization, start artist history module
	// using Beyonce as example
	$.post("inc/content/fetch-artist-history.php",{artist_id: "91577"},function(artist_data) {
		update_artist(JSON.parse(artist_data));
	});

	// Position individual artist history search #autocomplete_container
	$("#autocomplete_container").css({"top": autocomplete_top, "left": autocomplete_left});
	
	// Put loading GIF in main visualization placeholder until
	// visualization is finished loading		
	$("#countries").css({"min-width": $(document).innerWidth() + "px", "min-height": (0.5*$(document).innerHeight()) + "px"});
	$("#countries").html($("<div class='loading'><img src='inc/img/data-loading.gif'></div>").css("top",$("#countries").innerHeight()/2));

	
	//////// Define functions
	
	// JavaScript string comparison prototype function --
	// replicates autocomplete behavior without using MySQL LIKE
	// http://stackoverflow.com/questions/1314045/emulating-sql-like-in-javascript
	String.prototype.like = function(search) {
		if (typeof search !== 'string' || this === null) {return false; }
		// Remove special chars
		search = search.replace(new RegExp("([\\.\\\\\\+\\*\\?\\[\\^\\]\\$\\(\\)\\{\\}\\=\\!\\<\\>\\|\\:\\-])", "g"), "\\$1");
		// Replace % and _ with equivalent regex
		search = search.replace(/%/g, '.*').replace(/_/g, '.');
		// Check matches
		return RegExp('^' + search + '$', 'gi').test(this);
	}
	
	// D3 prototype - moveToFront() moves
	// selected SVG elements to front/top of stack	
	// http://bl.ocks.org/eesur/4e0a69d57d3bfc8a82c2
	d3.selection.prototype.moveToFront = function() {  
		return this.each(function(){
			this.parentNode.appendChild(this);
		});
	};	
	
	// D3 prototype - moveToBack() moves
	// selected SVG elements to back/bottom of stack	
	// http://bl.ocks.org/eesur/4e0a69d57d3bfc8a82c2
    d3.selection.prototype.moveToBack = function() {  
        return this.each(function() { 
            var firstChild = this.parentNode.firstChild; 
            if (firstChild) { 
                this.parentNode.insertBefore(this, firstChild); 
            } 
        });
    };	


	// This function updates and regenerates artist history SVG
	function update_artist(artist_data) {
		$("#autocomplete_container").css("visibility","hidden");

		var a_data = artist_data["data"];
		var a_min_date = new Date(artist_data["min_date"]);
		var a_max_date = new Date(artist_data["max_date"]);

		var customTimeFormat = d3.time.format.multi([
		  ["%b", function(d) { return d.getMonth(); }],
		  ["%Y", function() { return true; }]
		]);

		var a_country_name = artist_data["country"];

		var a_artist = artist_data["artist"];
		var a_width, a_height;
		var song_title_label, artist_name_label;
		artist_retrieved_name = a_artist;
		
		var a_boxheight = 50;
		var a_margin = {top: 50, left: 100, right: 50, bottom: 50};

		var a_width = $("#artist_svg_wrapper").innerWidth();
		var a_height = 300 + a_margin.top;
		var a_chart_spacer = (a_height - a_margin.top - a_margin.bottom)/4;

		var a_y_scale = d3.scale.ordinal()
			.domain(["United States","United Kingdom","Japan"])
			.range([a_margin.top + a_chart_spacer, a_margin.top + a_chart_spacer*2, a_margin.top + a_chart_spacer*3]);
	
		var a_time_scale = d3.time.scale()
			.domain([a_min_date, a_max_date])
			.nice(d3.time.year)
			.range([a_margin.left, a_width-a_margin.right]);
	
		var a_us_scale = d3.scale.sqrt()
			.domain([100,1])
			.range([1,50]);
	
		var a_uk_scale = d3.scale.sqrt()
			.domain([100,1])
			.range([1,50]);
	
		var a_jp_scale = d3.scale.sqrt()
			.domain([100,1])
			.range([1,50]);

		var a_r_scale = d3.scale.sqrt()
			.domain([100,1])
			.range([1,25]);
			
		var a_opacity_scale = d3.scale.linear()
			.domain([100,1])
			.range([0.1,1]);
			
		var a_xAxis = d3.svg.axis()
			.scale(a_time_scale)
			.tickFormat(customTimeFormat)
			.orient("bottom");

		
		// If #artist_history_svg not yet created,
		// generate it here
		if($("#artist_history_svg").length === 0) {
		
			var artist_svg = d3.select("#artist_svg_wrapper")
						.append("svg")
						.attr("width",a_width)
						.attr("height",a_height)
						.attr("id","artist_history_svg")
						.append("g")
							.attr("class","canvas")
							.attr("width",a_width-a_margin.left-a_margin.right)
							.attr("height",a_height-a_margin.top-a_margin.bottom);


			var a_rankings_us = artist_svg.append("line")
				.classed("rankings_us",true)
				.classed("axis",true)
				.attr("y1",a_y_scale("rankings_us"))
				.attr("x1", a_margin.left)
				.attr("x2", a_width-a_margin.right)
				.attr("y2", a_y_scale("rankings_us"))
				.attr("stroke-width",5);

			var a_rankings_uk = artist_svg.append("line")
				.classed("rankings_uk",true)
				.classed("axis",true)
				.attr("y1",a_y_scale("rankings_uk"))
				.attr("x1", a_margin.left)
				.attr("x2", a_width-a_margin.right)
				.attr("y2", a_y_scale("rankings_uk"))
				.attr("stroke-width",5);

			var a_rankings_jp = artist_svg.append("line")
				.classed("rankings_jp",true)
				.classed("axis",true)
				.attr("y1",a_y_scale("rankings_jp"))
				.attr("x1", a_margin.left)
				.attr("x2", a_width-a_margin.right)
				.attr("y2", a_y_scale("rankings_jp"))
				.attr("stroke-width",5);
	
			var a_xAxis_line = artist_svg.append("g")
				.attr("id","artist_history_timeline")
				.attr("class","time_axis")
				.attr("transform","translate(0," + (a_height*3/4+50) + ")")
				.call(a_xAxis);
	
			var artist_name_label = artist_svg.append("text")
					.attr("class","artist_name_text")
					.attr("x", 25)
					.attr("y",a_margin.top-15);
	
			// Append graphics representing US, UK, and Japan
			artist_svg.append("svg:image")
				.attr("xlink:href","inc/img/usa.png")
				.attr("width",50)
				.attr("height",50)
				.attr("x", 25)
				.attr("y",a_y_scale("United States") - 25);

			artist_svg.append("svg:image")
				.attr("xlink:href","inc/img/uk.png")
				.attr("width",50)
				.attr("height",50)
				.attr("x", 25)
				.attr("y",a_y_scale("United Kingdom") - 25);

			artist_svg.append("svg:image")
				.attr("xlink:href","inc/img/japan.png")
				.attr("width",50)
				.attr("height",50)
				.attr("x", 25)
				.attr("y",a_y_scale("Japan") - 25);
	
			// Create tooltip placeholder
			var artist_tooltip = d3.select("#artist_svg_wrapper").append("div")	
				.attr("id","artist_tooltip")
				.attr("class","tooltip")
				.style("opacity", 0);

		}  else {
		
			// if SVG already created, bind it to variable artist_svg here
			artist_svg = d3.select("#artist_history_svg");
			artist_tooltip = d3.select("#artist_tooltip");
			
		} // end if(typeof svg undefined)
	
		d3.select("#artist_history_timeline").call(a_xAxis);
		
		song_title_label = artist_svg.append("text")
			.attr("class","song_title_text")
			.attr("opacity",0);

		d3.select(".artist_name_text")
			.text(artist_retrieved_name);
			
		switch(a_country_name) {
			case "United States":
				var a_class_name = "artist_us";
				break;
			case "United Kingdom":
				var a_class_name = "artist_uk";
				break;
			case "Japan":
				var a_class_name = "artist_japan";
				break;
			default:
				var a_class_name = "artist_unassigned";
				break;
		}

		// Remove all existing marks on artist_svg, if any
		artist_svg.selectAll("circle").remove();

		// Iterate through singles data for artist
		for(var a_chart in a_data) {

			var a_chartdata = a_data[a_chart];
			
			switch(a_chart) {		
				case "billboard-us":
					var a_chart_axis = "United States";
					break;
				case "top100-uk":
					var a_chart_axis = "United Kingdom";
					break;
				case "jwave":
					var a_chart_axis = "Japan";
					break;
			}		

			// Create circle elements for this group of single ranking data
			artist_svg.selectAll("." + a_chart + ".artist_circle")
				.data(a_chartdata)
				.enter()
				.append("circle")
					.attr("class",function(d) {
						return a_class_name + " " + d.msid + " artist_circle";
					})
					.attr("cx", function(d) { return a_time_scale(new Date(d.date)); })
					.attr("cy", function() { 
						return a_y_scale(a_chart_axis);
					})
					.attr("r", function(d) { 	
						return a_r_scale(d.ranking);
					})
					.style("fill", function(d) { if(Number(d.ranking) === 1) { return "#FFCC66"; }})
					.attr("stroke-width", 2)
					.attr("opacity", function(d) { 
						return a_opacity_scale(d.ranking);
					})
					.on("mouseover", function(d) {
						artist_svg.selectAll(".artist_circle")
							.classed("artist_deselect",true);
					
						artist_svg.selectAll(".artist_circle." + d.msid)
							.moveToFront()						
							.classed("artist_deselect",false);

						var position_top = Number(d3.select(this).attr("cy")) + 15;
						artist_tooltip.style("opacity", 0.9);		
						
						artist_tooltip.html(d.single_title);
						if(Number(d3.select(this).attr("cx")) < a_width/2) {
							var position_left = Number(d3.select(this).attr("cx"));
							artist_tooltip.style("left", position_left + "px")	
								.style("right","auto")	
								.style("top", position_top + "px")
								.style("border-right","0px")
								.style("border-left","2px solid #AAA");
							
						} else {
							var position_right = a_width - (Number(d3.select(this).attr("cx")));
							artist_tooltip.style("right", position_right + "px")		
								.style("left","auto")	
								.style("top", position_top + "px")
								.style("border-left","0px")
								.style("border-right","2px solid #AAA");

						}

					}).on("mouseout", function(d) {
						
						artist_svg.selectAll(".artist_circle")
							.classed("artist_deselect",false);

						artist_tooltip.style("opacity",0);

					});
					
		} // end for(var a_chart in a_data)

		artist_svg.selectAll(".artist_circle")
			.filter(function(d) { return Number(d.ranking) == 1; })
			.moveToFront();

	} // end function update_artist()

	// Get artist ID data from file
	var get_artists = $.get("inc/data/artist_autocomplete.csv", function(return_artists) {
		artist_ids = $.csv.toObjects(return_artists);
	});
	
	// Get date keys from file
	// Using jQuery CSV library:
	// https://github.com/evanplaice/jquery-csv/
	var get_dates = $.get("inc/data-analysis-scripts/dates.csv", function(return_dates) {
		dates = $.csv.toArray(return_dates);		
	});


	// Get animation data from file
	// Using jQuery CSV library:
	// https://github.com/evanplaice/jquery-csv/
	var get_data = $.get("inc/data-analysis-scripts/singles-data.csv", function(return_data) {
		var raw_data = $.csv.toObjects(return_data);
		raw_data.forEach(function(data_point) {
			var this_date = data_point["date"];
			if(!(this_date in data)) {
				data[this_date] = [];
			}
			data[this_date].push(data_point);
		});
	});
	
	// Generate main visualization when artists, dates, and single data are all loaded
	$.when(get_dates,get_data,get_artists).done(function() {
		$(".loading").remove();
		
		// Bind artist history search to name input field and autocomplete
		$("input[name='artist']").on("input", function() {
			var search = $(this).val();
			if(search.length > 0) {
				$("#autocomplete_container").css("visibility","visible");
				var artist_matches = [];
				artist_ids.forEach(function(artist) {
					var match_name = artist["artist_name"];
					if(match_name.like('%' + search + '%')) {
						artist_matches.push(artist);
					}
				});

				var artist_match_string = "";
				artist_matches.forEach(function(artist) {
					artist_match_string += "<div class='autocomplete_row' id='" + artist["artist_id"] + "'>" + artist["artist_name"] + "</div>";
				});
				$("#autocomplete_container").html(artist_match_string);
				$(".autocomplete_row").click(function() {
					var result_content = $(this).text();
					var artist_id = $(this).attr("id");
					$("input[name='artist']").val(result_content);
					artist_retrieved_name = result_content;
					$.post("inc/content/fetch-artist-history.php",{search_name: result_content, artist_id: artist_id},function(artist_data) {
						update_artist(JSON.parse(artist_data));
					});
				});
			} else {
				$("#autocomplete_container").css("visibility","hidden");
			}
		});
		

		// Proceed with variables for creating the main visualization
		var width = $("#countries").innerWidth();
		var height = $("#countries").innerHeight();
		var margin = {top: 25, left: 250, right: 50, bottom: 25};
		var boxwidth = (width - margin.left - margin.right) / 100;
		var boxheight = 20;
		var chart_spacing = (height - margin.bottom)/4;

		// Create main visualization SVG
		svg = d3.select("#countries")
			.append("svg")
			.attr("id","main_animation_svg")
			.attr("width",width)
			.attr("height",height)
			.classed("svg-content-responsive", false)
			.append("g")
				.attr("class","canvas")
				.attr("width",width-margin.left-margin.right)
				.attr("height",height-margin.top-margin.bottom);

		// Specifies placement of individual country axes
		var y_scale = d3.scale.ordinal()
			.domain(["United States","United Kingdom","Japan"])
			.range([chart_spacing, chart_spacing*2, chart_spacing*3]);

		var x_scale = d3.scale.linear()
			.domain([1,100])
			.range([margin.left, width-margin.right]);
			
		// Create gridlines that correspond to ranking positions	
		var gridlines = svg.selectAll(".gridline")
			.data([1,10,20,30,40,50,60,70,80,90,100])
			.enter()
			.append("line")
			.attr("x1",function(d) { return x_scale(d); })
			.attr("y1", height-margin.bottom)
			.attr("x2", function(d) { return x_scale(d); })
			.attr("y2", margin.top)
			.attr("stroke","#FFFFFF")
			.attr("stroke-width",2);

		// Create individual country chart axes
		var rankings_us = svg.append("line")
			.classed("rankings_us",true)
			.classed("axis",true)
			.attr("y1",y_scale("United States"))
			.attr("x1", margin.left - 10)
			.attr("y2", y_scale("United States"))
			.attr("x2", width - margin.right + 10)
			.attr("stroke-width",5);

		var rankings_uk = svg.append("line")
			.classed("rankings_uk",true)
			.classed("axis",true)
			.attr("y1",y_scale("United Kingdom"))
			.attr("x1", margin.left - 10)
			.attr("y2", y_scale("United Kingdom"))
			.attr("x2", width - margin.right + 10)
			.attr("stroke-width",5);

		var rankings_jp = svg.append("line")
			.classed("rankings_jp",true)
			.classed("axis",true)
			.attr("y1",y_scale("Japan"))
			.attr("x1", margin.left - 10)
			.attr("y2", y_scale("Japan"))
			.attr("x2", width - margin.right + 10)
			.attr("stroke-width",5);

		// Create animation playback controls
		svg.append("svg:image")
			.attr("xlink:href","inc/img/play.png")
			.attr("id","play_pause")
			.attr("width",30)
			.attr("height",30)
			.attr("x", 15)
			.attr("y",margin.top)
			.style("cursor","pointer")
			.on("click", function() {
				animation_tooltip.style("opacity",0);
				peak_rank_tooltip.style("opacity",0);				
				if(tracker_paused == true) {
					d3.select(this).classed("paused",false);
					d3.select(this).classed("play",true);
					tracker_paused = false;
					manual_pause = false;
					d3.select(this).attr("xlink:href","inc/img/pause.png");
				} else {
					d3.select(this).classed("paused",true);
					d3.select(this).classed("play",false);
					tracker_paused = true;
					manual_pause = true;
					d3.select(this).attr("xlink:href","inc/img/play.png");
				}
				
				if(animation_completed == true) {
					date_pointer = 0;
					tracker_paused = false;
					manual_pause = false;
					d3.select(this).attr("xlink:href","inc/img/pause.png");
					run(animation_speed,date_pointer,true);
				}
			})
			.append("svg:title")
				.text("Play/Pause");

		svg.append("svg:image")
			.attr("xlink:href","inc/img/replay.png")
			.attr("id","replay")
			.attr("width",30)
			.attr("height",30)
			.attr("x", 15)
			.attr("y",margin.top + 35)
			.style("cursor","pointer")
			.on("click", function() {
				tracker_paused = false;
				manual_pause = false;				
				date_pointer = 0;
				d3.select("#play_pause").attr("xlink:href","inc/img/pause.png");
				run(animation_speed,date_pointer,true);
			})
			.append("svg:title")
				.text("Start Over");

		svg.append("text")
			.attr("class","speed_label")
			.attr("text-anchor","middle")
			.attr("x", 30)
			.attr("y",margin.top + 100)
			.text("slow")
			.on("click", function() {
				animation_speed = 100;
				speed_select(this,animation_speed);
			});

		svg.append("text")
			.attr("class","speed_label")
			.attr("text-anchor","middle")
			.attr("x", 30)
			.attr("y",margin.top + 125)
			.text("med")
			.on("click", function() {
				animation_speed = 50;
				speed_select(this,animation_speed);
			});

		svg.append("text")
			.attr("class","speed_label")
			.classed("speed_label_selected",true)
			.attr("text-anchor","middle")
			.attr("x", 30)
			.attr("y",margin.top + 150)
			.text("fast")
			.on("click", function() {
				animation_speed = 1;
				speed_select(this,animation_speed);
			});

		// This function controls speed changes for the animation,
		// used above
		function speed_select(element,resume_animation_speed) {
			svg.selectAll(".speed_label")
				.classed("speed_label_selected",false);
				
			d3.select(element).classed("speed_label_selected",true);
			tracker_paused = true;
			if(animation_completed == false) {
				animation(resume_animation_speed,date_pointer);
				if(manual_pause == false) {
					tracker_paused = false;			
				}
			}
		}

		// Create peak rank line marker placeholder
		var peak_rank_line = svg.append("line")
			.attr("stroke","#ffcc33")
			.attr("stroke-width",8)
			.attr("stroke-linecap","round")
			.attr("opacity",0);

		// Create main animation tooltip placeholders,
		// one for marker's displayed position and one for marker's peak position
		var animation_tooltip = d3.select("#countries").append("div")	
			.attr("id","animation_tooltip")
			.attr("class","tooltip")
			.style("opacity", 0);

		var peak_rank_tooltip = d3.select("#countries").append("div")	
			.attr("id","peak_rank_tooltip")
			.attr("class","tooltip")
			.style("opacity", 0);
			
		// Create axis component for rank positions
		var rank_axis = d3.svg.axis()
			.scale(x_scale)
			.tickValues([1,10,20,30,40,50,60,70,80,90,100]);

		// Create axis elements for rank positions
		var rank_axis_top = svg.append("g")
			.attr("class","rank_axis")
			.attr("transform","translate(0," + margin.top + ")")
			.call(rank_axis.orient("bottom"));

		var rank_axis_bottom = svg.append("g")
			.attr("class","rank_axis")
			.attr("transform","translate(0," + (height-margin.bottom) + ")")
			.call(rank_axis.orient("top"));

		// Append graphics representing US, UK, and Japan
		svg.append("svg:image")
			.attr("xlink:href","inc/img/usa.png")
			.attr("width",100)
			.attr("height",100)
			.attr("x", 130)
			.attr("y",y_scale("United States") - 50)
			.style("opacity",0.6);

		svg.append("svg:image")
			.attr("xlink:href","inc/img/uk.png")
			.attr("width",100)
			.attr("height",100)
			.attr("x", 130)
			.attr("y",y_scale("United Kingdom") - 50)
			.style("opacity",0.6);

		svg.append("svg:image")
			.attr("xlink:href","inc/img/japan.png")
			.attr("width",100)
			.attr("height",100)
			.attr("x", 130)
			.attr("y",y_scale("Japan") - 50)
			.style("opacity",0.6);
		
		// Append labels for chart names
		svg.append("text")
			.attr("class","chart_name_label")
			.attr("x",180)
			.attr("y",y_scale("United States") + 10)
			.attr("text-anchor","middle")
			.text("Billboard");

		svg.append("text")
			.attr("class","chart_name_label")
			.attr("x",180)
			.attr("y",y_scale("United States") + 10)
			.attr("dy","1.2em")
			.attr("text-anchor","middle")
			.text("Top 100");

		svg.append("text")
			.attr("class","chart_name_label")
			.attr("x",180)
			.attr("y",y_scale("United Kingdom") + 10)
			.attr("text-anchor","middle")
			.text("U.K. Top 200");

		svg.append("text")
			.attr("class","chart_name_label")
			.attr("x",180)
			.attr("y",y_scale("Japan") + 10)
			.attr("text-anchor","middle")
			.text("J-Wave Tokio");

		svg.append("text")
			.attr("class","chart_name_label")
			.attr("x",180)
			.attr("y",y_scale("Japan") + 10)
			.attr("dy","1.2em")
			.attr("text-anchor","middle")
			.text("Hot 100");

		// Specify variables that control the animation
		var startdate = new Date(1960,0,1);
		var enddate = new Date(2016,0,1);
		tracker_paused = true;
		date_pointer = 0;

		// Create an array of year tick labels for animation date controller
		var date_tick_labels = [];
		var base_date = startdate;
		date_tick_labels.push(base_date);
		while(base_date < enddate) {
			generate_date = new Date(base_date);
			if(base_date.getFullYear() == 2010) {
				generate_date.setYear(base_date.getFullYear() + 6);				
			} else {
				generate_date.setYear(base_date.getFullYear() + 5);
			}
			date_tick_labels.push(generate_date);
			base_date = generate_date;
		}

		var time_scale = d3.time.scale()
			.domain([date_tick_labels[0],date_tick_labels[date_tick_labels.length-1]])
			.range([margin.top,height-margin.bottom]);
	
		// Create time axis component and element
		var time_axis = d3.svg.axis()
			.scale(time_scale)
			.tickValues(date_tick_labels)
			.innerTickSize(0)
			.outerTickSize(5)
			.orient("right");
	

		var time_pointer = svg.append("rect")
			.attr("x", 65)
			.attr("y",time_scale(startdate) - 10)
			.attr("width",50)
			.attr("height",20)
			.attr("fill","#FFCC66")
			.attr("opacity",0.8);

		var time_axis_line = svg.append("g")
			.attr("class","animation_time_axis")
			.attr("transform","translate(65,0)")
			.call(time_axis)
			.selectAll("text")
				.attr("dx", ".35em");

		svg.attr("opacity",0.4);
		var startplay = d3.select("#countries").append("div")
			.attr("class","initial_start_text")
			.html("<span style='alignment-baseline:middle;'>Play</span>")
			.on("click", function() {
				svg.attr("opacity",1);
				d3.select(this).remove();
				
				//////////////////////////////////////////////////////////////////////////////
				// RUN THE VISUALIZATION/ANIMATION
				//////////////////////////////////////////////////////////////////////////////			

				tracker_paused = false;
				d3.select("#play_pause").attr("xlink:href","inc/img/pause.png");
				run(animation_speed,date_pointer,false);
				
			});
		

		// Define functions for generating animation visualization below

		function reset_animation() {
				markers = {};
				svg.selectAll(".cir_display").remove();
				svg.selectAll(".distr_bar").remove();
				date_pointer = 0;
		}
		
		
		
		// Define the setInterval that will actually loop through the animation
		function animation(animation_speed, resume_date_pointer) {

			clearInterval(timer);

			date_pointer = resume_date_pointer;

			timer = setInterval(function() {

				// Only iterate through if animation hasn't been paused
				if(!tracker_paused) {
		
					// Remove already displayed markers for recycling
					svg.selectAll(".cir_display").remove();
										
					// Define the next date in the animation for which we will
					// retrieve data
					var newdate = dates[date_pointer++];
					var date = new Date(newdate);
					if(date < startdate) {
						return;
					}
					date_key = newdate;		
			
					// If reach the end of the specified animation date range,
					// stop the animation and clear the setInterval
					if(date > enddate) {
						clearInterval(timer);
						svg.selectAll("[id^='id_']")
							.transition()
							.duration(500)
							.attr("opacity",0)
							.remove();
						animation_completed = true;
						d3.select("#play_pause").attr("xlink:href","inc/img/play.png");

					} else {
						animation_completed = false;
					}

					// Move the time pointer along the time scale
					time_pointer.attr("y", time_scale(date) - 10);
						
					// If the date_key is a key in the data object, then continue
					if(date_key in data) {
				
						var eventdata = data[date_key];		
					
						// Iterate through each single ranking event for this date
						eventdata.forEach(function(this_data) {
							var ranking = this_data["ranking"];

							/* Only show data for singles with ranking of at least 100; do this to normalize comparisons
							across all charts because UK top chart has 200 singles while US/Japan have 100 each */
							if(ranking > 100) { return; }
					
							var country_name = this_data["country_name"];
							var chart_name = this_data["chart"];
							var msid = this_data["msid"];
							if(!markers.hasOwnProperty(msid)) {
								markers[msid] = {"billboard-us": 0, "top100-uk": 0, "jwave": 0};
							}

							// Don't show anything if an artist's single appears on the chart
							// of their country of origin
							switch(country_name) {
								case "United States":
									var class_name = "artist_us";
									if(chart_name === "billboard-us") { return; }
									break;
								case "United Kingdom":
									var class_name = "artist_uk";
									if(chart_name === "top100-uk") { return; }

									break;
								case "Japan":
									var class_name = "artist_japan";
									if(chart_name === "jwave") { return; }

									break;
								default:
									var class_name = "artist_unassigned";
									break;
							}


							// Increment position ranking distributions and their 
							// representative bar markers for each chart
							switch(chart_name) {		
								case "billboard-us":
									var chart_axis = "United States";
									var distr_array = distr_us;
									distr_array["total"][ranking-1]["count"]++;
									us_distr_bars.data(distr_array["total"]).attr("transform",function(d) { return "translate(0," + (-0.5 * distr_scale(d.count)) + ")"; }).attr("height", function(d) { return distr_scale(d.count); });							

									break;
								case "top100-uk":
									var chart_axis = "United Kingdom";
									var distr_array = distr_uk;
									distr_array["total"][ranking-1]["count"]++;
									uk_distr_bars.data(distr_array["total"]).attr("transform",function(d) { return "translate(0," + (-0.5 * distr_scale(d.count)) + ")"; }).attr("height", function(d) { return distr_scale(d.count); });							
									break;
								case "jwave":
									var chart_axis = "Japan";
									var distr_array = distr_jp;
									distr_array["total"][ranking-1]["count"]++;
									jp_distr_bars.data(distr_array["total"]).attr("transform",function(d) { return "translate(0," + (-0.5 * distr_scale(d.count)) + ")"; }).attr("height", function(d) { return distr_scale(d.count); });							
									break;
							}		
	
							// IF this single has not yet made an appearance on this chart,
							// proceed with creating a new marker
							if(markers[msid][chart_name] == 0) {

								// Increment peak position ranking distributions and their
								// representative bar markers for each chart
								switch(chart_name) {		
									case "billboard-us":
										var chart_axis = "United States";
										var distr_array = distr_us;
										distr_array["entry"][ranking-1]["count"]++;
										us_distr_bars_entry.data(distr_array["entry"]).attr("transform",function(d) { return "translate(0," + (-0.5 * distr_scale(d.count)) + ")"; }).attr("height", function(d) { return distr_scale(d.count); });							

										break;
									case "top100-uk":
										var chart_axis = "United Kingdom";
										var distr_array = distr_uk;
										distr_array["entry"][ranking-1]["count"]++;
										uk_distr_bars_entry.data(distr_array["entry"]).attr("transform",function(d) { return "translate(0," + (-0.5 * distr_scale(d.count)) + ")"; }).attr("height", function(d) { return distr_scale(d.count); });							
										break;
									case "jwave":
										var chart_axis = "Japan";
										var distr_array = distr_jp;
										distr_array["entry"][ranking-1]["count"]++;
										jp_distr_bars_entry.data(distr_array["entry"]).attr("transform",function(d) { return "translate(0," + (-0.5 * distr_scale(d.count)) + ")"; }).attr("height", function(d) { return distr_scale(d.count); });							
										break;
					
								}		

								// Append a new circle for new elements that pass the test above --
								// the first time a foreign artist single appears on a country's chart
								svg.append("circle")
									.datum(this_data)
									.classed(class_name,true)
									.classed("c_" + chart_name,true)
									.classed("t_" + msid,true)
									.attr("id","id_" + msid)
									.attr("cy", function(d) { return y_scale(d.chart_from); })
									.attr("cx", function(d) { return x_scale(d.ranking); })
									.attr("r",8)
									.attr("stroke","none")
									.attr("opacity", 0.8)
									.on("mouseover", function(d) { 
								
										// On mouseover, pause the animation and show tooltips
										tracker_paused = true;
										var current_element = this;
										svg.selectAll("*").attr("opacity", function() {
											return (this === current_element) ? 1.0 : 0.4;
										});

										var peak_rank = d.peak_position;
										var position_top = Number(d3.select(this).attr("cy"));


										var this_date = new Date(d.date);
										var formatted_this_date = months[this_date.getMonth()] + " " + this_date.getDate() + ", " + this_date.getFullYear();
										var peak_date = new Date(d.peak_date);
										var formatted_peak_date = months[peak_date.getMonth()] + " " + peak_date.getDate() + ", " + peak_date.getFullYear();
										
										animation_tooltip.transition()		
											.duration(200)		
											.style("opacity", 0.9);		
									
										animation_tooltip.html(d.single_title + "<br><span style='font-size:0.8em;'>" + d.artist + " (" + formatted_this_date + ")</span>");
										peak_rank_tooltip.html("<span style='font-size:0.8em; color:#660000'>Peak position #" + d.peak_position + " (" + formatted_peak_date + ")</span>");

										// Position current ranking info tooltip at location of mouse hover,
										// flipping the tooltip based on relative page position
										if(d.ranking < 50) {
											var position_left = Number(d3.select(this).attr("cx"));

											animation_tooltip.style("left", position_left + "px")	
												.style("right","auto")	
												.style("top", (position_top + 12) + "px")
												.style("border-right","0px")
												.style("border-left","2px solid #AAA");
										
										} else {
											var position_right = width - (Number(d3.select(this).attr("cx")));
											animation_tooltip.style("right", position_right + "px")		
												.style("left","auto")	
												.style("top", (position_top + 12) + "px")
												.style("border-left","0px")
												.style("border-right","2px solid #AAA");
										}
									
										// Position peak ranking info tooltip at location of mouse hover,
										// flipping the tooltip based on relative page position
										if(peak_rank < 50) {
											peak_rank_tooltip.style("bottom", (height-position_top + 12) + "px")
												.style("left", x_scale(peak_rank) + "px")
												.style("right","auto")
												.style("border-right","0px")
												.style("border-left","2px solid #ffcc33");
											
										} else {
											peak_rank_tooltip.style("bottom", (height-position_top + 12) + "px")
												.style("right", (width - x_scale(peak_rank)) + "px")
												.style("left","auto")
												.style("border-left","0px")
												.style("border-right","2px solid #ffcc33");
										}
							
										peak_rank_line.attr("x1", x_scale(d.ranking))
											.attr("y1", y_scale(chart_axis))
											.attr("x2", x_scale(d.ranking))												
											.attr("y2", y_scale(chart_axis))
											.attr("opacity",0.9)
											.moveToFront()
											.transition()
												.duration(500)
												.attr("x1", x_scale(peak_rank));

										peak_rank_tooltip.transition()
											.duration(200)
											.style("opacity",0.9)													
											
										d3.select(this).attr("r",15)
											.attr("opacity",1)
											.moveToFront();													

									})
									.on("mouseout", function(d) { 
								
										// On mouseout, remove tooltips and resume
										// animation (if not manually paused using 
										// the play/pause button)
										svg.selectAll("*").attr("opacity", 1);
										svg.selectAll("circle").attr("opacity",0.8);
									
										if(manual_pause != true) {
											tracker_paused = false;
										}
									
										peak_rank_line.attr("opacity",0)
											.moveToBack();

										animation_tooltip.style("opacity",0);
										peak_rank_tooltip.style("opacity",0);
									
										d3.select(this).attr("r",8);
									
									}).transition()
										.duration(function() {
											switch(animation_speed) {
												case 1:
													return 500;
													break;
												case 50:
													return 750;
													break;
												case 100:
													return 1200;
													break;
												default:
													return 500;
											}		
										})
										.attr("cy", function() { return y_scale(chart_axis); })
										.each("end", function(d) {
											// After a marker appears, class it
											// so on next setInterval iteration the marker
											// is removed to free up space
											d3.select(this).classed("cir_display",true);

										});
									
								// Indicate that a marker for this single on
								// this chart has been created
								markers[msid][chart_name] = 1;
								
							} // end if(markers[msid][chart_name] == 0)
						}); // end eventdata.forEach()
					} // end if(date_key in data)
				} // end if(!tracker_paused)
			} // end setInterval function
			,animation_speed); // end setInterval

		} // end function animation()


		// Define the function that runs the animation
		function run(animation_speed,resume_date_pointer,replay) {

			// If animation is being replayed, remove all existing markers and start over
			if(replay) {
				reset_animation();				
			}

			// Create objects to hold data about distribution of ranking positions for each individual country chart
			distr_uk = {total: new Array(), entry: new Array()};
			for(var i = 1; i <= 100; i++) {
				distr_uk["total"].push({country_axis: y_scale("United Kingdom"), rank: i, count: 0});
				distr_uk["entry"].push({country_axis: y_scale("United Kingdom"), rank: i, count: 0});

			}	

			distr_us = {total: new Array(), entry: new Array()};
			for(var i = 1; i <= 100; i++) {
				distr_us["total"].push({country_axis: y_scale("United States"), rank: i, count: 0});
				distr_us["entry"].push({country_axis: y_scale("United States"), rank: i, count: 0});

			}	

			distr_jp = {total: new Array(), entry: new Array()};
			for(var i = 1; i <= 100; i++) {
				distr_jp["total"].push({country_axis: y_scale("Japan"), rank: i, count: 0});
				distr_jp["entry"].push({country_axis: y_scale("Japan"), rank: i, count: 0});

			}	

			distr_scale = d3.scale.linear()
				.domain([0,400])
				.range([0,chart_spacing - 20]);
			
			// create rect elements that will change size according to
			// distribution (count of singles) of rank positions in each country
			// One distribution is for the number of singles ENTERING the chart at
			// a given position;
			// Another distribution is for the number of singles PEAKING on the chart 
			// at a given position ranking
			 uk_distr_bars = svg.selectAll(".distr_uk")
				.data(distr_uk["total"])
				.enter()
				.append("rect")
					.attr("class","distr_uk distr_bar")
					.attr("y", y_scale("United Kingdom"))
					.attr("x", function(d) { return x_scale(d.rank) - boxwidth/2; })
					.attr("width",boxwidth)
					.attr("height",0)
					.attr("fill","#FFCC33")
					.attr("stroke","none");

			 uk_distr_bars_entry = svg.selectAll(".distr_uk_entry")
				.data(distr_uk["entry"])
				.enter()
				.append("rect")
					.attr("class","distr_uk_entry distr_bar")
					.attr("y", y_scale("United Kingdom"))
					.attr("x", function(d) { return x_scale(d.rank) - boxwidth/2; })
					.attr("width",boxwidth)
					.attr("height",0)
					.attr("fill","#666666")
					.attr("stroke","none");

			 us_distr_bars = svg.selectAll(".distr_us")
				.data(distr_us["total"])
				.enter()
				.append("rect")
					.attr("class","distr_us distr_bar")
					.attr("y", y_scale("United States"))
					.attr("x", function(d) { return x_scale(d.rank) - boxwidth/2; })
					.attr("width",boxwidth)
					.attr("height",0)
					.attr("fill","#FFCC33")
					.attr("stroke","none");

			 us_distr_bars_entry = svg.selectAll(".distr_us_entry")
				.data(distr_us["entry"])
				.enter()
				.append("rect")
					.attr("class","distr_us_entry distr_bar")
					.attr("y", y_scale("United States"))
					.attr("x", function(d) { return x_scale(d.rank) - boxwidth/2; })
					.attr("width",boxwidth)
					.attr("height",0)
					.attr("fill","#666666")
					.attr("stroke","none");
	
			 jp_distr_bars = svg.selectAll(".distr_jp")
				.data(distr_jp["total"])
				.enter()
				.append("rect")
					.attr("class","distr_jp distr_bar")
					.attr("y", y_scale("Japan"))
					.attr("x", function(d) { return x_scale(d.rank) - boxwidth/2; })
					.attr("width",boxwidth)
					.attr("height",0)
					.attr("fill","#FFCC33")
					.attr("stroke","none");

			 jp_distr_bars_entry = svg.selectAll(".distr_jp_entry")
				.data(distr_jp["entry"])
				.enter()
				.append("rect")
					.attr("class","distr_jp_entry distr_bar")
					.attr("y", y_scale("Japan"))
					.attr("x", function(d) { return x_scale(d.rank) - boxwidth/2; })
					.attr("width",boxwidth)
					.attr("height",0)
					.attr("fill","#666666")
					.attr("stroke","none");
			

			animation(animation_speed,resume_date_pointer);

		} // end function run()


	}); // end $.post()
}); // $(document).ready()