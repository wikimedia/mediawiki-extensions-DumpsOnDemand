<?php

namespace MediaWiki\Extension\DumpsOnDemand;

use MediaWiki\Extension\DumpsOnDemand\Jobs\DoDatabaseDumpJob;

class Hooks {
	/**
	 * Registration handler to exclude the dump job from regular execution.
	 */
	public static function onRegistration(): void {
		global $wgJobTypesExcludedFromDefaultQueue, $wgDumpsOnDemandUseDefaultJobQueue;

		if ( !$wgDumpsOnDemandUseDefaultJobQueue ) {
			$wgJobTypesExcludedFromDefaultQueue[] = DoDatabaseDumpJob::JOB_NAME;
		}
	}
}
