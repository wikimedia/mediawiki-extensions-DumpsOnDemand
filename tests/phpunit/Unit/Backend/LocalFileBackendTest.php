<?php

namespace MediaWiki\Extension\DumpsOnDemand\Tests\Unit\Backend;

use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\DumpsOnDemand\Backend\LocalFileBackend;
use MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWiki\MainConfigNames;
use MediaWikiUnitTestCase;
use function explode;

/**
 * @covers \MediaWiki\Extension\DumpsOnDemand\Backend\LocalFileBackend
 */
class LocalFileBackendTest extends MediaWikiUnitTestCase {
	private const WIKI_ID = 'unittestdb-unittestschema-unittestprefix';

	public function setUp(): void {
		global $wgDBname, $wgDBmwschema, $wgDBprefix;

		[ $wgDBname, $wgDBmwschema, $wgDBprefix ] = explode( '-', self::WIKI_ID );
	}

	private function getBackend( string $extension = '' ): LocalFileBackend {
		return new LocalFileBackend(
			new OutputSinkFactory( null, $extension ),
			new HashConfig( [
				MainConfigNames::UploadDirectory => 'UploadDirectory',
				MainConfigNames::UploadPath => 'UploadPath'
			] )
		);
	}

	public function testGetAllRevisionsFileUrl(): void {
		static::assertSame(
			'UploadPath/' . self::WIKI_ID . '_all_revisions.xml',
			$this->getBackend()->getAllRevisionsFileUrl()
		);
	}

	public function testGetCurrentRevisionsFileUrl(): void {
		static::assertSame(
			'UploadPath/' . self::WIKI_ID . '_current_revisions.xml',
			$this->getBackend()->getCurrentRevisionsFileUrl()
		);
	}

	public function testGetAllRevisionsFilePath(): void {
		static::assertSame(
			'UploadDirectory/' . self::WIKI_ID . '_all_revisions.xml',
			$this->getBackend()->getAllRevisionsFilePath()
		);
	}

	public function testGetCurrentRevisionsFilePath(): void {
		static::assertSame(
			'UploadDirectory/' . self::WIKI_ID . '_current_revisions.xml',
			$this->getBackend()->getCurrentRevisionsFilePath()
		);
	}

	public function testFileNameWithExtension(): void {
		static::assertStringEndsWith(
			'.test',
			$this->getBackend( 'test' )->getAllRevisionsFilePath()
		);
		static::assertStringEndsWith(
			'.test',
			$this->getBackend( 'test' )->getCurrentRevisionsFilePath()
		);
		static::assertStringEndsWith(
			'.test',
			$this->getBackend( 'test' )->getAllRevisionsFileUrl()
		);
		static::assertStringEndsWith(
			'.test',
			$this->getBackend( 'test' )->getCurrentRevisionsFileUrl()
		);
	}
}
