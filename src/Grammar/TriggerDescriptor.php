<?php
namespace UserAwards\Grammar;

/**
 * Maybe a little too much abstraction....
 * [ trigger_descriptor ]
 * 		- [ entity_type ] = [ value ]
 * 		ex: name = hours
 */
class TriggerDescriptor extends AutoParser implements ParserInterface {
	public $input_string;
	public $key, $value;
	function __construct( $input_string ) {
		parent::__construct( $input_string );
	}

	public function parse( $input_string ) {
		$serialized = explode("=", $input_string);

		$this->key = $serialized[0];
		$this->value = $serialized[1];
	}
}
?>