import ServerSideRender from '@wordpress/server-side-render';

wp.blocks.registerBlockType('odds-widget/block', {
	title: 'Odds widget',
	description: 'Odds comparison table',
	keywords: ['odds', 'betting'],
	icon: 'editor-kitchensink',
	category: 'widgets',
	attributes: {},

	edit: (props) => {
		const { attributes, setAttributes } = props;
		return (
			<div>
				{/*<InspectorControls>*/}
				{/*	<PanelBody*/}
				{/*		title={__('Options', 'odds-widget')}*/}
				{/*		initialOpen={true}*/}
				{/*	>*/}
				{/*		<PanelRow>*/}
				{/*			<SelectControl*/}
				{/*				label={__('Odds format', 'odds-widget')}*/}
				{/*				value={attributes.odds_format === undefined ? widgetProps.defaults.odds_format : attributes.odds_format}*/}
				{/*				options={widgetProps.options.odds_formats.map(option => {return {*/}
				{/*					label: __(option.charAt(0).toUpperCase() + option.slice(1), 'odds-widget'),*/}
				{/*					value: option,*/}
				{/*				}})}*/}
				{/*				onChange={(newval) => setAttributes({ odds_format: newval })}*/}
				{/*			/>*/}
				{/*		</PanelRow>*/}
				{/*		<PanelRow>*/}
				{/*			<SelectControl*/}
				{/*				label={__('Sport', 'odds-widget')}*/}
				{/*				value={attributes.sport === undefined ? widgetProps.defaults.sport : attributes.sport}*/}
				{/*				options={widgetProps.options.sports.map(sport => {return {*/}
				{/*					label: __(sport.charAt(0).toUpperCase() + sport.slice(1), 'odds-widget'),*/}
				{/*					value: sport*/}
				{/*				}})}*/}
				{/*				onChange={(newval) => setAttributes({ odds_format: newval })}*/}
				{/*			/>*/}
				{/*		</PanelRow>*/}
				{/*	</PanelBody>*/}
				{/*</InspectorControls>*/}

				<ServerSideRender
					block="odds-widget/block"
				/>
			</div>
		)
	},
})