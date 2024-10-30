=== Cross-references ===
Contributors: hervada
Tags: cross-references,links,posts,related,permalinks,slugs
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 1.4

Insert cross-references between posts and pages easily and get a list of backward references on each post and page automatically.

== Description ==
With this plugin you can manually set a reference to another post or page in posts und pages and you get all backward references automatically listed for every post and page.

_Note May 1st, 2009: The development of this plugin is being discontinued, because I personally do not use WordPress any more. There will be no new release of it from me. It is now still compatible with the latest WordPress version, but soon or later it will be no more. You are welcome to download the source code from the Subversion repository and develop it further._

= Usage Basics =
Just place [cref post] at your post or page content to get both a forward and a backward link between these. The plugin shows automatically a list of related entries at the end of each post and page.

To start using the plugin is easy: With the import tool you can convert all your internal html links `<a href="...">...</a>` to cross-references. The plugin is safe: You can change the post slugs at any time, the plugin will change all references for you. And if you once do not want to use the plugin any more, you can completely deinstall it leaving your blog with simple html links again.

= Usage Details =
When writing a post or page, you can refer to other posts or pages by their slug or numerical ID. At the Settings/Cross-References page on your admin panel you can switch between referencing by ID and by slug (even if you already set references, these will be automatically updated).

* [cref <i>post</i>] sets a reference from the current post to <i>post</i>. Example: "As I said on my first post [cref hello-world], I won't write about my job." The current entry is shown at the blog as "As I said on my first post Hello World!, I won't write about my job.", with a html link from "Hello World!" to the "Hello World!" post. At the "Hello World!" post the current post appears under "Related Posts".

* [cref <i>post</i> <i>alternate text</i>] same as [cref <i>post</i>] but the link has another text instead of the post title. Example: "As I said on my [cref hello-world first post], I won't write about my job." This is shown as "As I said on my first post, I won't write about my job.", with a html link from "first post" to the "Hello World!" post.

* [cref.from <i>post</i>] sets a backward reference from <i>post</i> to the current post. Example: "[cref.from my-vision]"
A forward reference is not shown at all, and both the current and the <i>post</i> post appear at each others related posts list.

= Related Posts List =
You get automatically a list of all the posts and pages that link to the current post or page at the end of each entry.

For a greater control on the appearence and position of this list you can optionally customize your templates:

* Go to the Settings/Cross-References page on your admin panel and set up "Do not show related posts list"
* At your template files place `<?php the_crossreferences() ?>` wherever you want to place the related posts list.

Parameters: `the_crossreferences( $before, $between, $after, $id, $emptylist )`

It shows the related posts list for post $id, or for the current post if $id is not given or is 0.

* If there are some related posts: It shows $before, then all post titles with $between in between, then $after.
* If there are no related posts: It shows $emptylist.

You can apply CSS styles to the related posts list, because it is enclosed within a DIV (class="crossreferences").

For theme/plugin developers: You can also control the sort order and the appearence of the related posts list with filters.

For more information and examples about function parameters, styles and filters see the page "Other Notes".

= Tools =
This plugin adds a page at the admin panel under Settings / Cross-References where you can set the plugin options and run some tools:

* Import all html links `<a href="...">...</a>` that refer to other posts or pages in your blog converting them to [cref...] references.
* Switch between [cref post-slug] and [cref post-ID]. You can switch at any time: if your blog has already some references set, they will be updated.
* Configure the "related posts" lists, or hide it if you call `the_crossreferences()` in your templates.
* Rename a page slug (you can rename a post slug directly within the admin panel / manage posts, but this does not work for pages because of WordPress internals).
* Set how to show references when exporting your blog to XML. You can let them unchanged as [cref...] or show the post title, optionally with a html link to the referred blog post.
* Rebuild the plugin table, if the plugin was some time inactive, in order to fix possible errors at the related posts lists.
* Deinstall completely the plugin, deleting the plugin's table and options from your database. All existing [cref ...] references are optionally replaced with post titles, with or without html links - this ensures your blog remains readable if you decide to remove this plugin.

= International =
This plugin works for any blog language, because you can customize its wording, and it works fine with multilanguage plugins such as qTranslate. Please let me know if you have any localization issues.

The plugin settings page is right now in this languages:

* English,
* German,
* Italian (translated by Leonardo Saracini),
* Catalan, and
* Belorussian (translated by FatCow).

== Installation ==
= First installation =
1. Download the .zip file and entpack it.
1. Upload the 'cross-references-plugin' folder as a whole to '/wp-content/plugins'.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally, go to the dashboard / Settings / Cross-references page in order to adjust some settings.

= Upgrade from v. 1.3 or newer =
You can use the WordPress Plugin Update or update manually:

1. Download the .zip file and entpack it.
1. Upload the 'cross-references-plugin' folder as a whole to '/wp-content/plugins' overwriting all existing files.
1. Optionally, go to the dashboard / Settings / Cross-references page in order to adjust some settings or run some tools.

