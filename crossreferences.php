<?php 
/*
Plugin Name: Cross-references
Version: 1.4.04
Plugin URI: http://wordpress.org/extend/plugins/cross-references-plugin
Description: Insert cross-references between posts or pages with [cref postID], such as [cref 1], or optionally with [cref post-slug], for example [cref hello-world], and get all backward references automatically at the end of each post or page.
Author: Francesc Hervada-Sala
Author URI: http://francesc.hervada.cat/

    --------------------------------------------------------------------
    Copyright © 2007, 2008, 2009 Francesc Hervada-Sala
    http://francesc.hervada.cat/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
*/
define('CREF_FOLDER', 'cross-references-plugin'); //The name of the plugin folder, change this if you rename it
if(!isset($wpdb->crossreferences)) $wpdb->crossreferences = $table_prefix . "crossreferences";
if(get_option("cref_dbversion")!=2) cref_setup();

add_filter('the_content', 'cref_link_crossreferences'); 
add_filter('the_content_rss', 'cref_link_crossreferences');
add_filter('the_excerpt', 'cref_link_crossreferences');
add_filter('the_excerpt_rss', 'cref_link_crossreferences');
function cref_link_crossreferences($data) { 
	return cref_process_crossreferences($data,1);
} 

if(get_option('cref_show_backlinks')==1) add_filter('the_content', 'cref_list_crossreferences'); 
function cref_list_crossreferences($text) {
if(is_feed())
	return $text;
else
	return $text.get_the_crossreferences(
		get_option('cref_before'),
		get_option('cref_between'),
		get_option('cref_after'),
		0,
		get_option('cref_empty')
		);
}

if(get_option('cref_translate_export')>=1) add_filter('the_content_export', 'cref_translate_crossreferences'); 
function cref_translate_crossreferences($data) { 
	return cref_process_crossreferences($data,get_option('cref_translate_export')-1);
}

function cref_process_crossreferences($data,$link) { 
	preg_match_all("/\[cref(\.from|)\s+([^\s\]]*)(\s+(.*?)|)\]/",$data,$results, PREG_SET_ORDER);
	
	if($results)  {
	foreach($results as $result) {
		if($result[1]!='.from') {
			$refID=cref_get_postID($result[2]);
			$post = get_post($refID);
			if($post->ID) {
                                $title = $result[4];
                                $ptitle = apply_filters('the_title',$post->post_title);
                                if($title=='') $title = $ptitle;
                                if(1==$link and 'publish'==$post->post_status) {
                                        $name = '<a href="'.get_permalink($post->ID).'" title="'.$ptitle.'">'.$title.'</a>';
                                } else {
                                        $name = $title;
                                }
                                $data = str_replace($result[0], $name, $data );
			}
		} else {
			$data = str_replace($result[0], '', $data );
		}
	}}
return $data;
} 

function cref_get_postID($ref) {
global $wpdb;
	if(get_option('cref_use_slugs') == 1 or !is_numeric($ref)) $where = "post_name='%s'"; else $where = "ID=%d";
	$row = $wpdb->get_row($wpdb->prepare(
		"SELECT ID, post_parent, post_type FROM `$wpdb->posts` WHERE post_type IN ('post','page','revision') AND ".$where,$ref),ARRAY_A);
	if(!$row) return -1;
	if($row['post_type']=='revision') return $row['post_parent'];
	return $row['ID'];
}

