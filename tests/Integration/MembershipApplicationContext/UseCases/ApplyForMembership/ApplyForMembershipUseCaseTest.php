<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\MembershipApplicationContext\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\ApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Authorization\MembershipApplicationTokens;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\ApplicationPiwikTracker;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\ApplicationTracker;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplicationValidationResult;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplyForMembershipUseCase;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\MembershipApplicationValidator;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedApplicationTokenFetcher;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryApplicationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplyForMembershipUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCaseTest extends \PHPUnit_Framework_TestCase {

	const ID_OF_NON_EXISTING_APPLICATION = 1337;
	const FIRST_APPLICATION_ID = 1;
	const ACCESS_TOKEN = 'Gimmeh all the access';
	const UPDATE_TOKEN = 'Lemme change all the stuff';

	/**
	 * @var ApplicationRepository
	 */
	private $repository;

	/**
	 * @var TemplateBasedMailerSpy
	 */
	private $mailer;

	/**
	 * @var TokenGenerator
	 */
	private $tokenGenerator;

	/**
	 * @var MembershipApplicationValidator
	 */
	private $validator;

	/**
	 * @var ApplicationTracker
	 */
	private $tracker;

	/**
	 * @var ApplicationPiwikTracker
	 */
	private $piwikTracker;

	public function setUp() {
		$this->repository = new InMemoryApplicationRepository();
		$this->mailer = new TemplateBasedMailerSpy( $this );
		$this->tokenGenerator = new FixedTokenGenerator( self::ACCESS_TOKEN );
		$this->validator = $this->newSucceedingValidator();
		$this->tracker = $this->createMock( ApplicationTracker::class );
		$this->piwikTracker = $this->createMock( ApplicationPiwikTracker::class );
	}

	private function newSucceedingValidator(): MembershipApplicationValidator {
		$validator = $this->getMockBuilder( MembershipApplicationValidator::class )
			->disableOriginalConstructor()->getMock();

		$validator->expects( $this->any() )
			->method( 'validate' )
			->willReturn( new ApplicationValidationResult() );

		return $validator;
	}

	public function testGivenValidRequest_applicationSucceeds() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertTrue( $response->isSuccessful() );
	}

	private function newUseCase(): ApplyForMembershipUseCase {
		return new ApplyForMembershipUseCase(
			$this->repository,
			$this->newTokenFetcher(),
			$this->mailer,
			$this->validator,
			$this->tracker,
			$this->piwikTracker
		);
	}

	private function newTokenFetcher(): ApplicationTokenFetcher {
		return new FixedApplicationTokenFetcher( new MembershipApplicationTokens(
			self::ACCESS_TOKEN,
			self::UPDATE_TOKEN
		) );
	}

	private function newValidRequest(): ApplyForMembershipRequest {
		$request = new ApplyForMembershipRequest();

		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );
		$request->setApplicantCompanyName( '' );
		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );
		$request->setApplicantSalutation( ValidMembershipApplication::APPLICANT_SALUTATION );
		$request->setApplicantTitle( ValidMembershipApplication::APPLICANT_TITLE );
		$request->setApplicantFirstName( ValidMembershipApplication::APPLICANT_FIRST_NAME );
		$request->setApplicantLastName( ValidMembershipApplication::APPLICANT_LAST_NAME );
		$request->setApplicantStreetAddress( ValidMembershipApplication::APPLICANT_STREET_ADDRESS );
		$request->setApplicantPostalCode( ValidMembershipApplication::APPLICANT_POSTAL_CODE );
		$request->setApplicantCity( ValidMembershipApplication::APPLICANT_CITY );
		$request->setApplicantCountryCode( ValidMembershipApplication::APPLICANT_COUNTRY_CODE );
		$request->setApplicantEmailAddress( ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS );
		$request->setApplicantPhoneNumber( ValidMembershipApplication::APPLICANT_PHONE_NUMBER );
		$request->setApplicantDateOfBirth( ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH );
		$request->setPaymentIntervalInMonths( ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS );
		$request->setPaymentAmountInEuros( (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO );

		$request->setPaymentBankData( $this->newValidBankData() );

		$request->setTrackingInfo( $this->newTrackingInfo() );
		$request->setPiwikTrackingString( 'foo/bar' );

		return $request->assertNoNullFields();
	}

	private function newValidBankData(): BankData {
		$bankData = new BankData();

		$bankData->setIban( new Iban( ValidMembershipApplication::PAYMENT_IBAN ) );
		$bankData->setBic( ValidMembershipApplication::PAYMENT_BIC );
		$bankData->setAccount( ValidMembershipApplication::PAYMENT_BANK_ACCOUNT );
		$bankData->setBankCode( ValidMembershipApplication::PAYMENT_BANK_CODE );
		$bankData->setBankName( ValidMembershipApplication::PAYMENT_BANK_NAME );

		return $bankData->assertNoNullFields()->freeze();
	}

	private function newTrackingInfo() {
		return new MembershipApplicationTrackingInfo(
			ValidMembershipApplication::TEMPLATE_CAMPAIGN,
			ValidMembershipApplication::TEMPLATE_NAME
		);
	}

	public function testGivenValidRequest_applicationGetsPersisted() {
		$this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$expectedApplication = ValidMembershipApplication::newDomainEntity();
		$expectedApplication->assignId( self::FIRST_APPLICATION_ID );

		$application = $this->repository->getApplicationById( $expectedApplication->getId() );
		$this->assertNotNull( $application );

		$this->assertEquals( $expectedApplication, $application );
	}

	public function testGivenValidRequest_confirmationEmailIsSend() {
		$this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->mailer->assertCalledOnceWith(
			new EmailAddress( ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS ),
			[
				'membershipType' => 'active',
				'membershipFee' => '10.00',
				'paymentIntervalInMonths' => 3,
				'salutation' => 'Herr',
				'title' => '',
				'lastName' => 'The Great'
			]
		);
	}

	public function testGivenValidRequest_tokenIsGeneratedAndReturned() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertSame( self::ACCESS_TOKEN, $response->getAccessToken() );
		$this->assertSame( self::UPDATE_TOKEN, $response->getUpdateToken() );
	}

	public function testWhenValidationFails_failureResultIsReturned() {
		$this->validator = $this->newFailingValidator();

		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	private function newFailingValidator(): MembershipApplicationValidator {
		$validator = $this->getMockBuilder( MembershipApplicationValidator::class )
			->disableOriginalConstructor()->getMock();

		$validator->expects( $this->any() )
			->method( 'validate' )
			->willReturn( $this->newInvalidValidationResult() );

		return $validator;
	}

	private function newInvalidValidationResult(): ApplicationValidationResult {
		$invalidResult = $this->createMock( ApplicationValidationResult::class );

		$invalidResult->expects( $this->any() )
			->method( 'isSuccessful' )
			->willReturn( false );

		return $invalidResult;
	}

	public function testGivenValidRequest_moderationIsNotNeeded() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertFalse( $response->getMembershipApplication()->needsModeration() );
	}

	public function testGivenRequestWithHighYearlyAmount_moderationIsNeeded() {
		$request = $this->newValidRequest();
		$request->setPaymentAmountInEuros( '1000.01' );
		$request->setPaymentIntervalInMonths( 12 );

		$this->assertRequestResultsInModeration( $request );
	}

	public function testGivenRequestWithHighQuarterlyAmount_moderationIsNeeded() {
		$request = $this->newValidRequest();
		$request->setPaymentAmountInEuros( '250.01' );
		$request->setPaymentIntervalInMonths( 3 );

		$this->assertRequestResultsInModeration( $request );
	}

	private function assertRequestResultsInModeration( ApplyForMembershipRequest $request ) {
		$response = $this->newUseCase()->applyForMembership( $request );
		$this->assertTrue( $response->getMembershipApplication()->needsModeration() );
	}

}