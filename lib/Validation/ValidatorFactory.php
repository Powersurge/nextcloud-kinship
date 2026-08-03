<?php

declare(strict_types=1);

namespace OCA\Kinship\Validation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

/**
 * Standalone Illuminate validation factory for use outside Laravel.
 */
class ValidatorFactory {
	private Factory $factory;

	public function __construct() {
		$langPath = dirname(new \ReflectionClass(Translator::class)->getFileName()) . '/lang';
		$loader = new FileLoader(new Filesystem(), $langPath);
		$translator = new Translator($loader, 'en');
		$this->factory = new Factory($translator);
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<string, mixed> $rules
	 * @param array<string, string> $messages
	 * @param array<string, string> $attributes
	 */
	public function make(
		array $data,
		array $rules,
		array $messages = [],
		array $attributes = [],
	): Validator {
		return $this->factory->make($data, $rules, $messages, $attributes);
	}
}
