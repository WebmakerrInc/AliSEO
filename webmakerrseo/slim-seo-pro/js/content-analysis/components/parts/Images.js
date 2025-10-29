import { __ } from '@wordpress/i18n';
import Image from './Image';

const Images = ( { images, scrollable = true } ) => (
	<table className='ssp-ca-table'>
		<thead>
			<tr>
				<th align='left'>{ __( 'Image', 'slim-seo-pro' ) }</th>
				<th align='left'>{ __( 'Dimension (px)', 'slim-seo-pro' ) }</th>
				<th align='left'>{ __( 'Size (KB)', 'slim-seo-pro' ) }</th>
				<th align='left'>{ __( 'Filename', 'slim-seo-pro' ) }</th>
			</tr>
		</thead>
		<tbody>
			{
				images.map( ( image, index ) => <Image key={ index } image={ image } scrollable={ scrollable } /> )
			}
		</tbody>
	</table>
);

export default Images;