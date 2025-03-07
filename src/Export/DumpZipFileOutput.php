<?php

namespace MediaWiki\Extension\DumpsOnDemand\Export;

use DumpFileOutput;
use RuntimeException;
use ZipArchive;
use function pathinfo;
use function unlink;

class DumpZipFileOutput extends DumpFileOutput {
	private ZipArchive $archive;

	public function __construct( string $file ) {
		$this->archive = new ZipArchive();
		$res = $this->archive->open( $file, ZipArchive::OVERWRITE );

		if ( $res !== true ) {
			throw new RuntimeException( 'Failed to open zip file', $res );
		}

		[ 'dirname' => $dirname, 'filename' => $filename ] = pathinfo( $file );
		parent::__construct( "$dirname/$filename" );
	}

	/**
	 * @inheritDoc
	 * @param string $string
	 */
	public function writeCloseStream( $string ): void {
		parent::writeCloseStream( $string );
		$this->archive->addFile( $this->filename );
		$this->archive->close();
		unlink( $this->filename );
	}

	/**
	 * @inheritDoc
	 * @param string|string[] $newname
	 * @param bool $open
	 */
	public function closeAndRename( $newname, $open = false ): void {
		parent::closeAndRename( $newname, $open );

		$newname = $this->checkRenameArgCount( $newname );

		$this->renameOrException( $newname );

		$this->archive->addFile( $newname );
		unlink( $newname );

		if ( !$open ) {
			$this->archive->close();
		}
	}
}
