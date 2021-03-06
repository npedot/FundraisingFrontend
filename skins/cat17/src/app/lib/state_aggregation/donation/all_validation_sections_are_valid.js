'use strict';

module.exports = function ( state ) {
	return (
		state.validity.paymentData === true &&
		state.donationInputValidation.paymentType.isValid === true &&
		state.validity.address === true &&
		(
			( state.donationFormContent.paymentType === 'BEZ' && state.validity.bankData === true ) ||
			( state.donationFormContent.paymentType !== 'BEZ' && state.validity.bankData !== false )
		)
	);
};
