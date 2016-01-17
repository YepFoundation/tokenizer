<?php

use Yep\Tokenizer\PhpTokenizer;

class PhpTokenizerTest extends PHPUnit_Framework_TestCase {
	public function testPhpTokenizerDefault() {
		$tokenizer = new PhpTokenizer();

		$tokens = $tokenizer->tokenize('<?php echo 123.56; ?>');

		$expected = [
			[1 => PhpTokenizer::TOKEN_OPEN_TAG, 2 => '<?php ', 4 => 0, 8 => 0, 16 => 6],
			[1 => PhpTokenizer::TOKEN_ECHO, 2 => 'echo', 4 => 1, 8 => 6, 16 => 4],
			[1 => PhpTokenizer::TOKEN_WHITESPACE, 2 => ' ', 4 => 2, 8 => 10, 16 => 1],
			[1 => PhpTokenizer::TOKEN_DNUMBER, 2 => '123.56', 4 => 3, 8 => 11, 16 => 6],
			[1 => PhpTokenizer::TOKEN_UNKNOWN, 2 => ';', 4 => 4, 8 => 17, 16 => 1],
			[1 => PhpTokenizer::TOKEN_WHITESPACE, 2 => ' ', 4 => 5, 8 => 18, 16 => 1],
			[1 => PhpTokenizer::TOKEN_CLOSE_TAG, 2 => '?>', 4 => 6, 8 => 19, 16 => 2],
		];

		$this->assertEquals($expected, $tokens);
	}
}
