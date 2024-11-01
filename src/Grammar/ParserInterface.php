<?php

namespace UserAwards\Grammar;

/**
 * Used By:
 * - WPAward\Grammar\Core.php,
 * - WPAward\Grammar\Trigger.php,
 * - WPAward\Grammar\TriggerDescriptor.php
 */
interface ParserInterface
{
	public function parse( $parse_string );
}
?>