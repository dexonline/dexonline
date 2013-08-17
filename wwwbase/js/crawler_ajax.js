var crawlerStatus = {}


crawlerStatus.fetchStatus = function() {

	var selectedDomain = $('select[name=dropDown]').val();

	$.ajax({

		url: '../ajax/fetchCrawlerStatus.php',
		type: 'post',
		data: { method: 'fetch_total', domain: selectedDomain },
		success: function(data) {
			//face refresh la informatiile generale
			$('.total').html(data + '</br>');

		}
	});
}

crawlerStatus.fetchHttpStatus = function() {

	var selectedDomain = $('select[name=dropDown]').val();

	$.ajax({

		url: '../ajax/fetchCrawlerStatus.php',
		type: 'post',
		data: { method: 'fetch_per_http_code', domain: selectedDomain },
		success: function(data) {
			//face refresh la HTTP status
			$('.perHttpCode').html(data);

			//schimba automat dimensiunea logo-ului
			$('#logo').height($('#info').height() * 2/3);
			$('#logo').css('margin-top', $('#info').height() * 2/7);
			$('#logo').css('margin-left', $('#info').height() * 1/4);
		}

	});
}


$(document).ready(function() {


	crawlerStatus.fetchStatus();
	crawlerStatus.fetchHttpStatus();

	setInterval(crawlerStatus.fetchStatus, 5000);
	setInterval(crawlerStatus.fetchHttpStatus, 5000);
	
	$('select[name=dropDown]').change(function() {


		$('.inSelection').html($('select[name=dropDown]').val());
		crawlerStatus.fetchStatus();
		crawlerStatus.fetchHttpStatus();
	});
});