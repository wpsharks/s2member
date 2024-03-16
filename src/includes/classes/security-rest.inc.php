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
		 * s2Member's Security Gate (protects WordPress content).
		 *
		 * @package s2Member\Security
		 * @since 240312
		 *
		 * @attaches-to ``add_filter('rest_pre_dispatch');``
		 *
		 * @return null May give a 403 error *(exiting script execution)*, when/if content is NOT available to the current User/Member.
		 */
		public static function security_gate($response, $handler, $request) // s2Member's Security Gate.
		{
      $route = $request->get_route(); // e.g. /wp/v2/pages
      $route = explode('/', trim($route, '/'));

      if (empty($route[2]) || empty($route[3]))
        return $response;
      
      $type  = sanitize_key($route[2]); // e.g. pages
      $id    = (int)$route[3];
    
      $s2_options = $GLOBALS['WS_PLUGIN__']['s2member']['o'];
      $s2_levels  = $GLOBALS['WS_PLUGIN__']['s2member']['c']['levels'];
      $ci         = $s2_options['ruris_case_sensitive'] ? '' : 'i';
    
      $user = (is_user_logged_in() && is_object($user = wp_get_current_user()) && !empty($user->ID)) ? $user : FALSE;
    
    
      // Skip early?
      if (empty($s2_options['membership_options_page'])
      ||  empty($id)
      ||  $s2_options['membership_options_page'] == $id
      ||  c_ws_plugin__s2member_systematics::is_wp_systematic_use_page()) {
        return $response;
      }
    
    
      // Post types array
      // Use: in_array($type, $post_types, true)
      $post_types = get_post_types(['show_in_rest' => true], 'names');
    
      // Get the regular path for this content, to check URI restriction
      $path = $_SERVER['REQUEST_URI']; // default
      if (in_array($type, ['posts', 'pages'], true) 
      ||  in_array($type, $post_types, true)) {
        $permalink = get_permalink($id);
        if (!is_wp_error($permalink)) {
            $path = str_replace(home_url(), '', $permalink);
        }
      } elseif ($type === 'categories') {
          $term_link = get_term_link($id, 'category');
          if (!is_wp_error($term_link)) {
              $path = str_replace(home_url(), '', $term_link);
          }
      } elseif ($type === 'tags') {
          $term_link = get_term_link($id, 'post_tag');
          if (!is_wp_error($term_link)) {
              $path = str_replace(home_url(), '', $term_link);
          }
      }
    
    
      // Login welcome page
      if($s2_options['login_welcome_page'] 
      && $id === (int)$s2_options['login_welcome_page'] 
      && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
      && (!$user || !$user->has_cap('access_s2member_level0')) 
      && $id !== (int)$s2_options['membership_options_page'])
        return new WP_Error('rest_forbidden', __('You do not have access to this page. [sys level 0]', 'text-domain'), array('status' => 403));
    
      // Login redirection override
      else if($s2_options['login_redirection_override'] 
      && ($login_redirection_uri = c_ws_plugin__s2member_login_redirects::login_redirection_uri($user, 'root-returns-false')) 
      && preg_match('/^'.preg_quote($login_redirection_uri, '/').'$/'.$ci, $path) 
      && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
      && (!$user || !$user->has_cap('access_s2member_level0')) 
      && $id !== (int)$s2_options['membership_options_page'])
        return new WP_Error('rest_forbidden', __('You do not have access to this page. [sys level 0]', 'text-domain'), array('status' => 403));
    
      // File download limit exceeded page
      else if($s2_options['file_download_limit_exceeded_page'] 
      && $id === (int)$s2_options['file_download_limit_exceeded_page'] 
      && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
      && (!$user || !$user->has_cap('access_s2member_level0')) 
      && $id !== (int)$s2_options['membership_options_page'])
        return new WP_Error('rest_forbidden', __('You do not have access to this page. [sys level 0]', 'text-domain'), array('status' => 403));
    
      // Do NOT protect Systematics (exceptions above)
      if (c_ws_plugin__s2member_systematics::is_systematic_use_page()) {
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
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this category. [catg level %d]', 'text-domain'), $n), array('status' => 403));
    
          // This specific categ
          else if($s2_options['level'.$n.'_catgs'] 
          && in_array($id, ($catgs = preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_catgs']))) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this category. [catg level %d]', 'text-domain'), $n), array('status' => 403));
    
          // Check parent categs
          else if($s2_options['level'.$n.'_catgs'] /* Check Category ancestry. */)
            foreach(preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_catgs']) as $catg)
              if($catg 
              && cat_is_ancestor_of($catg, $id) 
              && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
              && (!$user || !$user->has_cap('access_s2member_level'.$n)))
                return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this category. [catg level %d]', 'text-domain'), $n), array('status' => 403));
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
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this tag. [ptag level %d]', 'text-domain'), $n), array('status' => 403));
    
          // This tag
          else if($s2_options['level'.$n.'_ptags'] 
          && ($level_ptags = preg_split('/['."\r\n\t".';,]+/', $s2_options['level'.$n.'_ptags']))
          && ($tag = get_tag($id))
          && !empty($tag->name)
          && in_array($tag->name, $level_ptags)
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this tag. [ptag level %d]', 'text-domain'), $n), array('status' => 403));
        }
      }
    
    
      // All Posts & other inclusives.
      else if($type == 'posts'
      || in_array($type, $post_types, true)) {
        // Post Level restrictions.
        for($n = $s2_levels; $n >= 0; $n--) 
        {
          if($s2_options['level'.$n.'_posts'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [post level %d]', 'text-domain'), $n), array('status' => 403));
    
          // what about custom post types?
          else if(strpos($s2_options['level'.$n.'_posts'], 'all-') !== FALSE 
          && (in_array('all-'.$type, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts'])) || in_array('all-'.$type.'s', preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts']))) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [post level %d]', 'text-domain'), $n), array('status' => 403));
    
          // a specific post by ID
          else if($s2_options['level'.$n.'_posts'] 
          && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts'])) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [post level %d]', 'text-domain'), $n), array('status' => 403));
        }
    
        // Category Level restrictions.
        for($n = $s2_levels; $n >= 0; $n--)
        {
          if($s2_options['level'.$n.'_catgs'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [catg level %d]', 'text-domain'), $n), array('status' => 403));
    
          else if($s2_options['level'.$n.'_catgs'] 
          && ($catgs = preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_catgs']))
          && (in_category($catgs, $id) || c_ws_plugin__s2member_utils_conds::in_descendant_category($catgs, $id)) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [catg level %d]', 'text-domain'), $n), array('status' => 403));
        }
    
        if(has_term('', 'post_tag', $id)) // Here we take a look to see if this Post has any Tags. If so, we need to run the full set of routines against Tags also.
        {
          for($n = $s2_levels; $n >= 0; $n--) // Tag Level restrictions.
          {
            if($s2_options['level'.$n.'_ptags'] === 'all' 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [ptag level %d]', 'text-domain'), $n), array('status' => 403));
    
            else if($s2_options['level'.$n.'_ptags'] 
            && has_tag(preg_split('/['."\r\n\t".';,]+/', $s2_options['level'.$n.'_ptags']), $id) 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [ptag level %d]', 'text-domain'), $n), array('status' => 403));
          }
        }
    
        if(is_array($ccaps_req = get_post_meta($id, 's2member_ccaps_req', TRUE)) 
        && !empty($ccaps_req) 
        && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted'))
        {
          foreach($ccaps_req as $ccap) // The ``$user`` MUST satisfy ALL Custom Capability requirements. Stored as an array of Custom Capabilities.
            if(strlen($ccap) 
            && (!$user || !$user->has_cap('access_s2member_ccap_'.$ccap)) /* Does this ``$user``, have this Custom Capability? */)
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [ccap %s]', 'text-domain'), $ccap), array('status' => 403));
        }
    
        if($s2_options['specific_ids'] 
        && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['specific_ids'])) 
        && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
        && !c_ws_plugin__s2member_sp_access::sp_access($id))
          return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this post. [sp %d]', 'text-domain'), $id), array('status' => 403));
    
      }
    
    
      // All Pages & other inclusives.
      else if($type == 'pages') {
        for($n = $s2_levels; $n >= 0; $n--) // Page Level restrictions. Go through each Level.
        {
          // "all"
          if($s2_options['level'.$n.'_pages'] === 'all' 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [page level %d]', 'text-domain'), $n), array('status' => 403));
    
          // "all" of custom post type "page"
          else if(strpos($s2_options['level'.$n.'_posts'], 'all-') 
          && (in_array('all-page', preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts'])) || in_array('all-pages', preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_posts']))) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [page level %d]', 'text-domain'), $n), array('status' => 403));
    
          // Current page ID
          else if($s2_options['level'.$n.'_pages'] 
          && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['level'.$n.'_pages'])) 
          && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
          && (!$user || !$user->has_cap('access_s2member_level'.$n)))
            return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [page level %d]', 'text-domain'), $n), array('status' => 403));
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
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [ptag level %d]', 'text-domain'), $n), array('status' => 403));
    
            // Has a restricted tag
            else if($s2_options['level'.$n.'_ptags'] 
            && has_tag(preg_split('/['."\r\n\t".';,]+/', $s2_options['level'.$n.'_ptags']), $id) 
            && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
            && (!$user || !$user->has_cap('access_s2member_level'.$n)))
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [ptag level %d]', 'text-domain'), $n), array('status' => 403));
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
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [ccap %s]', 'text-domain'), $ccap), array('status' => 403));
        }
    
        // Specific Page restriction
        if($s2_options['specific_ids'] 
        && in_array($id, preg_split('/['."\r\n\t".'\s;,]+/', $s2_options['specific_ids'])) 
        && c_ws_plugin__s2member_no_cache::no_cache_constants('restricted') 
        && !c_ws_plugin__s2member_sp_access::sp_access($id))
          return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this page. [sp %d]', 'text-domain'), $id), array('status' => 403));
    
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
              return new WP_Error('rest_forbidden', sprintf(__('You do not have access to this content. [ruri level %d]', 'text-domain'), $n), array('status' => 403));
          }
      }

      return $response;
    }
	}
}
