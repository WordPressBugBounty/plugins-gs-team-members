/**
 * Access to the data localized by Integration_Gutenberg_Builder.
 *
 * Everything the editor needs (defaults, select options, translations,
 * taxonomy settings, visibility maps) comes from the shortcode builder's own
 * PHP providers, so the block never keeps a second copy of the schema.
 */

export const blockData = window.gs_team_builder_block || {};

export const BLOCK_NAME = 'gsteam/team-members';

export function translate( key, fallback ) {

	const translations = blockData.translations || {};

	if ( translations[ key ] ) return translations[ key ];

	return typeof fallback === 'string' ? fallback : key;
}

export function label( key ) {

	const labels = blockData.labels || {};

	return labels[ key ] || '';
}

/**
 * Select options for a setting, converted to the shape SelectControl expects.
 * Options flagged as `pro` by the builder keep their premium marker.
 */
export function fieldOptions( settingKey ) {

	const options = blockData.options || {};
	const list = Array.isArray( options[ settingKey ] ) ? options[ settingKey ] : [];

	return list.map( function( option ) {
		return {
			label: option.pro ? option.label + ' \u2014 ' + label( 'premium_notice' ) : option.label,
			value: String( option.value ),
			// Visual only — still clickable so the premium alert can fire.
			premiumLocked: !! option.pro && ! isProActive()
		};
	} );
}

/**
 * Whether a select value is locked behind Pro.
 * Uses the same `pro` flag the shortcode builder stamps on options.
 */
export function isPremiumOption( settingKey, value ) {

	const list = rawOptions( settingKey );

	return list.some( function( item ) {
		return String( item.value ) === String( value ) && !! item.pro;
	} );
}

export function premiumAlertMessage() {
	return label( 'premium_alert' ) || 'This is a premium feature. Please upgrade the plan.';
}

/**
 * Raw option list, keeping the original value types (term IDs stay numeric).
 */
export function rawOptions( settingKey ) {

	const options = blockData.options || {};

	return Array.isArray( options[ settingKey ] ) ? options[ settingKey ] : [];
}

export function settingDefaults() {
	return blockData.settings || {};
}

export function settingDefault( settingKey ) {

	const defaults = settingDefaults();

	return typeof defaults[ settingKey ] !== 'undefined' ? defaults[ settingKey ] : '';
}

export function taxonomySettings() {
	return blockData.taxonomy_settings || {};
}

export function isProActive() {
	return !! blockData.is_pro_active;
}

export function instancePrefix() {
	return blockData.instance_prefix || 'gstb_';
}

export function hasAcf() {
	const plugins = blockData.enabled_plugins || [];
	return plugins.indexOf( 'advanced-custom-fields' ) !== -1;
}

export function fontsData() {
	return blockData.fonts_data || [];
}

/**
 * The builder stores booleans as the strings 'on' / 'off'.
 */
export function isOn( value ) {
	return value === 'on' || value === true || value === 1 || value === '1';
}

export function toOnOff( checked ) {
	return checked ? 'on' : 'off';
}

export function isOneOf( value, allowed ) {
	return allowed.indexOf( value ) !== -1;
}
