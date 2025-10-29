import { FormTokenField, ToggleControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { postExcerptChanged, postTitleChanged } from '../helper/misc';
import { countWordsFromText, getHeadings, wordsFromText } from '../helper/text';
import Base from './Base';
import WarningIcon from './parts/WarningIcon';

const Keywords = ( { postContent, rawContent, paragraphs, words, images, slug } ) => {
	const [ postTitle, setPostTitle ] = useState( '' );
	const [ postExcerpt, setPostExcerpt ] = useState( '' );
	const [ mainKeyword, setMainKeyword ] = useState( SSPro.mainKeyword );
	const [ keywords, setKeywords ] = useState( SSPro.keywords ? SSPro.keywords.split( ';' ) : [] );
	const keywordsList = [];
	const headings = getHeadings( postContent );
	const wordsCount = words.length;
	const MIN_DENSITY = 0.5;
	const MAX_DENSITY = 3;
	let success = true;

	keywords.forEach( keyword => {
		keyword = keyword.trim().toLowerCase();

		if ( '' === keyword ) {
			return;
		}

		const criteria = {};
		const keywordLength = wordsFromText( keyword ).length;
		const count = countWordsFromText( keyword, rawContent );
		const density = count > 0 ? ( wordsCount >= 100 ? ( keywordLength * count / wordsCount ) * 100 : 1 ) : 0;

		criteria.density = {
			density,
			count,
			good: density >= MIN_DENSITY && density <= MAX_DENSITY,
			tooltip: sprintf( __( 'Recommended keyword density is between %.1f%% and %.1f%%', 'slim-seo-pro' ), MIN_DENSITY, MAX_DENSITY )
		};

		const appearInTitle = countWordsFromText( keyword, postTitle ) > 0;

		criteria.title = {
			good: appearInTitle,
			tooltip: appearInTitle ? __( 'Keyword is in the title', 'slim-seo-pro' ) : __( 'Keyword is not in the title', 'slim-seo-pro' )
		};

		const appearInSlug = countWordsFromText( keyword, slug ) > 0;

		criteria.slug = {
			good: appearInSlug,
			tooltip: appearInSlug ? __( 'Keyword is in the slug', 'slim-seo-pro' ) : __( 'Keyword is not in the slug', 'slim-seo-pro' )
		};

		const appearInExcerpt = countWordsFromText( keyword, postExcerpt ) > 0;

		criteria.excerpt = {
			good: appearInExcerpt,
			tooltip: appearInExcerpt ? __( 'Keyword is in the excerpt', 'slim-seo-pro' ) : __( 'Keyword is not in the excerpt', 'slim-seo-pro' )
		};

		const appearInFirstParagraph = paragraphs.length > 0 && countWordsFromText( keyword, paragraphs[ 0 ] ) > 0;

		criteria.firstParagraph = {
			good: appearInFirstParagraph,
			tooltip: appearInFirstParagraph ? __( 'Keyword is in the introductory paragraph', 'slim-seo-pro' ) : __( 'Keyword is not in the introductory paragraph', 'slim-seo-pro' )
		};

		const appearInHeadings = headings.length > 0 && headings.filter( heading => countWordsFromText( keyword, heading ) > 0 ).length > 0;

		criteria.headings = {
			good: appearInHeadings,
			tooltip: appearInHeadings ? __( 'Keyword is in the headings', 'slim-seo-pro' ) : __( 'Keyword is not in the headings', 'slim-seo-pro' )
		};

		const imagesLength = images.length;

		if ( imagesLength > 0 ) {
			const imagesAltCount = images.filter( image => countWordsFromText( keyword, image.alt ) > 0 ).length;

			if ( 0 === imagesAltCount ) {
				criteria.imageAlt = {
					good: false,
					tooltip: __( 'Keyword is not in any image\'s alt text', 'slim-seo-pro' )
				};
			} else if ( imagesLength > 1 && imagesLength === imagesAltCount ) {
				criteria.imageAlt = {
					good: false,
					tooltip: sprintf( __( 'Keyword is in %d/%d images\' alt text', 'slim-seo-pro' ), imagesAltCount, imagesLength )
				};
			} else {
				criteria.imageAlt = {
					good: true,
					tooltip: sprintf( __( 'Keyword is in %d/%d images\' alt text', 'slim-seo-pro' ), imagesAltCount, imagesLength )
				};
			}
		}

		for ( const [ criteriaKey, criteriaValue ] of Object.entries( criteria ) ) {
			if ( keyword === mainKeyword ) {
				if ( 'imageAlt' !== criteriaKey ) {
					success = success && criteriaValue.good;
				}
			} else {
				if ( 'density' === criteriaKey ) {
					success = success && criteriaValue.good;
				}
			}
		}

		keywordsList.push( {
			text: keyword,
			criteria
		} );
	} );

	success = success && keywordsList.length > 0;

	const handleKeywordsChange = newKeywords => setKeywords( newKeywords );
	const changeMainKeyword = kw => checked => setMainKeyword( prev => checked ? kw : '' );

	useEffect( () => {
		postTitleChanged( setPostTitle );
		postExcerptChanged( setPostExcerpt );
	}, [] );

	return (
		<Base title={ __( 'Keywords', 'slim-seo-pro' ) } open={ true } success={ success } hiddenFieldName="good_keywords">
			<FormTokenField
				value={ keywords }
				onChange={ handleKeywordsChange }
				__experimentalShowHowTo={ false }
				tokenizeOnSpace={ false }
			/>
			<div className='ssp-ca-component__description'>{ __( 'Enter one or more keywords to analyze. Separate multiple keywords with commas or Enter.', 'slim-seo-pro' ) }</div>

			<input type="hidden" name="slim_seo_pro[content_analysis][keywords]" value={ keywords.join( ';' ) } />

			{
				keywordsList.length > 0 && (
					<table className='ssp-ca-table'>
						<thead>
							<tr>
								<th align='left'>{ __( 'Keyword', 'slim-seo-pro' ) }</th>
								<th align='left'>{ __( 'Density', 'slim-seo-pro' ) }</th>
								<th align='center'>{ __( 'Title', 'slim-seo-pro' ) }</th>
								<th align='center'>{ __( 'Slug', 'slim-seo-pro' ) }</th>
								<th align='center'>{ __( 'Excerpt', 'slim-seo-pro' ) }</th>
								<th align='center'>{ __( 'Intro', 'slim-seo-pro' ) }</th>
								<th align='center'>{ __( 'Headings', 'slim-seo-pro' ) }</th>
								<th align='center'>{ __( 'Image alt', 'slim-seo-pro' ) }</th>
								<th align='right'>{ __( 'Main keyword', 'slim-seo-pro' ) }</th>
							</tr>
						</thead>
						<tbody>
							{
								keywordsList.map( ( keyword, index ) => {
									return (
										<tr key={ index }>
											<td align='left'>{ keyword.text }</td>
											<td align='left'><WarningIcon args={ keyword.criteria.density }>{ keyword.criteria.density.density.toFixed( 2 ) }% - { keyword.criteria.density.count }x</WarningIcon></td>
											<td align='center'><WarningIcon args={ keyword.criteria.title } /></td>
											<td align='center'><WarningIcon args={ keyword.criteria.slug } /></td>
											<td align='center'><WarningIcon args={ keyword.criteria.excerpt } /></td>
											<td align='center'><WarningIcon args={ keyword.criteria.firstParagraph } /></td>
											<td align='center'><WarningIcon args={ keyword.criteria.headings } /></td>
											<td align='center'>{ keyword.criteria.hasOwnProperty( 'imageAlt' ) ? <WarningIcon args={ keyword.criteria.imageAlt } /> : '' }</td>
											<td align='right'>
												<ToggleControl
													checked={ keyword.text === mainKeyword }
													onChange={ changeMainKeyword( keyword.text ) }
												/>
												<input type="hidden" name="slim_seo_pro[content_analysis][main_keyword]" value={ mainKeyword } />
											</td>
										</tr>
									);
								} )
							}
						</tbody>
					</table>
				)
			}
		</Base>
	);
};

export default Keywords;