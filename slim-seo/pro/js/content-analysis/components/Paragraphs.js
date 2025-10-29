import { __, _n, sprintf } from '@wordpress/i18n';
import { getSentences, scrollToText, shortenParagraph, wordsFromText } from '../helper/text';
import Base from './Base';

const Paragraphs = ( { paragraphs } ) => {
	const paragraphsLength = paragraphs.length;
	const badParagraphs = [];
	const MIN_SENTENCES = 2;
	const MAX_SENTENCES = 10;
	const MAX_WORDS = 200;

	paragraphs.forEach( paragraph => {
		if ( '' === paragraph ) {
			return;
		}

		const sentences = getSentences( paragraph );
		const sentencesCount = sentences.length;
		const wordsCount = wordsFromText( paragraph ).length;

		if ( sentencesCount < MIN_SENTENCES || sentencesCount > MAX_SENTENCES || wordsCount > MAX_WORDS ) {
			badParagraphs.push( {
				text: paragraph,
				short: shortenParagraph( paragraph ),
				firstSentence: sentences[ 0 ],
				sentencesCount,
				wordsCount
			} );
		}
	} );

	const badParagraphsLength = badParagraphs.length;

	const handleClick = paragraph => e => {
		e.preventDefault();

		scrollToText( paragraph.firstSentence );
	};

	return paragraphsLength > 0 && (
		<Base title={ __( 'Paragraphs', 'slim-seo-pro' ) } className='ssp-content-analysis-paragraphs' success={ 0 === badParagraphsLength }>
			{
				badParagraphsLength > 0
				? (
					<>
						<p>{ sprintf( _n( 'This paragraph can be improved to make it more readable.', '%d paragraphs can be improved to make them more readable.', badParagraphsLength, 'slim-seo-pro' ), badParagraphsLength ) }</p>
						<ul>
							{
								badParagraphs.map( ( paragraph, index ) => (
									<li key={ index }>
										<a href="#" onClick={ handleClick( paragraph ) }>{ paragraph.short }</a>
										<p className="description">
											{
												paragraph.sentencesCount < MIN_SENTENCES
												&& __( 'This paragraph has too few sentences. Please write more. Recommended number of sentences is ≥ 2.', 'slim-seo-pro' )
											}
											{
												paragraph.sentencesCount > MAX_SENTENCES
												&& __( 'This paragraph has too many sentences. Please write less. Recommended number of sentences is ≤ 10.', 'slim-seo-pro' )
											}
											{
												paragraph.wordsCount > MAX_WORDS
												&& __( 'This paragraph has too many words. Please write less. Recommended number of words is ≤ 200.', 'slim-seo-pro' )
											}
										</p>
									</li>
								) )
							}
						</ul>
					</>
				)
				: <p>{ __( 'All paragraphs have proper length and are readable.', 'slim-seo-pro' ) }</p>
			}
		</Base>
	);
};

export default Paragraphs;