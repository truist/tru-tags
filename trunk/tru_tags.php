<?php

$plugin['allow_html_help'] = 0;
$plugin['version'] = 'X.X';
$plugin['author'] = 'Nathan Arthur';
$plugin['author_uri'] = 'http://www.rainskit.com/';
$plugin['description'] = 'Article tagging';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

To learn more about tru_tags, check out the "introductory article":http://www.rainskit.com/blog/493/trutags-a-tagging-plugin-for-textpattern, "releases page":http://www.rainskit.com/reference/537/tru_tags-releases, "feature list":http://www.rainskit.com/reference/495/trutags-feature-list, "roadmap":http://www.rainskit.com/reference/554/tru_tags-roadmap, and "usage instructions":http://www.rainskit.com/reference/497/trutags-usage-instructions. 

You can also find the source code at "http://code.google.com/p/tru-tags/":http://code.google.com/p/tru-tags/.

I've taken the detailed help out of the plugin; my apologies.  It was too big and too difficult to keep maintaining on my site and in the plugin.  If, my site is ever down, however, the source HTML of the usage instructions is available at "http://code.google.com/p/tru-tags/source/browse/trunk/tru_tags-docs.html":http://code.google.com/p/tru-tags/source/browse/trunk/tru_tags-docs.html

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

#Copyright 2008 Nathan Arthur
#Released under the GNU Public License, see http://www.opensource.org/licenses/gpl-license.php for details
#This work is inspired by ran_tags by Ran Aroussi, originally found at http://aroussi.com/article/45/tagging-textpattern


### CONFIGURATION ###
#####################

# TURN BACK!  Configuration is no longer handled by editing the plugin.
# Check out the 'Extensions' tab in the Textpattern admin :)


# Changing these won't do any good.  They're just here as a convenience for development.
define('TRU_TAGS_FIELD', 'Keywords');
define('CLEAN_URLS', 'clean_urls');
define('TAG_SECTION', 'tag_section');
define('PARM_NAME', 'parm');
define('TAGS_IN_FEED_CATEGORIES', 'tags_in_feed_categories');
define('TAGS_IN_FEED_BODY', 'tags_in_feed_body');
define('TAGS_IN_WRITE_TAB', 'tags_in_write_tab');

global $tru_tags_prefs;
$tru_tags_prefs = tru_tags_load_prefs();


### PRIMARY TAG FUNCTIONS ###
#############################

function tru_tags_handler($atts) {
	$tag_parameter = tru_tags_tag_parameter(array(), false);
	if (!empty($tag_parameter)) {
		if (tru_tags_redirect_if_needed($tag_parameter)) {
			return '';
		}
		$clean_atts = tru_tags_fixup_query_atts($atts, $tag_parameter);
		$result = doArticles($clean_atts, true);		#function in TXP code
		if (trim($result) == '') {
			if (isset($atts['noarticles'])) {
				tru_tags_redirect($atts['noarticles'], true);
			} else {
				txp_die(gTxt('404_not_found'), '404');
			}
		} else {
			return $result;
		}
	} else {
		return tru_tags_cloud($atts);
	}
}


function tru_tags_archive($atts) {
	global $tru_tags_current_archive_tag;
	$tags = array_unique(tru_tags_cloud_query(tru_tags_get_standard_cloud_atts($atts, false, false)));
	sort($tags);
	foreach ($tags as $tag) {
		$tru_tags_current_archive_tag = $tag;
		$clean_atts = tru_tags_fixup_query_atts($atts, $tag);
		$results[] = doArticles($clean_atts, true);		#function in TXP code
	}
	return join(' ', $results);
}


function tru_tags_current_archive_tag($atts) {
	global $tru_tags_current_archive_tag;
	extract(lAtts(array('link' => '0'),  $atts, 0));
	if ($link) {
		return '<a href="' . tru_tags_linkify_tag($tru_tags_current_archive_tag) . '">' . $tru_tags_current_archive_tag . '</a>';
	} else {
		return $tru_tags_current_archive_tag;
	}
}


function tru_tags_cloud($atts) {
	return tru_tags_list(tru_tags_get_standard_cloud_atts($atts, false, false));
}


function tru_tags_list($atts) {
	$atts = tru_tags_get_standard_cloud_atts($atts, true, false);
	$all_tags = tru_tags_cloud_query($atts);

	return tru_tags_render_cloud($atts, $all_tags, $all_tags);
}


function tru_tags_from_article($atts) {
	global $thisarticle;
	extract($thisarticle);

	$all_tags = tru_tags_get_tags_for_article($thisid);
	
	$atts = tru_tags_get_standard_cloud_atts($atts, false, true);
	$all_tags_for_weight = $all_tags;
	if ($atts['useoverallcounts']) {
		$all_tags_for_weight = tru_tags_cloud_query($atts);
	}

	return tru_tags_render_cloud($atts, $all_tags, $all_tags_for_weight);
}


function tru_tags_if_has_tags($atts, $thing) {
	global $thisarticle;
	extract($thisarticle);

	$tags_field = TRU_TAGS_FIELD;

	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	return parse(EvalElse($thing, $rs));
}


function tru_tags_if_tag_search($atts, $thing) {
	extract(lAtts(array('tag' => ''),  $atts, 0));

	$tag_parameter = tru_tags_tag_parameter(array('striphyphens' => 1), false);
	$condition = (!empty($tag_parameter)) ? true : false;
	if ($condition && !empty($tag)) {
		$condition = ($tag_parameter == $tag);
	}
	return parse(EvalElse($thing, $condition));
}


