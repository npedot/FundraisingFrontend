<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Infrastructure\TemplateTestCampaign;
use WMDE\Fundraising\Frontend\Presentation\DonationConfirmationPageSelector;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationPageSelectorTest extends \PHPUnit_Framework_TestCase {

	public function testWhenConfigIsEmpty_selectPageReturnsDefaultPageTitle() {
		$selector = new DonationConfirmationPageSelector( $this->newCampaignConfig( [] ) );
		$this->assertSame( 'defaultConfirmationPage', $selector->selectPage() );
	}

	public function testWhenConfigContainsOneElement_selectPageReturnsThatElement() {
		$selector = new DonationConfirmationPageSelector( $this->newCampaignConfig( [ 'ThisIsJustATest.twig' ] ) );
		$this->assertSame( 'ThisIsJustATest.twig', $selector->selectPage() );
	}

	public function testWhenConfigContainsSomeElements_selectPageReturnsOne() {
		$selector = new DonationConfirmationPageSelector(
			$this->newCampaignConfig( [ 'ThisIsJustATest.twig', 'AnotherOne.twig' ] )
		);
		$this->assertNotEmpty( $selector->selectPage() );
	}

	private function newCampaignConfig( array $templates ) {
		return [
			'default' => 'defaultConfirmationPage',
			'campaigns' => [
				new TemplateTestCampaign( [
					'code' => 'example',
					'active' => true,
					'startDate' => '1970-01-01 00:00:00',
					'endDate' => '2038-12-31 23:59:59',
					'templates' => $templates
				] )
			]
		];
	}

}
