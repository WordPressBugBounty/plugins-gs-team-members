/**
 * Native field wrappers.
 *
 * Every control is a thin wrapper around a core @wordpress/components field.
 * The wrappers exist so the builder's storage conventions stay intact:
 *  - booleans are kept as the strings 'on' / 'off'
 *  - a setting keeps the value type its PHP default declares
 *  - composite values keep their comma separated string format
 *
 * `premium` marks the fields the builder renders with its Pro overlay, so the
 * free version shows them read only with a notice.
 */

import {
	fieldOptions,
	fontsData,
	isOn,
	isPremiumOption,
	isProActive,
	label as uiLabel,
	premiumAlertMessage,
	rawOptions,
	settingDefault,
	toOnOff,
	translate
} from './data';

const React = window.React;

const {
	ToggleControl,
	SelectControl,
	TextControl,
	RangeControl,
	CheckboxControl,
	FormTokenField,
	BaseControl
} = wp.components;

const { PanelColorSettings } = wp.blockEditor;

/**
 * Keep the value type the block attribute schema declares, so the REST block
 * renderer does not reject the attribute and silently fall back to its default.
 */
function castToSettingType( settingKey, value ) {

	if ( typeof settingDefault( settingKey ) === 'number' ) {

		const numeric = Number( value );

		return Number.isNaN( numeric ) ? 0 : numeric;
	}

	return String( value );
}

function numericValue( settingKey, value ) {

	const numeric = Number( value );

	if ( ! Number.isNaN( numeric ) ) return numeric;

	return Number( settingDefault( settingKey ) ) || 0;
}

/**
 * Add the premium notice below a field, and stop interaction for controls that
 * have no `disabled` prop of their own.
 */
function withPremium( premium, control, lockInteraction ) {

	if ( ! premium ) return control;

	return (
		<div className={ lockInteraction ? 'gsteam-builder-block--locked' : '' }>
			{ control }
			<p className="gsteam-builder-block--premium">{ uiLabel( 'premium_notice' ) }</p>
		</div>
	);
}

export function ToggleField( { attributes, setAttributes, settingKey, label, help, premium } ) {

	return withPremium( premium, (
		<ToggleControl
			label={ label }
			help={ help }
			checked={ isOn( attributes[ settingKey ] ) }
			disabled={ !! premium }
			onChange={ ( checked ) => setAttributes( { [ settingKey ]: toOnOff( checked ) } ) }
		/>
	) );
}

export function SelectField( { attributes, setAttributes, settingKey, label, help, options, optionsKey, onChange } ) {

	const lookupKey = optionsKey || settingKey;
	const list = options || fieldOptions( lookupKey );

	const handleChange = function( value ) {

		// Match the shortcode builder: refuse Pro options and tell the user why.
		if ( isPremiumOption( lookupKey, value ) && ! isProActive() ) {
			window.alert( premiumAlertMessage() );
			return;
		}

		if ( onChange ) {
			onChange( value );
			return;
		}

		setAttributes( { [ settingKey ]: value } );
	};

	// Native SelectControl cannot style individual options; render our own
	// <select> so premium rows stay visible (and clickable for the alert).
	return (
		<BaseControl
			label={ label }
			help={ help }
			className="components-select-control gsteam-builder-block--select"
		>
			<select
				className="components-select-control__input"
				value={ String( attributes[ settingKey ] ?? '' ) }
				onChange={ ( event ) => handleChange( event.target.value ) }
			>
				{ list.map( function( option ) {

					const premiumLocked = typeof option.premiumLocked !== 'undefined'
						? !! option.premiumLocked
						: ( isPremiumOption( lookupKey, option.value ) && ! isProActive() );

					return (
						<option
							key={ String( option.value ) }
							value={ String( option.value ) }
							className={ premiumLocked ? 'gsteam-builder-block--premium-option' : undefined }
						>
							{ option.label }
						</option>
					);
				} ) }
			</select>
		</BaseControl>
	);
}

export function TextField( { attributes, setAttributes, settingKey, label, help, placeholder, premium } ) {

	return withPremium( premium, (
		<TextControl
			label={ label }
			help={ help }
			placeholder={ placeholder }
			value={ attributes[ settingKey ] ?? '' }
			disabled={ !! premium }
			onChange={ ( value ) => setAttributes( { [ settingKey ]: castToSettingType( settingKey, value ) } ) }
		/>
	) );
}

export function NumberField( { attributes, setAttributes, settingKey, label, help, min, max, premium } ) {

	return withPremium( premium, (
		<TextControl
			type="number"
			label={ label }
			help={ help }
			min={ min }
			max={ max }
			value={ attributes[ settingKey ] }
			disabled={ !! premium }
			onChange={ ( value ) => setAttributes( {
				[ settingKey ]: castToSettingType( settingKey, '' === value ? settingDefault( settingKey ) : value )
			} ) }
		/>
	) );
}

export function SliderField( { attributes, setAttributes, settingKey, label, help, min, max, step, premium } ) {

	return withPremium( premium, (
		<RangeControl
			label={ label }
			help={ help }
			min={ min }
			max={ max }
			step={ step }
			value={ numericValue( settingKey, attributes[ settingKey ] ) }
			disabled={ !! premium }
			onChange={ ( value ) => setAttributes( { [ settingKey ]: castToSettingType( settingKey, value ) } ) }
		/>
	) );
}

/**
 * Colors get their own panel, which is how core blocks expose them.
 * `colors` is a list of { settingKey, label }.
 */
