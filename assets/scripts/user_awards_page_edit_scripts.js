/**
 * Name: user_awards_page_edit_scripts.js
 * Description: Holds all applicable JavaScripts needed for full functionality of the UserAwards Post Edit page.
 */

function injectUserIDHiddenInput( user_id, elem ) {
	// Parsing a Number out of our user_id string.
	user_id = parseInt(user_id);

	// HTML to insert
	inputHTML = '<input type="hidden" value="' + user_id + '"/>';

	if ( ! elem instanceof jQuery )
	{
		console.log("The element we're giving to injectUserIDHiddenInput is not a jQuery object");
		return false;
	}

	// We'll get a User ID of 0 (falsy) if no selection occoured
	if ( ! user_id )
	{
		alert("Please choose the user that you'd like to assign your selected awards to");
		return false;
	}

	return true;
}

jQuery(document).ready(function($) {
	/**
	 * UserAwards Post Edit List screen:
	 * - Bulk Actions:
	 * 		* If the bulk action that we're doing is "Assign to User",
	 *   		then we have to harvest the user information somehow.
	 *
	 *   		We will do this by opening up a modal that displays all
	 *   		of the current website members. The user chooses a member,
	 *   		and the ID of that member is then inserted as a hidden
	 *   		input into our whole edit form here.
	 */

	var modal_get_user = $("#modal-get-user");
	var modal_get_user_submit = modal_get_user.find(".button-primary");
	var modal_get_user_cancel = modal_get_user.find(".button-secondary");
	var modal_user_select = $("#UserAwards_UserID");
	var posts_filter = $("#posts-filter"); // The form used to submit our bulk actions... I think...
	var bulk_actions_submit = $(".bulkactions input[type=\"submit\"]");
	var modal_get_user_link = $("#modal-get-user-link");
	var modal_open_actions = ["UserAwards_Assign", "UserAwards_Give"];

	// Thickbox modal will be opened when we click on our bulk actions "submit" button
	// and have the "assign_to_user" action selected
	bulk_actions_submit.click(function(e) {

		// Not the most "performance" centric design, but boy is it easy to obtain a contextual select
		var bulk_actions_dropdown = $(this).prev("select");
		var dumb_users = 1; // Are users dumb and not selecting any items?

		// Checking if action selected is an action in which we open a modal
		if ( $.inArray( bulk_actions_dropdown.val(), modal_open_actions ) < 0 )
		{
			return
		}

		// Prevent Submission
		e.preventDefault();

		// Alert the user they're dumb and need to select items
		$("#the-list .entry").each(function( index ) {
			if ( $(this).find(".check-column input").is(":checked") )
			{
				dumb_users = 0;
			}

			if ( ! dumb_users )
			{
				return;
			}
		});

		// Dumb user check goes here I guess.
		if ( dumb_users )
		{
			alert("You must select an award that you want to give to a user to perform this bulk action");
			return;
		}

		// Open up our modal.
		modal_get_user_link.click()
	});

	// Thickbox Modal Submit Button Click.
	modal_get_user_submit.click(function() {

		// Parsing a Number out of our user_id string.
		user_id = parseInt(modal_user_select.val());

		// We'll get a User ID of 0 (falsy) if no selection occoured
		if ( ! user_id )
		{
			alert("Please choose the user that you'd like to assign your selected awards to");
			return;
		}

		// HTML to inject
		inputHTML = '<input name="UserAwards_UserID" type="hidden" value="' + user_id + '"/>';

		// Inject or Update
		if ( $('input[name="UserAwards_UserID"]').length )
		{
			$('input[name="UserAwards_UserID"]').val(user_id);
		}
		else
		{
			posts_filter.prepend(inputHTML);
		}

		// Act like we're clicking the cancel button (close the modal)
		modal_get_user_cancel.click();

		// Submit the form!!!
		posts_filter.submit();
	});

	// Thickbox Modal Cancel Button Click
	modal_get_user_cancel.click(function() {
		tb_remove();
	});
});