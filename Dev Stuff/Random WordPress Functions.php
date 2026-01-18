<?php
// addFirstLastClass() - Add first and last classes to list items returned from wp_list_pages.
function addFirstLastClass($pageList) {
	// pattern to focus on just li's
	$allLisPattern = '/<li class="page_item(.*)<\/li>/s';
	preg_match($allLisPattern,$pageList,$allLis);
	$liClassPattern =  "/<li[^>]+class=\"([^\"]+)/i";

	// first let's break out each li
	$liArray = explode("\n",$allLis[0]);

	// count to get last li
	$liArrayCount = count($liArray);

	$lastLiPosition = $liArrayCount-1;

	// get the class name(s) of first class and last class
	preg_match($liClassPattern,$liArray[0],$firstMatch);
	preg_match($liClassPattern,$liArray[$lastLiPosition],$lastMatch);

	// add the new class names and replace the complete first and last lis
	$newFirstLi = str_replace($firstMatch[1],$firstMatch[1]. " first_item",$liArray[0]);
	$newLastLi = str_replace($lastMatch[1],$lastMatch[1]. " last_item",$liArray[$lastLiPosition]);
	// replace first and last of the li array with new lis

	// rebuild newPageList
	// set first li
	$newPageList .= $newFirstLi.'';

	$i=1;

	while($i<$lastLiPosition)	{
		$newPageList .= $liArray[$i];
		$i++;
	}

	// set last li
	$newPageList .= $newLastLi;

	// lastly, replace old list with new list
	$pageList = str_replace($allLis[0],$newPageList,$pageList);

	return $pageList;
}

add_filter('wp_list_pages', 'addFirstLastClass');

// author_comment() - Apply specified class to comments made by post author. Default class: "author".
function author_comment($authclass = "author", $uid = 1) {
	global $comment;

	$authcomment = ($comment->user_id == $uid) ? $authclass : null;

	echo $authcomment;
}

// auto_custom_field() - Automatically add a custom field to posts/pages.
function auto_custom_field($post_ID) {
	global $wpdb;

	if (!wp_is_post_revision($post_ID)) {
		add_post_meta($post_ID, 'field-name', 'custom value', true);
	}
}

add_action('publish_post', 'auto_custom_field');
add_action('publish_page', 'auto_custom_field');

// Change WordPress URLs via functions.php
update_option('siteurl','http://example.com/blog');
update_option('home','http://example.com/blog');

// Add a custom logo to the admin pages.  Image _MUST_ be 30x31, located in the theme images folder and named 'admin-logo.gif'.
function admin_logo() {
	$logo = dirname(__FILE__).'/images/admin-logo.gif';
	$logourl = get_bloginfo('template_directory').'/images/admin-logo.gif';

	if (file_exists($logo)) {
		echo '
			<style type="text/css">
				#header-logo { background-image: url('.$logourl.') !important; }
			</style>
		';
	}
}

add_action('admin_head', 'admin_logo');

// newgravatar() - Add custom gravatar for users without one.
function newgravatar($avatar_defaults) {
    $myavatar = get_bloginfo('template_directory').'/images/default-gravatar.jpg';

    $avatar_defaults[$myavatar] = "Your Custom Gravatar";

    return $avatar_defaults;
}

add_filter('avatar_defaults','newgravatar');

// custom_login() - Turn off default admin-login.css call and replace it with your own style (for custom login branding).
if (basename($_SERVER['PHP_SELF']) == 'wp-login.php') {
	add_action('style_loader_tag', create_function('$a', "return null;"));
}

function custom_login() {
	echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('template_directory') . '/custom-login.css" />';
}

add_action('login_head', 'custom_login');

