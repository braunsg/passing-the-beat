# passing-the-beat

This repository holds source code for the Passing The Beat visualization created by Steven Braun. The visualization can be accessed here:

http://www.stevengbraun.com/dev/passing-the-beat

Source code provided here includes the framework for the visualizations as well as some sample data files.

For answers to any questions, contact Steven via email at braunsg[at]gmail.com.

# What's in here

	index.php
		The index page into which the data and visualizations are loaded
		
	inc/config
		Default configuration files (e.g., data sources, stylesheet)
	
	inc/content
		The main HTML content for index.php
		
	inc/data
		The data used to create the visualizations
		
	inc/data-analysis-scripts
		PHP scripts used in the original data analysis, as well as example MySQL tables
		
	inc/img
		Images used in index.php
		
	inc/lib
		JavaScript libraries/functions used to generate the visualizations
		
# General workflow

The directory inc/data-analysis-scripts includes samples of the PHP scripts used to aggregate and analyze all data for these visualizations. The general workflow is as follows:

	ingest-musicid-data.php
		A script that ingests into a MySQL database the raw historical chart data for the U.S. Billboard Top 100, U.K. Top 200, and J-Wave Tokio Hot 100 music charts
		
	map-single-ids.php
		A script that goes through the raw data and maps potential song duplicates to single authority IDs
		For example, songs that are released under multiple record labels across multiple countries may be misunderstood as distinct; here, those errors are reduced by finding matches between records based on single title plus artist name
		
	echonest-retrieve-artist-location-genre.php
		A script that uses the EchoNest API to retrieve data about locations/countries of origin for each unique artist presented in the visualizations
	
	ingest-musicid-crossovers-mapped.php
		Determines all crossover songs -- singles by one artist that make an appearance on the top music chart(s) of other countries
		
	get-single-crossover-artist-data-mapped.php		
		Creates static CSV files for all crossover singles identified above
		
	generate-chart-data-file.php	
		Aggregates and reduces all data in the above song data CSV files into a single file for faster loading in the visualization
		
	generate-artist-history.php
		Generates CSV files holding historical crossover data for individual artists	
