<?php

function feedtune_manage_default_feeds()
{
    $enabled_feeds = get_option('feedtune_enabled_feeds', []);

    if (empty($enabled_feeds['rss'])) {
        remove_action('do_feed_rss', 'do_feed_rss', 10, 1);
    }

    if (empty($enabled_feeds['rss2'])) {
        remove_action('do_feed_rss2', 'do_feed_rss2', 10, 1);
    }

    if (empty($enabled_feeds['atom'])) {
        remove_action('do_feed_atom', 'do_feed_atom', 10, 1);
    }

    if (empty($enabled_feeds['comments'])) {
        remove_action('do_feed_rss2_comments', 'do_feed_rss2_comments', 10, 1);
        remove_action('do_feed_atom_comments', 'do_feed_atom_comments', 10, 1);
    }

    // Remove feed links from HTML if disabled
    add_action('wp_head', function () use ($enabled_feeds) {
        if (empty($enabled_feeds['rss']) && empty($enabled_feeds['rss2']) && empty($enabled_feeds['atom'])) {
            remove_action('wp_head', 'feed_links', 2);
        }

        if (empty($enabled_feeds['comments'])) {
            remove_action('wp_head', 'feed_links_extra', 3);
        }
    }, 1);
}

function feedtune_handle_feed_redirects()
{
    $redirect_option = get_option('feedtune_feed_redirect', 'none');
    $redirect_http_code = intval(get_option('feedtune_redirect_http_code', 301));

    add_action('template_redirect', function () use ($redirect_option, $redirect_http_code) {
        if (is_feed()) {
            global $wp;

            if ($redirect_option === 'parent') {
                // For non-comment feeds
                if (!is_comment_feed()) {
                    // Remove the 'feed' suffix and redirect to the parent URL
                    $parent_url = preg_replace('/\/feed\/?$/', '', home_url($wp->request));
                    if (!empty($parent_url)) {
                        wp_redirect($parent_url, $redirect_http_code);
                        exit;
                    }
                }
                // For comment feeds, redirect to the post URL (without 'feed')
                elseif (is_comment_feed()) {
                    $post_id = get_queried_object_id();
                    if ($post_id) {
                        $post_url = get_permalink($post_id);
                        if ($post_url) {
                            wp_redirect($post_url, $redirect_http_code);
                            exit;
                        }
                    }
                }
                // Default fallback for feeds
                wp_redirect(home_url(), $redirect_http_code);
                exit;
            } elseif ($redirect_option === 'home') {
                wp_redirect(home_url(), $redirect_http_code);
                exit;
            } elseif ($redirect_option === '404') {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                nocache_headers();

                include(get_query_template('404'));
                exit;
            }
        }
    });
}