// customLoop() - Set up a shortcode to display a custom loop of posts in a WP Page.  Example: [loop category="news" query="" pagination="false"]
function customLoop($atts, $content = null) {
	extract(shortcode_atts(array(
		"pagination" => 'true',
		"query"      => '',
		"category"   => '',
	), $atts));

	global $wp_query,$paged,$post;

	$temp     = $wp_query;
	$wp_query = null;
	$wp_query = new WP_Query();

	if ($pagination == 'true') {
		$query .= '&paged='.$paged;
	}

	if (!empty($category)) {
		$query .= '&category_name='.$category;
	}

	if (!empty($query)) {
		$query .= $query;
	}

	$wp_query->query($query);

	ob_start();
	?>

	<h2><?php echo $category; ?></h2>
	<ul class="loop">
		<?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
			<li><a href="<?php the_permalink() ?>" rel="bookmark"><?php echo $thumbnail_image; the_title(); ?></a></li>
		<?php endwhile; ?>
	</ul>

	<?php if ($pagination == 'true') { ?>
		<div class="navigation">
			<div class="alignleft"><?php previous_posts_link('« Previous') ?></div>
			<div class="alignright"><?php next_posts_link('More »') ?></div>
		</div>
	<?php } ?>

	<?php
	$wp_query = null; $wp_query = $temp;

	$content = ob_get_contents();

	ob_end_clean();

	return $content;
}

add_shortcode("loop", "customLoop");

// get_page_selector() - Show WP 3.0 Nav Menus in select dropdowns
function get_page_selector($menu) {
	$page_menu_items = wp_get_nav_menu_items($menu,array(
		'meta_key'=>'_menu_item_object',
		'meta_value'=>'page',
	));

	$selector   = array();
	$selector[] = 'Select a Page';
	$selector[] = "<select id=\"page-selector\" name=\"page-selector\" onchange=\"location.href = document.getElementById('page-selector').value;\">";

	$selector[] = '<option value="">Select a Page</option>';

	foreach ($page_menu_items as $page_menu_item) {
		$link = get_page_link($page_menu_item->object_id);
		$selector[] = '<option value="'.$link.'">'.$page_menu_item->title.'</option>';
	}

	$selector[] = '</select>';

	return implode("\n",$selector);
}

// post_tags() - Custom list of tags associated with a post.  Defaults: comma and space between each tag and returns "no tags" if there are no tags associated.
function post_tags($sep=", ", $notags="${2:no tags") {
	global $post;

	$posttags = get_the_tags();

	if ($posttags) {
		$lasttag = end($posttags);

		foreach($posttags as $tag) {
			if ($tag == $lasttag) {
				echo '<a href="'.get_bloginfo('url').'/tag/'.$tag->slug.'">'.$tag->name.'</a>';
			} else {
				echo '<a href="'.get_bloginfo('url').'/tag/'.$tag->slug.'">'.$tag->name.'</a>'.$sep;
			}
		}
	} else {
		echo $notags;
	}
}

// direct_email() - Send a link to a post by email.  Link text default: "Send by email".
function direct_email($text="Send by email") {
	global $post;

	$title   = htmlspecialchars($post->post_title);
	$subject = 'Someone wants you to see this: '.htmlspecialchars(get_bloginfo('name')).' - '.$title;
	$body    = 'I read this article and thought you might enjoy it: '.$title.'. You can read it here: '.get_permalink($post->ID);
	$link    = '<a rel="nofollow" href="mailto:?subject='.rawurlencode($subject).'&body='.rawurlencode($body).'" title="'.$text.': '.$title.'">'.$text.'</a>';

	echo $link;
}

// Adds an "even" or "odd" post class to each post using the post_class() function.
function eo_post_class ($classes) {
	global $current_class;

	$current_class = 'odd';

	global $current_class;

	$classes[] = $current_class;

	$current_class = ($current_class == 'odd') ? 'even' : 'odd';

	return $classes;
}

add_filter ('post_class','eo_post_class');

// fix_excerpt() - Get rid of the "[...]" trailer from post excerpts and replace with a link to the rest of the post.
function fix_excerpt($text) {
	return str_replace('[...]', '<br /><a class="more-link" href="'. get_permalink($post->ID) . '">Read More &raquo;</a>', $text);
}

add_filter('get_the_excerpt', 'fix_excerpt');
add_filter('the_excerpt', 'fix_excerpt');

