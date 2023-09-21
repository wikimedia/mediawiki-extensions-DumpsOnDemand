<?php

namespace MediaWiki\Extension\DumpsOnDemand\Tests\Integration\Backend;

use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\DumpsOnDemand\Backend\LocalFileBackend;
use MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWiki\MainConfigNames;
use MediaWikiIntegrationTestCase;
use function touch;

/**
 * @covers \MediaWiki\Extension\DumpsOnDemand\Backend\LocalFileBackend
 */
class LocalFileBackendTest extends MediaWikiIntegrationTestCase {

	public function testTimestampsMissing(): void {
		$backend = new LocalFileBackend(
			new OutputSinkFactory(),
			new HashConfig( [
				// wfTempDir uses the global $wgTmpDirectory, which isn't defined in unit tests.
				// Append a directory so that there is an empty directory to check in.
				MainConfigNames::UploadDirectory => $this->getNewTempDirectory(),
				MainConfigNames::UploadPath => ''
			] )
		);

		static::assertFalse( $backend->getCurrentRevisionsFileTimestamp() );
		static::assertFalse( $backend->getAllRevisionsFileTimestamp() );
	}

	public function testTimestampPresent(): void {
		$tempDir = $this->getNewTempDirectory();

		$backend = new LocalFileBackend(
			new OutputSinkFactory(),
			new HashConfig( [
				// wfTempDir uses the global $wgTmpDirectory, which isn't defined in unit tests.
				// Append a directory so that there is an empty directory to check in.
				MainConfigNames::UploadDirectory => $tempDir,
				MainConfigNames::UploadPath => ''
			] )
		);

		touch( $backend->getAllRevisionsFilePath() );
		touch( $backend->getCurrentRevisionsFilePath() );

		static::assertNotFalse( $backend->getAllRevisionsFileTimestamp() );
		static::assertNotFalse( $backend->getCurrentRevisionsFileTimestamp() );
	}
}
