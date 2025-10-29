import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkFilename, getImageDetail, scrollToImage } from '../../helper/media';
import WarningIcon from '../parts/WarningIcon';

const Image = ( { image, scrollable = true } ) => {
	const [ imageDetail, setImageDetail ] = useState( [] );
	const filenameGood = checkFilename( imageDetail?.filename );
	const imageSrc = image.src ?? imageDetail.src;
	const imageWidth = imageDetail?.width;
	const imageHeight = imageDetail?.height;
	const imageSize = imageDetail?.size;

	const handleClick = imageSrc => e => {
		e.preventDefault();

		if ( !scrollable ) {
			return;
		}

		scrollToImage( imageSrc );
	};

	useEffect( () => {
		const fetchImageDetail = async () => {
			const imgDetail = await getImageDetail( image );

			setImageDetail( imgDetail );
		};

		fetchImageDetail();
	}, [ image ] );

	return (
		<tr onClick={ handleClick( imageSrc ) } className="ssp-ca-image">
			<td>{ imageSrc && <img src={ imageSrc } /> }</td>
			<td>
				{
					imageWidth && imageHeight
						? (
							<WarningIcon
								args={ {
									good: imageWidth < 1920 && imageHeight < 1920,
									tooltip: __( 'Recommended image dimension is ≤ 1920px in width and height', 'slim-seo-pro' )
								} }
							>
								{ `${ imageWidth }x${ imageHeight }` }
							</WarningIcon>
						)
						: __( 'N/A', 'slim-seo-pro' )
				}
			</td>
			<td>
				{
					imageSize
						? (
							<WarningIcon
								args={ {
									good: imageSize < 2048,
									tooltip: __( 'Recommended image size is < 2MB', 'slim-seo-pro' )
								} }
							>
								{ imageSize }
							</WarningIcon>
						)
						: __( 'N/A', 'slim-seo-pro' )
				}
			</td>
			<td>
				<WarningIcon
					args={ {
						good: filenameGood,
						tooltip: __( 'Recommended filename is in lowercase and without spaces', 'slim-seo-pro' )
					} }
				>
					{ imageDetail?.filename }
				</WarningIcon>
			</td>
		</tr>
	);
};

export default Image;