function tru_tags_tag_parameter($atts, $safehtml = true) {
	global $tru_tags_prefs;
	extract(lAtts(array('striphyphens' => '0', 'urlencode' => 0),  $atts, 0));
                                  
	$parm = urldecode(strip_tags(gps($tru_tags_prefs[PARM_NAME]->value)));
	if ('lookup' == $striphyphens) {
		$atts = tru_tags_get_standard_cloud_atts(array(), false, false);
		$tag_list = array_unique(tru_tags_cloud_query($atts));
		foreach ($tag_list as $cloud_tag) {
			if ($parm == str_replace(' ', '-', $cloud_tag)) {
				$parm = $cloud_tag;
				break;
			}
		}
	} else if ($striphyphens) {
		$parm = str_replace('-', ' ', $parm);
	}
	if ($urlencode) {
		$parm = urlencode($parm);
	 } else if ($safehtml) {
		$parm = htmlspecialchars($parm);
	}

	return $parm;
}


function tru_tags_search_parameter() {
	trigger_error(gTxt('deprecated_tag'), E_USER_NOTICE);
	return strip_tags(gps('q'));
}


function tru_tags_related_tags_from_search($atts) {
	$tag_parameter = tru_tags_tag_parameter(array(), false);
	extract(lAtts(array('tag_parameter' => $tag_parameter),  $atts, 0));
	if (!empty($tag_parameter)) {
        $tags_field = TRU_TAGS_FIELD;
		$all_tags = array();

		$query_atts = tru_tags_fixup_query_atts($atts, $tag_parameter);
		$rs = tru_tags_redo_article_search($query_atts);
		if ($rs) {
			while ($a = nextRow($rs)) {
				$article_tags = array();
				if (array_key_exists($tags_field, $a)) {
					$article_tags = explode(",", trim(tru_tags_strtolower($a[$tags_field])));
				}
				$all_tags = array_merge($all_tags, tru_tags_trim_tags($article_tags));
			}
		}

		$alt_tag_parameter = str_replace('-', ' ', $tag_parameter);
		foreach ($all_tags as $key => $tag) {
			if ($tag == $tag_parameter || $tag == $alt_tag_parameter) {
				unset($all_tags[$key]);
			}
		}
		
		$cloud_atts = tru_tags_get_standard_cloud_atts($atts, false, false);
		$all_tags_for_weight = $all_tags;
		if ($cloud_atts['useoverallcounts']) {
			$all_tags_for_weight = tru_tags_cloud_query($cloud_atts);
		}

		return tru_tags_render_cloud($cloud_atts, $all_tags, $all_tags_for_weight);
	} else {
		return '';
	}
}


### CLOUD SUPPORT FUNCTIONS ###
###############################

function tru_tags_get_standard_cloud_atts($atts, $isList, $isArticle) {
	return lAtts(array('wraptag'	=> ($isList ? 'ul' : ''),
			'break'		=> ($isList ? 'li' : ', '),
			'class'		=> '',
			'breakclass'	=> '',
			'section'	=> '',
			'minpercent'	=> '100',
			'maxpercent'	=> ($isList || $isArticle ? '100' : '200'),
			'showcounts'	=> '',
			'countwrapchars'	=> '[]',
			'usereltag'	=> ($isArticle ? '1' : ''),
			'generatelinks'	=> '1',
			'mintagcount'	=> '0',
			'maxtagcount'	=> '1000',
			'setsizes'	=> ($isArticle ? '0' : '1'),
			'usenofollow'	=> '',
			'sort'		=> 'alpha',
			'useoverallcounts'	=> '',
			'setclasses'	=> ($isArticle ? '0' : '1'),
			'title'		=> '',
			'listlimit'	=> '',
			'keep'		=> 'largest',
			'cutoff'	=> 'chunk',
			'texttransform'	=> 'none',
			'linkpath'	=> '',
			'linkpathtail'	=> '',
			'filtersearch'	=> '1',
			'excludesection'=> '',
			'activeclass'	=> 'tagActive',
			'time'		=> 'past'
		),$atts, 0);
}


function tru_tags_cloud_query($atts) {
	extract($atts);

	$section_clause = '';
	if ($section <> '') {
		$keys = split(',', $section);
		foreach ($keys as $key) {
			$keyparts[] = " Section = '" . trim($key) . "'";
		}
		$section_clause = " AND (" . join(' or ', $keyparts) . ")";
	}

	$tags_field = TRU_TAGS_FIELD;
	include_once txpath.'/publish/search.php';

	$filter = tru_tags_filter_sections($excludesection);
	$filter .= ($filtersearch ? filterSearch() : '');

	switch ($time) {
		case 'any':
			$time = ""; break;
		case 'future':
			$time = " and Posted > now()"; break;
		default:
			$time = " and Posted <= now()";
	}
	global $prefs;
	extract($prefs);
	if (!$publish_expired_articles) {
		$time .= " and (now() <= Expires or Expires = ".NULLDATETIME.")";
	}

	$all_tags = array();
	$rs = safe_rows("$tags_field", "textpattern", "$tags_field <> ''" . $section_clause . $filter . " and Status >= '4'" . $time);
	foreach ($rs as $row) {
		$temp_array = array();
		if (array_key_exists($tags_field, $row)) {
			$temp_array = explode(",", trim(tru_tags_strtolower($row[$tags_field])));
		}
		$all_tags = array_merge($all_tags, tru_tags_trim_tags($temp_array));
	}

	return $all_tags;
}


function tru_tags_filter_sections($excludesection) {
	$sections = explode(',', $excludesection);
	$filters = array();
	foreach ($sections as $section) {
		$filters[] = "and Section != '".doSlash($section)."'";
	}
	return join(' ', $filters);
}


