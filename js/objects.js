$(document).ready(function(){
	$(".btn-primary").click(function() {
		var name = $(this).closest("tr").find(".name").text();
		window.location.href = 'objects_watch.php?name=' + name;
		});

	$(".btn-success").click(function() {
		var name = $(this).closest("tr").find(".name").text();
		window.location.href = 'objects_create_edit.php?name=' + name + "&isEdit=1";
		});

	$(".btn-danger").click(function() {
		var name = $(this).closest("tr").find(".name").text();
		window.location.href = 'objects_delete.php?name=' + name;
		});
});