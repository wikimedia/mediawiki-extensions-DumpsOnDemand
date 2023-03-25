<?php

namespace MediaWiki\Extension\DumpsOnDemand\Tests\Unit;

use MediaWiki\Extension\DumpsOnDemand\Hooks;
use MediaWiki\Extension\DumpsOnDemand\Jobs\DoDatabaseDumpJob;
use MediaWikiUnitTestCase;

/**
 * @covers \MediaWiki\Extension\DumpsOnDemand\Hooks::onRegistration
 */
class HooksTest extends MediaWikiUnitTestCase {

	/**
	 * @dataProvider provideForOnRegistration
	 *
	 * @param array $expected
	 * @param bool $useDefaultQueue
	 */
	public function testOnRegistration( array $expected, bool $useDefaultQueue ): void {
		global $wgJobTypesExcludedFromDefaultQueue, $wgDumpsOnDemandUseDefaultJobQueue;

		$wgJobTypesExcludedFromDefaultQueue = [];
		$wgDumpsOnDemandUseDefaultJobQueue = $useDefaultQueue;

		Hooks::onRegistration();

		static::assertSame(
			$expected,
			$wgJobTypesExcludedFromDefaultQueue
		);
	}

	/**
	 * Data provider for testOnRegistration.
	 *
	 * @return array
	 */
	public static function provideForOnRegistration(): array {
		return [
			[ [], true ],
			[ [ DoDatabaseDumpJob::JOB_NAME ], false ]
		];
	}
}