function tru_tags_render_cloud($atts, $all_tags, $all_tags_for_weight) {
	global $tru_tags_prefs;
	extract($atts);

	$tags_weight = array_count_values($all_tags_for_weight);

	foreach ($tags_weight as $tag => $weight) {
		if (!in_array($tag, $all_tags) 
		   || $tags_weight[$tag] < $mintagcount 
		   || $tags_weight[$tag] > $maxtagcount) {
			unset($tags_weight[$tag]);
		}
	}

	$sort_by_count = (strpos($sort, 'count') !== false);
	$sort_ascending = (strpos($sort, 'asc') !== false);
	$tags_weight = tru_tags_sort_tags($tags_weight, $sort_by_count, $sort_ascending);

	if ($listlimit > 0 && $listlimit < count($tags_weight)) {
		$resorted_tags = array();
		if ($keep == 'largest') {
			$resorted_tags = array_keys(tru_tags_sort_tags($tags_weight, true, false));
		} else {
			if ($keep == 'random') {
				foreach ($tags_weight as $tag => $weight) {
					$resorted_tags[$tag] = rand(0, $weight);
				}
				$resorted_tags = array_keys(tru_tags_sort_tags($resorted_tags, true, false));
			} else if ($keep == 'alpha') {
				$resorted_tags = array_keys($tags_weight);
				natcasesort($resorted_tags);
			}
			$cutoff = 'exact';
		}

		$last_good_index = $listlimit - 1;
		if ($cutoff == 'chunk') { //alternative is 'exact'
			$last_weight = -1;
			for ($i = 0; $i < $listlimit + 1; $i++) {
				$new_weight = $tags_weight[$resorted_tags[$i]];
				if ($new_weight != $last_weight) {
					$last_good_index = $i - 1;
					$last_weight = $new_weight;
				}
			}
			if ($last_good_index < 0) {
				$last_good_index = $listlimit - 1;
			}
		}

		$resorted_tags = array_chunk($resorted_tags, $last_good_index + 1);
		$resorted_tags = $resorted_tags[0];

		foreach ($tags_weight as $tag => $weight) {
			if (!in_array($tag, $resorted_tags)) {
				unset($tags_weight[$tag]);
			}
		}
	}

	if ($generatelinks) {
		if ($linkpath) {
			$urlprefix = $linkpath;
			$urlsuffix = $linkpathtail;
		} else {
			if (tru_tags_clean_urls()) {
				$urlprefix = hu . $tru_tags_prefs[TAG_SECTION]->value . '/';
			} else {
				$urlprefix = hu . '?s=' . $tru_tags_prefs[TAG_SECTION]->value . '&amp;' . $tru_tags_prefs[PARM_NAME]->value . '=';
			}
			$urlsuffix = (tru_tags_clean_urls() ? '/' : '');
		}

		if ($usereltag) {
			if ($usenofollow) {
				$urlsuffix .= '" rel="tag nofollow';
			} else {
				$urlsuffix .= '" rel="tag';
			}
		} else if ($usenofollow) {
			$urlsuffix .= '" rel="nofollow';
		}
	}

	if (count($tags_weight) > 0) {
		$max = max($tags_weight);
		$min = min($tags_weight);
	} else {
		$max = $min = 0;
	}
	$stepvalue = ($max == $min) ? 0 : ($maxpercent - $minpercent) / ($max - $min);

	$tags_html = array();
	$tag_search_tag = tru_tags_tag_parameter(array('striphyphens' => '1'));
	$tag_search_tag = function_exists("htmlspecialchars_decode") ? htmlspecialchars_decode($tag_search_tag) : html_entity_decode($tag_search_tag);
	foreach ($tags_weight as $tag => $weight) {
		$tag_weight = floor($minpercent + ($weight - $min) * $stepvalue);
		
		$style = '';
		if ($setsizes)
			$style = ' style="font-size: ' . $tag_weight . '%;"';
		
		$tag_class = '';
		if ($setclasses) {
			$tag_class = ' class="';
			if ($weight == $min) {
				$tag_class .= "tagSizeSmallest";
			} else if ($weight == $max) {
				$tag_class .= "tagSizeLargest";
			} else {
				$tag_class .= "tagSizeMedium";
			}
			$tag_class .= ' tagSize' . ($weight + 1 - $min);
			if ($tag == $tag_search_tag) {
				$tag_class .= ' ' . $activeclass;
			}
			$tag_class .= '"';
		}

		//adapted from code by gdtroiano, see http://forum.textpattern.com/viewtopic.php?pid=102875#p102875
		$titlecount = '';
		if ($title)
			$titlecount = ' title="' . $title . '"';
		$displaycount= '';
		$count = $countwrapchars{0} . $weight . $countwrapchars{1};
		if ($showcounts == 'title' || $showcounts == 'both')
			$titlecount = ' title="' . $title . $count . '"';
		if ($showcounts && $showcounts != 'title')
			$displaycount = ' ' . $count;

		if ($texttransform == 'capitalize') {
			$tag = ucwords($tag);
		} else if ($texttransform == 'uppercase') {
			$tag = strtoupper($tag);
		} else if ($texttransform == 'lowercase') {
			$tag = strtolower($tag);
		} else if ($texttransform == 'capfirst') {
			$tag = ucfirst($tag);
		}
		
		if ($generatelinks) {
			$wholeurl = '"' . $urlprefix . urlencode(str_replace(' ', '-', $tag)) . $urlsuffix . '"';
			$tags_html[] = '<a href=' . $wholeurl . $tag_class . $style . $titlecount . '>' . htmlspecialchars($tag) . '</a>' . $displaycount;
		} else if ($tag_class || $style || $titlecount) {
			$tags_html[] = '<span' . $tag_class . $style . $titlecount . '>' . htmlspecialchars($tag) . '</span>' . $displaycount;
		} else {
			$tags_html[] = htmlspecialchars($tag) . $displaycount;
		}
	}
	return tru_tags_do_wrap($tags_html, $wraptag, $break, $class, $breakclass);
}


### CLEAN URL FUNCTIONS ###
###########################

if (tru_tags_clean_urls()) {
	register_callback('tru_tags_clean_url_handler', 'pretext');
}


