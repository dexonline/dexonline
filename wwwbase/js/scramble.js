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
			    letterArray = word.charArray
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
// printeaza literele cuvantului random din baza de date
function drawLetters(array){
	var layers;
	$('canvas').removeLayers();
	for (var i=0; i<=array.length; i++) {

		var posX = 100+(i*30);

		$('canvas').drawText({
  			draggable: true,
  			fillStyle: '#9cf',
  			strokeStyle: '#25a',
  			strokeWidth: 2,
  			x: 320, y: -30,
  			fontSize: 32.5,
  			fontFamily: 'Verdana, sans-serif',
  			text: array[i].toUpperCase(),
  			name: array[i]
		})
	.animateLayer(i, {			
  			x: posX, y: 390
		})	
  	}
  	layers = $('canvas').getLayers();
  	console.log(layers);
  	keylisten(layers,posX);
}
//asculta tot documentul pentru apasarea unei taste, daca tasta corespunde numelui layer-ului atunci se se muta pozitia pozitia acelui layer pe Y = 150.
function keylisten(layers,posX){
	$(document).keypress(function(e){
		var test = String.fromCharCode(e);
		console.log(test);
	})
	for(var i = 0; i<=layers.length;i++) {	
		if(test == layers[i]) {
		$('canvas').animateLayer(i,{
		x:posX, y: 150
		});
		}
	}
}



// END OF FILE  
});


