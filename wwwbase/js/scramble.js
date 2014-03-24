$(document).ready(function() {
	$('.difficultyButton').on("click", function() {
		// this e pentru a prelua valoarea butonului tocmai apasat, si nu a unuia oarecare
		var difficulty = $(this).attr("value");
		$.ajax({
			type: "POST",
			url: wwwRoot + "ajax/scramble.php",
			data: { difficulty : difficulty },
		})
		.done(function(response) {
				var word = $.parseJSON(response);
				$('#result').html(word.randomWord);
				$('#noWords').html(word.noWords);
		})
		.fail(function() {
				console.log("Nu merge");
		});
	});

	$('.Search').on('click', function() {
		var searchWord = $(.searchWord).val();
		$.ajax({
			type:"POST",
			url: wwwRoot + "ajax/scramble.php"
			data: { searchWord : searchWord }
		})
		.done(function(response)){
			var result = $.parseJSON(response);
			$('.ifFound').html(result.Found);
		}
		.fail(function() {
			console.log("Nu merge");
		})
	});

	$('canvas')
		.drawArc({
  		layer: true,
  		draggable: true,
  		fillStyle: '#36c',
  		x: 150, y: 150,
  		radius: 50
	})
	.drawRect({
  		layer: true,
  		draggable: true,
  		fillStyle: '#6c1',
  		x: 100, y: 100,
  		width: 100, height: 100
	});	
});


