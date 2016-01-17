<?php

use Yep\Tokenizer\Tokenizer;

class TokenizerTest extends PHPUnit_Framework_TestCase {
	public function testTokenizerDefault() {
		$tokenizer = new Tokenizer(
			[
				'TOKEN_DATE'       => '\d+\.\d+\.\d+',
				'TOKEN_WORD'       => '\w+',
				'TOKEN_SEPARATOR'  => '\s*\-\s*',
				'TOKEN_WHITESPACE' => '\s+',
			]
		);

		$tokens = $tokenizer->tokenize('John Doe - 02.01.1987 Foo Bar - 10.11.1987');

		$expected = [
			[1 => 'TOKEN_WORD', 2 => 'John', 4 => 0, 8 => 0, 16 => 4],
			[1 => 'TOKEN_WHITESPACE', 2 => ' ', 4 => 1, 8 => 4, 16 => 1],
			[1 => 'TOKEN_WORD', 2 => 'Doe', 4 => 2, 8 => 5, 16 => 3],
			[1 => 'TOKEN_SEPARATOR', 2 => ' - ', 4 => 3, 8 => 8, 16 => 3],
			[1 => 'TOKEN_DATE', 2 => '02.01.1987', 4 => 4, 8 => 11, 16 => 10],
			[1 => 'TOKEN_WHITESPACE', 2 => ' ', 4 => 5, 8 => 21, 16 => 1],
			[1 => 'TOKEN_WORD', 2 => 'Foo', 4 => 6, 8 => 22, 16 => 3],
			[1 => 'TOKEN_WHITESPACE', 2 => ' ', 4 => 7, 8 => 25, 16 => 1],
			[1 => 'TOKEN_WORD', 2 => 'Bar', 4 => 8, 8 => 26, 16 => 3],
			[1 => 'TOKEN_SEPARATOR', 2 => ' - ', 4 => 9, 8 => 29, 16 => 3],
			[1 => 'TOKEN_DATE', 2 => '10.11.1987', 4 => 10, 8 => 32, 16 => 10]
		];

		$this->assertEquals($expected, $tokens);
	}

	public function testTokenizerCaseSensitive() {
		$tokenizer = new Tokenizer(
			[
				'TOKEN_LOWER' => '[a-z]+',
				'TOKEN_UPPER' => '[A-Z]+',
			]
		);

		$tokens = $tokenizer->tokenize('FooBAR');

		$expected = [
			[1 => 'TOKEN_UPPER', 2 => 'F', 4 => 0, 8 => 0, 16 => 1],
			[1 => 'TOKEN_LOWER', 2 => 'oo', 4 => 1, 8 => 1, 16 => 2],
			[1 => 'TOKEN_UPPER', 2 => 'BAR', 4 => 2, 8 => 3, 16 => 3]
		];

		$this->assertSame($expected, $tokens);
	}

	public function testTokenizerCaseInsensitive() {
		$tokenizer = new Tokenizer(
			[
				'TOKEN_LOWER' => '[a-z]+',
				'TOKEN_UPPER' => '[A-Z]+',
			],
			'i'
		);

		$tokens = $tokenizer->tokenize('FooBAR');

		$expected = [
			[1 => 'TOKEN_LOWER', 2 => 'FooBAR', 4 => 0, 8 => 0, 16 => 6]
		];

		$this->assertSame($expected, $tokens);
	}

	/**
	 * @expectedException \Yep\Tokenizer\UnexpectedTokenException
	 */
	public function testTokenizerWithUnexpectedTokenException() {
		$tokenizer = new Tokenizer(
			[
				'TOKEN_UPPER' => '[A-Z]+',
			]
		);

		$tokenizer->tokenize('FooBAR');
	}
}
