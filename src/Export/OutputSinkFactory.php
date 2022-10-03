<?php

namespace MediaWiki\Extension\DumpsOnDemand\Export;

use DumpFileOutput;
use DumpOutput;
use function extension_loaded;
use function wfLogWarning;

class OutputSinkFactory {
	/**
	 * Class name of a DumpFile descendant.
	 *
	 * @var string
	 */
	private string $sinkClass;

	/**
	 * File extension of the output file.
	 *
	 * @var string
	 */
	private string $extension;

	/**
	 * @param string|null $sinkClass Class name of a DumpFile descendant
	 * @param string $extension File extension of the output file
	 */
	public function __construct( ?string $sinkClass = null, string $extension = '' ) {
		$this->sinkClass = $sinkClass ?? DumpFileOutput::class;
		$this->extension = $extension;
	}

	/**
	 * Create a OutputSink for the given file.
	 *
	 * @param string $file
	 * @return DumpOutput
	 */
	public function makeNewSinkForFile( string $file ): DumpOutput {
		return new $this->sinkClass( $file );
	}

	/**
	 * Return the file extension of the file the sink creates.
	 *
	 * @return string
	 */
	public function getExtension(): string {
		return $this->extension;
	}

	/**
	 * Creates an instance of this class based on the available compression extensions.
	 * It prefers zlib over zip and bz2 and falls back to regular xml files without compression if
	 * none of the extensions are present.
	 *
	 * @return self
	 */
	public static function fromAvailable(): self {
		if ( extension_loaded( 'zlib' ) ) {
			return new self( DumpGzFileOutput::class, 'gz' );
		} elseif ( extension_loaded( 'zip' ) ) {
			return new self( DumpZipFileOutput::class, 'zip' );
		} elseif ( extension_loaded( 'bz2' ) ) {
			return new self( DumpBz2FileOutput::class, 'bz2' );
		} else {
			return new self();
		}
	}

	/**
	 * Creates an instance of this class based on the given compression format extension.
	 * If the required extension is not available, it will fallback to regular xml files without
	 * compression.
	 * When given null (the default value), a compression format will be picked based on the
	 * available formats. @see fromAvailable
	 *
	 * @param string|null $extension File extension of the compression format to use
	 * @return self
	 */
	public static function fromExtension( ?string $extension ): self {
		if ( $extension === 'zip' && extension_loaded( 'zip' ) ) {
			return new self( DumpZipFileOutput::class, 'zip' );
		} elseif ( $extension === 'bz2' && extension_loaded( 'bz2' ) ) {
			return new self( DumpBz2FileOutput::class, 'bz2' );
		} elseif ( $extension === 'gz' && extension_loaded( 'zlib' ) ) {
			return new self( DumpGzFileOutput::class, 'gz' );
		} elseif ( $extension === null ) {
			return self::fromAvailable();
		} else {
			wfLogWarning(
				"Unsupported compression format chosen ($extension). " .
				'The required PHP extension might not be loaded.'
			);

			return new self();
		}
	}
}
