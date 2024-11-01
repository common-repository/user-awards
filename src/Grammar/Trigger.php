<?php
namespace UserAwards\Grammar;

/**
 * Class used to contain all trigger related functionality. This is a help because of the fact that
 * we're able to abstract all the details of our trigger into specific class variables
 *
 * [ triggers ] - [ descriptor ] [ operator ] [ control ]
 * 	- [ descriptor ]
 * 		- [ entity_type ] = [ value ]
 * 		ex: name = hours
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
 */
class Trigger extends AutoParser implements ParserInterface {
	public $descriptor, $operator, $control;

	function __construct( $string ) {
		parent::__construct($string);
	}

	private $valid_operators = [
		'',
		'gt',
		'lt',
		'eq',
		'gteq',
		'lteq'
	];

	// General function that throws an error if we don't have an item in an array.
	private function throwIfNotValidated( $valid_items, $item, $eMsg ) {
		if ( ! in_array( $item, $valid_items ) )
		{
			throw new \InvalidArgumentException( $eMsg );
		}

		return true;
	}
	private function validateOperator( $input ) {
		if ( $this->throwIfNotValidated( $this->valid_operators, $input, "Trigger Control Operator Not Valid" ) )
		{
			return $input;
		}
	}

	private function validateTriggerControl( $input ) {
		if ( $this->operator !== "eq" && ! is_numeric( $input ) )
		{
			throw new \InvalidArgumentException("Trigger control must be a numeric if you're not testing equality");
		}

		// Force numeric inputs to be an int value
		if ( is_numeric( $input ) )
		{
			$input = intval( $input );
		}

		return $input;
	}

	public function parse( $string ) {
		$serialized = explode(" ", $string);

		if ( empty( $serialized ) ) {
			throw new \InvalidArgumentException("AwardGrammarTrigger parse string must not be empty");
		}

		$this->descriptor = $serialized[0];
		$this->operator = $this->validateOperator($serialized[1]);
		$this->control = $this->validateTriggerControl($serialized[2]);
	}
}
?>