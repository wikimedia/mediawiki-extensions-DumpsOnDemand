<?php

namespace MediaWiki\Extension\DumpsOnDemand\Export;

use DumpFileOutput;
use function bzclose;
use function bzopen;
use function bzwrite;

class DumpBz2FileOutput extends DumpFileOutput {
	/**
	 * @param string $file
	 */
	public function __construct( string $file ) {
		$this->filename = $file;
		$this->handle = bzopen( $file, 'wt' );
	}

	/**
	 * @inheritDoc
	 * @param string $string
	 */
	public function writeCloseStream( $string ): void {
		$this->write( $string );

		if ( $this->handle ) {
			bzclose( $this->handle );
			$this->handle = false;
		}
	}

	/**
	 * @inheritDoc
	 * @param string $string
	 */
	public function write( $string ): void {
		bzwrite( $this->handle, $string );
	}

	/**
	 * @inheritDoc
	 * @param string $newname
	 * @param bool $open
	 */
	public function closeAndRename( $newname, $open = false ): void {
		$newname = $this->checkRenameArgCount( $newname );

		if ( $this->handle ) {
			bzclose( $this->handle );
			$this->handle = false;
		}
		$this->renameOrException( $newname );
		if ( $open ) {
			$this->handle = bzopen( $this->filename, 'wt' );
		}
	}
}
