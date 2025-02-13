<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Model;

class PackageJson {

	private $data;

	public function __construct(private string $path) {
		$jsonString = file_get_contents($this->path);
		var_dump($this->path);
		var_dump($jsonString);
		$this->data = json_decode($jsonString, true);
	}

	public function setVersion(string $version): self {
		$this->data['version'] = $version;
		return $this;
	}

	public function __toString(): string {
		$encoded = json_encode($this->data/*, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE*/);
		//$encoded = preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', $encoded);
		$encoded = self::format($encoded, true, true);
		return $encoded;
	}

	public function writeBack(): void {
		file_put_contents($this->path, (string)$this);
	}

	/**
	 * This code is based on the function found at:
	 *  http://recursive-design.com/blog/2008/03/11/format-json-with-php/
	 *
	 * Originally licensed under MIT by Dave Perrett <mail@recursive-design.com>
	 *
	 *
	 * @param  string $json
	 * @param  bool   $unescapeUnicode Un escape unicode
	 * @param  bool   $unescapeSlashes Un escape slashes
	 * @return string
	 */
	public static function format($json, $unescapeUnicode, $unescapeSlashes) {
		$result = '';
		$pos = 0;
		$strLen = strlen($json);
		$indentStr = "  "; // FIXME: might be able to detect existing indent
		$newLine = "\n";
		$outOfQuotes = true;
		$buffer = '';
		$noescape = true;

		for ($i = 0; $i < $strLen; $i++) {
			// Grab the next character in the string
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ('"' === $char && $noescape) {
				$outOfQuotes = !$outOfQuotes;
			}

			if (!$outOfQuotes) {
				$buffer .= $char;
				$noescape = '\\' === $char ? !$noescape : true;
				continue;
			}
			if ('' !== $buffer) {
				if ($unescapeSlashes) {
					$buffer = str_replace('\\/', '/', $buffer);
				}

				if ($unescapeUnicode && function_exists('mb_convert_encoding')) {
					// https://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
					$buffer = preg_replace_callback('/(\\\\+)u([0-9a-f]{4})/i', function ($match) {
						$l = strlen($match[1]);

						if ($l % 2) {
							$code = hexdec($match[2]);
							// 0xD800..0xDFFF denotes UTF-16 surrogate pair which won't be unescaped
							// see https://github.com/composer/composer/issues/7510
							if (0xD800 <= $code && 0xDFFF >= $code) {
								return $match[0];
							}

							return str_repeat('\\', $l - 1) . mb_convert_encoding(
								pack('H*', $match[2]),
								'UTF-8',
								'UCS-2BE'
							);
						}

						return $match[0];
					}, $buffer);
				}

				$result .= $buffer.$char;
				$buffer = '';
				continue;
			}

			if (':' === $char) {
				// Add a space after the : character
				$char .= ' ';
			} elseif ('}' === $char || ']' === $char) {
				$pos--;
				$prevChar = substr($json, $i - 1, 1);

				if ('{' !== $prevChar && '[' !== $prevChar) {
					// If this character is the end of an element,
					// output a new line and indent the next line
					$result .= $newLine;
					for ($j = 0; $j < $pos; $j++) {
						$result .= $indentStr;
					}
				} else {
					// Collapse empty {} and []
					$result = rtrim($result);
				}
			}

			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line
			if (',' === $char || '{' === $char || '[' === $char) {
				$result .= $newLine;

				if ('{' === $char || '[' === $char) {
					$pos++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
		}

		return $result;
	}
}
