<?php
/**
 * Defined constants to be used in different operation of the source.
 *
 * Should be self explanatory to what they are, but some will have comments.
 */

if ( ! defined('USER_AWARDS_DB_VERSION_KEY') )
{
	define('USER_AWARDS_DB_VERSION_KEY', 'user_awards_version');
}

if ( ! defined('USER_AWARDS_DB_VERSION_VALUE') )
{
	define('USER_AWARDS_DB_VERSION_VALUE', '0.1');
}

if ( ! defined('USER_AWARDS_POST_TYPE') )
{
	define('USER_AWARDS_POST_TYPE', 'user_awards_cpt');
}

/**
 * Awards Create/Update Action Type Names
 */

/** Action name of the auto give input */
if ( ! defined('USER_AWARDS_AUTO_GIVE_TYPE') )
{
	define('USER_AWARDS_AUTO_GIVE_TYPE', 'UserAwards_Auto_Give');
}

/** Action name of the user apply input */
if ( ! defined('USER_AWARDS_USER_APPLY_TYPE') )
{
	define('USER_AWARDS_USER_APPLY_TYPE', 'UserAwards_User_Apply');
}

/**
 * Action name of the grammar meta type input.
 * This is the name of the meta value that we apply to "posts", i.e. awards, whenever a user
 * creates or updates an award. Very useful.
 */
if ( ! defined('USER_AWARDS_GRAMMAR_META_TYPE') )
{
	define('USER_AWARDS_GRAMMAR_META_TYPE', 'UserAwards_Grammar');
}

/** Name for grammar meta entity input */
if ( ! defined('USER_AWARDS_GRAMMAR_META_ENTITY') )
{
	define('USER_AWARDS_GRAMMAR_META_ENTITY', 'UserAwards_Grammar_Entity');
}

/** Name for grammar meta trigger type input */
if ( ! defined('USER_AWARDS_GRAMMAR_META_TRIGGER_TYPE') )
{
	define('USER_AWARDS_GRAMMAR_META_TRIGGER_TYPE', 'UserAwards_Grammar_Trigger_Type');
}

/** Name for grammar meta "where" input */
if ( ! defined('USER_AWARDS_GRAMMAR_META_WHERE') )
{
	define('USER_AWARDS_GRAMMAR_META_WHERE', 'UserAwards_Grammar_Where');
}

/** Name for grammar meta trigger descriptor input */
if ( ! defined('USER_AWARDS_GRAMMAR_META_TRIGGER_DESCRIPTOR') )
{
	define('USER_AWARDS_GRAMMAR_META_TRIGGER_DESCRIPTOR', 'UserAwards_Grammar_Trigger_Descriptor');
}

/** Name for grammar meta trigger operator */
if ( ! defined('USER_AWARDS_GRAMMAR_META_TRIGGER_OPERATOR') )
{
	define('USER_AWARDS_GRAMMAR_META_TRIGGER_OPERATOR', 'UserAwards_Grammar_Trigger_Operator');
}

/** Name for grammar meta trigger control input */
if ( ! defined('USER_AWARDS_GRAMMAR_META_TRIGGER_CONTROL') )
{
	define('USER_AWARDS_GRAMMAR_META_TRIGGER_CONTROL', 'UserAwards_Grammar_Trigger_Control');
}

/**
 * Database Level Constants
 */
if ( ! defined('USER_AWARDS_TABLE_USER_AWARDS') )
{
	define('USER_AWARDS_TABLE_USER_AWARDS', 'user_awards');
}
?>