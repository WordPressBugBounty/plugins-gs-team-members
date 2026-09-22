/**
 * Theme grouping helpers.
 *
 * Direct port of the shortcode builder's theme predicates
 * (dev/shortcode/includes/helpers.js + shortcode.vue) so a field shows up in
 * the block for exactly the same themes as it does in the builder.
 */

import { isOn, isOneOf } from './data';

export function themesV2() {
	return [
		'gs-grid-style-one', 'gs-grid-style-two', 'gs-grid-style-three', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-grid-style-six',
		'gs-team-circle-one', 'gs-team-circle-two', 'gs-team-circle-three', 'gs-team-circle-four', 'gs-team-circle-five',
		'gs-team-horizontal-one', 'gs-team-horizontal-two', 'gs-team-horizontal-three', 'gs-team-horizontal-four', 'gs-team-horizontal-five',
		'gs-team-flip-one', 'gs-team-flip-two', 'gs-team-flip-three', 'gs-team-flip-four', 'gs-team-flip-five',
		'gs-team-table-one', 'gs-team-table-two', 'gs-team-table-three', 'gs-team-table-four', 'gs-team-table-five',
		'gs-team-list-style-one', 'gs-team-list-style-two', 'gs-team-list-style-three', 'gs-team-list-style-four', 'gs-team-list-style-five'
	];
}

export function themesV2Carousel() {
	return themesV2().filter( ( theme ) => theme.indexOf( 'table' ) === -1 && theme.indexOf( 'list' ) === -1 );
}

export function themesV2Filter() {
	return themesV2Carousel();
}

export function oldCarouselThemes() {
	return [ 'gs_tm_theme7' ];
}

export function oldFilterThemes() {
	return [ 'gs_tm_theme9', 'gs_tm_theme12' ];
}

export function noBgColorThemes() {
	return [
		'gs-grid-style-one', 'gs-grid-style-four', 'gs-grid-style-five',
		'gs-team-circle-one', 'gs-team-circle-two', 'gs-team-circle-three', 'gs-team-circle-four', 'gs-team-circle-five',
		'gs-team-horizontal-one', 'gs-team-horizontal-three',
		'gs-team-flip-one', 'gs-team-flip-two', 'gs-team-flip-three', 'gs-team-flip-four', 'gs-team-flip-five',
		'gs-team-table-one', 'gs-team-table-two', 'gs-team-table-three', 'gs-team-table-four', 'gs-team-table-five',
		'gs-team-list-style-four', 'gs-team-list-style-five',
		'gs_tm_theme1', 'gs_tm_theme2', 'gs_tm_theme3', 'gs_tm_theme4', 'gs_tm_theme5', 'gs_tm_theme6', 'gs_tm_theme7',
		'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme10', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme14',
		'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme21_dense', 'gs_tm_theme19', 'gs_tm_theme20',
		'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme24', 'gs_tm_theme25', 'gs_tm_grid2', 'gs_tm_drawer2'
	];
}

export function infoBgColorThemes() {
	return [
		'gs-grid-style-one', 'gs-grid-style-four', 'gs-grid-style-five',
		'gs-team-circle-three', 'gs-team-circle-four',
		'gs-team-horizontal-one', 'gs-team-horizontal-three',
		'gs-team-flip-one', 'gs-team-flip-two', 'gs-team-flip-three', 'gs-team-flip-four', 'gs-team-flip-five',
		'gs_tm_drawer2', 'gs_tm_theme22', 'gs_tm_theme1', 'gs_tm_theme2', 'gs_tm_theme7', 'gs_tm_theme8',
		'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme25'
	];
}

export function isCarouselActive( attributes ) {
	const theme = attributes.gs_team_theme;
	return ( themesV2Carousel().indexOf( theme ) !== -1 && isOn( attributes.carousel_enabled ) )
		|| oldCarouselThemes().indexOf( theme ) !== -1;
}

export function isFilterActive( attributes ) {
	const theme = attributes.gs_team_theme;
	return ( themesV2Filter().indexOf( theme ) !== -1 && isOn( attributes.filter_enabled ) && ! isOn( attributes.carousel_enabled ) )
		|| oldFilterThemes().indexOf( theme ) !== -1;
}

export function canShowFilterToggle( attributes ) {
	return ! isOn( attributes.carousel_enabled )
		&& attributes.gs_member_link_type !== 'drawer'
		&& themesV2Filter().indexOf( attributes.gs_team_theme ) !== -1;
}

/**
 * Pagination settings — mirrors is_display_pagination_settings() in shortcode.vue.
 */
export function isDisplayPaginationSettings( attributes ) {

	if ( isOn( attributes.filter_enabled ) && attributes.gs_team_filter_type === 'normal-filter' ) {
		return false;
	}

	if ( isOn( attributes.carousel_enabled ) ) {
		return false;
	}

	const unsupported = [
		'gs_tm_theme7', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16',
		'gs_tm_theme19', 'gs_tm_theme21', 'gs_tm_theme21_dense', 'gs_tm_theme22', 'gs_tm_theme23',
		'gs_tm_theme24', 'gs_tm_theme25', 'gs_tm_drawer2'
	];

	return unsupported.indexOf( attributes.gs_team_theme ) === -1;
}

export function canShowColumns( theme ) {
	return ! isOneOf( theme, [ 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme17', 'gs_tm_theme18', 'gs_tm_theme21', 'gs_tm_theme21_dense' ] );
}

export function canShowLinking( theme ) {
	return ! isOneOf( theme, [ 'gs_tm_theme13', 'gs_tm_drawer2', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme25' ] );
}
