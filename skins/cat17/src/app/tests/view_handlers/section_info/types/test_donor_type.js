'use strict';

var test = require( 'tape-catch' ),
	objectAssign = require( 'object-assign' ),
	jQueryElementStub = require( '../../../jQueryElementStub' ),
	jQueryPseudoHtmlGenerator = require( '../../../jQueryPseudoHtmlGenerator' ),
	createContainerElement = require( '../createContainerElement' ),
	DonorType = require( '../../../../lib/view_handler/section_info/types/donor_type' )
;

test.Test.prototype.assertNthAppendedTextEquals = function ( node, nthCall, value, msg ) {
	this.equals( node.html.args[ 0 ][ 0 ].append.args[ nthCall ][ 0 ].text.args[ 0 ][ 0 ].toString(), value, msg );
};

test( 'Donor type info without entered data indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	icon.data.withArgs( 'display-error' ).returns( true );
	text.data.withArgs( 'empty-text' ).returns( 'nothing entered so far' );

	handler.update( 'person', '', '', '', '', '', '', '', '', 'DE', '', { dataEntered: false, isValid: null } );

	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce, 'no data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-error' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'nothing entered so far' ).calledOnce, 'fallback address type text is set' );
	t.ok( longText.html.calledOnce );
	t.equals( longText.html.args[ 0 ][ 0 ].toString(), '<span>', 'long text filled with custom mark-up' );

	delete global.jQuery;

	t.end();
} );

test( 'Donor type info for private person indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'person', 'Herr', 'Dr.', 'test', 'user', '', 'demostr 4', '10112', 'Bärlin', 'DE', 'me@you.com', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce, 'data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-person' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'Privatperson' ).calledOnce, 'address type text is set' );
	t.ok( longText.html.calledOnce );
	t.equals( longText.html.args[ 0 ][ 0 ].toString(), '<span>', 'long text filled with custom mark-up' );
	t.assertNthAppendedTextEquals( longText, 0, 'Herr Dr. test user', 'name set' );
	t.assertNthAppendedTextEquals( longText, 1, 'demostr 4', 'street set' );
	t.assertNthAppendedTextEquals( longText, 2, '10112 Bärlin', 'address set' );
	t.assertNthAppendedTextEquals( longText, 3, 'Deutschland', 'country translated and set' );
	t.assertNthAppendedTextEquals( longText, 4, 'me@you.com', 'email set' );

	delete global.jQuery;

	t.end();
} );

test( 'Donor type info for company indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'firma', 'Frau', 'Prof.', 'state left', 'from private', 'ACME INC', 'acmestr 133b', '12331', 'Wien', 'AT', 'us@acme.com', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce, 'data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-firma' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'Firma' ).calledOnce, 'address type text is set' );
	t.ok( longText.html.calledOnce );
	t.equals( longText.html.args[ 0 ][ 0 ].toString(), '<span>', 'long text filled with custom mark-up' );
	t.assertNthAppendedTextEquals( longText, 0, 'ACME INC', 'name set' );
	t.assertNthAppendedTextEquals( longText, 1, 'acmestr 133b', 'street set' );
	t.assertNthAppendedTextEquals( longText, 2, '12331 Wien', 'address set' );
	t.assertNthAppendedTextEquals( longText, 3, 'Österreich', 'country translated and set' );
	t.assertNthAppendedTextEquals( longText, 4, 'us@acme.com', 'email set' );

	delete global.jQuery;

	t.end();
} );

test( 'Donor type info for anonymous indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	handler.update( 'anonym', 'some', 'state', 'irrelevant', 'for', 'an', 'anonymous', 'record', 'left', 'DE', 'nospam@me.info', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce, 'data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-anonym' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'anonym' ).calledOnce, 'address type text is set' );
	t.ok( longText.html.calledOnce );
	t.ok( longText.html.withArgs( '' ).calledOnce, 'long text reset' );
	t.ok( longText.append.notCalled );

	t.end();
} );

test( 'Icon is correctly determined from value', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	t.equals( handler.getValueIcon( 1, { dataEntered: true, isValid: true } ), 'icon-1' );

	t.end();
} );

test( 'Icon is error if can not be determined from value and error display set', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	icon.data.withArgs( 'display-error' ).returns( true );

	t.equals( handler.getValueIcon( 'outOfBounds', { dataEntered: true, isValid: true } ), 'icon-error' );

	t.end();
} );

test( 'Icon is null if can not be determined from value and no error display', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	icon.data.withArgs( 'display-error' ).returns( false );

	t.equals( handler.getValueIcon( 'outOfBounds', { dataEntered: true, isValid: false } ), null );

	t.end();
} );

/**
 * This unintuitive state (dataEntered false despite value given) seems to be a possible outcome of
 * lib/state_aggregation/membership/donor_type_and_address_are_valid.js
 */
test( 'Icon is null if not dataEntered', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	icon.data.withArgs( 'display-error' ).returns( false );

	t.equals( handler.getValueIcon( 0, { dataEntered: false, isValid: null } ), null );

	t.end();
} );
