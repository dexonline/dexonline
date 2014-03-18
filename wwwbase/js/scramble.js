var button = document.getElementsByClassName("testButton");

$(document).ready(function() {
	button.addEventListener("click", function() {
		var obj;
		obj.difficulty = $("button.btn").attr("value");
		$.ajax({
			type: "POST",
			url: "../ajax/scramble.php",
			data: { difficulty : obj.difficulty },
			}).done(function(response) {
				alert("Merge!");
				var word = $.parseJSON(response);
				// etc...
			}).fail(function() {
				alert("Nu merge!");
		});
	});
});