function tru_tags_clean_url_handler($event, $step) {
	global $tru_tags_prefs;
	$subpath = preg_replace("/https?:\/\/.*(\/.*)/Ui","$1",hu);
	$regsafesubpath = preg_quote($subpath, '/');
	$req = preg_replace("/^$regsafesubpath/i",'/',$_SERVER['REQUEST_URI']);

	$qs = strpos($req, '?');
	$qatts = ($qs ? '&'.substr($req, $qs + 1) : '');
	if ($qs) $req = substr($req, 0, $qs);

	$parts = array_values(array_filter(split('/', $req)));
	if (count($parts) == 2 && $parts[0] == $tru_tags_prefs[TAG_SECTION]->value) {
		$tag = $parts[1];
		$_SERVER['QUERY_STRING'] = $tru_tags_prefs[PARM_NAME]->value . '=' . $tag . $qatts;
		$_SERVER['REQUEST_URI'] = $subpath . $tru_tags_prefs[TAG_SECTION]->value . '/?' . $_SERVER['QUERY_STRING'];
		if (count($_POST) > 0) {
			$_POST['section'] = $tru_tags_prefs[TAG_SECTION]->value;
			$_POST[$tru_tags_prefs[PARM_NAME]->value] = $tag;
		} else {
			$_GET['section'] = $tru_tags_prefs[TAG_SECTION]->value;
			$_GET[$tru_tags_prefs[PARM_NAME]->value] = $tag;
		}
	}
}


function tru_tags_clean_urls() {
	global $tru_tags_prefs;
	return ('clean' == $tru_tags_prefs[CLEAN_URLS]->value);
}


### ADMIN SIDE FUNCTIONS ###
############################

if (@txpinterface == 'admin') {
	add_privs('tru_tags', '1,2');
	register_tab('extensions', 'tru_tags', 'tru_tags');
	register_callback('tru_tags_admin_tab', 'tru_tags');

	if ($tru_tags_prefs[TAGS_IN_WRITE_TAB]->value) {
		register_callback('tru_tags_admin_write_tab_handler', 'article');
	}
}


function tru_tags_admin_tab($event, $step) {
	require_privs('tru_tags');

	$results = tru_tags_admin_tab_handle_input();
	
	$atts = tru_tags_get_standard_cloud_atts(array(), false, false);
	$all_tags = tru_tags_cloud_query($atts);
	$cloud = tru_tags_render_cloud($atts, $all_tags, $all_tags);
	
	$redirects = tru_tags_load_redirects();
	
	tru_tags_admin_tab_render_page($results, $cloud, $redirects);
}


