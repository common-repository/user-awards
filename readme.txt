=== User Awards ===
Contributors: kwmartin
Donate link: N/A
Tags: awards, user engagement
Requires at least: 5.1.1
Tested up to: 5.2.2
Stable tag: 0.1.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let your users know how much you appreciate them! Enhances your site with the ability to assign and give awards to users based on the actions that they take.

== Description ==
Activating this plugin now means that you are able to award users for specific actions that they take.

**NOTE**: Currently this only works for actions that update or add to user meta values.

At a basic level, the following happens when you activate this plugin:

* Awards _custom post type_ is added to the administration window. Regular post type window but with a few additional meta boxes that provide access to the core behavior of this plugin.

* A new table is created under the name of `{wpdb_prefix}user_awards`. This contains all award assignment references to users. The _User Awards_ sub-menu provides an interface to help perform administrative actions on the table.

There is also a `User Awards` sub-menu which gives a tabular view of all the awards that are assigned to users. This is accessible from the `Awards` admin menu in your WordPress administration area.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/user-awards` directory install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the
This section describes how to install the plugin and get it working.
3. Activate the plugin through the ‘Plugins’ screen in WordPress. You should be notified if the plugin activation was successful.
4. Click on the `Awards` menu item on the administrator sidebar in order to interact with the User Awards plugin administration actions.

== Usage ===

Understanding the different actions you can take in each available window is key to having this plugin work for you.

## Award List Window

This window displays all of the specific "Award" post types.

There are two different bulk actions available to you:

* __Bulk Assign__ - Assign multiple awards to a user

* __Bulk Give__ - Give multiple awards to a user

## New Award / Edit Award Window

These administration windows have three meta boxes associated with them. Below are descriptions of each metabox and why it is included.

### Awards Trigger

_Text Input_. Accepts an **awards trigger string**, which will describe the behavior of how an award will be assigned to users. Documentation for the awards trigger is shown below.

#### Example

You have a membership blog site, and because you're a nice person, you want to award your members for being engaged with your site and liking at least ten blog posts!

A previous developer implemented a like button on each of your site's blog posts that increments a `post_likes` *user_meta* value on the user that clicks it (e.g. If a member likes 3 blog posts, they will have a `post_likes` meta value of `3`).

We've decided to name our award the "User Engagement" award. In order to _assign_ it to a member based on the prerequisites, you would put something like this in the "Awards Trigger" input.

`CURRENT_USER_META ASSIGNED WHERE key=post_likes EQ 10`

This string tells the award to assign itself to the user if the `post_likes` value of the current user's meta was updated or created to equal a value of `10`.

### Auto-Give Award

_Checkbox input_. Check this box to automatically have the award be *given* to a user when it would originally be assigned.

### Apply/Give Award To User

_Select Input combined with a checkbox input_. Select a user from your member list to either assign/give an award by clicking on the *Assign* submit button.

## User Awards Window

This window allows administrators to __physically see and update__ the status of all of the awards that are assigned to users.

This window will allow you to perform the following actions:

* Singular/Bulk remove awards from members
* Singular give awards to members
* Edit Awards

== Documentation ==

## Awards Trigger Syntax

Explanation of each of the items that make up our trigger string, with accepted values of each listed under.

* [ entity ] -- Used to scope your awards trigger to a specific action.
	- CURRENT_USER_META -- Consider the meta value of the current user

* [ trigger_type ] -- Type of action that is performed to the current entity.
	- UPDATED -- When entity value is updated (Listens to calls of the  update_user_meta() function)
	- CREATED -- When entity value is created (Listens to calls of the add_user_meta() function)
	- ASSIGNED -- Listens to calls of both the update_user_meta() and add_user_meta() function.
	- ~EXCLUDED~ -- Not Implemented

* [ trigger ] - Made up of three separate values itself, [ descriptor ] [ operator ] [ control ]
	- [ descriptor ]
		- [ entity_type ] = [ value ] ex: key = hours

    - [ operator ]
    	- GT - greater than
    	- LT - less than
    	- EQ - equal to
    	- GTEQ - greater than equal to
    	- LTEQ - less than equal to

    - [ control ]
    	- Value used to compare against. e.g. 2
    	- *NOTE*: The control *can also be a string*, but in order for this to work, you must use the EQ operator, as shown above.

EXAMPLE:

`CURRENT_USER_META UPDATED WHERE key=total_hours GT 600`

This example creates a wp action handler that only applies when a user's meta tags are updated.
In the handler, we will compare the meta tag being updated to the given comparitors in the [ trigger ].
i.e. we will look for a meta tag of the current user that is labeled "total_hours" and check to see if the value is
greater than 600. If that's the case then the award will be assigned. If not then nothing happens.

## $UserAward Global Object

The awards trigger syntax, while nice, is too limited in its current form. Our plugin provides a global _$UserAward_ variable that allows developers to interact with the core API of the plugin in order to award items through methods that simply are not possible / too complex.

You will find documentation and usage for functions available to you below.

global $UserAward;

/**
 * Check to see if a user already has a specific award
 * @param  int $user_id  - WPUser_ID
 * @param  int $award_id - UserAward_ID (Post ID)
 * @return bool           Whether or not this user has an award with the current award id
 */
$UserAwards->UserHasAward( $user_id, $award_id );

/**
 * Assigns multiple awards to users using AssignAward
 * @param  int $user_id  - WPUser_ID
 * @param  array $award_ids - Array of UserAward_IDs (Post ID)
 * @return bool             - True if awards were assigned, false if there was an error with assigning awards
 */
$UserAwards->AssignAwards( $user_id, $award_ids );

/**
 * Function that marks an award as assigned to a user.
 * We insert a new record into our awards table that relates the award to the user.
 *
 * We do check to see if there is an auto-assignment of the award before we finish up our function though.
 *
 * @param int $user_id  - ID of the user that we are "awarding" the award to
 * @param int $award_id - ID of the award that we are "awarding"
 * @return bool 		- True if award was assigned,
 *                  	  False if:
 *                  	  	- User already has that award
 *                  	  	- Error with assigning our award
 */
$UserAwards->AssignAward( $user_id, $award_id );

/**
 * Give multiple awards to users using GiveAward().
 * @param  int $user_id  - WPUser_ID
 * @param  array $award_ids - Array of UserAward_IDs (Post ID)
 * @return bool             - True if awards were given, false if there was an error with giving awards
 */
$UserAwards->GiveAwards( $user_id, $award_ids );

/**
 * Function that will mark an award as given to a user,
 * which essentially means that we mark the "date_given" time with
 * an actual date.
 *
 * Returns the return value of a `db->update` call
 *
 * @param int $user_id  - ID of the user that we are "awarding" the award to
 * @param int $award_id - ID of the award that we are "awarding"
 * @return mixed        - Return value of a $wpdb->update() call
 */
$UserAwards->GiveAward( $user_id, $award_id );

/**
 * Removes awards from our database.
 * If "$award_id" is null, then we are going to delete everything in the database with the specific "$user_id"
 *
 * @param int $user_id  - ID of the user that we are "awarding" the award to
 * @param int $award_id - ID of the award that we are "awarding"
 * @return mixed 		- Return the value of a $wpdb->delete() call
 */
$UserAwards->RemoveUserAward( $user_id, $award_id = NULL );

/**
 * Function that grabs as many awards assigned to the user as we can based on the parameters given.
 * For example, if just a user_id is supplied, then we will return all of the awards with that user_id.
 * If an award_id is supplied along with our user_id then we will probably get only one award. Hopefully
 *
 * @param int $user_id  - ID of the user that we are "awarding" the award to
 * @param int $award_id - ID of the award that we are "awarding"
 * @return mixed 		- Returnes the value of a $wpdb->get_results() call
 */
$UserAwards->GetUserAward( $user_id, $award_id = NULL);

== Frequently Asked Questions ==
No frequently asked questions are known at this time.

== Changelog ==

= Version 0.0.2 =
Updating readme.txt

= Version 0.0.1 =
Initial version of the plugin.

== Upgrade Notice ==

No Upgrade notices at this time

== Screenshots ==

1. Award List Window. Shows the awards that are available to give to users.
2. New/Edit Award Window #1
3. New/Edit Award Window #2
4. User Awards window. Shows any awards that are assigned to users.

== Attribution ==

This plugin's icon is not an original piece of work. It was made by [**Freepik** from Flaticon.com](www.flaticon.com)