= Upgrade from v. 1.2 or older =
You can use the WordPress Plugin Update or update manually:

1. Download the .zip file and entpack it.
1. Upload the 'cross-references-plugin' folder as a whole to '/wp-content/plugins'.
1. Deactivate the plugin through the 'Plugins' menu in WordPress.
1. At your server **delete the file** '/wp-content/plugins/cross-references.php'
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally, go to the dashboard / Settings / Cross-references page in order to adjust some settings or run some tools. I recommend you to do a table rebuild, which will ensure the cross-references table at your database has no errors.

== Frequently Asked Questions ==

= Are references automatically updated whenever the refered/referer post title changes? =

Yes, since the title of the post is read dynamically when it is shown.

= What happens if I delete a post to which there are references? =

All links will be removed from the referer posts, but their text (possibly the title of the removed post) will remain unchanged.

= What happens if I set a reference to a draft/pending/private post? =

No links will be shown on the referer posts (just possibly the title of the hidden post) until it is made public. The hidden post will also not appear at the back links list from other posts. The same happens if you set a reference to a published post and then you hide it.

= What happens if I change a slug which is being referred to? =

Because of the way WordPress handles slugs, if you reference posts by slug all references to it would be broken. You need to rename slugs with the tool "slug rename" at the plugin settings page (Admin panel / Settings / Cross-References).

= After upgrading this plugin from v1.2 to 1.3 I get a fatal error =

If you get an error such as: `Fatal error: Cannot redeclare cref_setup() (previously declared in [...]/wp-content/plugins/cross-references-plugin/crossreferences.php:227)
in [...]/wp-content/plugins/crossreferences.php on line 95` you need to remove the file /wp-content/plugins/crossreferences.php, which is the old plugin v1.2.

= Is this plugin compatible with the multilanguage plugin qTranslate? =

Yes. At the plugin settings page just write for example:

`<h1>[lang_es]Entradas Relacionadas: [/lang_es][lang_en]Related Posts: [/lang_en]</h1>`


= Known issues with other plugins =

"HeadSpace2" Plugin - In cross-references v. 1.3 until 1.4.01 there was a bug. This bug could have caused problems with other plugins that change post titles, too. Please update to cross-references v. 1.4.02 or above or downgrade to v. 1.2 to avoid this problems.

"Raw HTML" Plugin - [cref ...] tags will not work when placed into a block  `<!--start_raw--> ... [cref ...] ...<!--end_raw-->`. This is because of the way the Raw HTML plugin works. It overwrites the content in raw-blocks after all content filters apply with the original contents. Workaround: `<!--start_raw--> ... <!--end_raw-->[cref ..]<!--start_raw--> ... <!--end_raw-->`.

== Related Posts List Styles ==
The related posts list can be formatted via CSS style sheet applying format to DIV elements of class "crossreferences".

Examples:

Place this at your theme's style.css file in order to get a light grey background color for the list.

	`div.crossreferences { background-color:#f0f0f0; }`

Put this to get green linked posts.

	`div.crossreferences a { color:green; }`

== Template Funtions ==

= Funtion `the_crossreferences` =

You can place this function at your templates, in order to get the related posts list for a particular post or page. You do not need to use this, because the plugin shows the list automatically, but this function gives you a greater control on the appearance and the position of the list. If you use this function, please set up "Do not show related posts list" at the admin panel / Settings / Cross-references. Otherwise the related posts list will appear twice.

`the_crossreferences($before,$between,$after,$id,$emptylist)`

 `$before` - Optional. This string being printed once before the related posts list (only if there is 1 related post or more)
 `$between` - Optional. This string being printed between 2 entries in the related posts list.
 `$after` - Optional. This string being printed once after the related posts list (only if there is 1 related post or more)
 `$emptylist` - Optional. This string being printed if there are no related posts.
 `$id` - Optional. The post id number to which the related posts list are shown. Defaults to the current post or page.

Examples:

If you write `<?php the_crossreferences() ?>` you get an output such as (here without hyperlinks):

*	`<ul><li>Post title 1</li><li>Post title 2</li><li>Post title 3</li></ul>` (if there are 3 related posts)
*	Nothing (if there are no related posts)

If you write 	`<?php the_crossreferences('See also:', '; ', '.') ?>` you get an output such as:

*	`See also: Post title 1; Post title 2; Post title 3.` (if there are 3 related posts)
*	Nothing (if there are no related posts)

If you write 	`<?php the_crossreferences('See also:', '; ', '.',0,'There are currently no related posts.') ?>` you get an output such as:

*	`See also: Post title 1; Post title 2; Post title 3.` (if there are 3 related posts)
*	`There are currently no related posts.` (if there are no related posts)

If you write 	`<?php the_crossreferences('<h2>Related posts</h2><ul><li>') ?>` you get an output such as:

