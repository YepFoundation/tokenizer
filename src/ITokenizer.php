<?php
namespace Yep\Tokenizer;

interface ITokenizer {
	const TYPE = 1, VALUE = 2, POSITION = 4, OFFSET = 8, LENGTH = 16;

	public function tokenize($code);
}