export function ColorFieldsPanel( { attributes, setAttributes, title, initialOpen, colors } ) {

	const colorSettings = colors.map( function( color ) {
		return {
			label: color.label,
			value: attributes[ color.settingKey ],
			onChange: ( value ) => setAttributes( {
				[ color.settingKey ]: value || settingDefault( color.settingKey )
			} )
		};
	} );

	return (
		<PanelColorSettings
			title={ title }
			initialOpen={ !! initialOpen }
			colorSettings={ colorSettings }
		/>
	);
}

/**
 * Taxonomy include / exclude lists. Tokens are term names; the stored value is
 * a comma-separated list of term IDs (Team validate_shortcode_settings uses
 * sanitize_text_field on these keys).
 */
export function TermsField( { attributes, setAttributes, settingKey, optionsKey, label, help } ) {

	const terms = rawOptions( optionsKey || settingKey );
	const raw = attributes[ settingKey ];
	const selected = ! raw
		? []
		: ( Array.isArray( raw ) ? raw : String( raw ).split( ',' ).map( ( v ) => v.trim() ).filter( Boolean ) );

	const termLabel = ( termId ) => {

		const term = terms.find( ( item ) => String( item.value ) === String( termId ) );

		return term ? term.label : String( termId );
	};

	const termId = ( token ) => {

		const term = terms.find( ( item ) => item.label === token );

		return term ? term.value : null;
	};

	return (
		<BaseControl help={ help }>
			<FormTokenField
				label={ label }
				value={ selected.map( termLabel ) }
				suggestions={ terms.map( ( term ) => term.label ) }
				__experimentalExpandOnFocus
				__experimentalShowHowTo={ false }
				onChange={ ( tokens ) => setAttributes( {
					[ settingKey ]: tokens.map( termId ).filter( ( id ) => null !== id ).join( ',' )
				} ) }
			/>
		</BaseControl>
	);
}

export function DeviceCheckbox( { label, checked, onChange } ) {

	return (
		<CheckboxControl
			label={ label }
			checked={ !! checked }
			onChange={ onChange }
		/>
	);
}

/**
 * Typography object control (name / role / details / info / ribbon).
 */
export function TypographyField( { attributes, setAttributes, settingKey, title, premium } ) {

	const typo = attributes[ settingKey ] && typeof attributes[ settingKey ] === 'object'
		? attributes[ settingKey ]
		: {};

	const fonts = fontsData().map( ( font ) => ( {
		label: font.label || font.value,
		value: font.value || font.label || ''
	} ) );

	const set = ( key, value ) => setAttributes( {
		[ settingKey ]: Object.assign( {}, typo, { [ key ]: value } )
	} );

	return withPremium( premium, (
		<div className="gsteam-builder-block--typography">
			<h3>{ title }</h3>
			{ ! premium && (
				<React.Fragment>
					<SelectControl
						label={ translate( 'font-family', 'Family' ) }
						value={ typo.font_family || '' }
						options={ fonts.length ? fonts : [ { label: 'Default', value: '' } ] }
						onChange={ ( value ) => set( 'font_family', value ) }
					/>
					<RangeControl
						label={ translate( 'font-size', 'Size' ) }
						value={ typo.size ? Number( typo.size ) : 0 }
						onChange={ ( value ) => set( 'size', value ) }
						min={ 0 }
						max={ 200 }
					/>
					<TextControl
						label={ translate( 'color', 'Color' ) }
						value={ typo.color || '' }
						onChange={ ( value ) => set( 'color', value ) }
						placeholder="#000000"
					/>
					<TextControl
						label={ translate( 'hover-color', 'Hover Color' ) }
						value={ typo.hover_color || '' }
						onChange={ ( value ) => set( 'hover_color', value ) }
						placeholder="#000000"
					/>
					<SelectControl
						label={ translate( 'font-weight', 'Weight' ) }
						value={ typo.weight ? String( typo.weight ) : '' }
						options={ [
							{ label: 'Default', value: '' },
							{ label: '100', value: '100' },
							{ label: '200', value: '200' },
							{ label: '300', value: '300' },
							{ label: '400', value: '400' },
							{ label: '500', value: '500' },
							{ label: '600', value: '600' },
							{ label: '700', value: '700' },
							{ label: '800', value: '800' },
							{ label: '900', value: '900' }
						] }
						onChange={ ( value ) => set( 'weight', value ) }
					/>
					<SelectControl
						label={ translate( 'text-transform', 'Transform' ) }
						value={ typo.transform || '' }
						options={ [
							{ label: 'Default', value: '' },
							{ label: 'Uppercase', value: 'uppercase' },
							{ label: 'Lowercase', value: 'lowercase' },
							{ label: 'Capitalize', value: 'capitalize' },
							{ label: 'None', value: 'none' }
						] }
						onChange={ ( value ) => set( 'transform', value ) }
					/>
					<RangeControl
						label={ translate( 'line-height', 'Line Height' ) }
						value={ typo.line_height ? Number( typo.line_height ) : 0 }
						onChange={ ( value ) => set( 'line_height', value ) }
						min={ 0 }
						max={ 10 }
						step={ 0.1 }
					/>
					<RangeControl
						label={ translate( 'letter-spacing', 'Letter Spacing' ) }
						value={ typo.letter_spacing ? Number( typo.letter_spacing ) : 0 }
						onChange={ ( value ) => set( 'letter_spacing', value ) }
						min={ -5 }
						max={ 20 }
						step={ 0.1 }
					/>
				</React.Fragment>
			) }
		</div>
	), true );
}