*	`<h2>Related posts</h2><ul><li>Post title 1</li><li>Post title 2</li></ul>` (if there are 2 related posts)
*	Nothing (if there are no related posts)

If you write 	`<h2>Related posts</h2><?php the_crossreferences() ?>` you get an output such as:

*	`<h2>Related posts</h2><ul><li>Post title 1</li><li>Post title 2</li></ul>` (if there are 2 related posts)
*	`<h2>Related posts</h2>` (if there are no related posts)

It is better to enclose the call to `the_crossreferences()` within a `if function_exists()` block, otherwise your template will raise an error if the plugin is inactive.

`if (function_exists('the_crossreferences')) the_crossreferences();`

= Function `get_the_crossreferences` =

Same as above, but you get a string instead of an output to screen:

`$string = get_the_crossreferences($before,$between,$after,$id,$emptylist);`


== Template Funtion Filters ==
There are the following filters available for the functions `the_crossreferences()` and `get_the_crossreferences()`:

= `cref_order_by` =
With this filter you can define the sort order of the related posts list. Examples:

Place this at the functions.php file of your template in order to sort the related posts from older to newer:

`add_filter('cref_order_by','mytheme_order_by');
function mytheme_order_by() { return 'post_date'; }`

Place this in order to sort the related posts by post title:

`add_filter('cref_order_by','mytheme_order_by');
function mytheme_order_by() { return 'post_title'; }`

Place this in order to sort the related posts by post title descending Z-A:

`add_filter('cref_order_by','mytheme_order_by');
function mytheme_order_by() { return 'post_title DESC'; }`


= `cref_post_from` =
With this filter you can define how the link to a related post is shown.

Parameters: $default, $post

* $default - The default appearence (hyperlinked post title)
* $post    - Related post info. You can use this properties:

	`$post->ID - The ID number
	$post->post_title - Post Title
	$post->post_excerpt - Post Excerpt
	$post->post_content - Post Content
	etc. (all fields from the table wp_posts)`

Examples:

Place this at your template to get each related post as:  `<hyperlinked post title> (<author>, <date>)`

`add_filter('cref_post_from','mytheme_post_from',10,2);
function mytheme_post_from($default,$post) {
	$author = get_author_name($post->post_author);
	$date = mysql2date(get_option('date_format'),$post->post_date);
	return "$default ($author, $date)";
}`

Place this to get the same as above but being the whole (not just the title) hyperlinked to the related post:

`add_filter('cref_post_from','mytheme_post_from',10,2);
function mytheme_post_from($default,$post) {
	$url = get_permalink($post->ID);
	$title = $post->post_title;
	$author = get_author_name($post->post_author);
	$date = mysql2date(get_option('date_format'),$post->post_date);
	return '<a href="'.$url.'">'.$title.' ('.$author.', '.$date.')</a>';
}`

= `cref_before` =
= `cref_between` =
= `cref_after` =
= `cref_emptylist` =

With this filters you can control the parameters of the template function (s. above).

== Change Log ==

= Version 1.4 8/10/2008 =

* New utility "Import HMTL links": converts all `<a href="...">...</a>` post links to posts/pages in this blog to [cref ...] cross-references.
* Fixed bug on deinstall converting references to html links: [cref.from ...] appeared as an html link.
* Rebuild now automatically corrects [cref ID] where ID is a revision ID instead of a post/page ID.
* v1.4.01 (8/11/2008) Fixed bug which could cause some other plugins to crash (wp_title filter).
* v1.4.02 (9/3/2008) Fixed bug: Related post titles were possibly wrong if HeadSpace2 plugin was active. This bug could cause problems regarding post titles with other plugins, too.
* v1.4.03 (11/16/2008) Fixed bug: a php warning was shown on some systems after deleting a post.
* v1.4.04 (02/08/2009) Fixed same bug again: On some servers there were still warnings after deleting a post. BTW renaming slugs directly at the edit panel seems not to work any more with WP 2.7, please use the rename tool under "Settings" instead.
* 03/22/2009: Added italian translation by Leonardo Saracini.

= Version 1.3 - 7/24/2008 =

* You can now use the post slugs instead of post IDs when refering to posts.
* Related posts lists can now automatically be shown at the end of each post/page without editing any templates.
* Added support for items appearance and sort order customization of the related posts lists via CSS style sheets and WP filters.
* Added support for multilanguage plugins (tested with qTranslate).
* XML export can now be configured to show up references unchanged (as [cref ...]), as html links or as mere titles.
* New plugin options page at the admin panel to adjust settings and run utilities.
* New utility "rebuild" repairs all errors at the crossreferences table.
* New utility "deinstall" lets your blog posts and database clean when removing this plugin.
* New utility "slug rename" allows renaming page slugs.

= Version 1.2 - 9/24/2007 =

* No links are shown to or from draft/pending/private posts

= Version 1.1 - 9/15/2007 =

* First public release
