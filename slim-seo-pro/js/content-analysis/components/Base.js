import { Icon } from './parts/WarningIcon';

const Base = ( {
	title,
	success = false,
	open = false,
	hiddenFieldName = '',
	className = '',
	children
} ) => (
	<details className={ `ssp-ca-component ${ className }` } open={ open }>
		<summary>
			<Icon success={ success } />
			{ title }
		</summary>
		{ hiddenFieldName ? <input type="hidden" name={ `slim_seo_pro[content_analysis][${ hiddenFieldName }]` } value={ success ? 1 : 0 } /> : '' }
		{ children }
	</details>
);

export default Base;