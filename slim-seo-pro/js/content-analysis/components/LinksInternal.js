import { RawHTML } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { linksFromText } from '../helper/text';
import Base from './Base';

const LinksInternal = ( { postContent } ) => {
	const links = linksFromText( postContent );
	const internalOutboundLinks = links.filter( link => {
		return 0 === link.indexOf( SSPro.homeURL ) || 0 === link.indexOf( '/' ) || 0 === link.indexOf( '#' );
	} );
	const internalOutboundLinksLength = internalOutboundLinks.length;

	return (
		<Base title={ __( 'Internal Links', 'slim-seo-pro' ) } success={ internalOutboundLinksLength > 0 }>
			{
				internalOutboundLinksLength > 0
				? <p>{ sprintf( _n( '%d outbound internal link was found.', '%d outbound internal links were found.', internalOutboundLinksLength, 'slim-seo-pro' ), internalOutboundLinksLength ) }</p>
				: <p>{ __( 'No outbound internal links were found. It\'s recommended to add internal links to other posts to help users find related and useful content, assist search engines in understanding the context of your content, discover new posts, and increase the link juice of your posts.', 'slim-seo-pro' ) }</p>
			}
			{
				SSPro.SSLMActivated
				? <RawHTML>{ __( 'To see internal links pointing to this post, please check the <strong>Internal Inbound</strong> in the <strong>Link Manager</strong> tab.', 'slim-seo-pro' ) }</RawHTML>
				: <RawHTML>{ __( 'To see internal links pointing to this post, please enable the <strong>Link Manager</strong> feature.', 'slim-seo-pro' ) }</RawHTML>
			}
		</Base>
	);
};

export default LinksInternal;