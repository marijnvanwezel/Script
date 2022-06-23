<?php

namespace MediaWiki\Extension\Script\Views;

use ParserOutput;

/**
 * Interface for creating different kinds of views.
 */
interface View {
	/**
	 * Returns the HTML used for the title of the page.
	 *
	 * @see ParserOutput::setDisplayTitle()
	 * @return string The HTML for the title
	 */
	public function getTitleHTML(): string;

	/**
	 * Returns the HTML used for the content of the page.
	 *
	 * @see ParserOutput::setText()
	 * @param string $rawText The raw text content of the page
	 * @return string The HTML for the content
	 */
	public function getContentHTML( string $rawText ): string;

	/**
	 * Returns a list of page indicators. The key of the returned array
	 * will be the ID of the returned indicator, and the value the content
	 * of the indicator.
	 *
	 * @return array
	 */
	public function getIndicators(): ?array;

	/**
	 * Returns the array of module names to be loaded.
	 *
	 * @return string[]
	 */
	public function getModules(): array;
}