<?php
use Yep\Reflection\ReflectionClass;
use Yep\Tokenizer\TokenIterator;
use Yep\Tokenizer\Tokenizer;

class TokenIteratorTest extends PHPUnit_Framework_TestCase {
	protected function prepareTestTokens() {
		$tokenizer = new Tokenizer(
			[
				'TOKEN_A' => 'A',
				'TOKEN_B' => 'B',
				'TOKEN_C' => 'C',
				'TOKEN_D' => 'D',
				'TOKEN_E' => 'E',
			]
		);

		$tokens = $tokenizer->tokenize('ABCDE');

		return $tokens;
	}

	public function testTokenIterator() {
		$tokens = $this->prepareTestTokens();
		$iterator = new TokenIterator($tokens);

		$this->assertSame(-1, $iterator->key());
		$this->assertFalse($iterator->valid());
		$this->assertNull($iterator->current());

		$iterator->next();
		$this->assertSame(0, $iterator->key());
		$this->assertTrue($iterator->valid());
		$this->assertSame($tokens[0], $iterator->current());

		$iterator->next();
		$iterator->next();
		$iterator->next();
		$this->assertSame(3, $iterator->key());

		$iterator->prev();
		$this->assertSame(2, $iterator->key());
		$this->assertTrue($iterator->valid());
		$this->assertSame($tokens[2], $iterator->current());

		$iterator->rewind();
		$this->assertSame(-1, $iterator->key());
		$this->assertFalse($iterator->valid());
		$this->assertNull($iterator->current());

		$iterator->seek(4);
		$this->assertSame(4, $iterator->key());
		$this->assertTrue($iterator->valid());
		$this->assertSame($tokens[4], $iterator->current());

		$iterator->step(true);
		$step = $iterator->step(true);
		$this->assertSame(2, $iterator->key());
		$this->assertTrue($iterator->valid());
		$this->assertSame($tokens[2], $iterator->current());
		$this->assertSame($step, $iterator->current());

		$step = $iterator->step();
		$this->assertSame(3, $iterator->key());
		$this->assertTrue($iterator->valid());
		$this->assertSame($tokens[3], $iterator->current());
		$this->assertSame($step, $iterator->current());

		$iterator->rewind();

		$step = $iterator->step();
		$this->assertSame(0, $iterator->key());
		$this->assertTrue($iterator->valid());
		$this->assertSame($tokens[0], $iterator->current());
		$this->assertSame($step, $iterator->current());

		$step = $iterator->step(true);
		$this->assertSame(-1, $iterator->key());
		$this->assertFalse($iterator->valid());
		$this->assertNull($iterator->current());
		$this->assertNull($step);
	}

