<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaFormRulesRector;

use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use RectorPrefix202208\Symplify\EasyTesting\StaticFixtureSplitter;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Unit Tests for the JoomlaFormRulesRectorTest rule.
 *
 * @since  1.0.0
 */
final class JoomlaFormRulesRectorTest extends AbstractRectorTestCase
{
	private const RENAME_MAP = [
		'admin/models/rules/example.php'       => 'admin/src/Rule/ExampleRule.php',
	];

	public function provideConfigFilePath(): string
	{
		/**
		 * Tells Rector to use a CONFIGURED instance of our rule for testing purposes.
		 *
		 * Don't touch it! If this needs to change udate the config/configured_rule.php, not this method.
		 */
		return __DIR__ . '/config/configured_rule.php';
	}

	/**
	 * @return \Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
	 */
	public function provideData(): \Iterator
	{
		// Tells Rector to create test cases from the Fixture files. Don't touch it!
		return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
	}

	/**
	 * @return \Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
	 */
	public function provideDataMini(): array
	{
		return [
			[new SmartFileInfo(__DIR__ . '/Fixture/admin/models/rules/example.php.inc')],
		];
	}

	/**
	 * @dataProvider provideDataMini()
	 */
	public function testOneFileForDebug(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
	{
		$this->testRefactorNamespace($fileInfo);
	}

	/**
	 * @dataProvider provideData()
	 */
	public function testRefactorNamespace(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
	{
		$inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos($fileInfo);
		$expectedFileInfo                 = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();

		// This runs each test. Don't touch it!
		$this->doTestFileInfo($fileInfo);

		// Returns something like /var/folders/gd/9tlfz2cj0_94qc23mv39rplw0000gn/T/_temp_fixture_easy_testing/site/controller.php
		$relative    = $this->originalTempFileInfo->getRelativeFilePathFromDirectory($this->getFixtureTempDirectory());
		$newRelative = self::RENAME_MAP[$relative] ?? null;

		if (empty($newRelative))
		{
			$this->markTestIncomplete(
				sprintf(
					'You have not set up the expected target path for ‘%s’ in RENAME_MAP',
					$relative
				)
			);
		}

		$newAbsolute = realpath($this->getFixtureTempDirectory()) . '/' . $newRelative;

		$this->assertFilesWereAdded([
			new AddedFileWithContent(
				$newAbsolute,
				$expectedFileInfo->getContents()
			),
		]);
	}

	/**
	 * Set up before running the tests.
	 *
	 * We override the RectorPrefix202208\Symplify\EasyTesting\StaticFixtureSplitter class with our own. Rector's
	 * default test infrastructure creates files from our fixtures which have a random name. However, our rule being
	 * tested (our SUT -- System Under Test) relies on the filename to refactor the classes; this is an unfortunate
	 * requirement due to the not very reasonable way Joomla 3's MVC worked, especially for View classes. Since the
	 * Rector class is final and the methods called for testing are private we cannot extend that class and use our own
	 * custom object. No problem! We fork it and include it before its first use.
	 *
	 * TODO Move this to a PHPUnit bootstrap file.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	protected function setUp(): void
	{
		require_once __DIR__ . '/../../../../../override/Symplify/EasyTesting/StaticFixtureSplitter.php';

		parent::setUp();
	}
}
