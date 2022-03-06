<?php

namespace MediaWiki\Extension\DumpsOnDemand\Export;

use DumpFileOutput;
use function gzclose;
use function gzopen;
use function gzwrite;

class DumpGzFileOutput extends DumpFileOutput {
	/**
	 * @param string $file
	 */
	public function __construct( string $file ) {
		$this->filename = $file;
		$this->handle = gzopen( $file, 'wt' );
	}

	/**
	 * @inheritDoc
	 * @param string $string
	 */
	public function writeCloseStream( $string ): void {
		$this->write( $string );

		if ( $this->handle ) {
			gzclose( $this->handle );
			$this->handle = false;
		}
	}

	/**
	 * @inheritDoc
	 * @param string $string
	 */
	public function write( $string ): void {
		gzwrite( $this->handle, $string );
	}

	/**
	 * @inheritDoc
	 * @param string $newname
	 * @param bool $open
	 */
	public function closeAndRename( $newname, $open = false ): void {
		$newname = $this->checkRenameArgCount( $newname );

		if ( $this->handle ) {
			gzclose( $this->handle );
			$this->handle = false;
		}
		$this->renameOrException( $newname );
		if ( $open ) {
			$this->handle = gzopen( $this->filename, 'wt' );
		}
	}
}
