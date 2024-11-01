jQuery(document).ready(function($) {
	var list_rows = $("#the-list tr");

	list_rows.on("click", ".remove a", function(e) {
		if (confirm("Are you sure you would like to remove an award from a user?"))
		{
			return;
		} else {
			e.preventDefault();
		}
	});
});