function tru_tags_admin_tab_render_page($results, $cloud, $redirects) {
	global $event;
	pagetop('tru_tags', '');

	include(txpath . '/include/txp_prefs.php');
	global $tru_tags_prefs;
	
	echo startTable('layout', '', '', '10px').'<tr><td style="border-right:2px solid gray">'.  # I know, I know...
		startTable('layout', '', '', '', '10px').'<tr><td style="border-bottom:2px solid gray">'.
			startTable('list', '', '', '', '300px').
				tr(hCell(gTxt('Current tags'))).
				tr(td($cloud)).
			endTable().
		'</td></tr><tr><td>'.
			startTable('list', '', '', '', '300px').
				tr(hCell(gTxt('tru_tags Reference'))).
				tr(td('<a href="http://www.rainskit.com/reference/497/trutags-usage-instructions">Usage instructions</a>'.
				'<br><a href="http://forum.textpattern.com/viewtopic.php?id=15084">Forum pages</a>'.
				'<br><a href="http://www.rainskit.com/reference/537/tru_tags-releases">Releases page</a>'.
				'<br><a href="http://www.rainskit.com/reference/554/tru_tags-roadmap">Release roadmap</a>'.
				'<br><a href="http://www.rainskit.com/reference/495/trutags-feature-list">Feature list</a>'.
				'<br><br><a href="http://code.google.com/p/tru-tags/source/browse/trunk/tru_tags.php">tru_tags source code</a>'.
				'<br><a href="http://code.google.com/p/tru-tags/source/browse/trunk/tru_tags-docs.html">Source HTML for the usage instructions</a>'.
				'<br><br><a href="http://www.rainskit.com/blog/493/trutags-a-tagging-plugin-for-textpattern">tru_tags</a>, by <a href="http://www.rainskit.com/">Nathan Arthur</a>'.
				'<br><br>'.
				'<div id="paypal"><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick" /><input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" name="submit" alt="Make a donation to Nathan Arthur" /><img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" /><input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----"></form></div>'
				)).
			endTable().
		'</td></tr>'.endTable().
	'</td><td>'.
		startTable('layout', '', '', '10px').'<tr><td style="border-bottom:2px solid gray;width:400px">';

		if ($results) {
			echo startTable('list', '', '', '', '100%').
				tr(hCell(gTxt('Results'))).
				tr(tda($results), ' style="color:red"').
			endTable();
			echo '</td></tr><tr><td style="border-bottom:2px solid gray">';
		}
		
		
		echo startTable('list', '', '', '', '100%').
			tr(tag(gTxt('Article Tag Maintenance').' ('.gTxt('Case Sensitive').'!)', 'th', ' colspan="5"')).
			tr(
				form(
					tda(gTxt('Rename').': ', ' style="vertical-align:middle"').
					td(text_input('matchtag', '', '15')).
					tda(gTxt('to').':', ' style="vertical-align:middle"').
					td(text_input('replacetag', '', '15')).
					td(fInput('submit', 'replace', gTxt('Run'), 'publish').eInput('tru_tags')),
					'', ' verify(\'' . gTxt('are_you_sure') . '\')"'
				)
			).
			tr(
				form(
					tda(gTxt('Delete').': ', ' style="vertical-align:middle"').
					td(text_input('deletetag', '', '15')).
					tdcs('', 2).
					td(fInput('submit', 'delete', gTxt('Run'), 'publish').eInput('tru_tags')),
					'', ' verify(\'' . gTxt('are_you_sure') . '\')"'
				)
			).
		endTable();
		
	echo '</td></tr><tr><td style="border-bottom:2px solid gray">';
		
		echo startTable('list', '', '', '', '100%').
			tr(tag(gTxt('Redirections'), 'th', ' colspan="4"'));
			foreach ($redirects as $lefttag => $righttag) {
				echo tr(
					tda(href($lefttag, tru_tags_linkify_tag($lefttag)), ' style="text-align: center"').
					tda(htmlspecialchars('=>'), ' style="text-align: center"').
					tda(href($righttag, tru_tags_linkify_tag($righttag)), ' style="text-align: center"').
					td('<a href="index.php?event=tru_tags&amp;delete_redirect='.urlencode($lefttag).'"  onclick="return verify(\''.gTxt('are_you_sure').'\')">Delete</a>')
				);
			}
			echo tr(
				'<form name="redirect" id="redirect" method="post" action="index.php?event=tru_tags" onsubmit="return verify(\''.gTxt('are_you_sure').'\')">'.
					tda(text_input('lefttag', '', '15'), ' style="text-align:center"').
					tda(htmlspecialchars('=>'), ' style="vertical-align:middle;text-align:center"').
					tda(text_input('righttag', '', '15'), ' style="text-align:center"').
					tda('<a href="#" onclick="if (verify(\''.gTxt('are_you_sure').'\')) document.getElementById(\'redirect\').submit(); return false;">Add new</a>', ' style="vertical-align:middle"').
					fInput('hidden', 'redirect', '1').
				'</form>'
				
			).
		endTable();
	
	echo '</td></tr><tr><td>';
	
		echo startTable('list').
			tr(tag(gTxt('Preferences'), 'th', ' colspan="2"')).
			form(
				tr(
					tda(gTxt('Use clean URLs').' ('.gTxt('site default is').' '.$tru_tags_prefs[CLEAN_URLS]->default_value.'): ', ' style="vertical-align:middle"').
					td(radio_list(CLEAN_URLS,
							array('clean'=>gTxt('clean'), 'messy'=>gTxt('messy')),
							$tru_tags_prefs[CLEAN_URLS]->value))
				).
				tr(
					tda(gTxt('Tag section name').' (default is "'.$tru_tags_prefs[TAG_SECTION]->default_value.'"): ', ' style="vertical-align:middle"').
					td(text_input(TAG_SECTION, $tru_tags_prefs[TAG_SECTION]->value, '15'))
				).
				tr(
					tda(gTxt('URL parameter for tag search').' (default is "'.$tru_tags_prefs[PARM_NAME]->default_value.'"): '.
						'<br>(you shouldn\'t change this unless you really know what you are doing)', ' style="vertical-align:middle"').
					td(text_input(PARM_NAME, $tru_tags_prefs[PARM_NAME]->value, '15'))
				).
				tr(
					tda(gTxt('Put tags into RSS/Atom feeds, in "Category" elements').
						': <br>(you probably want this)', ' style="vertical-align:middle"').
					td(yesnoRadio(TAGS_IN_FEED_CATEGORIES, $tru_tags_prefs[TAGS_IN_FEED_CATEGORIES]->value))
				).
				tr(
					tda('Append the tag list to the body of RSS/Atom feeds, '.
						'with links, and with rel="tag":<br>If this is turned on,'.
						'you can define a "misc" form named tru_tags_feed_tags '.
						'that will be used to render the tags in the feed.',
						' style="vertical-align:middle"').
					td(yesnoRadio(TAGS_IN_FEED_BODY, $tru_tags_prefs[TAGS_IN_FEED_BODY]->value))
				).
				tr(
					tda(gTxt('Show a clickable list of tags on the "Write" page').': ',
						' style="vertical-align:middle"').
					td(yesnoRadio(TAGS_IN_WRITE_TAB, $tru_tags_prefs[TAGS_IN_WRITE_TAB]->value))
				).
				tr(
					td('').
					tda(fInput('submit', 'prefs', gTxt('Save'), 'publish').eInput('tru_tags'), ' style="text-align:right"')
				),
				'', ' verify(\'' . gTxt('are_you_sure') . '\')"'
			).
		endTable().'</td></tr>'.
		endTable();
	
	echo '</td></tr>'.endTable();
}


function tru_tags_admin_tab_handle_input() {
	if (gps('prefs')) {
		return tru_tags_admin_update_prefs();
	} else if (gps('delete')) {
		return tru_tags_admin_delete_tag(gps('deletetag'));
	} else if (gps('replace')) {
		$result = tru_tags_admin_replace_tag(gps('matchtag'), gps('replacetag'), false);
		return $result . '<br>' . tru_tags_admin_redirect_tag(gps('matchtag'), gps('replacetag'));
	} else if (gps('redirect')) {
		return tru_tags_admin_redirect_tag(gps('lefttag'), gps('righttag'));
	} else if (gps('delete_redirect')) {
		return tru_tags_admin_delete_redirect(gps('delete_redirect'));
	} else {
		return '';
	}
}


function tru_tags_admin_update_prefs() {
	global $tru_tags_prefs;
	$results = array();
	foreach ($tru_tags_prefs as $pref) {
		$valid_value = $pref->validate_value(gps($pref->name));
		if ($valid_value && $valid_value <> gps($pref->name)) {
			return $valid_value;  ### this is a sneaky way to handle validation - sorry ;)
		}
		if ($valid_value <> $pref->value) {
			if ($valid_value == $pref->default_value) {
				$result = tru_tags_delete_pref($pref->name);
			} else {
				$result = tru_tags_upsert_pref($pref->name, $valid_value);
			}
			if ($result) {
				$results[] = 'Updated ' . $pref->name;
				$pref->value = $valid_value;
			} else {
				$results[] = 'Error updating ' . $pref->name;
			}
		}
	}
	if (count($results) == 0) {
		return 'No records need updating';
	} else {
		return join('<br>', $results);
	}
}


