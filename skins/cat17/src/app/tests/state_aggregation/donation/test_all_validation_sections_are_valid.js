'use strict';

var test = require( 'tape-catch' ),
	allSectionsAreValid = require( '../../../lib/state_aggregation/donation/all_validation_sections_are_valid.js' )
;

test( 'Valid all over means valid', function ( t ) {
	t.ok( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: true,
				dataEntered: true
			}
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: true
		}
	} ) );
	t.end();
} );

test( 'Bank data validity ignored when not paying via debit', function ( t ) {
	t.ok( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'PPL'
		},
		donationInputValidation: {
			paymentType: {
				isValid: true,
				dataEntered: true
			}
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: null
		}
	} ) );
	t.end();
} );

test( 'No validity means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: ''
		},
		donationInputValidation: {
			paymentType: {
				isValid: false,
				dataEntered: false
			}
		},
		validity: {
			paymentData: false,
			address: false,
			bankData: false
		}
	} ) );
	t.end();
} );

test( 'Faulty payment data means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: true,
				dataEntered: true
			}
		},
		validity: {
			paymentData: false,
			address: true,
			bankData: true
		}
	} ) );
	t.end();
} );

test( 'Faulty payment type data means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: ''
		},
		donationInputValidation: {
			paymentType: {
				isValid: false,
				dataEntered: true
			}
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: true
		}
	} ) );
	t.end();
} );

test( 'Faulty address data means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: true,
				dataEntered: true
			}
		},
		validity: {
			paymentData: true,
			address: false,
			bankData: true
		}
	} ) );
	t.end();
} );
