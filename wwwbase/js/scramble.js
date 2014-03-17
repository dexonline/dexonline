$(document).ready(function() {
	$(":button").click(function() {
		var obj;
		obj.difficulty = $("button.btn").attr("value");
		$.ajax({
			type: "POST",
			url: "../ajax/scramble.php",
			data: obj,
			}).done(function(response) {
				alert("Merge!");
				var word = $.parseJSON(response);
				// etc...
			}).fail(function() {
				alert("Nu merge!");
		});
	});
});