function tru_tags_upsert_pref($name, $value) {
	if (!safe_query('create table if not exists ' . safe_pfx('tru_tags_prefs').
		' (name varchar(255) primary key, '.
		'value varchar(255) not null)')) {
		return 'Serious error - unable to create the ' . safe_pfx('tru_tags_prefs') . ' table.';
	}

	return safe_upsert('tru_tags_prefs', 'value="'.$value.'"', 'name="'.$name.'"');
}


function tru_tags_delete_pref($name) {
	if (safe_delete('tru_tags_prefs', 'name="'.$name.'"')) {
		if (safe_count('tru_tags_prefs', '1') == 0 && !safe_query('drop table ' . safe_pfx('tru_tags_prefs'))) {
			return false;
		}
		return true;
	} else {
		return false;
	}
}


function tru_tags_admin_delete_tag($deletetag) {
	if (trim($deletetag)) {
		return tru_tags_admin_replace_tag($deletetag, '', true);
	} else {
		return 'Please enter a value';
	}
}


function tru_tags_admin_replace_tag($matchtag, $replacetag, $allow_blank_replacetag) {
	$matchtag = trim($matchtag);
	$replacetag = trim($replacetag);
	if ($matchtag && ($allow_blank_replacetag || $replacetag)) {
		if (safe_update('textpattern', TRU_TAGS_FIELD.'=trim(both \',\' from replace(concat(",", '.TRU_TAGS_FIELD.', ","), concat(",", \''.addslashes($matchtag).'\', ","), \','.addslashes($replacetag).',\'))', '1')) {
			return 'Updated '.mysql_affected_rows().' rows ("'.htmlspecialchars($matchtag).'"=>"'.htmlspecialchars($replacetag).'")';
		} else {
			return 'Error: ' . mysql_error();
		}
	} else {
		return 'Please enter a value in both fields';
	}
}


function tru_tags_admin_redirect_tag($lefttag, $righttag) {
	$lefttag = addslashes(tru_tags_strtolower(trim($lefttag)));
	$righttag = addslashes(tru_tags_strtolower(trim($righttag)));
	if (!$lefttag || !$righttag) {
		return 'Please enter a value in both fields';
	}
	
	if (!safe_query('create table if not exists ' . safe_pfx('tru_tags_redirects').
		' (lefttag varchar(255) primary key, '.
		'righttag varchar(255) not null)')) {
		return 'Serious error - unable to create the ' . safe_pfx('tru_tags_redirects') . ' table.';
	}

	if (safe_insert('tru_tags_redirects', 'lefttag="'.$lefttag.'",righttag="'.$righttag.'"')) {
		return 'Redirect added ("'.htmlspecialchars($lefttag).'"=>"'.htmlspecialchars($righttag).'") - please test it!';
	} else {
		return 'Error adding record - does it already exist?';
	}
}


function tru_tags_admin_delete_redirect($lefttag) {
	if (safe_delete('tru_tags_redirects', 'lefttag="'.addslashes($lefttag).'"')) {
		if (safe_count('tru_tags_redirects', '1') == 0 && !safe_query('drop table ' . safe_pfx('tru_tags_redirects'))) {
			return 'Redirect deleted, but unable to drop table ' . safe_pfx('tru_tags_redirects');
		}
		return 'Redirect deleted ("'.htmlspecialchars($lefttag).'")';
	} else {
		return 'Error deleting redirect';
	}
}


function tru_tags_admin_write_tab_handler($event, $step) {
	$atts = tru_tags_get_standard_cloud_atts(array(), true, true);
	$atts['time'] = 'any';
	$cloud = array_unique(tru_tags_cloud_query($atts));
	sort($cloud);

	$id = (empty($GLOBALS['ID']) ? gps('ID') : $GLOBALS['ID']);
	$article_tags = (empty($id) ? array() : tru_tags_get_tags_for_article($id));

	$links = array();
	foreach ($cloud as $tag) {
		$style = (in_array($tag, $article_tags) ? ' class="tag_chosen"' : '');
		$links[] = '<a href="#advanced"'.$style.' onclick="this.setAttribute(\\\'class\\\', toggleTag(\\\''.addslashes(addslashes($tag)).'\\\')); return false;">' . addslashes(htmlspecialchars($tag)) . '<\/a>';
	}
	$to_insert = join(', ', $links);
	$to_insert = "<style>a.tag_chosen{background-color: #FEB; color: black;}</style>" . $to_insert;

	$js = <<<EOF
		var keywordsField = document.getElementById('keywords');
		var parent = keywordsField.parentNode;
		parent.appendChild(document.createElement('br'));
		var cloud = document.createElement('span');
		cloud.setAttribute('class', 'tru_tags_admin_tags');
		cloud.innerHTML = '{$to_insert}';
		parent.appendChild(cloud);

		function toggleTag(tagName) {
			var regexTag = tagName.replace(/([\\\\\^\\$*+[\\]?{}.=!:(|)])/g,"\\\\$1");
			var tagRegex = new RegExp("((^|,)\\\s*)" + regexTag + "\\\s*(,\\\s*|$)");
			var textarea = document.getElementById('keywords');
			var curval = textarea.value.replace(/,?\s+$/, '');
			if ('' == curval) {
				textarea.value = tagName;
			} else if (curval.match(tagRegex)) {
				textarea.value = curval.replace(tagRegex, '$1').replace(/,?\s+$/, '');
				return '';
			} else if (',' == curval.charAt(curval.length - 1)) {
				textarea.value += ' ' + tagName;
			} else {
				textarea.value += ', ' + tagName;
			}
			return 'tag_chosen';
		}
EOF;

	echo script_js($js);
}


### ATOM/RSS FEED FUNCTIONS ###
###############################

