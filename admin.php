<?php
/*
    Cross-references WordPress Plugin
    Copyright © 2007, 2008. 2009 Francesc Hervada-Sala
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

add_action('import_start','cref_import_start');
function cref_import_start() {
	remove_action("publish_post", "cref_save_crossreferences");
	remove_action("publish_page", "cref_save_crossreferences");
}

add_action('import_end','cref_import_end');
function cref_import_end() { 
	cref_rebuild(); 
}

function cref_options_page() {
global $cref_count_posts,$cref_count_refs;
switch($_REQUEST['action']) {
	case 'update':
		if(get_option('cref_use_slugs') != $_REQUEST['cref_use_slugs']) cref_rebuild_fast(-1);
		update_option( 'cref_use_slugs', $_REQUEST['cref_use_slugs'] );
		update_option( 'cref_show_backlinks', $_REQUEST['cref_show_backlinks'] );
		update_option( 'cref_translate_export', $_REQUEST['cref_translate_export'] );
		update_option( 'cref_before', $_REQUEST['cref_before'] );
		update_option( 'cref_between', $_REQUEST['cref_between'] );
		update_option( 'cref_after', $_REQUEST['cref_after'] );
		update_option( 'cref_empty', $_REQUEST['cref_empty'] );
		echo '<div id="message" class="updated fade"><p><strong>',__('Options were saved.','crossreferences'),'</strong></p></div>';
		break;
	case 'import';
		cref_import();
		if($cref_count_refs==0)
			$message = __('0 HTML links were found.','crossreferences');
		elseif($cref_count_refs==1)
			$message = __('1 HTML link was found.','crossreferences');
		else {
			$message = sprintf( __ngettext(
					'%d HTML links were converted in %d post or page.',
					'%d HTML links were converted in %d posts and pages.',
					$cref_count_posts,
					'crossreferences'
				), $cref_count_refs,$cref_count_posts);
		}
		echo '<div id="message" class="updated fade"><p><strong>',$message,'</strong></p></div>';
		break;
	case 'rename';
		$error=cref_rename($_REQUEST['cref_slug_old'],$_REQUEST['cref_slug_new']);
		if($error)
			echo '<div id="message" class="error fade"><p><strong>'.$error.'</strong></p></div>';
		else {
			echo '<div id="message" class="updated fade"><p><strong>',__('Slug was renamed.','crossreferences'),'</strong></p></div>';
		}
		break;
	case 'rebuild';
		cref_rebuild();
		echo '<div id="message" class="updated fade"><p><strong>',__('Table was rebuild.','crossreferences'),'</strong></p></div>';
		break;
	case 'deinstall';
		cref_deinstall($_REQUEST['cref_translate']);
		echo '<div id="message" class="updated fade"><p><strong>',__('Plugin was deinstalled.','crossreferences'),'</strong></p></div>';
		break;
}
if('deinstall' != $_REQUEST['action'])
	cref_options_page_content();
}

function cref_options_page_content() {
global $wpdb;
?>
<div class="wrap">
<h2><?php _e('Cross-references Options','crossreferences') ?></h2>
<form name="form1" method="post" action="">
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Post identification by','crossreferences'); ?></th>
<td>
<select name="cref_use_slugs" id="cref_use_slugs">
<?php
	$options = array(0 => __('ID (number)','crossreferences'), 1 => __('Slug (name)','crossreferences'));
	foreach ( $options as $key => $value) {
		$selected = (get_option('cref_use_slugs') == $key) ? 'selected="selected"' : '';
		echo "\n\t<option value='$key' $selected>$value</option>";
	}
?>
</select>
<br />
<?php 
_e('Define whether you want to set references with [cref slug] (for example [cref hello-world]) or with [cref ID] (for example [cref 1]). ','crossreferences');
$count = mysql_result(mysql_query("SELECT COUNT(*) FROM `$wpdb->crossreferences`"),0);
if($count > 0)
{
	echo '<br />';
	printf( __ngettext(
			'<em>Note:</em> If you change this now, %s existing reference at post or page will be automatically updated.',
			'<em>Note:</em> If you change this now, %s existing references at posts and pages will be automatically updated.',
			$count,
			'crossreferences'
		), number_format_i18n( $count ) );
}
?>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Related Posts List','crossreferences') ?></th>
<td>
<select name="cref_show_backlinks" id="cref_show_backlinks">
<?php
	$options = array(0 => __('Do not show related posts','crossreferences'), 1 => __('Show related posts','crossreferences'));
	foreach ( $options as $key => $value) {
		$selected = (get_option('cref_show_backlinks') == $key) ? 'selected="selected"' : '';
		echo "\n\t<option value='$key' $selected>$value</option>";
	}
?>
</select>
<br />
<?php _e('Whether the plugin inserts a related posts list at the end of each post and page. This does not require any template changes.','crossreferences') ?>
</td>
</tr>
<th scope="row"><?php _e('XML Export','crossreferences') ?></th>
<td>
<select name="cref_translate_export" id="cref_translate_export">
<?php
	$options = array(0 => __('Let references as [cref ...]','crossreferences'), 1 => __('Show related posts title','crossreferences'), 2 => __('Link to related posts','crossreferences'));
	foreach ( $options as $key => $value) {
		$selected = (get_option('cref_translate_export') == $key) ? 'selected="selected"' : '';
		echo "\n\t<option value='$key' $selected>$value</option>";
	}
?>
</select>
<br />
<?php _e('How the references are treated when exporting this blog as an xml file.','crossreferences') ?>
</td>
</tr>
</table>
<h2><?php _e('Display Options','crossreferences') ?></h2>
<?php _e('These options apply only if the related posts list is shown according to the option "Related Posts List" above. You can enter HTML here.<br />Use &amp;sp; to get a white space at the beginning or end of a string.','crossreferences') ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Before List','crossreferences') ?></th>
<td><input name="cref_before" type="text" id="cref_before" value="<?php form_option('cref_before'); ?>" size="60" class="code" /><br />
<?php 
_e('Show this at the beginning of the list. Examples: ','crossreferences');
printf("<br /><code>&lt;h3&gt;%s&lt;/h3&gt&lt;ul&gt;&lt;li&gt;</code><br /><code>&lt;p&gt;%s:&amp;sp;</code>",__('Related posts','crossreferences'),__('See also','crossreferences'));
?></td>
</tr>
<th scope="row"><?php _e('Between Items','crossreferences') ?></th>
<td><input name="cref_between" type="text" id="cref_between" value="<?php form_option('cref_between'); ?>" size="20" class="code" /><br />
<?php 
_e('Show this between items. Examples: ','crossreferences');
echo '<br /><code>&lt;/li&gt;&lt;li&gt;</code><br /><code>,&amp;sp;</code>';
?></td>
</tr>
<th scope="row"><?php _e('After List','crossreferences') ?></th>
<td><input name="cref_after" type="text" id="cref_after" value="<?php form_option('cref_after'); ?>" size="20" class="code" /><br />
<?php 
_e('Show this at the end of the list. Examples: ','crossreferences');
echo '<br /><code>&lt;/li&gt;&lt;/ul&gt;</code><br /><code>.&lt;/p&gt;</code>';
 ?></td>
</tr>
<th scope="row"><?php _e('Empty List','crossreferences') ?></th>
<td><input name="cref_empty" type="text" id="cref_empty" value="<?php form_option('cref_empty'); ?>" size="60" class="code" /><br />
<?php 
_e('Show this if there are no related posts. Example: ','crossreferences');
printf("<code>&lt;p&gt;%s&lt;/p&gt;</code>",__('There are no related posts yet.','crossreferences'));
?></td>
</tr>
</table>
<p class="submit">
<input type="hidden" name="action" value="update" />
<input type="submit" name="Submit" value="<?php _e('Save Changes','crossreferences') ?>" />
</p>
</form>
<h2><?php _e('Tools','crossreferences') ?></h2><form name="form1" method="post" action="">
<table class="form-table">
<tr>
<th scope="row"><?php _e('Slug Rename','crossreferences') ?></th>
<td>
<form name="form3" method="post" action="">
<input type="hidden" name="action" value="rename" />
<?php _e('Old slug:','crossreferences') ?> <input name="cref_slug_old" type="text" id="cref_slug_old" size="20" />
<?php _e('New slug:','crossreferences') ?> <input name="cref_slug_new" type="text" id="cref_slug_new" size="20" />
<input name="submit" type="Submit" value="<?php _e('Rename','crossreferences') ?>" /><br />
<?php _e('Changing page slugs will break your references (this is because of the way WordPress handles slugs). Here you can rename a slug, all references to it will be updated.','crossreferences') ?>
</form>
</td>
</tr>
<tr>
<th scope="row"><?php _e('Rebuild Table','crossreferences') ?></th>
<td>
<form name="form4" method="post" action="">
<input type="hidden" name="action" value="rebuild" />
<input name="submit" type="Submit" value="<?php _e('Rebuild','crossreferences') ?>" /><br />
<?php _e('The plugin stores information about your references in a database table. If the plugin was some time inactive, its information may not be accurate any more. You can rebuild the table, in order to correct all errors.','crossreferences') ?>
</form>
</td>
</tr>
<tr>
<th scope="row"><?php _e('Import HTML Links','crossreferences') ?></th>
<td>
<form name="form6" method="post" action="">
<input type="hidden" name="action" value="import" />
<input name="submit" type="Submit" value="<?php _e('Import','crossreferences') ?>" /><br />
<?php _e('Import all html links &lt;a href=...&gt;&lt;/a&gt; in posts and pages which refer to other posts or pages in this blog changing them to a [cref ...] reference.','crossreferences') ?>
</form>
</td>
</tr>
<tr>
<th scope="row"><?php _e('Deinstall Plugin','crossreferences') ?></th>
<td>
<form name="form5" method="post" action="">
<input type="hidden" name="action" value="deinstall" />
<input  name="submit" type="Submit" value="<?php _e('Deinstall','crossreferences') ?>" /><br />
<?php _e('If you do not want to use this plugin any more you can deinstall it completely: The plugin table and options are removed from the database and then the plugin is deactivated.','crossreferences'); 
if($count >0) {
    echo '<br />';
	printf( __ngettext(
			'What to do with the %s existing reference in a post or page:',
			'What to do with the %s existing references in posts and pages:',
			$count,
			'crossreferences'
		), number_format_i18n( $count ) );
	echo '<select name="cref_translate" id="cref_translate">';
	$options = array(0 => __('Let references as [cref ...]','crossreferences'), 1 => __('Show related posts title','crossreferences'), 2 => __('Link to related posts','crossreferences'));
	foreach ( $options as $key => $value) {
		$selected = $key == 0 ? 'selected="selected"' : '';
		echo "\n\t<option value='$key' $selected>$value</option>";
	}
	echo '</select>';
} else {
	echo '<input type="hidden" name="cref_translate" value="0" />';
}
?>
</form>
</td>
</tr>
</table>
</div>
<?php
}

function cref_rename($old,$new) {
global $wpdb;
	if(!$new or !$old)
		$error=__('Please enter both an old and a new post or page slug.','crossreferences');
	else {
		$wpdb->query( $wpdb->prepare("UPDATE `$wpdb->posts` SET post_name='%s' WHERE post_name='%s';",$new,$old) );
		$id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM `$wpdb->posts` WHERE post_name='%s';",$new) );
		if($id) {
			cref_change_all_post_slugs($id,$old,$new);
			if(0==$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `$wpdb->postmeta` WHERE post_id=%d AND meta_key='_wp_old_slug' AND meta_value='%s';"
			,$id,$old))) 
				$wpdb->query($wpdb->prepare("INSERT INTO `$wpdb->postmeta` (post_id,meta_key,meta_value) VALUES (%d,'_wp_old_slug','%s');"
				,$id,$old)); 
			cref_rebuild();
			$error=false;
		} else
			$error=sprintf(__('Unknown slug %s.','crossreferences'),"'$new'");
	}
return $error;
} 

function cref_rebuild() {
global $wpdb;
	$wpdb->query("TRUNCATE TABLE `$wpdb->crossreferences`;");
	$result = mysql_query("SELECT ID,post_content FROM `$wpdb->posts` WHERE post_type != 'revision' ORDER BY post_date, ID");
	while($row=mysql_fetch_array($result)) {
		cref_rebuild_content($row['ID'],$row['post_content'],0);
	}
}

function cref_rebuild_fast($link) {
global $wpdb;
	$result = mysql_query("SELECT DISTINCT ID,post_content FROM `$wpdb->crossreferences`
		INNER JOIN `$wpdb->posts` ON post_id=ID ORDER BY post_date, ID");
	while($row=mysql_fetch_array($result)) {
		$id=$row['ID'];
		$wpdb->query("DELETE FROM `$wpdb->crossreferences` WHERE `post_id`=$id;");
		cref_rebuild_content($id,$row['post_content'],$link);
	}
}

/*
rebuilds all [cref ...] ocurrences at the post content and updates the table wp_crossreferences
	$id = post ID
	$data = post content
Changes at the post content:
	if <slug> unknown at [cref <slug>] it looks up in the post old slugs log, if found it changes it
	Other changes:
		$link=-1 => [cref ID] is replaced by [cref slug] resp. [cref slug] is replaced by [cref ID]
		$link=1  => [cref ID/slug] is replaced by <a href="permalink">title</a> (only for published posts, otherwise like $link=2)
		$link=2  => [cref ID/slug] is replaced by title
*/
function cref_rebuild_content($id,$data,$link) { 
global $wpdb;
	$dirty=0;
	preg_match_all("/\[cref(\.from|)\s+([^\s\]]*)(\s+(.*?)|)\]/",$data,$results, PREG_SET_ORDER);                               
	if($results)  {
		foreach($results as $result) {
			list($ref_id,$newslug)=cref_get_postID_with_recovery($result[2],$link);
			if($ref_id > 0) {
				if($newslug != $result[2]) { //replace old slug/ID with the new one#
					$str = '[cref'.$result[1];
					$str.=' '.$newslug;
					if($result[4]) $str.=' '.$result[4];
					$str.=']';
					$data = str_replace($result[0], $str, $data);
					$dirty=1;
				}
				$post = get_post($ref_id);
				if($post->ID) {
					if($link > 0) {
						if(!$result[1]) {
							$title = $result[4];
							$ptitle = apply_filters('wp_title',$post->post_title);
							if($title=='') $title = $ptitle;
							if(2==$link and 'publish'==$post->post_status) {
								$name = '<a href="'.get_permalink($post->ID).'" title="'.$ptitle.'">'.$title.'</a>';
							} else {
								$name = $title;
							}
						} elseif($result[1]=='.from') {
							if(2==$link) 
								$name = '<a href="'.get_permalink($post->ID).'"></a>';
						}
						$data = str_replace($result[0], $name, $data );
						$dirty=1;
					}
					$wpdb->query("INSERT IGNORE INTO `$wpdb->crossreferences` 
						(`post_id`,`post_from`,`post_to`) VALUES ($id, $id, $ref_id);");
					if($result[1]=='.from')
						$wpdb->query("INSERT IGNORE INTO `$wpdb->crossreferences` 
							(`post_id`,`post_from`,`post_to`) VALUES ($id, $ref_id, $id);");
				}
			}
		}
	}
	if($dirty)
		$wpdb->query($wpdb->prepare("UPDATE `$wpdb->posts` SET post_content='%s' WHERE ID=%d",$data,$id));
} 

function cref_get_postID_with_recovery($ref,$link) {
global $wpdb;
	$flgSlug = get_option('cref_use_slugs') == 1;
	if($flgSlug) { 
		$where = "post_name='%s'"; 
		$newslugfield = ($link == -1) ? "ID" : "post_name"; 
	} else {
		if(is_numeric($ref)) $where = "ID=%d"; else $where = "post_name='%s'"; 
		$newslugfield = ($link == -1) ? "post_name" : "ID"; 
	}
	$row = $wpdb->get_row($wpdb->prepare("SELECT ID, post_parent, post_type, post_name FROM `$wpdb->posts` WHERE post_type IN ('post','page','revision') AND ".$where,$ref),ARRAY_A);
	#if (mysql_errno()) { echo mysql_error (); printf("SELECT ID, post_name FROM `$wpdb->posts` WHERE ".$where,$ref); }
	if($row) {
		if($row['post_type']=='revision') 
			$row = $wpdb->get_row($wpdb->prepare("SELECT ID, post_name FROM `$wpdb->posts` WHERE ID=%d",$row['post_parent']),ARRAY_A);
		return array($row['ID'],$row[$newslugfield]);
	} elseif($flgSlug) {
		//try with old slugs
		$row = $wpdb->get_row($wpdb->prepare("
			SELECT MIN(post_id) AS ID, MIN(post_name) AS post_name
			FROM `$wpdb->postmeta` INNER JOIN `$wpdb->posts` ON post_id=ID 
			WHERE meta_key='_wp_old_slug' AND meta_value='%s' 
			HAVING COUNT(*) = 1;
			",$ref),ARRAY_A);
		// if there are multiple identical _wp_old_slug values with now existing posts do not get it
		if($row) return array($row['ID'], $row[$newslugfield]);
	}
	return array(-1,$ref);
}

function cref_import() {
global $wpdb,$cref_useslugs,$cref_count_posts,$cref_count_refs;
	$result = mysql_query("SELECT ID,post_content FROM `$wpdb->posts` WHERE post_type != 'revision' ORDER BY post_date, ID");
	$cref_count_posts=0; $cref_count_refs=0;
	$cref_useslugs = get_option('cref_use_slugs');
	while($row=mysql_fetch_array($result)) {
		cref_import_content($row['ID'],$row['post_content']);
	}
	return $count;
}

/*
changes all <a href=...>...</a> ocurrences at the post content that refer to a post/page in this blog
to a [cref ...] reference.
*/
function cref_import_content($id,$data) { 
global $wpdb,$cref_useslugs,$cref_count_posts,$cref_count_refs;
	$dirty=0;
	preg_match_all("/<a\b[^>]*\bhref=\"?([^\s\"]+)\"?[^>]*>([^<]*)<\/a>/",$data,$results, PREG_SET_ORDER);                               
	if($results)  {
		foreach($results as $result) {
			$ref_id=url_to_postid($result[1]);
			if($ref_id > 0) {
				$post = get_post($ref_id);
				if($post->ID) {
					$str = '[cref';
					if(!$result[2]) $str.='.from';
					$str.=' '.($cref_useslugs == 1 ? $post->post_name : $ref_id);
					if($result[2] && $result[2] != $post->post_title) $str.=' '.$result[2];
					$str.=']';
					$data = str_replace($result[0], $str, $data);
					$dirty=1;$cref_count_refs++;
					$wpdb->query("INSERT IGNORE INTO `$wpdb->crossreferences` (`post_id`,`post_from`,`post_to`)
						SELECT $id, $id, $ref_id FROM $wpdb->posts WHERE ID=$ref_id;");
					if(!$result[2]) 	
						$wpdb->query("INSERT IGNORE INTO `$wpdb->crossreferences` (`post_id`,`post_from`,`post_to`)
							SELECT $id, $ref_id, $id FROM `$wpdb->posts` WHERE ID=$ref_id;");

				}
			}
		}
	}
	if($dirty) {
		$wpdb->query($wpdb->prepare("UPDATE `$wpdb->posts` SET post_content='%s' WHERE ID=%d",$data,$id));
		$cref_count_posts++;
	}
} 

function cref_deinstall($translate) {
global $wpdb;
	if($translate>0) cref_rebuild_fast($translate);
	$wpdb->query("DROP TABLE `$wpdb->crossreferences`;");
	delete_option("cref_dbversion");
	delete_option("cref_use_slugs");
	delete_option("cref_show_backlinks");
	delete_option("cref_translate_export");
	delete_option("cref_before");
	delete_option("cref_between");
	delete_option("cref_after");
	delete_option("cref_empty");
	deactivate_plugins(CREF_FOLDER.'/crossreferences.php');
}

?>