add_action("publish_post", "cref_save_crossreferences");
add_action("publish_page", "cref_save_crossreferences");
add_action("xmlrpc_publish_post", "cref_save_crossreferences");
function cref_save_crossreferences($id)
{
global $wpdb;
	$row = $wpdb->get_row("SELECT post_content FROM $wpdb->posts WHERE id=$id",ARRAY_A);
	preg_match_all("/\[cref(\.from|)\s+([^\s\]]*)(\s+(.*?)|)\]/",$row["post_content"],$results, PREG_SET_ORDER);
	$wpdb->query("DELETE FROM `$wpdb->crossreferences` WHERE `post_id`=$id;");
	if($results)  {
		foreach($results as $result) {
			$ref_id = cref_get_postID($result[2]);
			$wpdb->query("INSERT IGNORE INTO `$wpdb->crossreferences` (`post_id`,`post_from`,`post_to`)
				SELECT $id, $id, $ref_id FROM $wpdb->posts WHERE ID=$ref_id;");
			if($result[1]=='.from') 	
				$wpdb->query("INSERT IGNORE INTO `$wpdb->crossreferences` (`post_id`,`post_from`,`post_to`)
					SELECT $id, $ref_id, $id FROM `$wpdb->posts` WHERE ID=$ref_id;");
		}
	}
}

if( get_option('cref_use_slugs')==1 ) add_action('edit_post', 'cref_check_slugs_for_change');
function cref_check_slugs_for_change($post_id) {
	if ( !isset($_POST['wp-old-slug']) || !strlen($_POST['wp-old-slug']) )
		return;
	$post = &get_post($post_id);
	if ( $post->post_name == $_POST['wp-old-slug'] )
		return;

	cref_change_all_post_slugs($post->ID, $_POST['wp-old-slug'], $post->post_name);
	return;
}

function cref_change_all_post_slugs($id,$oldslug,$newslug)
{
	global $wpdb;
	$results = $wpdb->get_results("SELECT DISTINCT post_from,post_content FROM `$wpdb->crossreferences`
		INNER JOIN `$wpdb->posts` ON post_from=ID WHERE post_to=$id");
	foreach($results as $row) {
		$content = cref_change_post_slug($oldslug,$newslug,$row->post_content);
		$wpdb->query($wpdb->prepare("UPDATE `$wpdb->posts` SET post_content='%s' WHERE ID=%d",$content,$row->post_from));
	}	
}

function cref_change_post_slug($oldslug,$newslug,$data) { 
	preg_match_all("/\[cref(\.from|)\s+([^\s\]]*)(\s+(.*?)|)\]/",$data,$results, PREG_SET_ORDER);
	if($results)  {
		foreach($results as $result) {
			if($oldslug==$result[2]) {
				$str = '[cref'.$result[1];
				$str.=' '.$newslug;
				if($result[4]) $str.=' '.$result[4];
				$str.=']';
				$data = str_replace($result[0], $str, $data);
			}
		}
	}
	return $data;
} 


add_action("delete_post", "cref_delete_post");
function cref_delete_post($id)
{
	global $wpdb;
	$row = $wpdb->get_row("SELECT post_title FROM `$wpdb->posts` WHERE id=$id",ARRAY_A);
	if($row) {
		$title = $row['post_title'];
		$results = $wpdb->get_results("SELECT DISTINCT post_from,post_content FROM `$wpdb->crossreferences`
			INNER JOIN `$wpdb->posts` ON post_from=ID WHERE post_to=$id AND post_from!=$id");
		foreach($results as $row) {
			$content = cref_remove_crossreferences($id,$title,$row->post_content);
			$wpdb->query($wpdb->prepare("UPDATE `$wpdb->posts` SET post_content='%s' WHERE ID=%d",$content,$row->post_from));
		}
		$wpdb->query("DELETE FROM `$wpdb->crossreferences` WHERE post_from=$id OR post_to=$id OR post_id=$id");
	}
}

function cref_remove_crossreferences($ref_id,$title,$data) { 
	preg_match_all("/\[cref(\.from|)\s+([^\s\]]*)(\s+(.*?)|)\]/",$data,$results, PREG_SET_ORDER);

	if($results)  {
		foreach($results as $result) {
			if(cref_get_postID($result[2])==$ref_id) {
				if($result[1]=='') {
						$ptitle = $result[4];
						if($ptitle=='') $ptitle = $title;
						$data = str_replace($result[0], $ptitle, $data );
				} else {
					$data = str_replace($result[0], '', $data);
				}
			}
		}
	}
	return $data;
} 

function get_the_crossreferences($before='<ul><li>',$between='</li><li>',$after='</li></ul>',$id = 0,$emptylist='') {
	global $wpdb;
	$post_to = &get_post($id);
	$rows=$wpdb->get_results("
                SELECT DISTINCT $wpdb->posts.*
                FROM $wpdb->crossreferences
                        INNER JOIN $wpdb->posts ON post_from=ID
                WHERE post_to=$post_to->ID AND post_status='publish'
                ORDER BY ".apply_filters('cref_order_by','post_date DESC')
		);
	$text='';
	if($rows) {
		if($before) $text .= apply_filters('cref_before',$before);
		$sep='';
		foreach($rows as $post_from) {
			$title = apply_filters('the_title',$post_from->post_title);
			$entry = '<a href="'.get_permalink($post_from->ID).'" title="'.$title.'">'.$title.'</a>';
			$entry = apply_filters('cref_post_from',$entry,$post_from);
			$text .= apply_filters('cref_between',$sep).$entry;
			if(!$sep) $sep=$between;
		}
		if($after) $text .= apply_filters('cref_after',$after);
	} else {
		$text = apply_filters('cref_emptylist',$emptylist);
	}
	if($text) $text = '<div class="crossreferences">'.$text.'</div>';
return apply_filters('gettext',preg_replace('/&sp;/', ' ', $text));
}

#TEMPLATE FUNCTION

function the_crossreferences($before='<ul><li>',$between='</li><li>',$after='</li></ul>',$id = 0,$emptylist='') {
	echo get_the_crossreferences($before,$between,$after,$id,$emptylist);
}

# ADMIN

function cref_setup() {
global  $table_prefix,$wpdb;
	$T=$table_prefix.'crossreferences';
	if(!get_option('cref_dbversion')) {
		if($wpdb->get_var("SHOW TABLES LIKE '$T'") != $T) { #new installation
			$wpdb->query("CREATE TABLE $T (
			`post_id` BIGINT(20) UNSIGNED NOT NULL,		
			`post_from` BIGINT(20) UNSIGNED NOT NULL,
			`post_to` BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (`post_id`,`post_from`,`post_to`)) TYPE=MyISAM;");
			add_option('cref_dbversion','2');
		}
		else { #already existing with version <= v1.2, which did not show backlinks
			add_option('cref_show_backlinks','0');
			add_option('cref_dbversion','1');
		}
	}
	if(get_option('cref_dbversion')==1) update_option('cref_dbversion','2');
	cref_load_textdomain();
	add_option('cref_use_slugs','0');
	add_option('cref_show_backlinks','1');
	add_option('cref_translate_export','0');
	add_option('cref_before',sprintf('<h3>%s</h3><ul><li>',__('Related posts','crossreferences')));
	add_option('cref_between','</li><li>');
	add_option('cref_after','</li></ul>');
	add_option('cref_empty','');
}

function cref_load_textdomain() { load_plugin_textdomain('crossreferences',PLUGINDIR.'/'.CREF_FOLDER); }

add_action('admin_menu', 'cref_add_options_page');

function cref_add_options_page() {
cref_load_textdomain();
include_once dirname(__FILE__).'/admin.php';
add_options_page(__('Cross-references','crossreferences'), __('Cross-references','crossreferences'), 10, 'crossreferences', 'cref_options_page');
add_filter( 'plugin_action_links', 'cref_plugin_action_links', 10, 2 );
}

function cref_plugin_action_links($links, $file){
	static $this_plugin;

	if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=crossreferences">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links); // before other links
	}
	return $links;
}





?>
