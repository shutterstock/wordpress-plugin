/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
export default function save(props) {
  return (
    <img
      alt={__('Alt text')}
      className="wp-block-shutterstock-shutterstock-block"
      src={props.attributes?.img?.licensedImageUrl || props.attributes?.img?.preview_1500?.url}
    />
  );
}
