/**
 * General tab.
 *
 * Field list, order and conditional visibility are ported from the
 * "general_settings" tab of dev/shortcode/pages/shortcode.vue.
 */

import {
	NumberField,
	SelectField,
	SliderField,
	TextField,
	ToggleField
} from '../controls';

import {
	hasAcf,
	isOn,
	isOneOf,
	label as uiLabel,
	translate
} from '../data';

import {
	canShowFilterToggle,
	canShowLinking,
	isCarouselActive,
	isDisplayPaginationSettings,
	isFilterActive,
	themesV2Carousel
} from '../theme-groups';

import { ensureInitialVisibilityFields } from '../visibility';

const React = window.React;

const { PanelBody } = wp.components;

export default function GeneralPanels( { attributes, setAttributes } ) {

	const field = { attributes, setAttributes };

	const theme = attributes.gs_team_theme;
	const carouselOn = isCarouselActive( attributes );
	const filterOn = isFilterActive( attributes );
	const showFilterToggle = canShowFilterToggle( attributes );
	const showPagination = isDisplayPaginationSettings( attributes );
	const paginationOn = showPagination && isOn( attributes.gs_member_pagination );
	const linkingOn = canShowLinking( theme ) && isOn( attributes.gs_member_name_is_linked );
	const linkType = attributes.gs_member_link_type;
	const popupEnabled = linkingOn && (
		'popup' === linkType
		|| ( 'single_page' !== linkType && isOneOf( theme, [ 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme12' ] ) )
	);
	const showPopupColumns = popupEnabled && isOneOf( attributes.popup_style || 'default', [ 'default', 'style-six' ] );

	const changeTheme = ( nextTheme ) => setAttributes( Object.assign(
		{ gs_team_theme: nextTheme },
		ensureInitialVisibilityFields( attributes, nextTheme )
	) );

	return (
		<React.Fragment>

			<PanelBody title={ translate( 'style-theming' ) } initialOpen={ true }>

				<SelectField
					{ ...field }
					settingKey="gs_team_theme"
					label={ translate( 'style-theming' ) }
					help={ translate( 'select-preffered-style-theme' ) }
					onChange={ changeTheme }
				/>

				{ themesV2Carousel().indexOf( theme ) !== -1 && (
					<ToggleField
						{ ...field }
						settingKey="carousel_enabled"
						label={ translate( 'carousel_enabled' ) }
						help={ translate( 'carousel_enabled__details' ) }
					/>
				) }

			</PanelBody>

			{ ( showFilterToggle || showPagination ) && (
				<PanelBody title={ uiLabel( 'filter_panel' ) } initialOpen={ false }>

					{ showFilterToggle && (
						<ToggleField
							{ ...field }
							settingKey="filter_enabled"
							label={ translate( 'filter_enabled' ) }
							help={ translate( 'filter_enabled__details' ) }
						/>
					) }

					{ filterOn && (
						<React.Fragment>
							<SelectField
								{ ...field }
								settingKey="gs_team_filter_type"
								label={ translate( 'gs_team_filter_type', 'Filter Type' ) }
								help={ translate( 'gs_team_filter_type__details' ) }
							/>
							<ToggleField { ...field } settingKey="gs_member_srch_by_name" label={ translate( 'gs_member_srch_by_name', 'Search by Name' ) } />
							<ToggleField { ...field } settingKey="gs_member_srch_by_zip" label={ translate( 'gs_member_srch_by_zip', 'Search by Zip' ) } />
							<ToggleField { ...field } settingKey="gs_member_srch_by_tag" label={ translate( 'gs_member_srch_by_tag', 'Search by Tag' ) } />
							<ToggleField { ...field } settingKey="gs_member_srch_by_company" label={ translate( 'gs_member_srch_by_company', 'Search by Company' ) } />
							<ToggleField { ...field } settingKey="gs_member_filter_by_desig" label={ translate( 'gs_member_filter_by_desig', 'Filter by Designation' ) } />
							<ToggleField { ...field } settingKey="gs_member_filter_by_location" label={ translate( 'gs_member_filter_by_location', 'Filter by Location' ) } />
							<ToggleField { ...field } settingKey="gs_member_filter_by_language" label={ translate( 'gs_member_filter_by_language', 'Filter by Language' ) } />
							<ToggleField { ...field } settingKey="gs_member_filter_by_gender" label={ translate( 'gs_member_filter_by_gender', 'Filter by Gender' ) } />
							<ToggleField { ...field } settingKey="gs_member_filter_by_speciality" label={ translate( 'gs_member_filter_by_speciality', 'Filter by Specialty' ) } />
							<ToggleField { ...field } settingKey="gs_member_enable_clear_filters" label={ translate( 'gs_member_enable_clear_filters', 'Clear Filters' ) } />
							<ToggleField { ...field } settingKey="gs_member_enable_multi_select" label={ translate( 'gs_member_enable_multi_select', 'Multi Select' ) } />
							{ isOn( attributes.gs_member_enable_multi_select ) && (
								<ToggleField { ...field } settingKey="gs_member_multi_select_ellipsis" label={ translate( 'gs_member_multi_select_ellipsis', 'Multi Select Ellipsis' ) } />
							) }
							<ToggleField { ...field } settingKey="gs_filter_all_enabled" label={ translate( 'gs_filter_all_enabled', 'Show All Filter' ) } />
							{ isOn( attributes.gs_filter_all_enabled ) && (
								<TextField { ...field } settingKey="fitler_all_text" label={ translate( 'fitler_all_text', 'All Text' ) } />
							) }
							<ToggleField { ...field } settingKey="enable_child_cats" label={ translate( 'enable_child_cats', 'Enable Child Categories' ) } />
							<SelectField { ...field } settingKey="gs_team_filter_columns" label={ translate( 'gs_team_filter_columns', 'Filter Columns' ) } />
							<SelectField { ...field } settingKey="gs_tm_filter_cat_pos" label={ translate( 'gs_tm_filter_cat_pos', 'Filter Position' ) } />
						</React.Fragment>
					) }

					{ showPagination && (
						<ToggleField
							{ ...field }
							settingKey="gs_member_pagination"
							label={ translate( 'gs_member_pagination' ) }
							help={ translate( 'gs_member_pagination__details' ) }
						/>
					) }

					{ paginationOn && (
						<React.Fragment>
							<SelectField { ...field } settingKey="pagination_type" label={ translate( 'pagination_type' ) } help={ translate( 'pagination_type__details' ) } />
							{ isOneOf( attributes.pagination_type, [ 'load-more-button', 'load-more-scroll' ] ) && (
								<NumberField { ...field } settingKey="initial_items" label={ translate( 'initial_items' ) } min={ 1 } />
							) }
							{ isOneOf( attributes.pagination_type, [ 'normal-pagination', 'ajax-pagination' ] ) && (
								<NumberField { ...field } settingKey="team_per_page" label={ translate( 'team_per_page' ) } min={ 1 } />
							) }
							{ isOneOf( attributes.pagination_type, [ 'load-more-button' ] ) && (
								<React.Fragment>
									<NumberField { ...field } settingKey="load_per_click" label={ translate( 'load_per_click' ) } min={ 1 } />
									<TextField { ...field } settingKey="load_button_text" label={ translate( 'load_button_text' ) } />
								</React.Fragment>
							) }
							{ 'load-more-scroll' === attributes.pagination_type && (
								<NumberField { ...field } settingKey="per_load" label={ translate( 'per_load' ) } min={ 1 } />
							) }
						</React.Fragment>
					) }

				</PanelBody>
			) }

			<PanelBody title={ uiLabel( 'thumbnail_panel' ) } initialOpen={ false }>

				<SelectField
					{ ...field }
					settingKey="gs_member_thumbnail_sizes"
					label={ translate( 'gs_member_thumbnail_sizes', 'Thumbnail Size' ) }
				/>

				<ToggleField
					{ ...field }
					settingKey="link_preview_image"
					label={ translate( 'link_preview_image', 'Link Preview Image' ) }
				/>

			</PanelBody>

			{ canShowLinking( theme ) && (
				<PanelBody title={ uiLabel( 'linking_panel' ) } initialOpen={ false }>

					<ToggleField
						{ ...field }
						settingKey="gs_member_name_is_linked"
						label={ translate( 'gs_member_name_is_linked' ) }
						help={ translate( 'gs_member_name_is_linked__details' ) }
					/>

					{ linkingOn && (
						<React.Fragment>
							<SelectField
								{ ...field }
								settingKey="gs_member_link_type"
								label={ translate( 'gs_member_link_type' ) }
							/>
							{ 'popup' === linkType && (
								<SelectField { ...field } settingKey="popup_style" label={ translate( 'popup_style' ) } />
							) }
							{ showPopupColumns && (
								<SelectField { ...field } settingKey="gs_teammembers_pop_clm" label={ translate( 'gs_teammembers_pop_clm', 'Popup Columns' ) } />
							) }
							{ 'panel' === linkType && (
								<React.Fragment>
									<SelectField { ...field } settingKey="panel_style" label={ translate( 'panel_style' ) } />
									<SelectField { ...field } settingKey="panel" label={ translate( 'panel', 'Panel Side' ) } />
								</React.Fragment>
							) }
							{ 'drawer' === linkType && (
								<SelectField { ...field } settingKey="drawer_style" label={ translate( 'drawer_style' ) } />
							) }
						</React.Fragment>
					) }

				</PanelBody>
			) }

			{ carouselOn && (
				<PanelBody title={ uiLabel( 'slider_panel' ) } initialOpen={ false }>

					<ToggleField { ...field } settingKey="carousel_autoplay" label={ translate( 'carousel_autoplay' ) } help={ translate( 'carousel_autoplay__details' ) } />

					{ isOn( attributes.carousel_autoplay ) && (
						<React.Fragment>
							<ToggleField { ...field } settingKey="carousel_autoplay_hover_pause" label={ translate( 'carousel_autoplay_hover_pause' ) } />
							<NumberField { ...field } settingKey="carousel_autoplay_speed" label={ translate( 'carousel_autoplay_speed' ) } min={ 0 } />
							<NumberField { ...field } settingKey="carousel_autoplay_timeout" label={ translate( 'carousel_autoplay_timeout' ) } min={ 0 } />
						</React.Fragment>
					) }

					<ToggleField { ...field } settingKey="carousel_loop" label={ translate( 'carousel_loop' ) } help={ translate( 'carousel_loop__details' ) } />
					<NumberField { ...field } settingKey="carousel_items_to_scroll" label={ translate( 'carousel_items_to_scroll' ) } min={ 1 } />

				</PanelBody>
			) }

			<PanelBody title={ uiLabel( 'featuring_panel' ) } initialOpen={ false }>

				<ToggleField { ...field } settingKey="enable_featuring" label={ translate( 'enable_featuring', 'Enable Featuring' ) } />
				{ isOn( attributes.enable_featuring ) && (
					<ToggleField { ...field } settingKey="featured_badge" label={ translate( 'featured_badge', 'Featured Badge' ) } />
				) }

			</PanelBody>

			<PanelBody title={ uiLabel( 'content_panel' ) } initialOpen={ false }>

				<ToggleField { ...field } settingKey="enable_scroll_animation" label={ translate( 'enable_scroll_animation', 'Scroll Animation' ) } />
				<ToggleField { ...field } settingKey="gs_desc_allow_html" label={ translate( 'gs_desc_allow_html', 'Allow HTML in Description' ) } />
				{ ! isOn( attributes.gs_desc_allow_html ) && (
					<SliderField { ...field } settingKey="gs_tm_details_contl" label={ translate( 'gs_tm_details_contl', 'Details Control' ) } min={ 0 } max={ 500 } />
				) }
				<ToggleField { ...field } settingKey="gs_desc_scroll_contrl" label={ translate( 'gs_desc_scroll_contrl', 'Description Scroll' ) } />
				{ isOn( attributes.gs_desc_scroll_contrl ) && (
					<TextField { ...field } settingKey="gs_max_scroll_height" label={ translate( 'gs_max_scroll_height', 'Max Scroll Height' ) } />
				) }

			</PanelBody>

			{ hasAcf() && (
				<PanelBody title={ uiLabel( 'acf_panel' ) } initialOpen={ false }>

					<ToggleField { ...field } settingKey="show_acf_fields" label={ translate( 'show-acf-fields' ) } />
					{ isOn( attributes.show_acf_fields ) && (
						<SelectField { ...field } settingKey="acf_fields_position" label={ translate( 'acf_fields_position', 'ACF Fields Position' ) } />
					) }

				</PanelBody>
			) }

		</React.Fragment>
	);
}
