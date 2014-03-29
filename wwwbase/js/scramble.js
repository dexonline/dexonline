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
			drawLetters(word.randomWord);
			console.log(word.randomWord);

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
	$('canvas').removeLayers();
	for (var i = 0; i < array.length; i++) {

		var posX = 100 + ( i * 40);

		$('canvas').drawRect({
			draggable: true,
            fillStyle: 'black',
            groups: [i],
            dragGroups: [i],
            x: 320, y: -30,
            width: 35,
            height: 45,
            cornerRadius: 4
		})

	.drawText({
  			draggable: true,
  			groups:[i],
  			dragGroups: [i],
  			fillStyle: '#9cf',
  			strokeStyle: '#25a',
  			strokeWidth: 2,
  			x: 320, y: -30,
  			fontSize: 32.5,
  			fontFamily: 'Verdana, sans-serif',
  			text: array[i].toUpperCase(),
  			name: array[i]
		})
	.animateLayerGroup(i, {			
  			x: posX, y: 390
		});
  	}
  	var layers = $('canvas').getLayers();
 // 	console.log(layers);
  	keylisten(layers);
}
//asculta tot documentul pentru apasarea unei taste, daca tasta corespunde numelui layer-ului atunci se se muta pozitia pozitia acelui layer pe Y = 150.
function keylisten(layers){
	$('animate').on('click', function(){
		console.log($('animate').attr("value"));
		console.log(layers);
		for(var i = 0; i < layers.length; i++) {	
		  $('canvas').animateLayerGroup(i, {
		     y: 150
		  });
		}
    });
}



// END OF FILE  
});


