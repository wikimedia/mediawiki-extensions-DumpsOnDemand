<?php

namespace MediaWiki\Extension\DumpsOnDemand\Tests\Unit\Backend;

use DumpFileOutput;
use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\DumpsOnDemand\Backend\LocalFileBackend;
use MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWiki\MainConfigNames;
use MediaWikiUnitTestCase;
use function get_class;

/**
 * @covers \MediaWiki\Extension\DumpsOnDemand\Backend\FileBackend
 */
class FileBackendTest extends MediaWikiUnitTestCase {
	public function testGetOutputSink(): void {
		// Create an anonymous class that doesn't call the constructor to prevent file system
		// access.
		$dumpFileOutput = new class( 'DUMMY VALUE PLEASE IGNORE' ) extends DumpFileOutput {
			public function __construct( string $file ) {
				$this->filename = $file;
			}
		};

		$backend = new LocalFileBackend(
			new OutputSinkFactory( get_class( $dumpFileOutput ) ),
			new HashConfig( [
				MainConfigNames::UploadDirectory => 'UploadDirectory',
				MainConfigNames::UploadPath => 'UploadPath'
			] )
		);

		$sink = $backend->getOutputSink( 'test' );

		static::assertInstanceOf( get_class( $dumpFileOutput ), $sink );
		static::assertSame( 'test', $sink->getFilenames() );
	}

	/**
	 * @dataProvider provideExtensions
	 *
	 * @param string $extension
	 * @param string $expected
	 */
	public function testGetFileTypeDescriptionMessage( string $extension, string $expected ): void {
		$backend = new LocalFileBackend(
			new OutputSinkFactory( null, $extension ),
			new HashConfig( [
				MainConfigNames::UploadDirectory => 'UploadDirectory',
				MainConfigNames::UploadPath => 'UploadPath'
			] )
		);

		static::assertSame( $expected, $backend->getFileTypeDescriptionMessage() );
	}

	/**
	 * Data provider for testGetFileTypeDescriptionMessage.
	 *
	 * @return array
	 */
	public static function provideExtensions(): array {
		return [
			'None' => [ '', '' ],
			'gz' => [ 'gz', 'dumpsondemand-filetype-gz' ],
			'test' => [ 'test', 'dumpsondemand-filetype-test' ]
		];
	}
}
