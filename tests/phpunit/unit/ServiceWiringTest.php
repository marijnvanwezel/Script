<?php

namespace MediaWiki\Extension\Script\Tests\PHPUnit\Unit;

use ExtensionRegistry;
use MediaWikiUnitTestCase;

class ServiceWiringTest extends MediaWikiUnitTestCase {
	/**
	 * @coversNothing
	 */
	public function testServicesSortedAlphabetically() {
		$servicesNames = $this->getServicesNames();
		$sortedServices = $servicesNames;
		natcasesort( $sortedServices );

		$this->assertSame( $sortedServices, $servicesNames, 'Please keep services names sorted alphabetically' );
	}

	/**
	 * @coversNothing
	 */
	public function testServicesArePrefixed() {
		$servicesNames = $this->getServicesNames();

		foreach ( $servicesNames as $serviceName ) {
			$this->assertStringStartsWith( 'Script.', $serviceName, 'Please prefix services names with "Script."' );
		}
	}

	/**
	 * Returns the names of all WikiGuard services.
	 *
	 * @return array
	 */
	private function getServicesNames(): array {
		$allThings = ExtensionRegistry::getInstance()->getAllThings();
		$dirName = dirname( $allThings['Script']['path'] );

		return array_keys( require $dirName . '/Script.wiring.php' );
	}

}