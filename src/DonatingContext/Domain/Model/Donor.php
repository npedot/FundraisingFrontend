<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Domain\Model;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\DonorAddress;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class Donor {

	private $personName;
	private $physicalAddress;
	private $emailAddress;

	public function __construct( DonorName $name, DonorAddress $address, string $emailAddress ) {
		$this->personName = $name;
		$this->physicalAddress = $address;
		$this->emailAddress = $emailAddress;
	}

	public function getName(): DonorName {
		return $this->personName;
	}

	public function getPhysicalAddress(): DonorAddress {
		return $this->physicalAddress;
	}

	public function getEmailAddress(): string {
		return $this->emailAddress;
	}

}
