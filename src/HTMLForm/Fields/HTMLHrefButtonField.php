<?php

namespace MediaWiki\Extension\DumpsOnDemand\HTMLForm\Fields;

use InvalidArgumentException;
use MediaWiki\HTMLForm\Field\HTMLButtonField;
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
	private string $href;

	private ?string $target;

	private ?string $icon;

	/** @var string[]|null */
	private ?array $rel = null;

	/**
	 * @param array $info Associative Array. See the HTMLForm documentation for the syntax
	 *    Unlike its parent, a label is required.
	 */
	public function __construct( array $info ) {
		$this->href = $info['href'];
		$this->target = $info['target'] ?? null;
		$this->icon = $info['icon'] ?? null;

		if ( isset( $info['rel'] ) ) {
			$this->rel = (array)$info['rel'];
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

		$info['formnovalidate'] ??= true;

		parent::__construct( $info );
	}

	/**
	 * @inheritDoc
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
