<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace Phpbb\Epv\Tests\Tests;

use Composer\Composer;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\InvalidPackageException;
use Composer\Package\Loader\ValidatingArrayLoader;
use Composer\Package\Version\VersionParser;
use Phpbb\Epv\Files\FileInterface;
use Phpbb\Epv\Files\Type\ComposerFileInterface;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\OutputInterface;
use Phpbb\Epv\Tests\BaseTest;
use Phpbb\Epv\Tests\Exception\TestException;
use Phpbb\Epv\Tests\Type;

class epv_test_validate_composer extends BaseTest
{
	public function __construct($debug, OutputInterface $output, $basedir, $namespace, $titania, $opendir)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania, $opendir);

		$this->fileTypeFull = Type::TYPE_COMPOSER;
	}

	/**
	 * @param FileInterface $file
	 *
	 * @throws \Phpbb\Epv\Tests\Exception\TestException
	 */
	public function validateFile(FileInterface $file)
	{
		if (!$file instanceof ComposerFileInterface)
		{
			throw new TestException('This test expects a php type, but found something else.');
		}
		$json = $file->getJson();
		if (!$json || !is_array($json))
		{
			throw new TestException('Parsing composer file failed');
		}
		$this->file = $file;

		$this->validateName($json);
		$this->validateLicense($json);
		$this->validateVersion($json);
	}

	/**
	 * Validate if the provided license is the GPL.
	 *
	 * @param array $json
	 */
	private function validateLicense(array $json)
	{
		if (!isset($json['license']))
		{
			$this->addMessageIfBooleanTrue(true, Output::FATAL, 'The license key is missing');
			return;
		}

		if ($json['license'] === 'GPL-2.0')
		{
			$this->addMessageIfBooleanTrue(true, Output::WARNING, '"GPL-2.0" is a deprecated SPDX license identifier, use "GPL-2.0-only" instead.');
		}
		else if ($json['license'] !== 'GPL-2.0-only')
		{
			$this->addMessageIfBooleanTrue(true, Output::ERROR, 'It is required to use "GPL-2.0-only" as the license identifier. Other licenses are not allowed as per the extension database policies.');
		}
	}

	private function validateName(array $json)
	{
		if (!isset($json['name']))
		{
			$this->addMessageIfBooleanTrue(true, Output::FATAL, 'The name key is missing');
			return;
		}
		if (strpos($json['name'], '_') !== false)
		{
			$this->addMessageIfBooleanTrue(true, Output::FATAL, 'The namespace should not contain underscores');
		}
	}

	/**
	 * @param array $json
	 */
	private function validateVersion(array $json)
	{
		if (isset($json['extra']['soft-require']['phpbb/phpbb']))
		{
			// https://github.com/phpbb/customisation-db/blob/3.1.x/contribution/extension/type.php#L296
			$constraint = $json['extra']['soft-require']['phpbb/phpbb'];
			$regex = '/(<|<=|~|\^|>|>=)([0-9]+(\.[0-9]+)?)\.[*x]/';

			if (preg_match($regex, $constraint))
			{
				$replace = preg_replace($regex, '$1$2', $constraint);
				$this->addMessageIfBooleanTrue(true, Output::ERROR, sprintf(
					'An invalid version constraint is used in soft-require: phpbb/phpbb. You can\'t combine a <|<=|~|\^|>|>= with a *|x. Please replace %s with %s',
					$constraint,
					$replace
				));
			}
		}

		$parser = new ValidatingArrayLoader(new ArrayLoader(), true, null, ValidatingArrayLoader::CHECK_ALL);
		try
		{
			$parser->load($json);
		}
		catch (InvalidPackageException $exception)
		{
			$this->handleMessages($exception->getErrors(), Output::FATAL);
			$this->handleMessages($exception->getWarnings(), Output::WARNING);
		}
	}

	/**
	 * Add a array of errors as error into the report
	 *
	 * @param array $errorList
	 * @param int   $type
	 */
	private function handleMessages(array $errorList, $type = Output::ERROR)
	{
		foreach ($errorList as $error)
		{
			$this->output->addMessage($type, 'Composer validation: ' . $error);
		}
	}

	private function addMessageIfBooleanTrue($addMessage, $type, $message)
	{
		if ($addMessage)
		{
			$this->output->addMessage($type, $message, $this->file);
		}
	}

	public function testName()
	{
		return "Validate composer structure";
	}
}
