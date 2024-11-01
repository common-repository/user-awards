<?php
/**
 * Abstraction on our core parser that is a little more forgiving.
 * Made specifically to provide validation, parsing, and encapsulation on trigger string entities without causing errors
 * when there is no trigger string available.
 *
 * The specific times in which we don't have an award available would be during:
 * 	* Award Creation
 * 	* Creating awards with everything filled out EXCEPT for the trigger string.
 */

namespace UserAwards\Grammar;

class PluginParser extends Core implements ParserInterface {
	private function setDefaultValues() {
		$this->entity = '';
		$this->trigger_type = '';
		$this->trigger = new Trigger("  ");
	}

	public function nonUsableGrammar() {
		return (
			$this->trigger->descriptor === "" ||
			$this->trigger->control === ""
		);
	}

	public function parse( $grammar_string = NULL ) {
		try {
			parent::parse( $grammar_string );
		}
		catch (\InvalidArgumentException $e)
		{
			// Either there was an error with the parsing and/or there is currently no grammar string available. Set back to default empty values.
			$this->setDefaultValues();
		}
	}
}