// get_breadcrumbs() - Generate breadcrumb navigation links.
function get_breadcrumbs($args='') {
	parse_str($args);

	if (!isset($between)) $between = '&raquo;';
	if (!isset($name)) $name = 'Home'; //text for the 'Home' link
	if (!isset($currentBefore)) $currentBefore = '<span class="current">';
	if (!isset($currentAfter)) $currentAfter = '</span>';

	if (!is_home() && !is_front_page() || is_paged()) {
		echo '<div id="crumbs">';

		global $post;

		$home = get_bloginfo('url');
		echo '<a href="' . $home . '">' . $name . '</a> ' . $between . ' ';

		if (is_category()) {
			global $wp_query;

			$cat_obj = $wp_query->get_queried_object();
			$thisCat = $cat_obj->term_id;
			$thisCat = get_category($thisCat);
			$parentCat = get_category($thisCat->parent);

			if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $between . ' '));
			echo $currentBefore . 'Archive by category &#39;';
			single_cat_title();
			echo '&#39;' . $currentAfter;
		} elseif (is_day()) {
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $between . ' ';
			echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $between . ' ';
			echo $currentBefore . get_the_time('d') . $currentAfter;
		} elseif (is_month()) {
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $between . ' ';
			echo $currentBefore . get_the_time('F') . $currentAfter;
		} elseif (is_year()) {
			echo $currentBefore . get_the_time('Y') . $currentAfter;
		} elseif (is_single()) {
			$cat = get_the_category(); $cat = $cat[0];
			echo get_category_parents($cat, TRUE, ' ' . $between . ' ');
			echo $currentBefore;
			the_title();
			echo $currentAfter;
		} elseif (is_page() && !$post->post_parent) {
			echo $currentBefore;
			the_title();
			echo $currentAfter;
		} elseif (is_page() && $post->post_parent) {
			$parent_id  = $post->post_parent;
			$breadcrumbs = array();

			while ($parent_id) {
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}

			$breadcrumbs = array_reverse($breadcrumbs);

			foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $between . ' ';
			echo $currentBefore;
			the_title();
			echo $currentAfter;
		} elseif (is_search()) {
			echo $currentBefore . 'Search results for &#39;' . get_search_query() . '&#39;' . $currentAfter;
		} elseif (is_tag()) {
			echo $currentBefore . 'Posts tagged &#39;';
			single_tag_title();
			echo '&#39;' . $currentAfter;
		} elseif (is_author()) {
			global $author;

			$userdata = get_userdata($author);
			echo $currentBefore . 'Articles posted by ' . $userdata->display_name . $currentAfter;
		} elseif (is_404()) {
			echo $currentBefore . 'Error 404' . $currentAfter;
		}

		if (get_query_var('paged')) {
			if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) echo ' (';
			echo __('Page') . ' ' . get_query_var('paged');
			if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) echo ')';
		}

		echo '</div>';
	}
}

// generate_title_tag() - Generate title tag with extra SEO love.  Wrap function call with <title></title> in header.php.
function generate_title_tag() {
	if (is_single()) : bloginfo('name'); wp_title('|', true, 'left');
		echo (' - ');
		echo bloginfo('description');
	elseif (is_page() || is_paged()) : bloginfo('name');
		wp_title('|', true, 'left'); echo (' - ');
		echo bloginfo('description');
	elseif (is_author()) : bloginfo('name');
		echo (' | '); wp_title('Archives for ', true, 'right');
		echo (' - ');
		echo bloginfo('description');
	elseif (is_archive()) : bloginfo('name');
		echo (' | '); wp_title('Archives for ', true, 'right');
		echo (' - ');
		echo bloginfo('description');
	elseif (is_search()) : bloginfo('name');
		echo (' | '); wp_title('Search Results ', true, 'right');
		echo (' - ');
		echo bloginfo('description');
	elseif (is_404()) : bloginfo('name');
		echo (' | '); wp_title('Page Not Found ', true, 'right ');
		echo (' - ');
		echo bloginfo('description');
	else : bloginfo('name'); echo '&nbsp;'; wp_title('&raquo;', true, 'right');
		echo (' - ');
		echo bloginfo('description');
	endif;
}

// get_meta_value() - Get post meta content.
function get_meta_value($key, $print = false) {
	global $post;

	$value = get_post_meta($post->ID, $key, true);

	if ($print == false ) return $value; else echo $value;
}

