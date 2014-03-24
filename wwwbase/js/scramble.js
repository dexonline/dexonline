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
				var letterArray = new Array();
			    letterArray = word.charArray;
			   	drawLetters(letterArray);
		})
		.fail(function() {
				console.log("Nu merge");
		});
	});
// test pentru cuvinte returnate prin json
	$('.searchWord').keyup(function() {
		var searchWord = $(this).val();
		$.ajax({
			type:"POST",
			url: wwwRoot + "ajax/scramble.php",
			data: { searchWord : searchWord },
		})
		.done(function(response){
			var result = $.parseJSON(response);
			$('#ifFound').html(result.Found);
		})
		.fail(function() {
			console.log("Nu merge");
		});
	});
	
function drawLetters(array){
	$('canvas').removeLayers().delay(800);
	for (var i=0; i<=array.length; i++) {
		$('canvas').drawText({
  			draggable: true,
  			fillStyle: '#9cf',
  			strokeStyle: '#25a',
  			strokeWidth: 2,
  			x: 50, y: 50,
  			fontSize: 25,
  			fontFamily: 'Verdana, sans-serif',
  			text: array[i]
		})
	.animateLayer(i, {			
  			x: 100+(i*30), y: 390,
});	
  	}
  }
});


