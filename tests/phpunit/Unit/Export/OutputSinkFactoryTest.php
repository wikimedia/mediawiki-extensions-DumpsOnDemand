<?php

namespace MediaWiki\Extension\DumpsOnDemand\Tests\Unit\Export;

use DumpFileOutput;
use MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWikiUnitTestCase;
use function get_class;

class OutputSinkFactoryTest extends MediaWikiUnitTestCase {
	/**
	 * @covers \MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory::__construct
	 * @covers \MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory::getExtension
	 */
	public function testGetExtension(): void {
		$factory = new OutputSinkFactory( null, 'zip' );

		static::assertSame( 'zip', $factory->getExtension() );
	}

	/**
	 * @covers \MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory::__construct
	 * @covers \MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory::makeNewSinkForFile
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
