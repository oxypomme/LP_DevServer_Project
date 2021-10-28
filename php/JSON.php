<?php

namespace Crisis;

class JSON
{
	/**
	 * json_encode with base flags already defined
	 *
	 * Base flags : `JSON_UNESCAPED_SLASHES`, `JSON_UNESCAPED_UNICODE` and `JSON_NUMERIC_CHECK`
	 *
	 * @param mixed $value
	 * @param int $f Additional flags
	 * @return null|string The JSON or null if an error happened
	 */
	static function encode($value, int $f = 0): ?string
	{
		$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | $f;
		$res = \json_encode($value, $flags);
		if (!$res) {
			return null;
		}
		return $res;
	}
}
