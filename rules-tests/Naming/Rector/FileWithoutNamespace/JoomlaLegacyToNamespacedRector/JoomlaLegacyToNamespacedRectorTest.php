<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaLegacyToNamespacedRector;

use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use RectorPrefix202208\Symplify\EasyTesting\StaticFixtureSplitter;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Unit Tests for the JoomlaLegacyToNamespacedRector rule.
 *
 * @since  1.0.0
 */
final class JoomlaLegacyToNamespacedRectorTest extends AbstractRectorTestCase
{
	private const RENAME_MAP = [
		'admin/controller.php'              => 'admin/src/Controller/DisplayController.php',
		'admin/controllers/example.php'     => 'admin/src/Controller/ExampleController.php',
		'admin/controllers/foobar.php'      => 'admin/src/Controller/FoobarController.php',
		'admin/models/example.php'          => 'admin/src/Model/ExampleModel.php',
		'admin/models/foobar.php'           => 'admin/src/Model/FoobarModel.php',
		'admin/tables/example.php'          => 'admin/src/Table/ExampleTable.php',
		'admin/tables/foobar.php'           => 'admin/src/Table/FoobarTable.php',
		'admin/views/example/view.html.php' => 'admin/src/View/Example/HtmlView.php',
		'admin/views/example/view.json.php' => 'admin/src/View/Example/JsonView.php',
		'admin/views/foobar/view.html.php'  => 'admin/src/View/Foobar/HtmlView.php',

		'site/controller.php'              => 'site/src/Controller/DisplayController.php',
		'site/controllers/example.php'     => 'site/src/Controller/ExampleController.php',
		'site/controllers/foobar.php'      => 'site/src/Controller/FoobarController.php',
		'site/models/example.php'          => 'site/src/Model/ExampleModel.php',
		'site/models/foobar.php'           => 'site/src/Model/FoobarModel.php',
		'site/views/example/view.html.php' => 'site/src/View/Example/HtmlView.php',
		'site/views/example/view.json.php' => 'site/src/View/Example/JsonView.php',
		'site/views/foobar/view.html.php'  => 'site/src/View/Foobar/HtmlView.php',
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
			[new SmartFileInfo(__DIR__ . '/Fixture/admin/tables/example.php.inc')]
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
