<?php

namespace MediaWiki\Extension\DumpsOnDemand\HTMLForm\Fields;

use HTMLButtonField;
use InvalidArgumentException;
use OOUI\ButtonWidget;
use OOUI\Element;
use OOUI\HtmlSnippet;

/**
 * Adds a link button to the form.
 *
 * Additional recognized configuration parameters include:
 * - href: Link target
 * - target: Link behavior
 * - icon: Name of the icon to use. Using this makes using buttonlabel, buttonlabel-raw or
 * buttonlabel-message optional.
 * - rel: Link type
 */
class HTMLHrefButtonField extends HTMLButtonField {
	/**
	 * @var string
	 */
	private $href;

	/**
	 * @var string[]|null
	 */
	private $rel;

	/**
	 * @var string|null
	 */
	private $target;

	/**
	 * @var string|null
	 */
	private $icon;

	/**
	 * Initialise the object.
	 *
	 * @param array $info Associative Array. See the HTMLForm documentation for the syntax
	 * @throws InvalidArgumentException when no label has been set
	 */
	public function __construct( array $info ) {
		$this->href = $info['href'];

		if ( isset( $info['rel'] ) ) {
			$this->rel = (array)$info['rel'];
		}

		if ( isset( $info['target'] ) ) {
			$this->target = $info['target'];
		}

		if ( isset( $info['icon'] ) ) {
			$this->icon = $info['icon'];
		}

		if (
			!isset( $info['buttonlabel'] ) &&
			!isset( $info['buttonlabel-raw'] ) &&
			!isset( $info['buttonlabel-message'] )
		) {
			throw new InvalidArgumentException(
				'Any of buttonlabel, buttonlabel-message or buttonlabel-raw must be set.'
			);
		}

		$info['formnovalidate'] = $info['formnovalidate'] ?? true;

		parent::__construct( $info );
	}

	/**
	 * Get the OOUI widget for this field.
	 *
	 * @param string $value
	 * @return ButtonWidget
	 * @suppress PhanParamSignatureMismatch
	 */
	public function getInputOOUI( $value ): ButtonWidget {
		$config = [
				'name' => $this->mName,
				'value' => $this->getDefault(),
				'label' => new HtmlSnippet( $this->buttonLabel ),
				'type' => $this->buttonType,
				'classes' => [ $this->mClass ],
				'id' => $this->mID,
				'flags' => $this->mFlags,
				'href' => $this->href
			] + Element::configFromHtmlAttributes(
				$this->getAttributes( [ 'disabled', 'tabindex' ] )
			);

		if ( $this->target !== null ) {
			$config['target'] = $this->target;
		}

		if ( $this->icon !== null ) {
			$config['icon'] = $this->icon;
		}

		if ( $this->rel !== null ) {
			$config['rel'] = $this->rel;
		}

		return new ButtonWidget( $config );
	}
}
