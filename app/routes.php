<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \Silex\Application $app
 * @var \WMDE\Fundraising\Frontend\FunFunFactory $ffFactory
 */

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayRequest;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanRequest;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;

$app->get(
	'validate-email',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newValidateEmailUseCase();
		$responseModel = $useCase->validateEmail( $request->get( 'email', '' ) );

		// Presenter code:
		return $app->json( [ 'status' => $responseModel ? 'OK' : 'ERR' ] );
	}
);

$app->get(
	'list.rss',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newListCommentsUseCase();
		$responseModel = $useCase->listComments( new CommentListingRequest( 10 /* TODO: get real limit */ ) );

		// Presenter code:
		return 'TODO';
	}
);

$app->get(
	'page/{pageName}',
	function( Application $app, $pageName ) use ( $ffFactory ) {
		return $ffFactory->newDisplayPagePresenter()->present(
			$ffFactory->newDisplayPageUseCase()->getPage( new PageDisplayRequest( $pageName ) )
		);
	}
);

$app->post(
	'contact/subscribe',
	function( Request $request ) use ( $ffFactory ) {
		$useCase = $ffFactory->newAddSubscriptionUseCase();

		$subscriptionRequest = new SubscriptionRequest();
		$subscriptionRequest->setAddress( $request->get( 'address' ) );
		$subscriptionRequest->setCity( $request->get( 'city' ) );
		$subscriptionRequest->setPostcode( $request->get( 'postcode' ) );

		$subscriptionRequest->setFirstName( $request->get( 'firstName' ) );
		$subscriptionRequest->setLastName( $request->get( 'lastName' ) );
		$subscriptionRequest->setSalutation( $request->get( 'salutation' ) );
		$subscriptionRequest->setTitle( $request->get( 'title' ) );

		$subscriptionRequest->setEmail( $request->get( 'email' ) );
		$subscriptionRequest->setWikilogin( $request->get( 'wikilogin' ) );

		$subscriptionRequest->setWikiloginFromValues( [
			$request->request->get( 'wikilogin' ),
			$request->cookies->get( 'spenden_wikilogin' ),
		] );

		$responseModel = $useCase->addSubscription( $subscriptionRequest );
		// TODO forward/dispatch to matching 'page/name' route, depending on $responseModel->getType();
		return 'TODO';
	}
);

$app->get(
	'check-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$useCase = $ffFactory->newCheckIbanUseCase();
		$checkIbanResponse = $useCase->checkIban( new Iban( $request->get( 'iban', '' ) ) );
		return $app->json( $ffFactory->newIbanPresenter()->present( $checkIbanResponse ) );
	}
);

$app->get(
	'generate-iban',
	function( Request $request ) use ( $app, $ffFactory ) {
		$generateIbanRequest = new GenerateIbanRequest(
			$request->get( 'accountNumber', '' ),
			$request->get( 'bankCode', '' )
		);

		$generateIbanResponse = $ffFactory->newGenerateIbanUseCase()->generateIban( $generateIbanRequest );
		return $app->json( $ffFactory->newIbanPresenter()->present( $generateIbanResponse ) );
	}
);

return $app;