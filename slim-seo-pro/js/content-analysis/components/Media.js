import { useEffect, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { featuredImageChange } from '../helper/media';
import Base from './Base';
import Images from './parts/Images';

const Media = ( { images } ) => {
	const [ featuredImage, setFeaturedImage ] = useState( '' );

	const imagesLength = images.length;
	const noAltImages = 0 === imagesLength ? [] : images.filter( image => '' === image.alt );
	const noAltImagesLength = noAltImages.length;

	const featuredImageOk = !SSPro.supportThumbnail || Boolean( featuredImage );
	const success = featuredImageOk && noAltImagesLength === 0;

	useEffect( () => {
		if ( SSPro.supportThumbnail ) {
			featuredImageChange( setFeaturedImage );
		}
	}, [] );

	return (
		<Base title={ __( 'Media', 'slim-seo-pro' ) } success={ success } hiddenFieldName="good_media">
			{
				// Display this message only when featured image is ok.
				featuredImageOk && (
					imagesLength === 0
						? <p>{ __( 'This post does not have any images.', 'slim-seo-pro' ) }</p>
						: ( noAltImagesLength === 0 && <p>{ __( 'All images have proper sizes and alt text.', 'slim-seo-pro' ) }</p> )
				)
			}

			{
				SSPro.supportThumbnail && (
					featuredImage
						? (
							<>
								<p><strong>{ __( 'Featured image', 'slim-seo-pro' ) }</strong></p>
								<Images images={ [ { id: featuredImage } ] } scrollable={ false } />
							</>
						)
						: <p>{ __( 'Featured image is not set. It\'s recommended to set a featured image for this post.', 'slim-seo-pro' ) }</p>
				)
			}

			{
				noAltImagesLength > 0 && (
					<>
						<p><strong>{ __( 'Post content', 'slim-seo-pro' ) }</strong></p>
						<p>{ sprintf( _n( '%d image has no alt text. Please add alt text to the image below.', '%d images have no alt text. Please add alt text to the images below.', noAltImagesLength, 'slim-seo-pro' ), noAltImagesLength ) }</p>
						<Images images={ noAltImages } />
					</>
				)
			}
		</Base>
	);
};

export default Media;