// get_post_image() - Display the first image found in a post.
function get_post_image($imgnum = 0, $print = false) {
	global $post;

	$content = $post->post_content;

	$pattern = '~<img [^\>]*\ />~';

	preg_match($pattern, $content, $pics);

	if ($print == true && !empty($pics)) echo $pics[$imgnum]; else return $pics[$imgnum];
}

// List child pages
function child_pages() {
	global $post;

	if ($post->post_parent)
		$children = wp_list_pages("title_li=&child_of=".$post->post_parent."&echo=0");
	else
		$children = wp_list_pages("title_li=&child_of=".$post->ID."&echo=0");

	if ($children) {
		$output = '<ul>';
		$output .= $children;
		$output .= '</ul>';
	}

	print $output;
}

// copyright() - Display copyright info with live rolling date and date of first post. Default id for wrapper <div></div>: "copyright"
function copyright($id = "copyright") {
	/* Get all posts */
	$all_posts = get_posts('post_status=publish&order=ASC');

	/* Get first post */
	$first_post = $all_posts[0];

	/* Get date of first post */
	$first_date = $first_post->post_date_gmt;

	/* Display common footer copyright notice */
	echo '<div id="'.$id.'">';
	echo 'Copyright &copy; ';

	/* Display first post year and current year */
	if (substr($first_date,0,4) == date('Y') ) {
		/* Only display current year if no posts in previous years */
		echo date('Y');
	} else {
		echo substr($first_date,0,4) . "-" . date('Y');
	}

	/* Display blog name from 'General Settings' page */
	echo ' <strong>'.get_bloginfo('name').'</strong>. All rights reserved.';
	echo '</div>';
}

// Post Tag support for pages
function register_page_tags(){
	register_taxonomy_for_object_type('post_tag','page');
}

add_action('init', 'register_page_tags');

// promote_feed() - Add a feed promotion link at the end of each article on your blog. Default class for wrapper <div></div>: "promote"
function promote_feed($content,$class = "promote") {
	echo $content;

	if (is_single()) {
	?>
		<div class="<?php echo $class; ?>">
			<h3>Enjoyed this article?</h3>
			<p>Please consider subscribing to our <a class="feed" href="<?php bloginfo('rss2_url'); ?>" title="Subscribe via RSS">RSS feed!</a></p>
		</div>
	<?php
	}
}

add_filter('the_content','promote_blog');

// recent_posts() - List of most recent posts to your blog.  Default: Last 5 posts with list items for each post (wrap call in <ul></ul>).
function recent_posts($args='') {
	parse_str($args);

	if (!isset($numposts)) $numposts = 5;
	if (!isset($cat)) $cat = null;
	if (!isset($before)) $before = "<li>";
	if (!isset($after)) $after = "</li>";

	($cat != null) ? $cat = "&cat=" . $cat : $cat;

	$posts = new WP_Query( "showposts=" . $numposts . $cat );

	while ($posts->have_posts()) : $posts->the_post();
		$rec_posts .= $before . '<a href="' . get_permalink() . '">' . get_the_title() . '</a>' . $after;
	endwhile;

	echo $rec_posts;
}

// relativeTime() - display post times as a relative ("about an hour ago") timeframe.
function relativeTime($timestamp) {
	$difference = time() - $timestamp;

	if ($difference >= 60*60*24*365) {        // if more than a year ago
		$int = intval($difference / (60*60*24*365));
		$s = ($int > 1) ? 's' : '';
		$r = $int . ' year' . $s . ' ago';
	} elseif ($difference >= 60*60*24*7*5) {  // if more than five weeks ago
		$int = intval($difference / (60*60*24*30));
		$s = ($int > 1) ? 's' : '';
		$r = $int . ' month' . $s . ' ago';
	} elseif ($difference >= 60*60*24*7) {        // if more than a week ago
		$int = intval($difference / (60*60*24*7));
		$s = ($int > 1) ? 's' : '';
		$r = $int . ' week' . $s . ' ago';
	} elseif ($difference >= 60*60*24) {      // if more than a day ago
		$int = intval($difference / (60*60*24));
		$s = ($int > 1) ? 's' : '';
		$r = $int . ' day' . $s . ' ago';
	} elseif ($difference >= 60*60) {         // if more than an hour ago
		$int = intval($difference / (60*60));
		$s = ($int > 1) ? 's' : '';
		$r = $int . ' hour' . $s . ' ago';
	} elseif ($difference >= 60) {            // if more than a minute ago
		$int = intval($difference / (60));
		$s = ($int > 1) ? 's' : '';
		$r = $int . ' minute' . $s . ' ago';
	} else {                                // if less than a minute ago
		$r = 'moments ago';
	}

	return $r;
}

