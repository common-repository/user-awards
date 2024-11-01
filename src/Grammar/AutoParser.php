<?php
namespace UserAwards\Grammar;

/**
 * Class used to parse our input on instantiation.
 */
class AutoParser {
	public $input_string;

	function __construct( $input_string ) {
		$this->input_string = $input_string;
		$this->parse( $input_string );
	}
}