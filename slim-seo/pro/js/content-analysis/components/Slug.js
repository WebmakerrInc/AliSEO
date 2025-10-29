import { __ } from '@wordpress/i18n';
import Base from './Base';

const Slug = ( { slug } ) => {
	const slugLength = slug ? slug.split( '-' ).length : 0;

	return (
		<Base title={ __( 'Slug', 'slim-seo-pro' ) } success={ slugLength <= 5 }>
			<p>{ __( 'Keep the slug short and descriptive. Recommended slug length is ≤ 5 words.', 'slim-seo-pro' ) }</p>
		</Base>
	);
};

export default Slug;