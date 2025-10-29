import { __ } from '@wordpress/i18n';
import rs from 'text-readability';
import { fetcher } from '../../common/fetch';

export const isBlockEditor = () => document.body.classList.contains( 'block-editor-page' );

export const postContentChanged = callback => {
	if ( SSPro.builtWithBuilder ) {
		fetcher( 'content_analysis/builder_content', { post_id: SSPro.postID } ).then( response => callback( response ) );

		return;
	}

	if ( isBlockEditor() ) {
		const editor = wp.data.select( 'core/editor' );

		callback( editor.getEditedPostContent() );

		wp.data.subscribe( function() {
			callback( editor.getEditedPostContent() );
		} );

		return;
	}

	jQuery( document ).on( 'tinymce-editor-init', ( event, editor ) => {
		if ( 'content' !== editor.id ) {
			return;
		}

		callback( editor.getContent() );

		editor.on( 'input keyup mouseenter change', e => {
			callback( editor.getContent() );
		} );
	} );

	if ( 'undefined' !== typeof EasyMDE ) {
		callback( ' ' );
	}
};

const blockEditorPostAttributeChanged = ( attr, callback ) => {
	const editor = wp.data.select( 'core/editor' );
	const value = editor.getEditedPostAttribute( attr );

	if ( value ) {
		callback( value );
	}

	wp.data.subscribe( function() {
		const value = editor.getEditedPostAttribute( attr );

		if ( value ) {
			callback( value );
		}
	} );
};

export const postSlugChanged = callback => {
	if ( isBlockEditor() ) {
		blockEditorPostAttributeChanged( 'slug', callback );

		return;
	}

	callback( jQuery( '#post_name' ).val() || '' );

	jQuery( document ).on( 'click', '#edit-slug-buttons .save', e => {
		callback( jQuery( '#post_name' ).val() || '' );
	} );
};

export const postTitleChanged = callback => {
	if ( isBlockEditor() ) {
		blockEditorPostAttributeChanged( 'title', callback );

		return;
	}

	callback( jQuery( '#title' ).val() || '' );

	jQuery( document ).on( 'input', '#title', e => {
		callback( jQuery( '#title' ).val() || '' );
	} );
};

export const postExcerptChanged = callback => {
	if ( isBlockEditor() ) {
		blockEditorPostAttributeChanged( 'excerpt', callback );

		return;
	}

	callback( jQuery( '#excerpt' ).val() || '' );

	jQuery( document ).on( 'input', '#excerpt', e => {
		callback( jQuery( '#excerpt' ).val() || '' );
	} );
};

export const getFleschData = text => {
	const fleschLevels = [
		{
			min: 90,
			text: __( 'very easy', 'slim-seo-pro' )
		},
		{
			min: 80,
			text: __( 'easy', 'slim-seo-pro' )
		},
		{
			min: 70,
			text: __( 'fairly easy', 'slim-seo-pro' )
		},
		{
			min: 60,
			text: __( 'okay', 'slim-seo-pro' )
		},
		{
			min: 50,
			text: __( 'fairly difficult', 'slim-seo-pro' )
		},
		{
			min: 30,
			text: __( 'difficult', 'slim-seo-pro' )
		},
		{
			min: 0,
			text: __( 'very difficult', 'slim-seo-pro' )
		}
	];

	// Remove extra spaces (like after stripping shortcodes or HTML tags from page builder content).
	text = text.replace( /(\s){2,}/g, '$1' );
	let score = rs.fleschReadingEase( text );

	if ( isNaN( score ) || score < 0 ) {
		score = 0;
	}
	const level = fleschLevels.find( lv => score >= lv.min );

	return {
		score,
		result: level.text
	};
};