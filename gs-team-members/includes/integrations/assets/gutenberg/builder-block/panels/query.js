/**
 * Query tab.
 *
 * Ported from the "query_settings" tab of dev/shortcode/pages/shortcode.vue.
 */

import { NumberField, SelectField, TermsField, ToggleField } from '../controls';
import { isOn, label as uiLabel, taxonomySettings, translate } from '../data';

const React = window.React;

const { PanelBody } = wp.components;

const TAXONOMIES = [
	{ settingKey: 'group', optionsKey: 'group', enableKey: 'enable_group_tax', labelKey: 'group_tax_plural_label' },
	{ settingKey: 'location', optionsKey: 'location', enableKey: 'enable_location_tax', labelKey: 'location_tax_plural_label' },
	{ settingKey: 'language', optionsKey: 'language', enableKey: 'enable_language_tax', labelKey: 'language_tax_plural_label' },
	{ settingKey: 'specialty', optionsKey: 'specialty', enableKey: 'enable_specialty_tax', labelKey: 'specialty_tax_plural_label' },
	{ settingKey: 'gender', optionsKey: 'gender', enableKey: 'enable_gender_tax', labelKey: 'gender_tax_plural_label' },
	{ settingKey: 'include_extra_one', optionsKey: 'extra_one', enableKey: 'enable_extra_one_tax', labelKey: 'extra_one_tax_plural_label' },
	{ settingKey: 'include_extra_two', optionsKey: 'extra_two', enableKey: 'enable_extra_two_tax', labelKey: 'extra_two_tax_plural_label' },
	{ settingKey: 'include_extra_three', optionsKey: 'extra_three', enableKey: 'enable_extra_three_tax', labelKey: 'extra_three_tax_plural_label' },
	{ settingKey: 'include_extra_four', optionsKey: 'extra_four', enableKey: 'enable_extra_four_tax', labelKey: 'extra_four_tax_plural_label' },
	{ settingKey: 'include_extra_five', optionsKey: 'extra_five', enableKey: 'enable_extra_five_tax', labelKey: 'extra_five_tax_plural_label' }
];

function enabledTaxonomies() {

	const settings = taxonomySettings();

	return TAXONOMIES.filter( ( taxonomy ) => {

		if ( typeof settings[ taxonomy.enableKey ] === 'undefined' ) {
			return true;
		}

		return 'on' === settings[ taxonomy.enableKey ];

	} ).map( ( taxonomy ) => {
		return Object.assign( {}, taxonomy, {
			label: settings[ taxonomy.labelKey ] || taxonomy.optionsKey
		} );
	} );
}

export default function QueryPanels( { attributes, setAttributes } ) {

	const field = { attributes, setAttributes };
	const taxonomies = enabledTaxonomies();
	const showNum = ! isOn( attributes.gs_member_pagination );

	return (
		<React.Fragment>

			<PanelBody title={ translate( 'query-settings' ) } initialOpen={ true }>

				{ showNum && (
					<NumberField
						{ ...field }
						settingKey="num"
						label={ translate( 'num', 'Number of Members' ) }
						help={ translate( 'num--details', 'Use -1 to show all members' ) }
					/>
				) }

				<SelectField
					{ ...field }
					settingKey="orderby"
					label={ translate( 'orderby', 'Order By' ) }
				/>

				<SelectField
					{ ...field }
					settingKey="order"
					label={ translate( 'order' ) }
				/>

				<SelectField
					{ ...field }
					settingKey="taxonomy_orderby"
					label={ translate( 'taxonomy_orderby', 'Taxonomy Order By' ) }
				/>

				<SelectField
					{ ...field }
					settingKey="taxonomy_order"
					optionsKey="order"
					label={ translate( 'taxonomy_order', 'Taxonomy Order' ) }
				/>

				<ToggleField
					{ ...field }
					settingKey="taxonomy_hide_empty"
					label={ translate( 'taxonomy_hide_empty', 'Hide Empty Taxonomies' ) }
				/>

			</PanelBody>

			{ taxonomies.length > 0 && (
				<React.Fragment>

					<PanelBody title={ uiLabel( 'include_terms' ) } initialOpen={ false }>
						{ taxonomies.map( ( taxonomy ) => (
							<TermsField
								key={ 'include_' + taxonomy.settingKey }
								{ ...field }
								settingKey={ taxonomy.settingKey }
								optionsKey={ taxonomy.optionsKey }
								label={ taxonomy.label }
								help={ translate( taxonomy.settingKey + '--details' ) }
							/>
						) ) }
					</PanelBody>

					<PanelBody title={ uiLabel( 'exclude_terms' ) } initialOpen={ false }>
						<TermsField
							{ ...field }
							settingKey="exclude_group"
							optionsKey="exclude_group"
							label={ translate( 'exclude_group', 'Exclude Group' ) }
							help={ translate( 'exclude_group--details' ) }
						/>
					</PanelBody>

				</React.Fragment>
			) }

		</React.Fragment>
	);
}