	public function testTokenIteratorDoSomethingMagic() {
		$tokens = $this->prepareTestTokens();
		$iterator = new TokenIterator($tokens);
		$reflection = ReflectionClass::from($iterator);

		// Find expected tokens in both right
		$this->assertSame([$tokens[0], $tokens[1], $tokens[2]], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, false, false, false]));
		$iterator->rewind();

		// Find expected tokens in both left
		$iterator->seek(3);
		$this->assertSame([$tokens[0], $tokens[1], $tokens[2]], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, false, false, true]));

		// Find not expected tokens in both right
		$iterator->rewind();
		$this->assertSame([$tokens[0], $tokens[1], $tokens[2]], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_D', 'E'], false, false, false, false]));

		// Find not expected tokens in both left
		$iterator->seek(4);
		$this->assertSame([$tokens[2], $tokens[3]], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B'], false, false, false, true]));
		$iterator->next();
		$this->assertSame([$tokens[2]], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B'], false, false, false, true]));

		// Find one expected token in both right
		$iterator->rewind();
		$this->assertSame($tokens[0], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, true, false, false]));

		// Find one expected token in both left
		$iterator->seek(3);
		$this->assertSame($tokens[2], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, true, false, true]));

		// Find one not expected token in both right
		$iterator->rewind();
		$this->assertSame($tokens[2], $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B'], false, true, false, false]));

		// Find one not expected token in both left
		$iterator->seek(5);
		$this->assertSame($tokens[4], $reflection->invokeMethod('doSomethingMagic', [['B', 'TOKEN_C'], false, true, false, true]));
		$iterator->prev();
		$this->assertSame($tokens[0], $reflection->invokeMethod('doSomethingMagic', [['B', 'TOKEN_C'], false, true, false, true]));

		// Join expected tokens in both right
		$iterator->rewind();
		$this->assertSame('ABC', $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, false, true, false]));

		// Join expected tokens in both left
		$iterator->seek(3);
		$this->assertSame('ABC', $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, false, true, true]));

		// Join not expected tokens in both right
		$iterator->rewind();
		$this->assertSame('ABC', $reflection->invokeMethod('doSomethingMagic', [['TOKEN_D', 'E'], false, false, true, false]));

		// Join not expected tokens in both left
		$iterator->seek(4);
		$this->assertSame('CD', $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B'], false, false, true, true]));
		$iterator->next();
		$this->assertSame('C', $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B'], false, false, true, true]));

		// Join one expected token in both right
		$iterator->rewind();
		$this->assertSame('A', $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, true, true, false]));

		// Join one expected token in both left
		$iterator->seek(3);
		$this->assertSame('C', $reflection->invokeMethod('doSomethingMagic', [['TOKEN_A', 'B', 'TOKEN_C'], true, true, true, true]));
	}

	public function testTokenIteratorMethods() {
		$tokens = $this->prepareTestTokens();
		$iterator = new TokenIterator($tokens);

		// Find expected tokens in both right
		$this->assertSame([$tokens[0], $tokens[1], $tokens[2]], $iterator->nextAll('TOKEN_A', 'B', 'TOKEN_C'));
		$iterator->rewind();

		// Find expected tokens in both left
		$iterator->seek(3);
		$this->assertSame([$tokens[0], $tokens[1], $tokens[2]], $iterator->prevAll('TOKEN_A', 'B', 'TOKEN_C'));

		// Find not expected tokens in both right
		$iterator->rewind();
		$this->assertSame([$tokens[0], $tokens[1], $tokens[2]], $iterator->nextUntil('TOKEN_D', 'E'));

		// Find not expected tokens in both left
		$iterator->seek(4);
		$this->assertSame([$tokens[2], $tokens[3]], $iterator->prevUntil('TOKEN_A', 'B'));
		$iterator->next();
		$this->assertSame([$tokens[2]], $iterator->prevUntil('TOKEN_A', 'B'));

		// Find one expected token in both right
		$iterator->rewind();
		$this->assertSame($tokens[0], $iterator->nextOne('TOKEN_A', 'B', 'TOKEN_C'));

		// Find one expected token in both left
		$iterator->seek(3);
		$this->assertSame($tokens[2], $iterator->prevOne('TOKEN_A', 'B', 'TOKEN_C'));

		// Find one not expected token in both right
		$iterator->rewind();
		$this->assertSame($tokens[2], $iterator->nextOneNot('TOKEN_A', 'B'));

		// Find one not expected token in both left
		$iterator->seek(5);
		$this->assertSame($tokens[4], $iterator->prevOneNot('B', 'TOKEN_C'));
		$iterator->prev();
		$this->assertSame($tokens[0], $iterator->prevOneNot('B', 'TOKEN_C'));

		// Join expected tokens in both right
		$iterator->rewind();
		$this->assertSame('ABC', $iterator->joinNext('TOKEN_A', 'B', 'TOKEN_C'));

		// Join expected tokens in both left
		$iterator->seek(3);
		$this->assertSame('ABC', $iterator->joinPrev('TOKEN_A', 'B', 'TOKEN_C'));

		// Join not expected tokens in both right
		$iterator->rewind();
		$this->assertSame('ABC', $iterator->joinNextUntil('TOKEN_D', 'E'));

		// Join not expected tokens in both left
		$iterator->seek(4);
		$this->assertSame('CD', $iterator->joinPrevUntil('TOKEN_A', 'B'));
		$iterator->next();
		$this->assertSame('C', $iterator->joinPrevUntil('TOKEN_A', 'B'));

		// Join one expected token in both right
		$iterator->rewind();
		$this->assertSame('A', $iterator->nextOneValue('TOKEN_A', 'B', 'TOKEN_C'));
		$this->assertSame('B', $iterator->nextOneValue('TOKEN_A', 'B', 'TOKEN_C'));

		// Join one expected token in both left
		$iterator->seek(4);
		$this->assertSame('B', $iterator->prevOneValue('TOKEN_A', 'B'));
		$this->assertSame('A', $iterator->prevOneValue('TOKEN_A', 'B'));

		// Current value
		$iterator->rewind();
		$this->assertNull($iterator->currentValue());
		$iterator->next();
		$this->assertSame('A', $iterator->currentValue());

		// Current is
		$this->assertTrue($iterator->currentIs('TOKEN_A'));
		$this->assertFalse($iterator->currentIs('B'));

		$iterator->rewind();
		$this->assertFalse($iterator->currentIs('TOKEN_A'));

		// Run to end withount results
		$this->assertNull($iterator->joinNext('ERROR'));
	}
}
