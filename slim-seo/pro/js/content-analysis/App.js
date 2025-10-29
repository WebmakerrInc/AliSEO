import { createRoot, useEffect, useState } from '@wordpress/element';
import Keywords from './components/Keywords';
import LinksInternal from './components/LinksInternal';
import Media from './components/Media';
import Paragraphs from './components/Paragraphs';
import Readability from './components/Readability';
import Slug from './components/Slug';
import WordsCount from './components/WordsCount';
import { getImages } from './helper/media';
import { postContentChanged, postSlugChanged } from './helper/misc';
import { getParagraphs, rawText, wordsFromText } from './helper/text';

const App = () => {
	const [ postContent, setPostContent ] = useState( '' );
	const [ slug, setSlug ] = useState( '' );
	const rawContent = rawText( postContent );
	const words = wordsFromText( rawContent );
	const paragraphs = getParagraphs( postContent );
	const images = getImages( postContent );

	useEffect( () => {
		postContentChanged( setPostContent );
		postSlugChanged( setSlug );
	}, [] );

	return (
		<>
			<Keywords
				postContent={ postContent }
				rawContent={ rawContent }
				paragraphs={ paragraphs }
				words={ words }
				images={ images }
				slug={ slug } />
			<Media images={ images } />
			<WordsCount words={ words } images={ images } />
			<LinksInternal postContent={ postContent } />
			<Slug slug={ slug } />
			{ SSPro.siteLocale.startsWith( 'en' ) && <Readability rawContent={ rawContent } /> }
			<Paragraphs paragraphs={ paragraphs } />
		</>
	);
};

const container = document.getElementById( 'content-analysis-app' );
const root = createRoot( container );
root.render( <App /> );