register_callback('tru_tags_atom_handler', 'atom_entry');
function tru_tags_atom_handler($event, $step) { return tru_tags_feed_handler(true); }
register_callback('tru_tags_rss_handler', 'rss_entry');
function tru_tags_rss_handler($event, $step) { return tru_tags_feed_handler(false); }

function tru_tags_feed_handler($atom) {
	global $thisarticle, $tru_tags_prefs;
	extract($thisarticle);

	$tags = tru_tags_get_tags_for_article($thisid);
	
	if ($tru_tags_prefs[TAGS_IN_FEED_BODY]->value) {
		$extrabody = '';
		$FORM_NAME = 'tru_tags_feed_tags';
		if (fetch('form', 'txp_form', 'name', $FORM_NAME)) {
			$form = fetch_form($FORM_NAME);
			$extrabody = trim(parse($form));
		} else {
			$atts = tru_tags_get_standard_cloud_atts(array(), false, true);
			$extrabody = '<p>tags: ' . tru_tags_render_cloud($atts, $tags, $tags) . '</p>';
		}
		global $thisarticle;
		if (trim($thisarticle['excerpt'])) {
			$thisarticle['excerpt'] = trim($thisarticle['excerpt']).n.$extrabody.n;
		}
		$thisarticle['body'] = trim($thisarticle['body']).n.$extrabody.n;
	}

	if ($tru_tags_prefs[TAGS_IN_FEED_CATEGORIES]->value) {
		$output = array();
		foreach ($tags as $tag) {
			if ($atom) {
				$output[] = '<category term="' . htmlspecialchars($tag) . '" />';
			} else {
				$output[] = '<category>' . htmlspecialchars($tag) . '</category>';
			}
		}
		return n.join(n, $output).n;
	}
}


### PREFS FUNCTIONS / CLASSES ###
#################################

class tru_tags_pref {
	function tru_tags_pref($name, $default_value, $type) {
		$this->name = $name;
		$this->value = $default_value;
		$this->default_value = $default_value;
		$this->type = $type;
	}

	function validate_value($value) {
		if ($value) {
			return $value;
		} else if ($this->type == 'boolean') {
			return '0';
		} else {
			return 'Please enter a value for ' . $this->name;
		}
	}
}

function tru_tags_load_prefs() {
	$prefs = array();

	global $permlink_mode;
	$prefs[CLEAN_URLS] = new tru_tags_pref(CLEAN_URLS, ($permlink_mode != 'messy' ? 'clean' : 'messy'), 'string');
	$prefs[TAG_SECTION] = new tru_tags_pref(TAG_SECTION, 'tag', 'string');
	$prefs[PARM_NAME] = new tru_tags_pref(PARM_NAME, 't', 'string');
	$prefs[TAGS_IN_FEED_CATEGORIES] = new tru_tags_pref(TAGS_IN_FEED_CATEGORIES, '1', 'boolean');
	$prefs[TAGS_IN_FEED_BODY] = new tru_tags_pref(TAGS_IN_FEED_BODY, '0', 'boolean');
	$prefs[TAGS_IN_WRITE_TAB] = new tru_tags_pref(TAGS_IN_WRITE_TAB, '1', 'boolean');

	if (mysql_query("describe " . PFX . "tru_tags_prefs")) {
		$rs = safe_rows('*', 'tru_tags_prefs', '1');
		foreach ($rs as $row) {
			$prefs[$row['name']]->value = $row['value'];
		}
	}

	return $prefs;
}


### OTHER SUPPORT FUNCTIONS ###
###############################

function tru_tags_redirect_if_needed($tag_parameter) {
	$redirects = tru_tags_load_redirects();
	foreach ($redirects as $lefttag => $righttag) {
		if ($lefttag == $tag_parameter || $lefttag == str_replace('-', ' ', $tag_parameter)) {
			tru_tags_redirect($righttag, false);
			return true;
		}
	}

	return false;
}


function tru_tags_load_redirects() {
	$redirects = array();
	if (mysql_query("describe " . PFX . "tru_tags_redirects")) {
		$rs = safe_rows('*', 'tru_tags_redirects', '1 order by lefttag');
		foreach ($rs as $row) {
			$redirects[$row['lefttag']] = $row['righttag'];
		}
	}
	return $redirects;
}


function tru_tags_redirect($destination, $is_full_url) {
	global $tru_tags_prefs;
	if ($is_full_url) {
		$url = $destination;
		$message = 'The resource you requested has moved to ' . $destination;
	} else {
		$url = tru_tags_linkify_tag($destination, false);
		$message = 'The requested tag has been replaced by ' . $destination;
	}

	header('Location: ' . $url);
	txp_die($message, '301');
}


function tru_tags_linkify_tag($tag, $use_amp = true) {
	global $tru_tags_prefs;
	if (tru_tags_clean_urls()) {
		$urlprefix = hu . $tru_tags_prefs[TAG_SECTION]->value . '/';
	} else {
		$urlprefix = hu . '?s=' . $tru_tags_prefs[TAG_SECTION]->value . ($use_amp ? '&amp;' : '&') . $tru_tags_prefs[PARM_NAME]->value . '=';
	}
	$urlsuffix = (tru_tags_clean_urls() ? '/' : '');
	return $urlprefix . urlencode(str_replace(' ', '-', $tag)) . $urlsuffix;
}


function tru_tags_get_tags_for_article($articleID) {
	$tags_field = TRU_TAGS_FIELD;
	$rs = safe_row($tags_field, "textpattern", "ID='$articleID' AND $tags_field <> ''");
	$all_tags = array();
	if (array_key_exists($tags_field, $rs)) {
		$all_tags = explode(",", trim(tru_tags_strtolower($rs[$tags_field])));
	}

	return tru_tags_trim_tags($all_tags);
}


