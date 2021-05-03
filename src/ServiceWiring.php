<?php

namespace MediaWiki\Extensions\DumpsOnDemand;

use MediaWiki\Extensions\DumpsOnDemand\Backend\FileBackend;
use MediaWiki\Extensions\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWiki\MediaWikiServices;

return [
	'DumpsOnDemandFileBackend' => static function ( MediaWikiServices $services ) : FileBackend {
		$config = $services->getConfigFactory()->makeConfig( 'DumpsOnDemand' );

		return $services->getObjectFactory()->createObject(
			$config->get( 'DumpsOnDemandDumpFileBackend' ),
			[
				'assertClass' => FileBackend::class,
				'extraArgs' => [
					OutputSinkFactory::fromExtension( $config->get( 'DumpsOnDemandCompression' ) )
				]
			]
		);
	}
];
