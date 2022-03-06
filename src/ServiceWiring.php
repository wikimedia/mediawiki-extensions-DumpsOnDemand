<?php

namespace MediaWiki\Extension\DumpsOnDemand;

use MediaWiki\Extension\DumpsOnDemand\Backend\FileBackend;
use MediaWiki\Extension\DumpsOnDemand\Export\OutputSinkFactory;
use MediaWiki\MediaWikiServices;

return [
	'DumpsOnDemandFileBackend' => static function ( MediaWikiServices $services ): FileBackend {
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
