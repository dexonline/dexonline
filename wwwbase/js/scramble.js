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
		})
		.fail(function() {
				console.log("Nu merge");
		});
	});
});
