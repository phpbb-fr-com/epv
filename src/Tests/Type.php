<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace Phpbb\Epv\Tests;

class Type
{

	public const TYPE_COMPOSER = 1;
	public const TYPE_HTML = 2;
	public const TYPE_LANG = 4;
	public const TYPE_PHP = 8;
	public const TYPE_PLAIN = 16;
	public const TYPE_SERVICE = 32;
	public const TYPE_XML = 64;
	public const TYPE_YML = 128;
	public const TYPE_JSON = 256;
	public const TYPE_BINARY = 512;
	public const TYPE_CSS = 1024;
	public const TYPE_JS = 2048;
	public const TYPE_LOCK = 4096;
	public const TYPE_ROUTING = 8192;
	public const TYPE_MIGRATION = 16384;
}
