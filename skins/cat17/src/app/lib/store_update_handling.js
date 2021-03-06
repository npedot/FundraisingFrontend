'use strict';

/**
 * Connect store updates to validators, components and view handlers
 *
 * @module store_update_handling
 */

var createValidationDispatcherCollection = require( './validation_dispatcher_collection' ).createValidationDispatcherCollection,
	_ = require( 'underscore' );

_.mixin( require( 'underscore.path' ) );

function createStateAccessors( stateKey ) {
	var accessors = stateKey instanceof Array ? stateKey : [ stateKey ];
	return _.map( accessors, function ( accessor ) {
		return typeof accessor === 'string' ? function ( state ) {
			return _.path( state, accessor );
		} : accessor;
	} );
}

function getStateValues( state, accessors ) {
	return _.map( accessors, function ( accessor ) {
		return accessor( state );
	} );
}

module.exports = {
	/**
	 *
	 * validatorFactoryFunction must return an array of validation dispatchers
	 *
	 * @param {Function} validatorFactoryFunction
	 * @param {store.Store} store
	 * @param {Object} initialValues - initial values for the validation dispatchers so they don't fire on initialization
	 * @param {string} formContentName Field name for the store to access form contents, e.g. 'donationFormContent' or 'membershipFormContent'
	 */
	connectValidatorsToStore: function ( validatorFactoryFunction, store, initialValues, formContentName ) {
		var pickNonfalsy = _.partial( _.pick, _, _.identity ),
			completeInitialValues = _.extend( {}, store.getState()[ formContentName ], pickNonfalsy( initialValues ) ),
			validators = validatorFactoryFunction( completeInitialValues );
		createValidationDispatcherCollection( store, validators, formContentName );
	},
	connectComponentsToStore: function ( components, store, formContentName ) {
		store.subscribe( function () {
			var state = store.getState(),
				formContent = state[ formContentName ];

			// TODO check if formContent has changed before executing update actions
			components.forEach( function ( component ) {
				component.render( formContent );
			} );
		} );
	},
	connectViewHandlersToStore: function ( viewHandlers, store ) {
		var viewHandlerAccessors = _.map( viewHandlers, function ( viewHandler ) {
			return createStateAccessors( viewHandler.stateKey );
		} );
		store.subscribe( function () {
			var state = store.getState();
			// TODO check if state has changed before executing update actions

			viewHandlers.forEach( function ( viewHandlerConfig, viewHandlerIndex ) {
				viewHandlerConfig.viewHandler.update.apply(
					viewHandlerConfig.viewHandler,
					getStateValues( state, viewHandlerAccessors[ viewHandlerIndex ] )
				);
			} );
		} );
	},
	makeEventHandlerWaitForAsyncFinish: function ( handler, store ) {
		var unsubscribe = null;
		return function () {
			if ( !store.getState().asynchronousRequests.isValidating ) {
				handler();
				return;
			}

			if ( unsubscribe !== null ) {
				return;
			}
			unsubscribe = store.subscribe( function () {
				var state = store.getState();
				if ( !state.asynchronousRequests.isValidating ) {
					unsubscribe();
					unsubscribe = null;
					handler();
				}
			} );
		};
	}
};
