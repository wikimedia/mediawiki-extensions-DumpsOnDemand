<?php

namespace MediaWiki\Extensions\DumpsOnDemand\Tests\Unit\Export;

use DumpFileOutput;
use MediaWiki\Extensions\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWikiUnitTestCase;
use function get_class;

class OutputSinkFactoryTest extends MediaWikiUnitTestCase {
	/**
	 * @covers \MediaWiki\Extensions\DumpsOnDemand\Export\OutputSinkFactory::__construct
	 * @covers \MediaWiki\Extensions\DumpsOnDemand\Export\OutputSinkFactory::getExtension
	 */
	public function testGetExtension(): void {
		$factory = new OutputSinkFactory( null, 'zip' );

		static::assertSame( 'zip', $factory->getExtension() );
	}

	/**
	 * @covers \MediaWiki\Extensions\DumpsOnDemand\Export\OutputSinkFactory::__construct
	 * @covers \MediaWiki\Extensions\DumpsOnDemand\Export\OutputSinkFactory::makeNewSinkForFile
	 */
	public function testMakeNewSinkForFile(): void {
		// Create a mock sink that doesn't call the parent constructor to prevent calls to the
		// file system.
		$mockSink = new class( 'DUMMY VALUE PLEASE IGNORE' ) extends DumpFileOutput {
			public function __construct( string $file ) {
				$this->filename = $file;
			}
		};

		$factory = new OutputSinkFactory( get_class( $mockSink ) );

		$sink = $factory->makeNewSinkForFile( 'testfile' );

		static::assertInstanceOf( get_class( $mockSink ), $sink );
		static::assertSame( 'testfile', $sink->getFilenames() );
	}
}
