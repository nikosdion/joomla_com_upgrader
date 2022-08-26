<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaPostRefactoringClassRenameRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use RectorPrefix202208\Symplify\EasyTesting\StaticFixtureSplitter;

/**
 * Unit Tests for the JoomlaPostRefactoringClassRenameRector rule.
 *
 * @since  1.0.0
 */
final class JoomlaPostRefactoringClassRenameRectorTest extends AbstractRectorTestCase
{

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
	 * @dataProvider provideData()
	 */
	public function testRefactorNamespace(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
	{
		$inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos($fileInfo);
		$expectedFileInfo                 = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();

		// This runs each test. Don't touch it!
		$this->doTestFileInfo($fileInfo);
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
