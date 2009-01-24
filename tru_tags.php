<?php
//------------------------------------------------------//
// This file has been decoded with ort_plugindecode.php //
//------------------------------------------------------//
 
#$plugin['name'] = 'tru_tags';
$plugin['version'] = '2.1';
$plugin['author'] = 'Nathan Arthur';
$plugin['author_uri'] = 'http://www.truist.com/';
$plugin['description'] = 'Tagging support for Textpattern';
$plugin['type'] = '1';
$plugin['allow_html_help'] = '0';
 
if (!defined('txpinterface'))
	@include_once('zem_tpl.php');
 
if(0){
?>
# --- BEGIN PLUGIN HELP ---
To learn more about tru_tags, check out the "introductory article":/blog/493/trutags-a-tagging-plugin-for-textpattern, "releases page":http://www.truist.com/reference/537/tru_tags-releases, "feature list":http://www.truist.com/reference/495/trutags-feature-list, and "usage instructions":http://www.truist.com/reference/497/trutags-usage-instructions.

I've taken the detailed help out of the plugin; my apologies.  It was too big and too difficult to keep maintaining on my site and in the plugin.
# --- END PLUGIN HELP ---
<?php
}
# --- BEGIN PLUGIN CODE ---

#Copyright 2007 Nathan Arthur
#Released under the GNU Public License, see http://www.opensource.org/licenses/gpl-license.php for details
#This work is inspired by ran_tags by Ran Aroussi, originally found at http://aroussi.com/article/45/tagging-textpattern


### CONFIGURATION ###
#####################

#See http://www.truist.com/reference/497/trutags-usage-instructions for instructions

# Switch this to '1' to get clean url support or '0' to turn it off.
# It will probably figure it out, though, so you shouldn't have to change it.
global $permlink_mode;
define('TRU_TAGS_USE_CLEAN_URLS', $permlink_mode != 'messy');

# This tells tru_tags which section to use for tags.
define('TRU_TAGS_SECTION', 'tag');

# This tells tru_tags what attribute name to use for tag searches.
define('TRU_TAGS_TAG_PARAMETER_NAME', 't');

# This tells tru_tags to put tags into the RSS and Atom feeds, in 'Category' elements.
# You probably want this.
define('TRU_TAGS_ADD_TAGS_TO_FEED_XML', 1);

# By default, this will append a tag list like "tags: trees, flowers, animals, etc"
# (with links, and with rel="tag") to the body of RSS/Atom feeds. If you define a
# 'misc' form named tru_tags_feed_tags, tru_tags will use that form to render the tags
# for the feed, instead.
define('TRU_TAGS_ADD_TAGS_TO_FEED_BODY', 0);

# This shows a list of clickable tags in the admin side on the 'Write' page
define('TRU_TAGS_SHOW_TAGS_IN_ADMIN', 1);

# Changing this won't do any good.  It's just here as a convenience for development.
define('TRU_TAGS_FIELD', 'Keywords');


### PRIMARY TAG FUNCTIONS ###
#############################

