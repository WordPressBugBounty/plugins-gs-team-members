/**
 * Visibility tab.
 *
 * Ported from the "visibility_settings" tab of dev/shortcode/pages/shortcode.vue:
 * one row per field, four device checkboxes per row, split into the initial
 * view plus the popup, panel and drawer overlays.
 */

import { DeviceCheckbox } from '../controls';
import { isOn, isOneOf, translate } from '../data';

import {
	VISIBILITY_DEVICES,
	getDrawerVisibilityFieldKeys,
	getInitialVisibilityFieldKeys,
	getPanelVisibilityFieldKeys,
	getPopupVisibilityFieldKeys,
	getVisibilityField,
	updateVisibilityField,
	visibilityFieldLabel
} from '../visibility';

const React = window.React;

const { PanelBody } = wp.components;

function VisibilityGroup( { attributes, setAttributes, group, fieldKeys } ) {

	if ( ! fieldKeys.length ) return null;

	return (
		<React.Fragment>
			{ fieldKeys.map( function( fieldKey ) {

				const fieldValue = getVisibilityField( attributes, group, fieldKey );

				return (
					<div className="gsteam-builder-block--group" key={ group + '_' + fieldKey }>

						<p className="gsteam-builder-block--group-title">{ visibilityFieldLabel( fieldKey ) }</p>

						<div className="gsteam-builder-block--devices">
							{ VISIBILITY_DEVICES.map( ( device ) => (
								<DeviceCheckbox
									key={ device.key }
									label={ translate( device.translationKey ) }
									checked={ !! fieldValue[ device.key ] }
									onChange={ ( checked ) => setAttributes(
										updateVisibilityField( attributes, group, fieldKey, device.key, checked )
									) }
								/>
							) ) }
						</div>

					</div>
				);
			} ) }
		</React.Fragment>
	);
}

export default function VisibilityPanels( { attributes, setAttributes } ) {

	const group = { attributes, setAttributes };

	const linkingEnabled = isOn( attributes.gs_member_name_is_linked );
	const linkType = attributes.gs_member_link_type;
	const theme = attributes.gs_team_theme;

	const showPopup = linkingEnabled && isOneOf( linkType, [ 'popup', 'default' ] );
	const showPanel = ( linkingEnabled && 'panel' === linkType ) || 'gs_tm_theme19' === theme;
	const showDrawer = ( linkingEnabled && 'drawer' === linkType )
		|| isOneOf( theme, [ 'gs_tm_theme13', 'gs_tm_drawer2' ] );

	return (
		<React.Fragment>

			<PanelBody title={ translate( 'visibility-initial-view' ) } initialOpen={ true }>
				<VisibilityGroup
					{ ...group }
					group="initial"
					fieldKeys={ getInitialVisibilityFieldKeys( attributes.gs_team_theme ) }
				/>
			</PanelBody>

			{ showPopup && (
				<PanelBody title={ translate( 'visibility-popup' ) } initialOpen={ false }>
					<VisibilityGroup
						{ ...group }
						group="popup"
						fieldKeys={ getPopupVisibilityFieldKeys( attributes.popup_style ) }
					/>
				</PanelBody>
			) }

			{ showPanel && (
				<PanelBody title={ translate( 'visibility-panel' ) } initialOpen={ false }>
					<VisibilityGroup
						{ ...group }
						group="panel"
						fieldKeys={ getPanelVisibilityFieldKeys( attributes.panel_style ) }
					/>
				</PanelBody>
			) }

			{ showDrawer && (
				<PanelBody title={ translate( 'visibility-drawer' ) } initialOpen={ false }>
					<VisibilityGroup
						{ ...group }
						group="drawer"
						fieldKeys={ getDrawerVisibilityFieldKeys( attributes.drawer_style ) }
					/>
				</PanelBody>
			) }

		</React.Fragment>
	);
}
