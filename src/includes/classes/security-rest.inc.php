<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's Security Gate for REST requests.
 *
 * Copyright: © 2009-2024
 * {@link https://wpsharks.com/ WP Sharks}
 * (coded in the Switzerland)
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\Security
 * @since 240312
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_security_rest'))
{
	/**
	 * s2Member's Security Gate for REST requests.
	 *
	 * @package s2Member\Security
	 * @since 240312
	 */
	class c_ws_plugin__s2member_security_rest
	{
		/**
		 * Protects the resource selected by WordPress after route matching and validation.
		 *
		 * @package s2Member\Security
		 * @since 240312
		 *
		 * @attaches-to ``add_filter('rest_request_before_callbacks');``
		 *
		 * @param mixed $response Original response from earlier filters.
		 * @param array $handler Matched WordPress REST endpoint handler.
		 * @param WP_REST_Request $request Validated REST request.
		 *
		 * @return mixed Original response, or a membership WP_Error with HTTP 403.
		 */
		public static function security_gate($response, $handler, $request)
		{
			if(is_wp_error($response) || empty($handler['callback']) || !is_array($handler['callback']))
				return $response;

			//261005.2320 Use the matched controller and validated ID; route segments and query IDs can describe different resources.
			$controller = $handler['callback'][0];
			$id = (int)$request->get_param('id');
			if(!$id)
				return $response;

			if($controller instanceof WP_REST_Posts_Controller)
			{
				$post = get_post($id);
				$type = $post ? get_post_type_object($post->post_type) : NULL;
				if($type && self::controller_owns_resource($type, $controller, $request))
					return self::check_post_access($post, $response);
			}
			else if($controller instanceof WP_REST_Terms_Controller)
			{
				$term = get_term($id);
				$taxonomy = $term && !is_wp_error($term) ? get_taxonomy($term->taxonomy) : NULL;
				if($taxonomy && self::controller_owns_resource($taxonomy, $controller, $request))
					return self::check_resource_access($term->taxonomy === 'category' ? 'categories' : ($term->taxonomy === 'post_tag' ? 'tags' : 'terms'), $id, $response);
			}
			else if($controller instanceof WP_REST_Comments_Controller && ($comment = get_comment($id)) && ($post = get_post($comment->comment_post_ID)))
				return self::check_post_access($post, $response);

			return $response;
		}

		/**
		 * Determines whether the matched REST controller owns the requested resource.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @param WP_Post_Type|WP_Taxonomy $resource Registered post type or taxonomy.
		 * @param WP_REST_Controller $controller Matched endpoint controller.
		 * @param WP_REST_Request $request Validated REST request.
		 *
		 * @return bool True if the controller owns the resource; false otherwise.
		 */
		private static function controller_owns_resource($resource, $controller, $request)
		{
			if(method_exists($resource, 'get_rest_controller'))
				return $resource->get_rest_controller() === $controller;
			//261005.2335 Older REST-enabled WordPress releases expose the registered base but lack these accessors.
			if(empty($resource->show_in_rest))
				return FALSE;
			$namespace = !empty($resource->rest_namespace) ? $resource->rest_namespace : 'wp/v2';
			$base = !empty($resource->rest_base) ? $resource->rest_base : $resource->name;
			return (bool)preg_match('/^'.preg_quote('/'.$namespace.'/'.$base, '/').'\/\d+\/?$/', $request->get_route());
		}

		/**
		 * Checks REST access to a post, page, or comment parent.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @param WP_Post $post Existing post whose membership restrictions are checked.
		 * @param mixed $response Original response to preserve when access is allowed.
		 *
		 * @return mixed Original response, or a membership WP_Error with HTTP 403.
		 */
		private static function check_post_access($post, $response = NULL)
		{
			$type = $post->post_type === 'page' ? 'pages' : 'posts';
			return self::check_resource_access($type, (int)$post->ID, $response, $post->post_type);
		}

		/**
		 * Registers REST collection and search access filters for exposed resources.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @attaches-to ``add_action('rest_api_init');``
		 *
		 * @return void
		 */
		public static function register_collection_filters()
		{
			foreach(get_post_types(array('show_in_rest' => TRUE), 'names') as $post_type)
				add_filter('rest_'.$post_type.'_query', array(__CLASS__, 'filter_post_query'), PHP_INT_MAX, 2);
			foreach(get_taxonomies(array('show_in_rest' => TRUE), 'names') as $taxonomy)
				add_filter('rest_'.$taxonomy.'_query', array(__CLASS__, 'filter_term_query'), PHP_INT_MAX, 2);
			add_filter('rest_post_search_query', array(__CLASS__, 'filter_post_query'), PHP_INT_MAX, 2);
			add_filter('rest_term_search_query', array(__CLASS__, 'filter_term_query'), PHP_INT_MAX, 2);
			add_filter('rest_comment_query', array(__CLASS__, 'filter_comment_query'), PHP_INT_MAX, 2);
		}

		/**
		 * Determines whether configured restrictions require a REST collection access check.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @param bool $posts Include post/page, Specific Post/Page, and Custom Capability rules?
		 *
		 * @return bool True if an applicable restriction may deny access to collection items.
		 */
		private static function needs_query_check($posts = FALSE)
		{
			$o = $GLOBALS['WS_PLUGIN__']['s2member']['o'];
			if(empty($o['membership_options_page']))
				return FALSE;
			$user = wp_get_current_user();
			for($n = $GLOBALS['WS_PLUGIN__']['s2member']['c']['levels']; $n >= 0; $n--)
				foreach($posts ? array('posts', 'pages', 'catgs', 'ptags', 'ruris') : array('catgs', 'ptags', 'ruris') as $kind)
					if(!empty($o['level'.$n.'_'.$kind]) && !$user->has_cap('access_s2member_level'.$n))
						return TRUE;
			if(!empty($o['login_redirection_override']) && !$user->has_cap('access_s2member_level0'))
				return TRUE;
			if($posts)
			{
				if((!empty($o['login_welcome_page']) || !empty($o['file_download_limit_exceeded_page'])) && !$user->has_cap('access_s2member_level0'))
					return TRUE;
				if(!empty($o['specific_ids']) || c_ws_plugin__s2member_utils_gets::get_unavailable_singular_ids_with_ccaps($user->ID ? $user : FALSE))
					return TRUE;
			}
			return FALSE;
		}

		/**
		 * Excludes protected posts before REST collection/search pagination and counts.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @attaches-to ``add_filter('rest_{$post_type}_query'); add_filter('rest_post_search_query');``
		 *
		 * @param array $args Prepared WP_Query arguments.
		 * @param WP_REST_Request $request Validated collection or search request.
		 *
		 * @return array Query arguments with inaccessible posts excluded.
		 */
		public static function filter_post_query($args, $request)
		{
			if(!self::needs_query_check(TRUE))
				return $args;
			$scan = $args;
			//261005.2324 Scan only matching candidates in bounded batches; presentation filters must not decide REST access.
			$scan['fields'] = 'ids';
			$scan['posts_per_page'] = 200;
			$scan['nopaging'] = FALSE;
			$scan['paged'] = 1;
			$scan['orderby'] = 'ID';
			$scan['order'] = 'ASC';
			$scan['suppress_filters'] = TRUE;
			$scan['no_found_rows'] = TRUE;
			$excluded = array();
			for($offset = 0; ; $offset += 200)
			{
				$scan['offset'] = $offset;
				$ids = get_posts($scan);
				if(!$ids)
					break;
				_prime_post_caches($ids, TRUE, TRUE);
				foreach($ids as $id)
					if(($post = get_post($id)) && is_wp_error(self::check_post_access($post)))
						$excluded[] = (int)$id;
				if(count($ids) < 200)
					break;
			}
			return self::exclude_query_ids($args, $excluded, 'post__in', 'post__not_in');
		}

		/**
		 * Excludes protected terms from REST taxonomy collections and term search.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @attaches-to ``add_filter('rest_{$taxonomy}_query'); add_filter('rest_term_search_query');``
		 *
		 * @param array $args Prepared WP_Term_Query arguments.
		 * @param WP_REST_Request $request Validated collection or search request.
		 *
		 * @return array Query arguments with inaccessible terms excluded.
		 */
		public static function filter_term_query($args, $request)
		{
			if(!self::needs_query_check())
				return $args;
			$scan = $args;
			$scan['number'] = 0;
			$scan['offset'] = 0;
			$scan['fields'] = 'all';
			if(!empty($scan['post']))
				$scan['object_ids'] = $scan['post'];
			$terms = get_terms($scan);
			$excluded = array();
			if(!is_wp_error($terms))
				foreach($terms as $term)
					if(is_wp_error(self::check_resource_access($term->taxonomy === 'category' ? 'categories' : ($term->taxonomy === 'post_tag' ? 'tags' : 'terms'), (int)$term->term_id)))
						$excluded[] = (int)$term->term_id;
			return self::exclude_query_ids($args, $excluded, 'include', 'exclude');
		}

		/**
		 * Excludes comments whose parent posts are inaccessible to the current user.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @attaches-to ``add_filter('rest_comment_query');``
		 *
		 * @param array $args Prepared WP_Comment_Query arguments.
		 * @param WP_REST_Request $request Validated comment collection request.
		 *
		 * @return array Query arguments with comments on inaccessible posts excluded.
		 */
		public static function filter_comment_query($args, $request)
		{
			if(!self::needs_query_check(TRUE))
				return $args;
			$scan = $args;
			$scan['number'] = 200;
			$scan['fields'] = 'all';
			$scan['count'] = FALSE;
			$scan['orderby'] = 'comment_ID';
			$scan['order'] = 'ASC';
			$scan['hierarchical'] = FALSE;
			$excluded = $parents = array();
			for($offset = 0; ; $offset += 200)
			{
				$scan['offset'] = $offset;
				$comments = get_comments($scan);
				_prime_post_caches(array_unique(wp_list_pluck($comments, 'comment_post_ID')), TRUE, TRUE);
				foreach($comments as $comment)
				{
					$parent = (int)$comment->comment_post_ID;
					if(!isset($parents[$parent]))
						$parents[$parent] = ($post = get_post($parent)) && is_wp_error(self::check_post_access($post));
					if($parents[$parent])
						$excluded[] = (int)$comment->comment_ID;
				}
				if(count($comments) < 200)
					break;
			}
			return self::exclude_query_ids($args, $excluded, 'comment__in', 'comment__not_in');
		}

		/**
		 * Applies exclusions without letting an exhausted include list expand the query.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @param array $args Prepared collection query arguments.
		 * @param int[] $excluded Resource IDs denied by membership restrictions.
		 * @param string $include_key Query argument containing explicit included IDs.
		 * @param string $exclude_key Query argument containing excluded IDs.
		 *
		 * @return array Query arguments with exclusions and a restrictive include list.
		 */
		private static function exclude_query_ids($args, $excluded, $include_key, $exclude_key)
		{
			if(!$excluded)
				return $args;
			$args[$exclude_key] = array_values(array_unique(array_merge(isset($args[$exclude_key]) ? wp_parse_id_list($args[$exclude_key]) : array(), $excluded)));
			if(!empty($args[$include_key]))
			{
				$included = array_values(array_diff(wp_parse_id_list($args[$include_key]), $args[$exclude_key]));
				//261005.2324 Keep an exhausted include list restrictive instead of expanding it to the whole collection.
				$args[$include_key] = $included ? $included : array(0);
			}
			return $args;
		}

		/**
		 * Checks the REST restriction policy against a canonical content resource.
		 *
		 * @package s2Member\Security
		 * @since 261005
		 *
		 * @param string $type Resource kind: posts, pages, categories, tags, or terms.
		 * @param int $id Existing post or term ID.
		 * @param mixed $response Original response to preserve when access is allowed.
		 * @param string $post_type Actual registered post type for post/page restrictions.
		 *
		 * @return mixed Original response, or a membership WP_Error with HTTP 403.
		 */
		private static function check_resource_access($type, $id, $response = NULL, $post_type = '')
		{
      $s2_options = $GLOBALS['WS_PLUGIN__']['s2member']['o'];
      $s2_levels  = $GLOBALS['WS_PLUGIN__']['s2member']['c']['levels'];
      $ci         = $s2_options['ruris_case_sensitive'] ? '' : 'i';
    
      $user = (is_user_logged_in() && is_object($user = wp_get_current_user()) && !empty($user->ID)) ? $user : FALSE;
    
    
      // Skip early?
      if (empty($s2_options['membership_options_page'])
      ||  empty($id)
      ||  ($type === 'pages' && (int)$s2_options['membership_options_page'] === $id)) {
        return $response;
      }
    
    
      // Get the regular path for this content, to check URI restriction
      $path = ''; // Only canonical resource URIs participate in content restrictions.
      if (in_array($type, ['posts', 'pages'], true)) {
        $permalink = get_permalink($id);
        if ($permalink && !is_wp_error($permalink)) {
            $path = c_ws_plugin__s2member_utils_urls::parse_uri($permalink);
        }
      } elseif ($type === 'categories') {
          $term_link = get_term_link($id, 'category');
          if (!is_wp_error($term_link)) {
              $path = c_ws_plugin__s2member_utils_urls::parse_uri($term_link);
          }
      } elseif ($type === 'tags') {
          $term_link = get_term_link($id, 'post_tag');
          if (!is_wp_error($term_link)) {
              $path = c_ws_plugin__s2member_utils_urls::parse_uri($term_link);
          }
      }
    
    
      else if($type === 'terms' && ($term = get_term($id)) && !is_wp_error($term)) {
          $term_link = get_term_link($term);
          if (!is_wp_error($term_link)) {
              $path = c_ws_plugin__s2member_utils_urls::parse_uri($term_link);
          }
      }

      //261005.2320 Page exemptions cannot be inherited by terms with the same numeric ID.
      $page_id = $type === 'pages' ? $id : 0;

      // Login welcome page
      if($s2_options['login_welcome_page'] 
      && $page_id && $page_id === (int)$s2_options['login_welcome_page']
      && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
      && (!$user || !$user->has_cap('access_s2member_level0')) 
      && $page_id !== (int)$s2_options['membership_options_page'])
        return new WP_Error('rest_forbidden', __('You do not have access to this page. [sys level 0]', 's2member'), array('status' => 403));
    
      // Login redirection override
      else if($s2_options['login_redirection_override'] 
      && ($login_redirection_uri = c_ws_plugin__s2member_login_redirects::login_redirection_uri($user, 'root-returns-false')) 
      && preg_match('/^'.preg_quote($login_redirection_uri, '/').'$/'.$ci, $path) 
      && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
      && (!$user || !$user->has_cap('access_s2member_level0')) 
      && $page_id !== (int)$s2_options['membership_options_page'])
        return new WP_Error('rest_forbidden', __('You do not have access to this page. [sys level 0]', 's2member'), array('status' => 403));
    
      // File download limit exceeded page
      else if($s2_options['file_download_limit_exceeded_page'] 
      && $page_id && $page_id === (int)$s2_options['file_download_limit_exceeded_page']
      && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
      && (!$user || !$user->has_cap('access_s2member_level0')) 
      && $page_id !== (int)$s2_options['membership_options_page'])
        return new WP_Error('rest_forbidden', __('You do not have access to this page. [sys level 0]', 's2member'), array('status' => 403));
    
      // Do NOT protect Systematics (exceptions above)
      if (c_ws_plugin__s2member_systematics_sp::is_systematic_use_specific_page($page_id, $path)) {
        return $response;
      }
    
    
    
      // Categories & other inclusives.
      if($type == 'categories') {
        for($n = $s2_levels; $n >= 0; $n--)
        {
          // "all"
          if($s2_options['level'.$n.'_catgs'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this category. [catg level %d]', 's2member'), $n), array('status' => 403));
    
          // This specific categ
          else if($s2_options['level'.$n.'_catgs'] 
          && in_array($id, ($catgs = preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_catgs']))) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this category. [catg level %d]', 's2member'), $n), array('status' => 403));
    
          // Check parent categs
          else if($s2_options['level'.$n.'_catgs'] /* Check Category ancestry. */)
            foreach(preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_catgs']) as $catg)
              if($catg 
              && cat_is_ancestor_of($catg, $id) 
              && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
              && (!$user || !$user->has_cap('access_s2member_level'.$n)))
                return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this category. [catg level %d]', 's2member'), $n), array('status' => 403));
          }
      }
    
    
      // Post/Page Tags & other inclusives.
      else if($type == 'tags') {
        for($n = $s2_levels; $n >= 0; $n--) // Tag Level restrictions. Go through each Level.
        {
          // "all"
          if($s2_options['level'.$n.'_ptags'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this tag. [ptag level %d]', 's2member'), $n), array('status' => 403));
    
          // This tag
          else if($s2_options['level'.$n.'_ptags'] 
          && ($level_ptags = preg_split('/['."\r\n\t".';,]+/', $s2_options['level'.$n.'_ptags']))
          && ($tag = get_tag($id))
          && !empty($tag->name)
          && (in_array($tag->name, $level_ptags) || in_array($tag->slug, $level_ptags) || in_array((string)$tag->term_id, $level_ptags, TRUE))
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this tag. [ptag level %d]', 's2member'), $n), array('status' => 403));
        }
      }
    
    
      // All Posts & other inclusives.
      else if($type == 'posts') {
        // Post Level restrictions.
        for($n = $s2_levels; $n >= 0; $n--) 
        {
          if($s2_options['level'.$n.'_posts'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [post level %d]', 's2member'), $n), array('status' => 403));
    
          // what about custom post types?
          else if(strpos($s2_options['level'.$n.'_posts'], 'all-') !== FALSE 
          && (in_array('all-'.$post_type, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts'])) || in_array('all-'.$post_type.'s', preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts'])))
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [post level %d]', 's2member'), $n), array('status' => 403));
    
          // a specific post by ID
          else if($s2_options['level'.$n.'_posts'] 
          && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts'])) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [post level %d]', 's2member'), $n), array('status' => 403));
        }
    
        // Category Level restrictions.
        for($n = $s2_levels; $n >= 0; $n--)
        {
          if($s2_options['level'.$n.'_catgs'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [catg level %d]', 's2member'), $n), array('status' => 403));
    
          else if($s2_options['level'.$n.'_catgs'] 
          && ($catgs = preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_catgs']))
          && (in_category($catgs, $id) || c_ws_plugin__s2member_utils_conds::in_descendant_category($catgs, $id)) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [catg level %d]', 's2member'), $n), array('status' => 403));
        }
    
        if(has_term('', 'post_tag', $id)) // Here we take a look to see if this Post has any Tags. If so, we need to run the full set of routines against Tags also.
        {
          for($n = $s2_levels; $n >= 0; $n--) // Tag Level restrictions.
          {
            if($s2_options['level'.$n.'_ptags'] === 'all' 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [ptag level %d]', 's2member'), $n), array('status' => 403));
    
            else if($s2_options['level'.$n.'_ptags'] 
            && has_tag(preg_split('/['."\r\n\t".';,]+/', $s2_options['level'.$n.'_ptags']), $id) 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [ptag level %d]', 's2member'), $n), array('status' => 403));
          }
        }
    
        if(is_array($ccaps_req = get_post_meta($id, 's2member_ccaps_req', TRUE)) 
        && !empty($ccaps_req) 
        && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted'))
        {
          foreach($ccaps_req as $ccap) // The ``$user`` MUST satisfy ALL Custom Capability requirements. Stored as an array of Custom Capabilities.
            if(strlen($ccap) 
            && (!$user || !$user->has_cap('access_s2member_ccap_'.$ccap)) /* Does this ``$user``, have this Custom Capability? */)
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [ccap %s]', 's2member'), $ccap), array('status' => 403));
        }
    
        if($s2_options['specific_ids'] 
        && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['specific_ids'])) 
        && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
        && !c_ws_plugin__s2member_sp_access::sp_access($id, 'read-only'))
          return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [sp %d]', 's2member'), $id), array('status' => 403));
    
      }
    
    
      // All Pages & other inclusives.
      else if($type == 'pages') {
        for($n = $s2_levels; $n >= 0; $n--) // Page Level restrictions. Go through each Level.
        {
          // "all"
          if($s2_options['level'.$n.'_pages'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [page level %d]', 's2member'), $n), array('status' => 403));
    
          //261006.0145 The Posts restriction setting also accepts "all-page" and "all-pages" to protect every page.
          else if(strpos($s2_options['level'.$n.'_posts'], 'all-') !== FALSE
          && (in_array('all-page', preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts'])) || in_array('all-pages', preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts']))) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [page level %d]', 's2member'), $n), array('status' => 403));
    
          // Current page ID
          else if($s2_options['level'.$n.'_pages'] 
          && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_pages'])) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [page level %d]', 's2member'), $n), array('status' => 403));
        }
    
        // Tag restriction
        if(has_term('', 'post_tag', $id)) // Here we take a look to see if this Page has any Tags. If so, we need to run the full set of routines against Tags also.
        {
          for($n = $s2_levels; $n >= 0; $n--) // Tag Level restrictions (possibly through Page Tagger). Go through each Level.
          {
            // "all" tags
            if($s2_options['level'.$n.'_ptags'] === 'all' 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [ptag level %d]', 's2member'), $n), array('status' => 403));
    
            // Has a restricted tag
            else if($s2_options['level'.$n.'_ptags'] 
            && has_tag(preg_split('/['."\r\n\t".';,]+/', $s2_options['level'.$n.'_ptags']), $id) 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [ptag level %d]', 's2member'), $n), array('status' => 403));
          }
        }
    
        // Custom Capabilities
        if(is_array($ccaps_req = get_post_meta($id, 's2member_ccaps_req', TRUE)) 
        && !empty($ccaps_req) 
        && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted'))
        {
          foreach($ccaps_req as $ccap) // The ``$user`` MUST satisfy ALL Custom Capability requirements. Stored as an array of Custom Capabilities.
            if(strlen($ccap) 
            && (!$user || !$user->has_cap('access_s2member_ccap_'.$ccap)) /* Does this ``$user``, have this Custom Capability? */)
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [ccap %s]', 's2member'), $ccap), array('status' => 403));
        }
    
        // Specific Page restriction
        if($s2_options['specific_ids'] 
        && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['specific_ids'])) 
        && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
        && !c_ws_plugin__s2member_sp_access::sp_access($id, 'read-only'))
          return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [sp %d]', 's2member'), $id), array('status' => 403));
    
      }
    
    
      // Also check URIs & other inclusives.
      for($n = $s2_levels; $n >= 0; $n--) // URIs. Go through each Level.
      {
        // URI restriction
        if($s2_options['level'.$n.'_ruris']) // URIs configured at this Level?
          foreach(preg_split('/['."\r\n\t".']+/', c_ws_plugin__s2member_ruris::fill_ruri_level_access_rc_vars($s2_options['level'.$n.'_ruris'], $user)) as $str) {
            if($str 
            && preg_match('/'.preg_quote($str, '/').'/'.$ci, $path) 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this content. [ruri level %d]', 's2member'), $n), array('status' => 403));
          }
      }

      return $response;
    }
	}
}
