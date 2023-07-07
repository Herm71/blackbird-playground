/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import { Panel, PanelBody, CheckboxControl } from '@wordpress/components';


/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';

import './editor.scss';
/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	return (
		<div {...blockProps}>
			<InspectorControls key="setting">
				<Panel>
					<PanelBody>
						<CheckboxControl
							label="Open on page load"
							checked={attributes.openOnPageLoad}
							onChange={newValue => setAttributes({ openOnPageLoad: newValue })}
						></CheckboxControl>
					</PanelBody>
				</Panel>
			</InspectorControls>
			<details open="true" >
				<summary><input
					placeholder='Accordion Item Title'
					value={attributes.title}
					onKeyUp={event => {
						event.preventDefault();
					}}
					onChange={e => setAttributes({ title: e.target.value })}
					style={{ "width": "100%" }} /></summary>
				<InnerBlocks />
			</details>
		</div>
	);
}
