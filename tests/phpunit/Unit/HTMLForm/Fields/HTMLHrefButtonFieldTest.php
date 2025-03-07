<?php

namespace MediaWiki\Extension\DumpsOnDemand\Tests\Unit\HTMLForm\Fields;

use InvalidArgumentException;
use MediaWiki\Extension\DumpsOnDemand\HTMLForm\Fields\HTMLHrefButtonField;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWikiUnitTestCase;

/**
 * Unit tests for the HTMLHrefButtonField.
 *
 * @covers \MediaWiki\Extension\DumpsOnDemand\HTMLForm\Fields\HTMLHrefButtonField
 */
class HTMLHrefButtonFieldTest extends MediaWikiUnitTestCase {

	private const HREF = 'http://dev.wiki.local.wmftest.net:8080';

	/**
	 * Tests that the href attribute is passed through correctly.
	 */
	public function testHrefAttribute(): void {
		$field = new HTMLHrefButtonField( [
			'fieldname' => 'test',
			'buttonlabel' => 'test',
			'href' => self::HREF,
			'parent' => $this->createMock( HTMLForm::class )
		] );

		$widget = $field->getInputOOUI( '' );

		static::assertEquals( self::HREF, $widget->getHref() );
	}

	/**
	 * Tests that the target attribute is passed through correctly.
	 */
	public function testTargetAttribute(): void {
		$target = '_blank';

		$field = new HTMLHrefButtonField( [
			'fieldname' => 'test',
			'buttonlabel' => 'test',
			'href' => self::HREF,
			'target' => $target,
			'parent' => $this->createMock( HTMLForm::class )
		] );

		$widget = $field->getInputOOUI( '' );

		static::assertEquals( $target, $widget->getTarget() );
	}

	/**
	 * Test that the icon attribute results in the button sporting the correct icon.
	 */
	public function testIcon(): void {
		$icon = 'notice';

		$field = new HTMLHrefButtonField( [
			'fieldname' => 'test',
			'buttonlabel' => 'test',
			'href' => self::HREF,
			'icon' => $icon,
			'parent' => $this->createMock( HTMLForm::class )
		] );

		$widget = $field->getInputOOUI( '' );

		static::assertEquals( $icon, $widget->getIcon() );
	}

	/**
	 * Test that creating a button without a label is not possible.
	 */
	public function testButtonWithoutLabelOrIcon(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			'Any of buttonlabel, buttonlabel-message or buttonlabel-raw must be set.'
		);

		new HTMLHrefButtonField( [
			'fieldname' => 'test',
			'href' => self::HREF,
			'parent' => $this->createMock( HTMLForm::class )
		] );
	}

	/**
	 * Test that providing a value for the rel attribute sets it on the button.
	 */
	public function testSetNoopener(): void {
		$field = new HTMLHrefButtonField( [
			'fieldname' => 'test',
			'buttonlabel' => 'test',
			'href' => self::HREF,
			'rel' => [ 'noopener' ],
			'parent' => $this->createMock( HTMLForm::class )
		] );

		$widget = $field->getInputOOUI( '' );

		static::assertEquals(
			[ 'noopener' ],
			$widget->getRel()
		);
	}

	/**
	 * Test that providing a value for the rel attribute as an array sets it on the button.
	 */
	public function testSetNoopenerNoFollow(): void {
		$field = new HTMLHrefButtonField( [
			'fieldname' => 'test',
			'buttonlabel' => 'test',
			'href' => self::HREF,
			'rel' => [ 'noopener', 'nofollow' ],
			'parent' => $this->createMock( HTMLForm::class )
		] );

		$widget = $field->getInputOOUI( '' );

		static::assertEquals(
			[ 'noopener', 'nofollow' ],
			$widget->getRel()
		);
	}
}