// is_subpage() - Returns true if current page is a child, false otherwise.
function is_subpage() {
	global $post, $wpdb;

	if (is_page() && isset($post->post_parent) != 0) {
		$parent = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID = %d AND post_type = 'page' LIMIT 1", $post->post_parent));

		if ($parent->ID) return true; else return false;
	} else {
		return false;
	}
}

// is_subpage_of() - Returns true if current page is child of supplied page, false otherwise
function is_subpage_of($page) {
	global $post, $wpdb;

	$page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title='$page'");

	if (is_page() && $post->post_parent == $page_id) {
		return true;
	} else {
		return false;
	}
}

// close_comments() - Close comments on posts older than 1 month.
function close_comments($posts) {
	if (!is_single()) { return $posts; }

	if (time() - strtotime($posts[0]->post_date_gmt) > (30*24*60*60)) {
		$posts[0]->comment_status = 'closed';
		$posts[0]->ping_status = 'closed';
	}

	return $posts;
}

add_filter('the_posts', 'close_comments');

// tweet_this() - Add a Twitter link with URL shortening for each post. Default class for wrapper <div></div>: "tweet-this"
function tweet_this($content,$class="tweet-this") {
	if (is_single()) {
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$tweet_url = file_get_contents('http://tinyurl.com/api-create.php?url='.urlencode($url));

		$tweet_title = get_the_title(); // Title of current post
		$title_len = strlen($tweet_title); // Length of title

		if ($title_len > 100) { // Title longer than 100 characters?
			$tweet_title = substr($tweet_title,0,100); // Reduce title to 100 characters...
			$tweet_title .= '...'; // And append an ellipsis to the end
		}

		$tweet_status_url = "Currently reading \"$tweet_title\" $tweet_url"; // Set up the status and url to send to twitter
		$tweet_status_url = urlencode($tweet_status_url);

		// If the current page is an individual article, promote it with a Twitter link.
		$content .=  '<div class="'. $class .'">Enjoy this post? <a href="http://twitter.com/home?status='.$tweet_status_url.'">Tweet it!</a></div>';
	}

	return $content;
}

add_filter('the_content', 'tweet_this');

function pbar_nav() {
	global $wp_query, $wp_rewrite;

	$pages = '';
	$max = $wp_query->max_num_pages;

	if (!$current = get_query_var('paged')) $current = 1;

	$a['base'] = ($wp_rewrite->using_permalinks()) ? user_trailingslashit(trailingslashit(remove_query_arg('s', get_pagenum_link(1))) . 'page/%#%/', 'paged') : @add_query_arg('paged','%#%');

	if(!empty($wp_query->query_vars['s'])) $a['add_args'] = array('s' => get_query_var('s'));

	$a['total'] = $max;
	$a['current'] = $current;

	$total = 1; //1 - display the text "Page N of N", 0 - not display
	$a['mid_size'] = 5; //how many links to show on the left and right of the current
	$a['end_size'] = 1; //how many links to show in the beginning and end
	$a['prev_text'] = '&laquo; Previous'; //text of the "Previous page" link
	$a['next_text'] = 'Next &raquo;'; //text of the "Next page" link

	if ($max > 1) echo '<div class="pb-navigation">';

	if ($total == 1 && $max > 1) $pages = '<span class="pages">Page ' . $current . ' of ' . $max . '</span>'."\r\n";

	echo $pages . paginate_links($a);

	if ($max > 1) echo '</div>';
}
