<?php

namespace MediaWiki\Extension\DumpsOnDemand\Specials;

use JobQueueGroup;
use JobSpecification;
use ManualLogEntry;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Extension\DumpsOnDemand\Backend\FileBackend;
use MediaWiki\Extension\DumpsOnDemand\HTMLForm\Fields\HTMLHrefButtonField;
use MediaWiki\Extension\DumpsOnDemand\Jobs\DoDatabaseDumpJob;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\SpecialPage\FormSpecialPage;
use function time;

class SpecialRequestDump extends FormSpecialPage {
	private JobQueueGroup $jobQueueGroup;

	private FileBackend $fileBackend;

	/**
	 * The value of $wgDumpsOnDemandRequestLimit, which is the minimum time a regular user needs
	 * to wait before a new dump can be requested.
	 *
	 * @var int
	 */
	private int $requestLimit;

	/**
	 * @param FileBackend $fileBackend
	 * @param ConfigFactory $configFactory
	 * @param JobQueueGroup $jobQueueGroup
	 */
	public function __construct(
		FileBackend $fileBackend,
		ConfigFactory $configFactory,
		JobQueueGroup $jobQueueGroup
	) {
		parent::__construct( 'RequestDump' );

		$this->fileBackend = $fileBackend;
		$this->requestLimit = $configFactory->makeConfig( 'DumpsOnDemand' )
			->get( 'DumpsOnDemandRequestLimit' );
		$this->jobQueueGroup = $jobQueueGroup;
	}

	/**
	 * @inheritDoc
	 * @param HTMLForm $form
	 */
	protected function alterForm( HTMLForm $form ): void {
		$out = $this->getOutput();
		$out->addHelpLink( 'Help:Extension:DumpsOnDemand' );
		$out->addModuleStyles( 'oojs-ui.styles.icons-content' );

		$form->suppressDefaultSubmit();

		$fileTypeMessage = $this->fileBackend->getFileTypeDescriptionMessage();

		if ( $fileTypeMessage !== '' ) {
			$form->addHeaderHtml( $this->msg( $fileTypeMessage )->parseAsBlock() );
		}
	}

	/**
	 * @inheritDoc
	 * @return array
	 */
	protected function getFormFields(): array {
		$fields = [
			'current-revisions' => [
				'class' => HTMLHrefButtonField::class,
				'label-message' => 'dumpsondemand-current-revisions',
				'help-message' => 'dumpsondemand-current-revisions-help',
				'href' => $this->fileBackend->getCurrentRevisionsFileUrl(),
				'icon' => 'download'
			] + $this->getFormattedDate(
				$this->fileBackend->getCurrentRevisionsFileTimestamp()
			),
			'all-revisions' => [
				'class' => HTMLHrefButtonField::class,
				'label-message' => 'dumpsondemand-all-revisions',
				'help-message' => 'dumpsondemand-all-revisions-help',
				'href' => $this->fileBackend->getAllRevisionsFileUrl(),
				'icon' => 'download'
			] + $this->getFormattedDate(
				$this->fileBackend->getAllRevisionsFileTimestamp()
			),
			'request-dump' => [
				'type' => 'submit',
				'buttonlabel-message' => 'dumpsondemand-request-dump-button-label'
			]
		];

		if ( !$this->getAuthority()->isAllowed( 'dumpsondemand' ) ) {
			$fields['request-dump']['disabled'] = true;
		} elseif ( !$this->userCanRequestDump() ) {
			$fields['request-dump']['buttonlabel-message'] = 'dumpsondemand-dump-already-requested';
			$fields['request-dump']['disabled'] = true;
		}

		return $fields;
	}

	/**
	 * Get button config for the given timestamp.
	 * This will set the button label, and disable the button when no dump is available.
	 *
	 * @param int|false $unixTimestamp Unix timestamp of the dump, or false when no dump exists
	 * @return array
	 */
	private function getFormattedDate( $unixTimestamp ): array {
		if ( $unixTimestamp === false ) {
			return [
				'buttonlabel-message' => 'dumpsondemand-dump-unavailable',
				'disabled' => true
			];
		}

		$this->getLanguage()->userTimeAndDate(
			$unixTimestamp,
			$this->getUser()
		);

		return [
			'buttonlabel' => $this->getLanguage()->userTimeAndDate(
				$unixTimestamp,
				$this->getUser()
			)
		];
	}

	/**
	 * @inheritDoc
	 * @param array $data
	 * @return bool
	 */
	public function onSubmit( array $data ): bool {
		// The submit button is hidden, but any ordinary post request to the special page will
		// still allow anyone request a new dump. Check that the user can request the dump in the
		// submit handler to prevent unauthorized dump requests.
		if ( !$this->userCanRequestDump() ) {
			// Returning false will just show the user the form again without an error message.
			// An error message is not necessary, as regular users won't encounter this scenario.
			return false;
		}

		$logEntry = new ManualLogEntry( 'dumprequest', 'dumprequest' );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( $this->getPageTitle() );
		$logid = $logEntry->insert();
		$logEntry->publish( $logid );

		$this->jobQueueGroup->push( [
			'currentRevisions' => new JobSpecification(
				DoDatabaseDumpJob::JOB_NAME,
				[ 'fullHistory' => false ],
				[ 'removeDuplicates' => true ]
			),
			'allRevisions' => new JobSpecification(
				DoDatabaseDumpJob::JOB_NAME,
				[ 'fullHistory' => true ],
				[ 'removeDuplicates' => true ]
			)
		] );

		return true;
	}

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function getGroupName(): string {
		return 'wiki';
	}

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function getDisplayFormat(): string {
		return 'ooui';
	}

	/**
	 * @inheritDoc
	 */
	public function onSuccess(): void {
		$out = $this->getOutput();
		$out->addWikiMsg( 'dumpsondemand-dump-requested' );

		$out->returnToMain( null, $this->getPageTitle() );
	}

	/**
	 * Determines if the given user can request a new dump.
	 *
	 * @return bool
	 */
	private function userCanRequestDump(): bool {
		if ( !$this->getAuthority()->isAllowed( 'dumpsondemand' ) ) {
			return false;
		} elseif ( $this->getAuthority()->isAllowed( 'dumpsondemand-limit-exempt' ) ) {
			return true;
		}

		$hasJobQueued = !$this->jobQueueGroup->get( DoDatabaseDumpJob::JOB_NAME )->isEmpty();

		if ( $hasJobQueued ) {
			return false;
		}

		// Check the creation date of the last dump for this wiki (if any).
		// Both files will be checked as it could be that one of them failed.
		$allRevisions = $this->fileBackend->getAllRevisionsFileTimestamp() ?: -1;
		$currentRevisions = $this->fileBackend->getCurrentRevisionsFileTimestamp() ?: -1;
		// Either, or both of the dumps are not yet created.
		if ( $allRevisions < 0 || $currentRevisions < 0 ) {
			return true;
		}

		$timeSinceLastDump = time() - $allRevisions;

		// Has it been $wgDumpsOnDemandRequestLimit seconds since the last run?
		return $timeSinceLastDump > $this->requestLimit;
	}
}
