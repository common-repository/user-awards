<?php
namespace UserAwards\Listener;

class Core {
	private $award_id;
	private $grammar = null;
	public $grammarFunction = null;
	private $UserAwards = null;

	/**
	 * Class Constructor
	 * @param string $award_grammar_string - String of our trigger grammar to use that will put a listener up
	 * @param WPAward $UserAwards       - WPAward that performs award operations on a user, such as checking if the user should have an award or what not.
	 */
	function __construct( $award_id, $grammar, $UserAwards ) {
		$this->award_id = $award_id;
		$this->grammar = $grammar;
		$this->grammarFunction = strtolower($this->grammar->entity . '_' . $this->grammar->trigger_type);
		$this->UserAwards = $UserAwards;
	}

	public function add_listeners( $user ) {

		if ( ! is_a( $user, 'WP_User') ) {
			throw new \InvalidArgumentException("User is not a WP_User");
		};

		if ( ! $user->exists() )
		{
			throw new \UnexpectedValueException("User being supplied does not exist");
		}

		// Pre-set our trigger type based on whether the user has a user meta field or not.
		if ( $this->grammar->trigger_type === "assigned" )
		{
			if ( get_user_meta($user->ID, $this->grammar->trigger->descriptor, true) === '' )
			{
				$this->grammar->change_trigger_type("created");
			}
			else
			{
				$this->grammar->change_trigger_type("updated");
			}

			$this->grammarFunction = strtolower($this->grammar->entity . '_' . $this->grammar->trigger_type);
		}

		$action = "";

		if ( $this->grammar->trigger_type == "updated" )
		{
			// Wordpress action hook that is used whenever we "Update" a user meta value
			// https://codex.wordpress.org/Plugin_API/Action_Reference/updated_(meta_type)_meta
			$action = "updated_user_meta";
		}
		// Explicit Create
		else if ( $this->grammar->trigger_type == "created" )
		{
			// Wordpress action hook that is called whenever we "Add", i.e. create, a user meta value
			//https://codex.wordpress.org/Plugin_API/Action_Reference/added_(meta_type)_meta
			$action = "added_user_meta";
		}

		add_action($action, [ $this, $this->grammarFunction ], 10, 4); // example: current_user_meta_updated
	}

	/**
	 * Assign awards to user.
	 * @param  int $meta_id     - ID of meta value
	 * @param  int $user_id   - Unsure as to what really this is right not
	 * @param  int $_award_meta_key    - WordPress meta key
	 * @param  int $_award_meta_value - WordPress meta value
	 * @return bool 				  - Return whether or not we had a successful award
	 */
	function assignAward( $meta_id, $user_id, $_award_meta_key, $_award_meta_value )
	{
		// echo "Testing to see if we're assigning an award\n";
		// Award user if our current user's meta key passes the grammar control
		$descriptor = $this->grammar->trigger->descriptor;

		// Check if the updated meta key is the same as the meta key we are listening for
		if ( $_award_meta_key !== $descriptor )
		{
			return false;
		}

		// Check to see if we have a numeric vaulue. If we do, convert it to an int.
		if ( is_numeric( $_award_meta_value ) )
		{
			$_award_meta_value = intval( $_award_meta_value );
		}

		// Testing whether we should apply an award to a user.
		if ( ! $this->UserAwards->shouldApplyAward(
			$_award_meta_value,
			$this->grammar->trigger->control,
			$this->grammar->trigger->operator
		) )
		{
			return false;
		}

		// Finally, assign our award if we make it this far
		$award_assigned = $this->UserAwards->AssignAward( $user_id, $this->award_id );

		if ( ! $award_assigned )
		{
			throw new \RuntimeException("Award was not assigned on user meta update when it should have been");
		}

		return true;
	}

	/**
	 * Function that responds to a user metadata update. Will assign an award to a user if and only if they pass the
	 * award's trigger conditions.
	 */
	function current_user_meta_updated( $meta_id, $user_id, $_award_meta_key, $_award_meta_value )
	{
		$this->assignAward( $meta_id, $user_id, $_award_meta_key, $_award_meta_value );
		return true;
	}

	function current_user_meta_excluded( $meta_id, $user_id, $_award_meta_key, $_award_meta_value )
	{
		throw new \Exception("Not Implemented");
	}

	function current_user_meta_created( $meta_id, $user_id, $_award_meta_key, $_award_meta_value )
	{
		$this->assignAward( $meta_id, $user_id, $_award_meta_key, $_award_meta_value );
		return true;
	}

	function current_user_meta_assigned( $meta_id, $user_id, $_award_meta_key, $_award_meta_value )
	{
		$this->assignAward( $meta_id, $user_id, $_award_meta_key, $_award_meta_value );
		return true;
	}
}
?>