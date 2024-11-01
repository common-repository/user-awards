<?php

/**
 * Class that contains the rules for our grammar that will parse out our query and see if there is a need to trigger an award to be given to a user.
 *
 * The base structure for our grammar will be:
 * [ entity ] [ trigger_type ] WHERE [ trigger ]
 *
 * [ entity ]
 * 	- CURRENT_USER_META
 *
 * [ trigger_type ] -- These all consider the entity to per
 * - UPDATED
 * - CREATED
 * - ASSIGNED
 * - EXCLUDED
 *
 * [ trigger ] - [ descriptor ] [ operator ] [ control ]
 * 	- [ descriptor ]
 * 		- [ entity_type ] = [ value ]
 * 		ex: key = hours
 *
 * 	- [ operator ]
 * 		- GT - greater than
 * 		- LT - less than
 * 		- EQ - equal to
 * 	 	- GTEQ - greater than equal to
 * 	 	- LTEQ - less than equal to
 *
 *  - [ control ]
 *  	- Value used to compare against. e.g. 2
 *
 * EXAMPLE:
 * CURRENT_USER_META UPDATED WHERE key=total_hours GT 600
 *
 * This example creates a wp action handler that only applies when a user's meta tags are updated.
 * In the handler, we will compare the meta tag being updated to the given comparitors in the [ trigger ].
 * i.e. we will look for a meta tag of the current user that is labeled "total_hours" and check to see if the value is
 * greater than 600. If that's the case then we return true, if not we return negative.
 */

namespace UserAwards\Grammar;

class Core implements ParserInterface {
	public $entity, $trigger_type, $trigger, $input_string;

	/**
	 * Validation items for our grammar
	 */
	private $valid_entities = [
		'current_user_meta'
	];

	private $valid_trigger_types = [
		'updated',
		'created',
		'excluded',
		'assigned'
	];

	/** End validation items. */

	// General function that throws an error if we don't have an $item within $valid_items
	private function throwIfNotValidated( $valid_items, $item, $eMsg ) {
		if ( ! in_array( $item, $valid_items ) )
		{
			throw new \InvalidArgumentException( $eMsg );
		}

		return true;
	}

	/**
	 * Fuction to validate our entries
	 * @param  string $input          - Item to check against our valid entities
	 * @param  array $valid_entities  - Items to reference against our input. We're considering these items as valid input
	 * @param  string $eMsg           - Error message shown if our input item is validated.
	 * @return string
	 */
	private function _validate( $input, $valid_entities, $eMsg ) {
		if ( $this->throwIfNotValidated( $valid_entities, $input, $eMsg ) )
		{
			return $input;
		}
	}

	// Reserved for special users in which the trigger type can be changed. -- What?
	public function change_trigger_type( $trigger_type ) {
		$this->trigger_type = $trigger_type;
	}

	/**
	 * Function use to parse our trigger lang and apply basic information to our object. Very important.
	 * @param  string $grammar_string - Trigger lang applied to our award that specifies how we are awarding.
	 * @return void
	 */
	public function parse( $grammar_string ) {

		if ( empty( $grammar_string ) )
		{
			throw new \InvalidArgumentException("Award Grammar parse function, empty string");
		}

		$parseCount = 0;

		$serialized = explode(" ", strtolower($grammar_string));

		$parseValue = NULL;

		// Parse our string up until the WHERE clause
		// We process that whole string within our Trigger object
		// to build its properties.
		while ( $parseValue != "where" )
		{

			$parseValue = array_shift( $serialized );

			switch ( $parseCount ) {
				case 0:
					$this->entity = $this->_validate($parseValue, $this->valid_entities, "Entity not valid");
					break;
				case 1:
					$this->trigger_type = $this->_validate($parseValue, $this->valid_trigger_types, "Trigger type not valid");
					break;
				default:
					break;
			}

			// Possible malforming of the string wont include our where clause, which we "need" in order to make sure we are
			// getting our trigger statement.
			if ( empty( $serialized ) )
			{
				throw new \InvalidArgumentException("You must include a \"WHERE\" clause in your statement");
			}

			$parseCount++;
		}

		// Re-Stringify the query in order to get the "trigger" portion of it
		$serialized = implode(" ", $serialized);

		$this->trigger = new Trigger( $serialized );

		return true;
	}
}
?>