# fixes bug with 4.0.4's version of doWrap that caused spaces to show up before the commas
function tru_tags_do_wrap($list, $wraptag, $break, $class, $breakclass) {
	if (!$wraptag && !preg_match('/^\w+$/', $break)) {
		return join($break.n, $list);
	} else {
		return doWrap($list, $wraptag, $break, $class, $breakclass);
	}
}


function tru_tags_trim_tags($tag_array) {
	$trimmed = array();
	foreach ($tag_array as $tag) {
		if ("" != trim($tag)) {
			$trimmed[] = trim($tag);
		}
	}
	return $trimmed;
}


function tru_tags_fixup_query_atts($atts, $tag_parameter) {
	$keywords = explode(',', $tag_parameter);
	foreach ($keywords as $keyword) {
		if (strpos($keyword, '-') !== false) {
			$keywords[] = str_replace('-', ' ', $keyword);
		}
	}
	$atts['keywords'] = implode(',', $keywords);

	if (isset($atts['section']) && strpos($atts['section'], ',') !== false) {
		$atts['section'] = '';
	}

	if (isset($atts['excludesection'])) {
		unset($atts['excludesection']);
	}

	if (isset($atts['noarticles'])) {
		unset($atts['noarticles']);
	}
	
	if (!isset($atts['limit'])) {
		$atts['limit'] = '1000';
	}

	if (!isset($atts['allowoverride'])) {
		$atts['allowoverride'] = true;
	}

	if (isset($atts['searchform']) && !isset($atts['listform'])) {
		$atts['listform'] = $atts['searchform'];
	}

	return $atts;
}


function tru_tags_strtolower($str) {
	if (function_exists("mb_strtolower")) {
		return mb_strtolower($str, "UTF-8");
	} else {
		return strtolower($str);
	}
}


//these next two functions are gross, but I can't figure out another way to do it
function tru_tags_sort_tags($tags_weight, $sort_by_count, $sort_ascending) {
	global $tru_tags_tags_weight, $tru_tags_sort_ascending;

	$tru_tags_tags_weight = $tags_weight;
	$tru_tags_sort_ascending = $sort_ascending;

	$temp_array = array_keys($tags_weight);
	if ($sort_by_count) {
		usort($temp_array, "tru_tags_sort_tags_comparator");
	} else {
		natcasesort($temp_array);
		$temp_array = array_values($temp_array);
	}

	$sorted_array = array();
	foreach ($temp_array as $tag) {
		$sorted_array[$tag] = $tags_weight[$tag];
	}

	return $sorted_array;
}


function tru_tags_sort_tags_comparator($left, $right) {
	global $tru_tags_tags_weight, $tru_tags_sort_ascending;

	$left_weight = $tru_tags_tags_weight[$left];
	$right_weight = $tru_tags_tags_weight[$right];
	if ($left_weight == $right_weight) {
		$temp_array = array($left, $right);
		natcasesort($temp_array);
		$temp_array = array_values($temp_array);
		return ($temp_array[0] == $left ? -1 : 1);
	} else if ($tru_tags_sort_ascending) {
		return $left_weight - $right_weight;
	} else {
		return $right_weight - $left_weight;
	}
}



### BASTARD FUNCTIONS THAT SHOULDN'T HAVE TO EXIST ###
######################################################


function tru_tags_redo_article_search($atts) {
	$theAtts = lAtts(array('limit'     => 1000,
				'category'  => '',
				'section'   => '',
				'excerpted' => '',
				'author'    => '',
				'sortby'    => 'Posted',
				'sortdir'   => 'desc',
				'month'     => '',
				'keywords'  => '',
				'frontpage' => '',
				'id'        => '',
				'time'      => 'past',
				'status'    => '4',
				'offset'    => 0
			),$atts, 0);
	extract($theAtts);

	//Building query parts
	$frontpage = ($frontpage) ? filterFrontPage() : '';
	$category  = (!$category)  ? '' : " and ((Category1='".doslash($category)."') or (Category2='".doSlash($category)."')) ";
	$section   = (!$section)   ? '' : " and Section = '".doslash($section)."'";
	$excerpted = ($excerpted=='y')  ? " and Excerpt !=''" : '';
	$author    = (!$author)    ? '' : " and AuthorID = '".doslash($author)."'";
	$month     = (!$month)     ? '' : " and Posted like '".doSlash($month)."%'";
	$id        = (!$id)        ? '' : " and ID = '".intval($id)."'";
	switch ($time) {
		case 'any':
			$time = ""; break;
		case 'future':
			$time = " and Posted > now()"; break;
		default:
			$time = " and Posted < now()";
	}
	if (!is_numeric($status))
		$status = getStatusNum($status);

	$custom = '';
	// trying custom fields here
	$customFields = getCustomFields();
	if ($customFields) {
		foreach($customFields as $cField) {
			if (isset($atts[$cField]))
				$customPairs[$cField] = $atts[$cField];
		}
		if(!empty($customPairs)) 
			$custom =  buildCustomSql($customFields,$customPairs);
		else
			$custom = '';
	}

	//Allow keywords for no-custom articles. That tagging mode, you know
	if ($keywords) {
		$keys = doSlash(array_map('trim', split(',', $keywords)));
		foreach ($keys as $key) {
			$keyparts[] = "FIND_IN_SET('".$key."',Keywords)";
		}
		$keywords = " and (" . join(' or ',$keyparts) . ")";
	}

	if ($id)
		$statusq = " and Status >= '4'";
	else
		$statusq = " and Status='".doSlash($status)."'";

	$where = "1" . $statusq. $time.
		$id . $category . $section . $excerpted . $month . $author . $keywords . $custom . $frontpage;

		$pgoffset = $offset . ', ';

	$rs = safe_rows_start("*, unix_timestamp(Posted) as uPosted", 'textpattern',
		$where. ' order by ' . doslash($sortby) . ' ' . doSlash($sortdir) . ' limit ' . doSlash($limit));

	return $rs;
}



# --- END PLUGIN CODE ---

?>
