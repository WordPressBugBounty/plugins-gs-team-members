/**
 * Style tab.
 *
 * Ported from the "style_settings" tab of dev/shortcode/pages/shortcode.vue.
 */

import {
	ColorFieldsPanel,
	SelectField,
	ToggleField,
	TypographyField
} from '../controls';

import {
	isOn,
	isProActive,
	label as uiLabel,
	translate
} from '../data';

import {
	canShowColumns,
	infoBgColorThemes,
	isCarouselActive,
	isFilterActive,
	noBgColorThemes
} from '../theme-groups';

const React = window.React;

const { PanelBody } = wp.components;

export default function StylePanels( { attributes, setAttributes } ) {

	const field = { attributes, setAttributes };
	const premium = ! isProActive();
	const theme = attributes.gs_team_theme;
	const carouselOn = isCarouselActive( attributes );
	const filterOn = isFilterActive( attributes );
	const showBg = noBgColorThemes().indexOf( theme ) === -1;
	const showInfoBg = infoBgColorThemes().indexOf( theme ) !== -1;

	const colorItems = [];

	if ( showBg ) {
		colorItems.push(
			{ settingKey: 'tm_bg_color', label: translate( 'tm_bg_color', 'Member Background' ) },
			{ settingKey: 'tm_bg_color_hover', label: translate( 'tm_bg_color_hover', 'Member Background Hover' ) }
		);
	}

	if ( showInfoBg ) {
		colorItems.push( { settingKey: 'gs_tm_info_background', label: translate( 'gs_tm_info_background', 'Info Background' ) } );
	}

	colorItems.push(
		{ settingKey: 'description_link_color', label: translate( 'description_link_color', 'Description Link Color' ) },
		{ settingKey: 'info_icon_color', label: translate( 'info_icon_color', 'Info Icon Color' ) },
		{ settingKey: 'gs_tm_mname_background', label: translate( 'gs_tm_mname_background', 'Name Background' ) },
		{ settingKey: 'gs_tm_tooltip_background', label: translate( 'gs_tm_tooltip_background', 'Tooltip Background' ) },
		{ settingKey: 'gs_tm_hover_icon_background', label: translate( 'gs_tm_hover_icon_background', 'Hover Icon Background' ) },
		{ settingKey: 'gs_tm_ribon_color', label: translate( 'gs_tm_ribon_color', 'Ribbon Color' ) },
		{ settingKey: 'gs_tm_arrow_color', label: translate( 'gs_tm_arrow_color', 'Arrow Color' ) }
	);

	return (
		<React.Fragment>

			{ canShowColumns( theme ) && (
				<PanelBody title={ uiLabel( 'layout_panel' ) } initialOpen={ true }>

					<SelectField { ...field } settingKey="gs_team_cols" label={ translate( 'gs_team_cols', 'Columns (Desktop)' ) } />
					<SelectField { ...field } settingKey="gs_team_cols_tablet" label={ translate( 'gs_team_cols_tablet', 'Columns (Tablet)' ) } />
					<SelectField { ...field } settingKey="gs_team_cols_mobile_portrait" label={ translate( 'gs_team_cols_mobile_portrait', 'Columns (Mobile Portrait)' ) } />
					<SelectField { ...field } settingKey="gs_team_cols_mobile" label={ translate( 'gs_team_cols_mobile', 'Columns (Mobile)' ) } />

				</PanelBody>
			) }

			<PanelBody title={ uiLabel( 'image_filter_panel' ) } initialOpen={ true }>

				<SelectField
					{ ...field }
					settingKey="image_filter"
					label={ translate( 'image_filter' ) }
				/>

				<SelectField
					{ ...field }
					settingKey="hover_image_filter"
					label={ translate( 'hover_image_filter' ) }
				/>

			</PanelBody>

			<PanelBody title={ uiLabel( 'typography_panel' ) } initialOpen={ false }>

				<TypographyField
					{ ...field }
					settingKey="gs_tm_name_typography"
					title={ translate( 'gs_tm_name_typography', 'Name Typography' ) }
				/>
				<TypographyField
					{ ...field }
					settingKey="gs_tm_role_typography"
					title={ translate( 'gs_tm_role_typography', 'Role Typography' ) }
					premium={ premium }
				/>
				<TypographyField
					{ ...field }
					settingKey="gs_tm_details_typography"
					title={ translate( 'gs_tm_details_typography', 'Details Typography' ) }
					premium={ premium }
				/>
				<TypographyField
					{ ...field }
					settingKey="gs_tm_info_typography"
					title={ translate( 'gs_tm_info_typography', 'Info Typography' ) }
					premium={ premium }
				/>
				<TypographyField
					{ ...field }
					settingKey="gs_tm_ribbon_typography"
					title={ translate( 'gs_tm_ribbon_typography', 'Ribbon Typography' ) }
					premium={ premium }
				/>

			</PanelBody>

			{ carouselOn && (
				<PanelBody title={ uiLabel( 'carousel_style_panel' ) } initialOpen={ false }>

					<ToggleField { ...field } settingKey="carousel_navs_enabled" label={ translate( 'carousel_navs_enabled', 'Show Navs' ) } />
					{ isOn( attributes.carousel_navs_enabled ) && (
						<SelectField { ...field } settingKey="carousel_navs_style" label={ translate( 'carousel_navs_style' ) } />
					) }

					<ToggleField { ...field } settingKey="carousel_dots_enabled" label={ translate( 'carousel_dots_enabled', 'Show Dots' ) } />
					{ isOn( attributes.carousel_dots_enabled ) && (
						<SelectField { ...field } settingKey="carousel_dots_style" label={ translate( 'carousel_dots_style' ) } />
					) }

					<ColorFieldsPanel
						{ ...field }
						title={ uiLabel( 'carousel_style_panel' ) }
						initialOpen={ true }
						colors={ [
							{ settingKey: 'gs_slider_nav_color', label: translate( 'gs_slider_nav_color', 'Nav Color' ) },
							{ settingKey: 'gs_slider_nav_bg_color', label: translate( 'gs_slider_nav_bg_color', 'Nav Background' ) },
							{ settingKey: 'gs_slider_nav_hover_color', label: translate( 'gs_slider_nav_hover_color', 'Nav Hover Color' ) },
							{ settingKey: 'gs_slider_nav_hover_bg_color', label: translate( 'gs_slider_nav_hover_bg_color', 'Nav Hover Background' ) },
							{ settingKey: 'gs_slider_dot_color', label: translate( 'gs_slider_dot_color', 'Dot Color' ) },
							{ settingKey: 'gs_slider_dot_hover_color', label: translate( 'gs_slider_dot_hover_color', 'Dot Hover Color' ) }
						] }
					/>

				</PanelBody>
			) }

			{ filterOn && (
				<PanelBody title={ uiLabel( 'filter_style_panel' ) } initialOpen={ false }>

					<SelectField { ...field } settingKey="filter_style" label={ translate( 'filter_style' ) } />

					<ColorFieldsPanel
						{ ...field }
						title={ uiLabel( 'filter_style_panel' ) }
						initialOpen={ true }
						colors={ [
							{ settingKey: 'filter_text_color', label: translate( 'filter_text_color', 'Filter Text Color' ) },
							{ settingKey: 'filter_active_text_color', label: translate( 'filter_active_text_color', 'Filter Active Text' ) },
							{ settingKey: 'filter_bg_color', label: translate( 'filter_bg_color', 'Filter Background' ) },
							{ settingKey: 'filter_active_bg_color', label: translate( 'filter_active_bg_color', 'Filter Active Background' ) },
							{ settingKey: 'filter_border_color', label: translate( 'filter_border_color', 'Filter Border' ) },
							{ settingKey: 'filter_active_border_color', label: translate( 'filter_active_border_color', 'Filter Active Border' ) }
						] }
					/>

				</PanelBody>
			) }

			{ colorItems.length > 0 && (
				<ColorFieldsPanel
					{ ...field }
					title={ uiLabel( 'colors_panel' ) }
					initialOpen={ false }
					colors={ colorItems }
				/>
			) }

		</React.Fragment>
	);
}
