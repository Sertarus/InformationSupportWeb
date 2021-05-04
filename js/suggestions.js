$(document).ready(function(){
	$(".btn-primary").click(function() {
		var id = $(this).closest("tr").find(".id").text();
		window.location.href = 'suggestions_watch.php?id=' + id;
		});

	$(".btn-success").click(function() {
		var id = $(this).closest("tr").find(".id").text();
		window.location.href = 'suggestions_commit.php?id=' + id;
		});

	$(".btn-danger").click(function() {
		var id = $(this).closest("tr").find(".id").text();
		window.location.href = 'suggestions_cancel.php?id=' + id;
		});
});