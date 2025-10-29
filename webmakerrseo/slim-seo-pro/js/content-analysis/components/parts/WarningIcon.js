import { Tooltip } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const Icon = ( { success } ) => success
	? <span className="ssp-success" role="img" aria-label={ __( 'Success', 'slim-seo-pro' ) } />
	: <span className="ssp-warning" role="img" aria-label={ __( 'Warning', 'slim-seo-pro' ) } />;

export default ( { args, children } ) => (
	<Tooltip text={ args.tooltip } delay={ 0 }>
		<span className="ssp-ca-component__warning-icon">
			<Icon success={ args.good } />
			{ children }
		</span>
	</Tooltip>
);