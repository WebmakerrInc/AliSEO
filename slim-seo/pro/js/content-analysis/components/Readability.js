import { __, sprintf } from '@wordpress/i18n';
import { getFleschData } from '../helper/misc';
import Base from './Base';

const Readability = ( { rawContent } ) => {
	const { score, result } = getFleschData( rawContent );

	return (
		<Base title={ __( 'Readability', 'slim-seo-pro' ) } success={ score >= 60 }>
			<p>
				{
					sprintf( __( 'Your content readability score is %s, which is %s to read.', 'slim-seo-pro' ), score, result )
				}
				&nbsp;
				{
					score < 60 && sprintf( __( 'Try using shorter sentences and simpler words.', 'slim-seo-pro' ) )
				}
				&nbsp;
				{
					sprintf( __( 'Recommended readability score is ≥ 60.', 'slim-seo-pro' ) )
				}
			</p>
		</Base>
	);
};

export default Readability;