import { fetcher } from '../../common/fetch';
import { isBlockEditor } from './misc';

const imagesCache = {};

export const getImages = text => {
	let imgTags = text.match( /<img.*?>/gi );

	imgTags = Array.isArray( imgTags ) ? imgTags : [];

	if ( 0 === imgTags.length ) {
		return [];
	}

	const images = imgTags.map( imgTag => {
		return {
			src: imgTag.replace( /.*src="([^"]+)".*/, '$1' ),
			alt: imgTag.replace( /.*alt="([^"]*)".*/, '$1' ).replace( /^.*<img.*$/, '' ).toString().trim(),
			id: imgTag.replace( /.*wp-image-(\d+).*/, '$1' ).replace( /^.*<img.*$/, '' )
		};
	} );

	return images;
};

export const getImageDetail = async image => {
	const cacheKey = image.id || image.src;

	if ( imagesCache.hasOwnProperty( cacheKey ) ) {
		return imagesCache[ cacheKey ];
	}

	const imageDetail = await fetcher( 'content_analysis/image_detail', { id: image.id, src: image.src } );

	imagesCache[ cacheKey ] = imageDetail;

	return imageDetail;
};

export const scrollToImage = imageSrc => {
	let img;

	if ( !isBlockEditor() ) {
		const iframe = document.querySelector( '#content_ifr' );
		const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;

		img = iframeDocument.querySelector( 'img[src="' + imageSrc + '"]' );
	} else {
		img = document.querySelector( '.wp-block-image img[src="' + imageSrc + '"]' );;
	}

	if ( !img ) {
		return;
	}

	img.scrollIntoView( {
		behavior: 'smooth',
		block: 'center',
	} );
};

export const featuredImageChange = callback => {
	if ( isBlockEditor() ) {
		const editor = wp.data.select( 'core/editor' );

		callback( editor.getEditedPostAttribute( 'featured_media' ) );

		wp.data.subscribe( function() {
			callback( editor.getEditedPostAttribute( 'featured_media' ) );
		} );

		return;
	}

	const thumbnail = document.querySelector( '#_thumbnail_id' );

	if ( thumbnail ) {
		if ( -1 != thumbnail.value ) {
			callback( thumbnail.value );
		}

		const featuredImageContainer = document.querySelector( '#postimagediv .inside' );

		if ( featuredImageContainer ) {
			let previousImageSrc = featuredImageContainer.querySelector( 'img' )?.src || null;

			const observer = new MutationObserver( mutationsList => {
				mutationsList.forEach( mutation => {
					if ( 'childList' === mutation.type ) {
						const currentImageSrc = featuredImageContainer.querySelector( 'img' )?.src || null;

						if ( previousImageSrc !== currentImageSrc ) {
							previousImageSrc = currentImageSrc;

							const currentThumbnailValue = document.querySelector( '#_thumbnail_id' ).value;

							callback( -1 == currentThumbnailValue ? '' : currentThumbnailValue );
						}
					}
				} );
			} );

			observer.observe( featuredImageContainer, { childList: true, subtree: true } );
		}
	}
};

export const checkFilename = filename => {
	if ( !filename ) {
		return false;
	}

	const lastDotIndex = filename.lastIndexOf( '.' );

	filename = lastDotIndex !== -1 ? filename.substring( 0, lastDotIndex ) : filename;

	const wordsToRemove = [ 'img', 'image', 'pic', 'picture' ];
	const regexPattern = wordsToRemove.map( word => `\\b${ word }\\b` ).join( '|' );
	const regex = new RegExp( `(${ regexPattern }|\\b\\d+\\b|\\W)`, 'gi' );

	return '' !== filename.replace( regex, '' ).replace( /\s+/g, ' ' ).trim();
};