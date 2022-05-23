<?php

/*
 * FFI MediaWiki extension
 * Copyright (C) 2021  Marijn van Wezel
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace MediaWiki\Extension\FFI\Views;

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