<?php

namespace MediaWiki\Extension\DumpsOnDemand\Backend;

use Config;
use MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWiki\WikiMap\WikiMap;
use function file_exists;
use function filemtime;

class LocalFileBackend extends FileBackend {

	/**
	 * The value of $wgUploadDirectory.
	 *
	 * @var string
	 */
	private string $uploadDirectory;

	/**
	 * The value of $wgUploadPath.
	 *
	 * @var string
	 */
	private string $uploadPath;

	/**
	 * @param OutputSinkFactory $outputSinkFactory
	 * @param Config $config The config from the MainConfig service
	 */
	public function __construct( OutputSinkFactory $outputSinkFactory, Config $config ) {
		parent::__construct( $outputSinkFactory );

		$this->uploadDirectory = $config->get( 'UploadDirectory' );
		$this->uploadPath = $config->get( 'UploadPath' );
	}

	/**
	 * Determine the file timestamp of the given file.
	 *
	 * @param string $file
	 * @return false|int Unix timestamp or false if the file does not exist
	 */
	private function getFileTimestamp( string $file ) {
		return file_exists( $file ) ? filemtime( $file ) : false;
	}

	/**
	 * Create a file name for given kind of dump file.
	 *
	 * @param string $kind
	 * @return string
	 */
	private function createFileName( string $kind ): string {
		$file = WikiMap::getCurrentWikiId() . "_$kind.xml";

		if ( $this->outputSinkFactory->getExtension() !== '' ) {
			$file .= '.' . $this->outputSinkFactory->getExtension();
		}

		return $file;
	}

	/**
	 * @inheritDoc
	 * @return int|false
	 */
	public function getAllRevisionsFileTimestamp() {
		return $this->getFileTimestamp( $this->getAllRevisionsFilePath() );
	}

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function getAllRevisionsFileUrl(): string {
		return $this->uploadPath . '/' . $this->createFileName( 'all_revisions' );
	}

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function getAllRevisionsFilePath(): string {
		return $this->uploadDirectory . '/' . $this->createFileName( 'all_revisions' );
	}

	/**
	 * @inheritDoc
	 * @return int|false
	 */
	public function getCurrentRevisionsFileTimestamp() {
		return $this->getFileTimestamp( $this->getCurrentRevisionsFilePath() );
	}

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function getCurrentRevisionsFileUrl(): string {
		return $this->uploadPath . '/' . $this->createFileName( 'current_revisions' );
	}

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function getCurrentRevisionsFilePath(): string {
		return $this->uploadDirectory . '/' . $this->createFileName( 'current_revisions' );
	}
}