function tru_tags_handler($atts) {
	$tag_parameter = tru_tags_tag_parameter(array(), false);
	if (!empty($tag_parameter)) {
		$atts = tru_tags_fixup_query_atts($atts, $tag_parameter);
		return doArticles($atts, true);		#function in TXP code
	} else {
		return tru_tags_cloud($atts);
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
	$all_tags = tru_tags_get_tags_for_article();

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


function tru_tags_if_tag_search($atts, $thing)
{
	$tag_parameter = tru_tags_tag_parameter(array(), false);
	$condition = (!empty($tag_parameter)) ? true : false;
	return parse(EvalElse($thing, $condition));
}


function tru_tags_tag_parameter($atts, $safehtml = true)
{
	extract(lAtts(array('striphyphens' => 0, 'urlencode' => 0),  $atts, 0));

	$parm = urldecode(strip_tags(gps(TRU_TAGS_TAG_PARAMETER_NAME)));
	$parm = ($striphyphens ? str_replace('-', ' ', $parm) : $parm);
	if ($urlencode) {
		$parm = urlencode($parm);
	 } else if ($safehtml) {
		$parm = htmlspecialchars($parm);
	}

	return $parm;
}


function tru_tags_search_parameter()
{
	return strip_tags(gps('q'));
}


function tru_tags_related_tags_from_search($atts) {
	$tag_parameter = tru_tags_tag_parameter(array(), false);
	if (!empty($tag_parameter)) {
	        $tags_field = TRU_TAGS_FIELD;
		$all_tags = array();

		$query_atts = tru_tags_fixup_query_atts($atts, $tag_parameter);
		$rs = tru_tags_redo_article_search($query_atts);
		if ($rs) {
			while ($a = nextRow($rs)) {
				$article_tags = explode(",", trim(tru_tags_strtolower($a[$tags_field])));
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
			'excludesection'=> ''
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

	$all_tags = array();
	$rs = safe_rows("$tags_field", "textpattern", "$tags_field <> ''" . $section_clause . $filter . " and Status >= '4' and Posted < now()");
	foreach ($rs as $row) {
		$temp_array = explode(",", trim(tru_tags_strtolower($row[$tags_field])));
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
			if (TRU_TAGS_USE_CLEAN_URLS) {
				$urlprefix = hu . TRU_TAGS_SECTION . '/';
			} else {
				$urlprefix = hu . '?s=' . TRU_TAGS_SECTION . '&amp;t=';
			}
			$urlsuffix = (TRU_TAGS_USE_CLEAN_URLS ? '/' : '');
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

	foreach ($tags_weight as $tag => $weight) {
		$tag_weight = floor($minpercent + ($weight - $min) * $stepvalue);

		$style = '';
		if ($setsizes)
			$style = ' style="font-size: ' . $tag_weight . '%;"';

		$tag_class = '';
		if ($setclasses) {
			$tag_class = ' class="';
			if ($weight == $min)
				$tag_class .= "tagSizeSmallest";
			else if ($weight == $max)
				$tag_class .= "tagSizeLargest";
			else
				$tag_class .= "tagSizeMedium";
			$tag_class .= ' tagSize' . ($weight + 1 - $min);
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
			$tags_html[] = '<span' . $tag_class . $style . $titlecount . '>' . $tag . '</span>' . $displaycount;
		} else {
			$tags_html[] = $tag . $displaycount;
		}
	}
	return tru_tags_do_wrap($tags_html, $wraptag, $break, $class, $breakclass);
}


### CLEAN URL FUNCTIONS ###
###########################

if (TRU_TAGS_USE_CLEAN_URLS) {
	register_callback('tru_tags_clean_url_handler', 'pretext');
}

function tru_tags_clean_url_handler($event, $step) {
	$subpath = preg_quote(preg_replace("/https?:\/\/.*(\/.*)/Ui","$1",hu),'/');
	$req = preg_replace("/^$subpath/i",'/',$_SERVER['REQUEST_URI']);

	$qs = strpos($req, '?');
	$qatts = ($qs ? '&'.substr($req, $qs + 1) : '');
	if ($qs) $req = substr($req, 0, $qs);

	$parts = array_values(array_filter(split('/', $req)));
	if (count($parts) == 2 && $parts[0] == TRU_TAGS_SECTION) {
		$tag = $parts[1];
		$_SERVER['QUERY_STRING'] = TRU_TAGS_TAG_PARAMETER_NAME . '=' . $tag . $qatts;
		//$_SERVER['REQUEST_URI'] = $subpath . TRU_TAGS_SECTION . '/?' . $_SERVER['QUERY_STRING'];
		$_SERVER['REQUEST_URI'] = $subpath . TRU_TAGS_SECTION . '/?' . $_SERVER['QUERY_STRING'];
		if (count($_POST) > 0) {
			$_POST['section'] = TRU_TAGS_SECTION;
			$_POST[TRU_TAGS_TAG_PARAMETER_NAME] = $tag;
		} else {
			$_GET['section'] = TRU_TAGS_SECTION;
			$_GET[TRU_TAGS_TAG_PARAMETER_NAME] = $tag;
		}
	}
}


### ADMIN SIDE FUNCTIONS ###
############################

if (TRU_TAGS_SHOW_TAGS_IN_ADMIN)
	register_callback('tru_tags_admin_handler', 'article');

function tru_tags_admin_handler($event, $step) {
	$cloud = array_unique(tru_tags_cloud_query(tru_tags_get_standard_cloud_atts(array(), true, true)));
	sort($cloud);
	$links = array();
	foreach ($cloud as $tag) {
		$links[] = '<a href="#advanced" onclick="addTag(\\\''.$tag.'\\\'); return false;">' . htmlspecialchars($tag) . '<\/a>';
	}
	$to_insert = join(', ', $links);

	$js = <<<EOF
var keywordsField = document.getElementById('keywords');
var parent = keywordsField.parentNode;
parent.appendChild(document.createElement('br'));
var cloud = document.createElement('span');
cloud.setAttribute('class', 'tru_tags_admin_tags');
cloud.innerHTML = '{$to_insert}';
parent.appendChild(cloud);

function addTag(tagName) {
	var textarea = document.getElementById('keywords');
	var curval = textarea.value.replace(/\s+$/, '');
	if ('' == curval)
		textarea.value = tagName;
	else if (',' == curval.charAt(curval.length - 1))
		textarea.value += ' ' + tagName;
	else
		textarea.value += ', ' + tagName;
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
	$tags = tru_tags_get_tags_for_article();

	if (TRU_TAGS_ADD_TAGS_TO_FEED_BODY) {
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

	if (TRU_TAGS_ADD_TAGS_TO_FEED_XML) {
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


### OTHER SUPPORT FUNCTIONS ###
###############################

function tru_tags_get_tags_for_article() {
	global $thisarticle;
	extract($thisarticle);

	$tags_field = TRU_TAGS_FIELD;
	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	$all_tags = explode(",", trim(tru_tags_strtolower($rs[$tags_field])));

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

	if (isset($atts['section']) && strpos($atts['section'], ',') !== false)
		$atts['section'] = '';

	if (isset($atts['excludesection'])) {
		unset($atts['excludesection']);
	}

	if (!isset($atts['limit']))
		$atts['limit'] = '1000';

	$atts['allowoverride'] = true;

	return $atts;
}


function tru_tags_strtolower($str) {
	if (function_exists("mb_strtolower")) {
	//if (version_compare(phpversion(), "4.3.0", ">=")) {
		return mb_strtolower($str, "UTF-8");
	} else {
		return strtolower($str);
	}
}


//these next two functions are gross, but I can't figure out another way to do it
function tru_tags_sort_tags($tags_weight, $sort_by_count, $sort_ascending) {
	global $tru_tags_tags_weight, $tru_tags_sort_by_count, $tru_tags_sort_ascending;

	$tru_tags_tags_weight = $tags_weight;
	$tru_tags_sort_by_count = $sort_by_count;
	$tru_tags_sort_ascending = $sort_ascending;

	$temp_array = array_keys($tags_weight);
	usort($temp_array, "tru_tags_sort_tags_comparator");

	$sorted_array = array();
	foreach ($temp_array as $tag) {
		$sorted_array[$tag] = $tags_weight[$tag];
	}

	return $sorted_array;
}


function tru_tags_sort_tags_comparator($left, $right) {
	global $tru_tags_tags_weight, $tru_tags_sort_by_count, $tru_tags_sort_ascending;

	if ($tru_tags_sort_by_count) {
		$left_weight = $tru_tags_tags_weight[$left];
		$right_weight = $tru_tags_tags_weight[$right];
		if ($left_weight == $right_weight) {
			return strnatcasecmp($left, $right);
		} else if ($tru_tags_sort_ascending) {
			return $left_weight - $right_weight;
		} else {
			return $right_weight - $left_weight;
		}
	} else {
		return strnatcasecmp($left, $right);
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
		$keys = split(',',$keywords);
		foreach ($keys as $key) {
			$keyparts[] = " Keywords like '%".doSlash(trim($key))."%'";
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
