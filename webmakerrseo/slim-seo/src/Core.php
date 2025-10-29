<?php
namespace SlimSEO;

class Core {
        public function setup(): void {
                $basename = defined( 'SLIM_SEO_PLUGIN_BASENAME' ) ? SLIM_SEO_PLUGIN_BASENAME : 'slim-seo/slim-seo.php';

                add_filter( "plugin_action_links_{$basename}", [ $this, 'add_plugin_action_links' ] );
                add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );
        }

        public function add_plugin_action_links( array $links ): array {
                $links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=slim-seo' ) ) . '">' . __( 'Settings', 'slim-seo' ) . '</a>';

                return $links;
        }

        public function add_plugin_meta_links( array $meta, string $file ) {
                $basename = defined( 'SLIM_SEO_PLUGIN_BASENAME' ) ? SLIM_SEO_PLUGIN_BASENAME : 'slim-seo/slim-seo.php';
                if ( $file !== $basename ) {
                        return $meta;
                }

                $meta[] = '<a href="https://webmakerr.com/docs/webmakerrseo" target="_blank">' . esc_html__( 'Documentation', 'slim-seo' ) . '</a>';
                $meta[] = '<a href="https://wordpress.org/support/plugin/slim-seo/reviews/?filter=5" target="_blank" title="' . esc_html__( 'Rate Slim SEO on WordPress.org', 'slim-seo' ) . '" style="color: #ffb900">'
                        . str_repeat( '<span class="dashicons dashicons-star-filled" style="font-size: 16px; width:16px; height: 16px"></span>', 5 )
                        . '</a>';

                return $meta;
        }
}
