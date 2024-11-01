<?php
namespace UserAwards\BusinessLogic;

/**
 * API for our plugin
 * Will be given as a global variable
 * to wordpress in order to easily add
 * own functionality and interact with our abstraction.
 */
class Core {
	private $db;
	private $db_table;

	function __construct( $db ) {
		$this->db = $db;
		$this->db_table = $this->db->prefix . USER_AWARDS_TABLE_USER_AWARDS;

		// Signal to wordpress to delete all of our user associated links to "awards" if we delete that user from the db
		add_action( 'delete_user', [$this, 'RemoveUserAward'], 10, 1 );
	}

	/**
	 * Have we marked our award with the "Auto Give Award" meta?
	 * @param  int $award_id - ID of award to check
	 * @return boolean           - Raw value of WP meta that is stored.
	 */
	private function _auto_give_Award( $award_id ) {
		return get_post_meta( $award_id, 'WPAward_Auto_Give', true );
	}

	/**
	 * Check to see if a user already has a specific award
	 * @param  int $user_id  - WPUser_ID
	 * @param  int $award_id - WPAward_ID (Post ID)
	 * @return bool           Whether or not this user has an award with the current award id
	 */
	public function UserHasAward( $user_id, $award_id )
	{
		return count( $this->GetUserAward( $user_id, $award_id ) ) > 0;
	}

	/**
	 * Assigns multiple awards to users using AssignAward
	 * @param  int $user_id  - WPUser_ID
	 * @param  array $award_ids - Array of WPAward_IDs (Post ID)
	 * @return bool             - True if awards were assigned, false if there was an error with assigning awards
	 */
	public function AssignAwards( $user_id, $award_ids )
	{
		try
		{
			foreach( $award_ids as $award_id )
			{
				$this->AssignAward( $user_id, $award_id );
			}
		}
		catch ( Exception $e )
		{
			return false;
		}

		return true;
	}

	/**
	 * Assign an award to a user.
	 * We insert a new record into our awards table that relates the award to the user.
	 *
	 * We do check to see if there is an auto-assignment of the award before we finish up our function though.
	 *
	 * @param int $user_id  - ID of the user that we are "awarding" the award to
	 * @param int $award_id - ID of the award that we are "awarding"
	 */
	public function AssignAward( $user_id, $award_id ) {

		/**
		 * We don't want to return false because that means that we may trigger behavior that says we have not actually
		 * assigned an award to a person.
		 */
		if ( $this->UserHasAward( $user_id, $award_id ) )
		{
			return true;
		}

		$award_assigned = $this->db->insert(
			$this->db_table,
			[
				'user_id' => $user_id,
				'award_id' => $award_id,
			],
			[
				'user_id' => '%d',
				'award_id' => '%d'
			]
		);

		if ( ! $award_assigned )
		{
			return false;
		}

		if ( ! empty( $this->_auto_give_Award( $award_id ) ) )
		{
			$award_given = $this->GiveAward( $user_id, $award_id );
		}

		return true;
	}


	/**
	 * Give multiple awards to users
	 */
	public function GiveAwards( $user_id, $award_ids )
	{
		// Validate User
		// Validate Award
		try
		{
			foreach( $award_ids as $award_id )
			{
				$this->GiveAward( $user_id, $award_id );
			}
		}
		catch ( Exception $e )
		{
			return false;
		}

		return true;
	}

	/**
	 * Function that will mark an award as given to a user,
	 * which essentially means that we mark the "date_given" time with
	 * an actual date.
	 *
	 * Returns the return value of a `db->update` call
	 *
	 * @param int $user_id  - ID of the user that we are "awarding" the award to
	 * @param int $award_id - ID of the award that we are "awarding"
	 *
	 */
	public function GiveAward( $user_id, $award_id )
	{
		$award = $this->GetUserAward( $user_id, $award_id );

		if ( empty( $award ) )
		{
			$this->AssignAward( $user_id, $award_id );
		}

		if ( ! empty( $award[0]->date_given ) )
		{
			return 0;
		}

		// Marks the award as given to the user, instead of just assigned to the user
		$award_given = $this->db->update(
			$this->db_table,
			[
				'date_given' => date('Y-m-d G:i:s')
			],
			[
				'user_id' => $user_id,
				'award_id' => $award_id
			],
			NULL,
			[
				'user_id' => '%d',
				'award_id' => '%d'
			]
		);

		return $award_given;
	}

	/**
	 * Removes awards from our database.
	 * If "$award_id" is null, then we are going to delete everything in the database with the specific "$user_id"
	 *
	 * @param int $user_id  - ID of the user that we are "awarding" the award to
	 * @param int $award_id - ID of the award that we are "awarding"
	 */
	public function RemoveUserAward( $user_id, $award_id = NULL ) {

		$where_clause = [
			'user_id' => $user_id,
		];

		$where_format = [
			'user_id' => '%d'
		];

		// Add in our award id to the where clase if it is available. There's a chance that it isn't.
		if ( $award_id && $award_id > 0 )
		{
			$where_clause['award_id'] = $award_id;
			$where_format['award_id'] = '%d';
		}

		$award_deleted = $this->db->delete(
			$this->db_table,
			$where_clause,
			$where_format
		);

		return $award_deleted;
	}

	/**
	 * Function that grabs as many awards assigned to the user as we can based on the parameters given.
	 * For example, if just a user_id is supplied, then we will return all of the awards with that user_id.
	 * If an award_id is supplied along with our user_id then we will probably get only one award. Hopefully
	 *
	 * @param int $user_id  - ID of the user that we are "awarding" the award to
	 * @param int $award_id - ID of the award that we are "awarding"
	 */
	public function GetUserAward( $user_id, $award_id = NULL) {
		$query = "SELECT * FROM {$this->db_table} WHERE `user_id` = %d";

		$prep_params = [ $user_id ];

		if ( ! empty( $award_id ) )
		{
			$query .= " AND `award_id` = %d";
			$prep_params[] = $award_id;
		}

		$prep_query = $this->db->prepare($query, $prep_params);

		$awards = $this->db->get_results($prep_query);

		return $awards;
	}

	public function GetUserAwards( $values = NULL ) {
		$selector = NULL;

		// If we have a values array, then we limit the values that are returned.
		if ( $values && is_array($values) )
		{
			$selector = implode(", ", $values);
		}
		else
		{
			$selector = "*";
		}

		$query = "SELECT ". $selector . " FROM {$this->db_table}";
	}

	/**
	 * Compares $val_1 to $val_2 based on the operator given.
	 * @param mixed $val_1
	 * @param mixed $val_2
	 * @param string $op    - Operator.
	 */
	public function ShouldApplyAward( $val_1, $val_2, $op ) {
		switch ( $op ) {
			case 'gt':
				return $val_1 > $val_2;
			case 'lt':
				return $val_1 < $val_2;
			case 'eq':
				return $val_1 === $val_2;
			case 'gteq':
				return $val_1 >= $val_2;
			case 'lteq':
				return $val_1 <= $val_2;
			default: // This will be the path we take if our operator is blank.
				return false;
		}
	}
}
?>