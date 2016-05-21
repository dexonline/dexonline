<html>
	<head>

		<title>Romanian crawler log</title>

		<script src='http://code.jquery.com/jquery-2.0.3.js'></script>

		<script src='../js/crawler_ajax.js'></script>

		<link rel="StyleSheet" type="text/css" href="../css/crawler.css"/>

	</head>
	<body>
		

		<div id="crawlerTitle">
			<img src="../img/crawler/romanian_crawler_log.png">
		</div>


		<div id="selectDomain">
			<br>
			<span class="selectDomain">Select domain: </span>
			
			<select name="dropDown">
				{html_options values=$values output=$options} 
			</select>
			
			<br>
		</div>

		<div id="info">
				<span class="domain">
					<center>Showing: <span class="inSelection">all</span></center>
				<br>
				</span>			
				<span class="infoTitle"><center>General Stats</center></span>
				
				<div class="infoPanel">
					<span class="total"></span>
				</div>
				
				<span class="infoTitle"><center>HTTP Code Stats</center></span>
			
			<div class="infoPanel">
				<span class="perHttpCode"></span>
			</div>
		</div>

		<img id="logo" src="../img/logo-dexonline-2.png"/>

	</body>
</html>
