/**
 * Per device visibility helpers.
 *
 * Mirrors the builder's visibility tab and the PHP side
 * (includes/visibility.php), including the legacy key sync.
 */

import { blockData, isOn, toOnOff, translate } from './data';

export const VISIBILITY_DEVICES = [
	{ key: 'desktop', translationKey: 'visibility-desktop' },
	{ key: 'tablet', translationKey: 'visibility-tablet' },
	{ key: 'mobile_landscape', translationKey: 'visibility-large-mobile' },
	{ key: 'mobile', translationKey: 'visibility-mobile' }
];

function overlayFieldKeys() {

	const fields = blockData.overlay_visibility_fields;

	return Array.isArray( fields ) ? fields : [];
}

export function getInitialVisibilityFieldKeys( theme ) {

	const map = blockData.theme_visibility_fields || {};

	return Array.isArray( map[ theme ] ) ? map[ theme ] : [];
}

export function themeHasVisibilityField( theme, fieldKey ) {
	return getInitialVisibilityFieldKeys( theme ).indexOf( fieldKey ) !== -1;
}

export function getPopupVisibilityFieldKeys( popupStyle ) {

	const map = blockData.popup_style_visibility_fields || {};
	const style = popupStyle || 'default';

	return Array.isArray( map[ style ] ) ? map[ style ] : overlayFieldKeys();
}

export function getPanelVisibilityFieldKeys( panelStyle ) {

	const map = blockData.panel_style_visibility_fields || {};
	const style = panelStyle || 'default';

	return Array.isArray( map[ style ] ) ? map[ style ] : overlayFieldKeys();
}

export function getDrawerVisibilityFieldKeys( drawerStyle ) {

	const map = blockData.drawer_style_visibility_fields || {};
	const style = drawerStyle || 'default';

	return Array.isArray( map[ style ] ) ? map[ style ] : overlayFieldKeys();
}

export function visibilityFieldLabel( fieldKey ) {

	const translationKeys = blockData.visibility_translation_keys || {};

	return translate( translationKeys[ fieldKey ] || fieldKey, fieldKey );
}

export function isVisibilityFieldOn( field ) {

	if ( ! field ) return false;

	return !! ( field.desktop || field.tablet || field.mobile_landscape || field.mobile );
}

function translationKeyFor( fieldKey ) {

	const translationKeys = blockData.visibility_translation_keys || {};

	return translationKeys[ fieldKey ] || fieldKey;
}

function getVisibilityDeviceDefaults( visible ) {

	return {
		desktop: !! visible,
		tablet: !! visible,
		mobile_landscape: !! visible,
		mobile: !! visible
	};
}

function defaultVisibilityField( fieldKey, attributes ) {

	const legacyMap = blockData.visibility_legacy_key_map || {};
	const legacyKey = legacyMap[ fieldKey ];

	const visible = legacyKey && typeof attributes[ legacyKey ] !== 'undefined'
		? isOn( attributes[ legacyKey ] )
		: true;

	return Object.assign(
		getVisibilityDeviceDefaults( visible ),
		{ translation_key: translationKeyFor( fieldKey ) }
	);
}

function getGroup( attributes, group ) {

	const settings = attributes.visibility_settings || {};

	return settings[ group ] && typeof settings[ group ] === 'object' ? settings[ group ] : {};
}

export function getVisibilityField( attributes, group, fieldKey ) {

	const fields = getGroup( attributes, group );

	return fields[ fieldKey ] || defaultVisibilityField( fieldKey, attributes );
}

function legacySettingsFor( initialGroup ) {

	const legacyMap = blockData.visibility_legacy_key_map || {};
	const legacySettings = {};

	Object.keys( legacyMap ).forEach( function( fieldKey ) {

		if ( ! initialGroup[ fieldKey ] ) return;

		legacySettings[ legacyMap[ fieldKey ] ] = toOnOff( isVisibilityFieldOn( initialGroup[ fieldKey ] ) );
	} );

	return legacySettings;
}

export function updateVisibilityField( attributes, group, fieldKey, device, checked ) {

	const currentSettings = attributes.visibility_settings || {};
	const currentField = getVisibilityField( attributes, group, fieldKey );

	const updatedField = Object.assign( {}, currentField, { [ device ]: !! checked } );

	const updatedGroup = Object.assign( {}, getGroup( attributes, group ), { [ fieldKey ]: updatedField } );

	const changes = {
		visibility_settings: Object.assign( {}, currentSettings, { [ group ]: updatedGroup } )
	};

	if ( 'initial' === group ) {
		Object.assign( changes, legacySettingsFor( updatedGroup ) );
	}

	return changes;
}

/**
 * Toggle every device on a row. The next state follows the desktop checkbox,
 * matching the YouTube builder.
 */
export function toggleVisibilityRow( attributes, group, fieldKey ) {

	const currentField = getVisibilityField( attributes, group, fieldKey );
	const next = ! currentField.desktop;
	const updatedField = Object.assign( {}, currentField );

	VISIBILITY_DEVICES.forEach( function( device ) {
		updatedField[ device.key ] = next;
	} );

	const currentSettings = attributes.visibility_settings || {};
	const updatedGroup = Object.assign( {}, getGroup( attributes, group ), { [ fieldKey ]: updatedField } );

	const changes = {
		visibility_settings: Object.assign( {}, currentSettings, { [ group ]: updatedGroup } )
	};

	if ( 'initial' === group ) {
		Object.assign( changes, legacySettingsFor( updatedGroup ) );
	}

	return changes;
}

export function ensureInitialVisibilityFields( attributes, theme ) {

	const currentSettings = attributes.visibility_settings || {};
	const existingGroup = getGroup( attributes, 'initial' );

	const existingKeys = Object.keys( existingGroup );
	const allExistingHidden = existingKeys.length > 0
		&& existingKeys.every( ( fieldKey ) => ! isVisibilityFieldOn( existingGroup[ fieldKey ] ) );

	const updatedGroup = Object.assign( {}, existingGroup );

	getInitialVisibilityFieldKeys( theme ).forEach( function( fieldKey ) {

		if ( updatedGroup[ fieldKey ] ) return;

		const newField = defaultVisibilityField( fieldKey, attributes );

		updatedGroup[ fieldKey ] = allExistingHidden
			? Object.assign( newField, getVisibilityDeviceDefaults( false ) )
			: newField;
	} );

	const changes = {
		visibility_settings: Object.assign( {}, currentSettings, { initial: updatedGroup } )
	};

	Object.assign( changes, legacySettingsFor( updatedGroup ) );

	return changes;
}
