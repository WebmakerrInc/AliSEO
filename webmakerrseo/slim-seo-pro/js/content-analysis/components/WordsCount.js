import { __, sprintf } from '@wordpress/i18n';
import { wordsFromText } from '../helper/text';
import Base from './Base';

const WordsCount = ( { words, images } ) => {
	let wordsCount = words.length;

	if ( images.length ) {
		images.forEach( image => {
			wordsCount += wordsFromText( image.alt ).length;
		} );
	}

	return (
		<Base title={ sprintf( __( 'Words count: %d', 'slim-seo-pro' ), wordsCount ) } success={ wordsCount >= 500 } hiddenFieldName="good_words_count">
			<p>{ __( 'Don\'t create thin content. Recommended content length is ≥ 500 words.', 'slim-seo-pro' ) }</p>
		</Base>
	);
};

export default WordsCount;