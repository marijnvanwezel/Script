<?php

namespace MediaWiki\Extension\FFI;

use Title;

/**
 * Collection of utility functions that do not belong in a particular class.
 *
 * @note This class should only contain static methods.
 */
final class Utils {
	/**
	 * Disable the construction of this class by making the constructor private.
	 */
	private function __construct() {
	}

	/**
	 * Returns true if and only if the given Title represents a documentation page for a script.
	 *
	 * @param Title $title
	 * @param Title|null $for Will be set to the Title for which the given title is a doc page
	 * @return bool
	 * @throws Exceptions\InvalidEngineSpecificationException
	 */
	public static function isDocPage( Title $title, ?Title &$for = null ): bool {
		$docSuffix = wfMessage( 'ffi-doc-page-suffix' );

		if ( $docSuffix->isDisabled() ) {
			return false;
		}

		if ( $docSuffix->parse() !== $title->getSubpageText() ) {
			return false;
		}

		if ( FFIServices::getEngineStore()->getByName( $title->getBaseText() ) === null ) {
			return false;
		}

		$for = $title->getBaseTitle();
		return true;
	}

	/**
	 * Returns the title to the corresponding doc page.
	 *
	 * @note This function does not perform any validity checks. It is up to the caller to determine if the returned
	 * 	Title actually represents a valid doc page.
	 *
	 * @see Utils::isDocPage()
	 * @param Title $title The title to get the doc page for
	 * @return Title
	 */
	public static function getDocPage( Title $title ): Title {
		return $title->getSubpage( wfMessage( 'ffi-doc-page-suffix' )->parse() );
	}
}