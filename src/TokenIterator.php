<?php
namespace Yep\Tokenizer;

class TokenIterator implements \Iterator, \SeekableIterator {
	/** @var int */
	protected $position = -1;

	/** @var array */
	protected $tokens = [];

	/**
	 * TokenIterator constructor
	 *
	 * @param array $tokens
	 */
	public function __construct(array $tokens) {
		$this->tokens = $tokens;
	}

	// ↓ Iterator ↓
	/**
	 * Returns current token
	 *
	 * @return array|null
	 */
	public function current() {
		if (isset($this->tokens[$this->position])) {
			return $this->tokens[$this->position];
		}

		return null;
	}

	/**
	 * Moves to next position
	 */
	public function next() {
		++$this->position;
	}

	/**
	 * Returns current position
	 *
	 * @return int
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * Returns true, if current position is valid.
	 *
	 * @return bool
	 */
	public function valid() {
		return isset($this->tokens[$this->position]);
	}

	/**
	 * Moves to begin
	 */
	public function rewind() {
		$this->position = -1;
	}

	/**
	 * Moves to previous position
	 */
	public function prev() {
		--$this->position;
	}

	/**
	 * Moves to next or previous position and returns token, if is valid.
	 *
	 * @param bool $back
	 * @return array|null
	 */
	public function step($back = false) {
		if ($back) {
			$this->prev();
		}

		if (!$back) {
			$this->next();
		}

		if ($this->valid()) {
			return $this->current();
		}

		return null;
	}

	/**
	 * Moves to specific position
	 *
	 * @param int $position
	 */
	public function seek($position) {
		$this->position = $position;
	}

	// ↑ Iterator ↑

	/**
	 * Checks the current token
	 *
	 * @return bool
	 */
	public function currentIs() {
		$token = $this->current();

		if ($token === null) {
			return false;
		}

		$tokens = func_get_args();

		return
			in_array($token[ITokenizer::TYPE], $tokens, true)
			|| in_array($token[ITokenizer::VALUE], $tokens, true);
	}

	/**
	 * Returns current token value
	 *
	 * @return string|null
	 */
	public function currentValue() {
		if (isset($this->tokens[$this->position][ITokenizer::VALUE])) {
			return $this->tokens[$this->position][ITokenizer::VALUE];
		}

		return null;
	}

	/**
	 * Magic method which find and returns tokens
	 *
	 * @param array $tokens
	 * @param bool  $expected
	 * @param bool  $only_first
	 * @param bool  $join
	 * @param bool  $prev
	 * @return array|null|string
	 */
	protected function doSomethingMagic(array $tokens = [], $expected = true, $only_first = false, $join = false, $prev = false) {
		$found = [];

		while ($token = $this->step($prev)) {
			if (!empty($tokens) && (in_array($token[ITokenizer::TYPE], $tokens, true) || in_array($token[ITokenizer::VALUE], $tokens, true)) !== $expected) {
				if ($only_first) {
					continue;
				}

				$this->step(!$prev);
				break;
			}

			$found[] = $token;

			if ($only_first) {
				break;
			}
		}

		if ($prev) {
			$found = array_reverse($found);
		}

		if (!$join) {
			return $only_first ? array_shift($found) : $found;
		}

		$values = array_column($found, ITokenizer::VALUE);

		if (!$values) {
			return null;
		}

		return implode('', $values);
	}

	public function nextOne() {
		return $this->doSomethingMagic(func_get_args(), true, true); // expected, only_first
	}

	public function prevOne() {
		return $this->doSomethingMagic(func_get_args(), true, true, false, true); // expected, only_first, prev
	}

	public function nextOneNot() {
		return $this->doSomethingMagic(func_get_args(), false, true); // expected, only_first
	}

	public function prevOneNot() {
		return $this->doSomethingMagic(func_get_args(), false, true, false, true); // expected, only_first, prev
	}

	public function nextOneValue() {
		return $this->doSomethingMagic(func_get_args(), true, true, true); // expected, only_first, join
	}

	public function prevOneValue() {
		return $this->doSomethingMagic(func_get_args(), true, true, true, true); // expected, only_first, join, prev
	}

	public function nextAll() {
		return $this->doSomethingMagic(func_get_args()); // expected
	}

	public function prevAll() {
		return $this->doSomethingMagic(func_get_args(), true, false, false, true); // expected, prev
	}

	public function nextUntil() {
		return $this->doSomethingMagic(func_get_args(), false); // not expected
	}

	public function prevUntil() {
		return $this->doSomethingMagic(func_get_args(), false, false, false, true); // not expected, prev
	}

	public function joinNext() {
		return $this->doSomethingMagic(func_get_args(), true, false, true); // expected, join
	}

	public function joinPrev() {
		return $this->doSomethingMagic(func_get_args(), true, false, true, true); // expected, join, prev
	}

	public function joinNextUntil() {
		return $this->doSomethingMagic(func_get_args(), false, false, true); // not expected, join
	}

	public function joinPrevUntil() {
		return $this->doSomethingMagic(func_get_args(), false, false, true, true); // not expected, join, prev
	}
}
