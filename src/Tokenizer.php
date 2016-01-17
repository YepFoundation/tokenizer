<?php
namespace Yep\Tokenizer;

class Tokenizer implements ITokenizer {
	protected $regexp;
	protected $keys;
	protected $count = 0;

	/**
	 * Tokenizer constructor
	 *
	 * @param array  $patterns
	 * @param string $flags
	 */
	public function __construct(array $patterns, $flags = '') {
		$tmp = [];

		foreach ($patterns as $pattern_key => $pattern_value) {
			$tmp[] = "?<$pattern_key>$pattern_value";
		}

		$this->regexp = '~(' . implode(')|(', $tmp) . ')~Au' . $flags;
		$this->keys = array_keys($patterns);
		$this->count = count($patterns);
	}

	/**
	 * Tokenize input
	 *
	 * @param string $input
	 * @return array
	 * @throws UnexpectedTokenException
	 */
	public function tokenize($input) {
		preg_match_all($this->regexp, $input, $tokens, PREG_SET_ORDER);
		$length = 0;

		foreach ($tokens as $token_i => &$token) {
			$type = null;

			for ($i = 0; $i < $this->count; $i++) {
				if (isset($this->keys[$i], $token[$this->keys[$i]]) && $token[$this->keys[$i]] != null) {
					$type = $this->keys[$i];
					break;
				}
			}

			$token = [
				self::TYPE     => $type,
				self::VALUE    => $token[0],
				self::POSITION => $token_i,
				self::OFFSET   => $length,
			];

			$length += $token[self::LENGTH] = mb_strlen($token[self::VALUE]);
		}

		if ($length !== mb_strlen($input)) {
			$text = mb_substr($input, 0, $length);
			$line = mb_substr_count($text, "\n") + 1;
			$col = $length - mb_strrpos("\n$text", "\n") + 1;
			$token = str_replace("\n", '\n', mb_substr($input, $length, 10));

			throw new UnexpectedTokenException(sprintf('Unexpected "%s" on line %d, column %d.', $token, $line, $col));
		}

		return $tokens;
	}

}
