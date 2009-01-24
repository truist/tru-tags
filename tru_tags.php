<?php
//------------------------------------------------------//
// This file has been decoded with ort_plugindecode.php //
//------------------------------------------------------//
 
#$plugin['name'] = 'tru_tags';
$plugin['version'] = '1.7';
$plugin['author'] = 'Nathan Arthur';
$plugin['author_uri'] = 'http://www.truist.com/';
$plugin['description'] = 'Tagging support';
$plugin['type'] = '0';
$plugin['allow_html_help'] = '';
 
if (!defined('txpinterface'))
	@include_once('zem_tpl.php');
 
if(0){
?>
# --- BEGIN PLUGIN HELP ---

	<p>To learn more about tru_tags, check out the <a href="/blog/493/trutags-a-tagging-plugin-for-textpattern">introductory article</a> and <a href="http://www.truist.com/reference/495/trutags-feature-list">feature list</a>.</p>
	<p>Copyright 2006 Nathan Arthur</p>
	<p>Released under the GNU Public License, see http://www.opensource.org/licenses/gpl-license.php for details</p>
	<p>This work is based on ran_tags by Ran Aroussi, originally found at http://aroussi.com/article/45/tagging-textpattern.  It also contains code adapted from gdtroiano, see http://forum.textpattern.com/viewtopic.php?pid=102875#p102875.</p>
	<h3>Configuration</h3>
	<h4>Step 1: Create a new section</h4>
	<p>tru_tags depends on the existence of a special Textpattern section named &#8220;tag,&#8221; by default<sup><a href="#fn1144913186447bc419da46f">1</a></sup>.  Create that section, using whatever settings you like.  (You won&#8217;t be publishing articles to that section.)  Here&#8217;s what I use<sup><a href="#fn727918532447bc419df28c">2</a></sup>:</p>
	<p><img src="http://www.truist.com/images/2.png" height="280" width="398" alt="tag section configuration" class="diagram" /></p>
	<p class="footnote" id="fn1144913186447bc419da46f"><sup>1</sup> You can use a different name, but you have to use a special attribute in some of the plugin calls to make  everything work correctly.  See below for details.</p>
	<p class="footnote" id="fn727918532447bc419df28c"><sup>2</sup> Note that I use the &#8216;default&#8217; page &#8211; that choice may not be right for you.  This section will be shown whenever you click on a tag, to display the tag search results.  You&#8217;ll want a page that has the correct layout/headers/footers.  I use my default page, with <code>&lt;txp:if_section name="tag"&gt;</code> to change the page display in this case.</p>
	<h4>Step 2: Call the plugin from that section</h4>
	<p>To make tag searching and the default tag cloud work, you&#8217;ll need to call <code>&lt;txp:tru_tags_handler /&gt;</code> from the page you chose in Step 1.  I replaced the default <code>&lt;txp:article /&gt;</code> with something like this:</p>
<pre>&lt;txp:if_section name="tag"&gt;
  &lt;txp:tru_tags_handler /&gt;
&lt;txp:else /&gt;
  &lt;txp:article /&gt;
&lt;/txp:if_section&gt;</pre>
	<h4>Step 3: Configure your article form to display tags</h4>
	<p>To make each article show a list of all the tags associated with it, put someting like this in your article form:</p>
<pre>&lt;txp:tru_tags_if_has_tags&gt;
  tags: &lt;txp:tru_tags_from_article /&gt;
&lt;/txp:tru_tags_if_has_tags&gt;</pre>
	<h4>Step 4: Drop a custom tag cloud somewhere, if you want</h4>
	<p>If you&#8217;d like to show a tag cloud somewhere on your site (other than /tag/), put something like this in that page:</p>
<pre>&lt;txp:tru_tags_cloud /&gt;</pre>
	<p>See below for lots of formatting options, including the ability to output a simple list instead of a cloud (using <code>tru_tags_list</code>).</p>
	<h4>Step 5: Start tagging!</h4>
	<p>Whenever you write an article, put your tags into the Keywords field in Textpattern.  (The Keywords field hides behind the &#8220;Advanced Options&#8221; link on the left side of the &#8220;write&#8221; page.)  Tags should be separated by commas, and can have spaces<sup><a href="#fn1144913186447bc419da46f">1</a></sup>.</p>
	<p>You&#8217;ll probably want to install Rob Sable&#8217;s <a href="http://www.wilshireone.com/textpattern-plugins/rss-admin-show-adv-opts">rss_admin_show_adv_opts</a>, which will automatically expand the &#8220;Advanced Options&#8221; section of the &#8220;write&#8221; page, when you are writing articles.  That gives you easy access to the Keywords field.</p>
	<p class="footnote" id="fn1144913186447bc419da46f"><sup>1</sup> Tags with spaces will generate urls with dashes, which will work correctly.  Tags with dashes will also work correctly.</p>
	<h4>Step 6: Fancy display customization</h4>
	<p>You can use <code>tru_tags_if_tag_search</code>, <code>tru_tags_tag_parameter</code>, and <code>tru_tags_search_parameter</code> to customize your page titles or tag search results.  See below for details.  See the titlebar of <a href="http://www.truist.com/">truist.com</a> for an example.</p>
	<h4>Step 7: Turn on clean urls</h4>
	<p>Clean urls are now possible with tru_tags, provided that you are willing to modify Textpattern&#8217;s rewrite rules (in the .htaccess file).  But be warned &#8211; rewrite rules are complex.  I make no guarantees that this won&#8217;t eat your children.  <strong>You don&#8217;t have to do this</strong> but a lot of people want it.  This site uses it, if that helps.</p>
	<p>Here&#8217;s what to do:</p>
	<ol>
		<li>Add the following lines to Textpattern&#8217;s .htaccess file (found in the root of your Textpattern directory), adjusting them if necessary.  These lines should be <strong>after</strong> the</li>
	</ol>
	<p><div class="pre"></p>
	<p>RewriteRule &#94;(.&#43;) &#45; [PT,L]</p>
	<p></div></p>
	<p>and <strong>before</strong> the</p>
	<p><div class="pre"></p>
	<p>RewriteRule &#94;(.&#42;) index.php</p>
	<p></div></p>
	<p>Here&#8217;s the bit to insert:</p>
	<p><div class="pre"></p>
	<p>#This path should match the relative url of your Textpattern install<br />
RewriteBase /<br />
#If you're using a section other than &#34;tag&#34;, change the following two rules accordingly<br />
RewriteRule &#94;tag/([&#94;/]&#43;)$ tag/$1/ [R,L]<br />
RewriteRule &#94;tag/(.&#43;)/$ &#63;s&#61;tag&amp;t&#61;$1</p>
	<p></div></p>
	<ol>
		<li><strong>Check to make sure your site is still working.</strong></li>
		<li>Edit the tru_tags plugin (admin -> plugins, click &#8216;Edit&#8217;) and change the <code>tru_tags_use_clean_urls()</code> function to return <code>1</code>.  (It&#8217;s the first function.)</li>
		<li>Clean urls should be working now.  Try clicking a tag link to see what url it references.</li>
		<li>By default, article tags will set the <code>rel="tag"</code> attribute, and the tag cloud (or list) won&#8217;t.  This can be controlled with the <code>usereltag</code> attribute; see below for details.</li>
	</ol>
	<h3>Tag reference</h3>
	<h4><code>tru_tags_handler</code></h4>
	<p>This is the main function that drives tag search and shows the generic tag cloud.  It should be called from the page that is used in the &#8216;tag&#8217; section.  It generally calls <code>doArticles()</code> (in the Textpattern code) to display tag search results, but if no tag was passed in the url it will call <code>tru_tags_cloud</code> instead.</p>
	<p>This tag accepts most of the standard <code>txp:article</code> attributes, which will be applied during the tag search.  Note that <code>tru_tags_handler</code> <strong>does not</strong> support using multiple sections with the <code>section</code> attribute, when doing a tag search.  If multiple sections are passed, none are used.</p>
	<p><strong>Note:</strong> In Textpattern, the <code>limit</code> attribute is defaulted to <code>10</code>, to limit the output to 10 articles per page, and the <code>txp:older</code> and <code>txp:newer</code> tags are used to paginate the full list.  The <code>txp:older</code> and <code>txp:newer</code> tags do not work with tru_tags, but the <code>limit</code> is still used by Textpattern when it outputs the articles.  Therefore, tru_tags uses a default limit of <code>1000</code> when doing an article search.  You can override this limit by setting the <code>limit</code> attribute on <code>tru_tags_handler</code>.</p>
	<p>This tag will also accept all of the attributes used by <code>tru_tag_cloud</code>.  See below for details.</p>
	<h4><code>tru_tags_if_has_tags</code></h4>
	<p>This conditional tag can be used in an article form, and will evaluate its contents (e.g. &#8220;return true&#8221;) if the current article has tags.</p>
	<h4><code>tru_tags_from_article</code></h4>
	<p>This tag can be used in an article form to return a list of tags associated with the current article.  Typically (see below), each tag in the list will be a link (<code>&lt;a href=...&gt;</code>) to the tag search url for that particular tag.</p>
	<p>This tag accepts the standard <code>wraptag</code>, <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes.</p>
	<p>If you don&#8217;t want to have the tags be links, you can set <code>generatelinks="0"</code> to turn the links off.</p>
	<p>It also accepts a <code>tagsection</code> attribute, which tells tru_tags what relative url to use to find the &#8216;tag&#8217; section.  This can be useful if you have named the section something other than &#8216;tag&#8217;.  By default, this is set to <code>tag</code>.</p>
	<p>If you have clean urls turned on (see above), you can use the <code>usereltags</code> attribute to specify whether links in the list should have the <a href="http://microformats.org/wiki/reltag"><code>rel="tag"</code></a>  (used by <a href="http://www.technorati.com/">Technorati</a>) attribute set.  Note that this won&#8217;t work if you turn off links.  Also note that tags don&#8217;t show up in the RSS/Atom feeds, unless you <a href="http://forum.textpattern.com/viewtopic.php?pid=40907#p40907">hack the textpattern source</a>.  This is on by default.</p>
	<p>Again if you have clean urls turned on, you can use the <code>usenofollow</code> attribute to specify whether links in the list should have the <a href="http://googleblog.blogspot.com/2005/01/preventing-comment-spam.html"><code>rel="nofollow"</code></a> attribute set.  This is off by default.</p>
	<h4><code>tru_tags_cloud</code></h4>
	<h4><code>tru_tags_list</code></h4>
	<p>These two tags do the exact same thing &#8211; <code>tru_tags_cloud</code> just provides different defaults to <code>tru_tags_list</code>.</p>
<p><code>tru_tags_cloud</code> can be used on any page, and is generally used to generate a simple tag cloud of all the tags used on your site.  The cloud is really just a list of links, much like that generated by <code>tru_tags_from_article</code>, but with a <code>style</code> attribute set on each link to give it a font size ranging from 100% to 200%.</p>
<p><code>tru_tags_list</code> can be used on any page, and is generally used to output a bulleted list of all the tags used on your site.  By default, the tags will all have a font-size of 100%.</p>
<p><code>tru_tags_cloud</code> and <code>tru_tags_list</code> both set the <code>class</code> attribute of each tag, specifying two classes.  The first class groups the tags into categories, with classes of <code>tagSizeSmallest</code>, <code>tagSizeMedium</code>, and <code>tagSizeLargest</code>.  Using these, you could make the smallest and largest tags have different styles than all the others.</p>
	<p>The second class indicates the &#8220;step&#8221; of the current tag, with classes of <code>tagSize1</code>, <code>tagSize2</code>, and so on.  These give you precise control over each tag size, if you want it.</p>
	<p>If you use these classes to create special CSS rules, you may also want to set the <code>setsizes</code> attribute, described below.</p>
	<h5>Both tags accept the following attributes:</h5>
	<ul>
		<li>The standard <code>wraptag</code>, <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes.  <code>tru_tags_cloud</code> has a default <code>break</code> of a comma.  <code>tru_tags_list</code> has a default <code>wraptag</code> of <code>ul</code> and a default <code>break</code> of <code>li</code>.</li>
		<li><code>tagsection</code>, which tells tru_tags what relative url to use to find the &#8216;tag&#8217; section.  This can be useful if you have named the section something other than &#8216;tag&#8217;.  By default, this is set to <code>tag</code>.</li>
		<li><code>section</code>, which tells it to limit the list to tags from the given section or sections.  For example, <code>&lt;txp:tru_tags_cloud section="blog,reference" /&gt;</code> would only show tags from the &#8220;blog&#8221; and &#8220;reference&#8221; sections.  By default, this is set to blank (to show tags from all sections).
	<ul>
		<li><strong>Note:</strong> if you use <code>section</code> to limit the cloud to a particular section, it won&#8217;t limit the tag search feature to that section.  The tag search finds (tagged) articles from all sections, no matter what.  That&#8217;s a side-effect of the use of <code>doArticles()</code>, and I don&#8217;t think there&#8217;s anything I can do about it.</li>
	</ul></li>
		<li><code>minpercent</code> and <code>maxpercent</code>, which can be used to control the weighted font sizes in the tag cloud/list.  <code>tru_tags_cloud</code> defaults to <code>100</code> and <code>200</code>, respectively, and <code>tru_tags_list</code> defaults to <code>100</code> and <code>100</code>.</li>
		<li><code>showcounts</code>, which will append a number indicating the number of times a tag has been used, to each tag in the list.  For example, you might see: <span class="pre"><a href="/?s=tag&#38;t=life">life</a> [3], <a href="/?s=tag&#38;t=tech">tech</a> [5]</span> in my tag cloud, if this was turned on.  This is off by default.  Use <code>1</code> or <code>true</code> to turn it on.
	<ul>
		<li>This can also be used to put the counts in the <code>title</code> attribute of the links, which will make it appear in a tooltip.  Use <code>showcounts="title"</code> or <code>showcounts="both"</code> (to show it in both places) to turn it on.</li>
	</ul></li>
		<li><code>countwrapchars</code>, which controls the characters used to show the tag count, if <code>showcounts</code> is turned on.  By default this is <code>[]</code>.  The first character will be put on the left side of the number, and the second character will be put on the right.  For example, a <code>countwrapchars</code> of <code>()</code> would show: <span class="pre"><a href="/?s=tag&#38;t=life">life</a> (3), <a href="/?s=tag&#38;t=tech">tech</a> (5)</span></li>
		<li>If you have clean urls turned on (see above), you can use the <code>usereltags</code> attribute to specify whether links in the list should have the <a href="http://microformats.org/wiki/reltag"><code>rel="tag"</code></a>  (used by <a href="http://www.technorati.com/">Technorati</a>) attribute set.  This is off by default.</li>
		<li>Again if you have clean urls turned on, you can use the <code>usenofollow</code> attribute to specify whether links in the list should have the <a href="http://googleblog.blogspot.com/2005/01/preventing-comment-spam.html"><code>rel="nofollow"</code></a> attribute set.  This is off by default.</li>
		<li>If you don&#8217;t want the tags to be links, you can set <code>generatelinks="0"</code> to turn them off.</li>
		<li><code>mintagcount</code> and <code>maxtagcount</code> can be used to hide tags that only have a few articles, or that have too many.  They are defaulted to <code>0</code> and <code>1000</code>, respectively.  For example, <code>mintagcount="2"</code> would hide any tags that were only associated with a single article.  If you do this, you may want to add a link to the default tag cloud, usually found at <code>/tag/</code>.</li>
		<li>If you want to control the size(s) of the tags yourself (through CSS), set <code>setsizes="0"</code> to turn off the <code>style="font-size: XXX%"</code> attribute generation.  That will leave behind the default CSS classes, which you can use to control the display of your cloud.</li>
		<li><code>sort</code> can be used to sort the cloud by tag frequency, rather than the default of alphabetically.  Use <code>sort="count"</code> to sort by frequency in descending order, and <code>sort="count asc"</code> to sort by frequency in ascending order.</li>
	</ul>
	<p>Note that you can use the attributes to make each tag do the same thing.  <code>tru_tags_cloud</code> is just a convenience function for generating a tag cloud using <code>tru_tags_list</code>.  Therefore, it&#8217;s possible to have a tag cloud with tag counts showing, or have a bulleted list with variable font sizes, etc.</p>
	<h4><code>tru_tags_if_tag_search</code></h4>
	<p>This conditional tag can be used anywhere and will evaluate its contents (e.g. &#8220;return true&#8221;) if the current url indicates that there is a tag search going on.  This can be useful if you want to do something like customize the titlebar when using the tag search.</p>
	<h4><code>tru_tags_tag_parameter</code></h4>
	<p>This tag can be used anywhere and will return the name of the current tag under search, during a tag search.  This is generally used with <code>tru_tags_if_tag_search</code>.</p>
	<h4><code>tru_tags_search_parameter</code></h4>
	<p>This tag can be used anywhere and will return the text the user typed into the standard search box, during a regular search.  This tag is not specifically related to tagging, but can be handy for customizing the titlebar on search result pages.</p>


 
# --- END PLUGIN HELP ---
<?php
}
# --- BEGIN PLUGIN CODE ---



#Copyright 2006 Nathan Arthur
#Released under the GNU Public License, see http://www.opensource.org/licenses/gpl-license.php for details
#This work is based on ran_tags by Ran Aroussi, originally found at http://aroussi.com/article/45/tagging-textpattern


#See http://www.truist.com/reference/497/trutags-usage-instructions for instructions
function tru_tags_use_clean_urls()
{
	return 0; #DO NOT ENABLE THIS unless you've read the instructions
}


function tru_tags_handler($atts) {
	extract($_GET);
	if (isset($t)) {
		$keywords = explode(',', strip_tags($t));
		foreach ($keywords as $keyword) {
			if (strpos($keyword, '-') !== false)
				$keywords[] = str_replace('-', ' ', $keyword);
		}
		$atts['keywords'] = implode(',', $keywords);

		if ($atts['section'] && strpos($atts['section'], ',') !== false)
			$atts['section'] = '';

		if (!$atts['limit'])
			$atts['limit'] = '1000';
		$atts['allowoverride'] = true;

		return doArticles($atts, true);
	} else {
		return tru_tags_cloud($atts);
	}
}


function tru_tags_if_has_tags($atts, $thing) {
	global $thisarticle;
	extract($thisarticle);

	$tags_field = tru_tags_field();

	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	return parse(EvalElse($thing, $rs));
}


function tru_tags_from_article($atts) {

	global $thisarticle, $siteurl, $path_from_root;

	extract($_SERVER);
	extract($thisarticle);

	extract(lAtts(array('wraptag'       => '',
						'break'         => ', ',
						'class'         => '',
						'breakclass'	=> '',
						'tagsection'	=> 'tag',
						'usereltag'	=> '1',
						'generatelinks'	=> '1',
						'usenofollow'	=> ''
						),$atts));

	$tags_field = tru_tags_field();

	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	$all_tags = explode(",", trim(mb_strtolower($rs[$tags_field], "UTF-8")));
	sort($all_tags);
	$all_tags = array_unique($all_tags);

	if ($generatelinks) {
		if (tru_tags_use_clean_urls()) {
			$urlprefix = 'http://' . $siteurl . $path_from_root . $tagsection . '/';
			if ($usereltag) {
				if ($usenofollow) {
					$urlsuffix = '/" rel="tag nofollow';
				} else {
					$urlsuffix = '/" rel="tag';
				}
			} else if ($usenofollow) {
				$urlsuffix = '/" rel="nofollow';
			} else {
				$urlsuffix = '/';
			}
		} else {
			$urlprefix = 'http://' . $siteurl . $path_from_root . '?s=' . $tagsection . '&amp;t=';
			$urlsuffix = '';
		}

		for($i=0; $i<count($all_tags); $i++) {
			$tags_html[] = '<a href="' . $urlprefix . str_replace(' ', '-', trim($all_tags[$i])) . $urlsuffix . '">' . trim($all_tags[$i]) .'</a>';
		}
	} else {
		$tags_html = $all_tags;
	}

	return doWrap($tags_html, $wraptag, $break, $class, $breakclass);
}


function tru_tags_cloud($atts) {
	$atts = lAtts(array('wraptag'	=> '',
				'break'	=> ', ',
				'class'	=> '',
				'breakclass'	=> '',
				'tagsection'	=> 'tag',
				'section'	=> '',
				'minpercent'	=> '100',
				'maxpercent'	=> '200',
				'showcounts'	=> '',
				'countwrapchars'	=> '[]',
				'usereltag'	=> '',
				'generatelinks'	=> '1',
				'mintagcount'	=> '0',
				'maxtagcount'	=> '1000',
				'setsizes'	=> '1',
				'usenofollow'	=> '',
				'sort'		=> 'alpha'
			),$atts);

	return tru_tags_list($atts);
}


function tru_tags_list($atts) {
	global $siteurl, $path_from_root;
	extract($_SERVER);

	$atts = lAtts(array('wraptag'	=> 'ul',
				'break'	=> 'li',
				'class'	=> '',
				'breakclass'	=> '',
				'tagsection'	=> 'tag',
				'section'	=> '',
				'minpercent'	=> '100',
				'maxpercent'	=> '100',
				'showcounts'	=> '',
				'countwrapchars'	=> '[]',
				'usereltag'	=> '',
				'generatelinks'	=> '1',
				'mintagcount'	=> '0',
				'maxtagcount'	=> '1000',
				'setsizes'	=> '1',
				'usenofollow'	=> '',
				'sort'		=> 'alpha'
			),$atts);
	extract($atts);

	$tags_field = tru_tags_field();

	$section_clause = '';
	if ($section <> '') {
		$keys = split(',', $section);
		foreach ($keys as $key) {
			$keyparts[] = " Section = '" . trim($key) . "'";
		}
		$section_clause = " AND (" . join(' or ', $keyparts) . ")";
	}

	include_once txpath.'/publish/search.php';
	$filter = filterSearch();
	$all_tags = array();
	$rs = safe_rows("$tags_field", "textpattern", "$tags_field <> ''" . $section_clause . $filter . " and Status >= '4' and Posted < now()");
	foreach ($rs as $row) {
		$temp_array = explode(",", trim(mb_strtolower($row[$tags_field], "UTF-8")));
		for ($i = 0; $i < count($temp_array); $i++) {
			$temp_array[$i] = trim($temp_array[$i]);
		}
		$all_tags = array_merge($all_tags, $temp_array);
	}
	sort($all_tags);

	$tags_weight = array_count_values($all_tags);
	$tags_unique = array_unique($all_tags);

	foreach ($tags_unique as $key => $tag) {
		if ($tags_weight[$tag] < $mintagcount || $tags_weight[$tag] > $maxtagcount) {
			unset($tags_unique[$key]);
			unset($tags_weight[$tag]);
		}
	}

	if (strpos($sort, 'count') !== false) {
		if (strpos($sort, 'asc') !== false) {
			array_multisort($tags_weight, SORT_ASC, $tags_unique);
		} else {
			array_multisort($tags_weight, SORT_DESC, $tags_unique);
		}
	}

	$max = max($tags_weight);
	$min = min($tags_weight);
	$stepvalue = ($max == $min) ? 0 : ($maxpercent - $minpercent) / ($max - $min);

	if ($generatelinks) {
		if (tru_tags_use_clean_urls()) {
			$urlprefix = 'http://' . $siteurl . $path_from_root . $tagsection . '/';
			if ($usereltag) {
				if ($usenofollow) {
					$urlsuffix = '/" rel="tag nofollow';
				} else {
					$urlsuffix = '/" rel="tag';
				}
			} else if ($usenofollow) {
				$urlsuffix = '/" rel="nofollow';
			} else {
				$urlsuffix = '/';
			}
		} else {
			$urlprefix = 'http://' . $siteurl . $path_from_root . '?s=' . $tagsection . '&amp;t=';
			$urlsuffix = '';
		}
	}

	foreach ($tags_unique as $tag) {
		$tag_weight = floor($minpercent + ($tags_weight[$tag] - $min) * $stepvalue);

		if ($setsizes)
			$style = ' style="font-size: ' . $tag_weight . '%;"';

		$tag_class = ' class="';
		if ($tags_weight[$tag] == $min)
			$tag_class .= "tagSizeSmallest";
		else if ($tags_weight[$tag] == $max)
			$tag_class .= "tagSizeLargest";
		else
			$tag_class .= "tagSizeMedium";
		$tag_class .= ' tagSize' . ($tags_weight[$tag] + 1 - $min);
		$tag_class .= '"';

		//adapted from code by gdtroiano, see http://forum.textpattern.com/viewtopic.php?pid=102875#p102875
		$titlecount = '';
		$displaycount= '';
		$count = $countwrapchars{0} . $tags_weight[$tag] . $countwrapchars{1};
		if ($showcounts == 'title' || $showcounts == 'both')
			$titlecount = ' title="' . $count . '"';
		if ($showcounts && $showcounts != 'title')
			$displaycount = ' ' . $count;

		if ($generatelinks) {
			$wholeurl = '"' . $urlprefix . str_replace(' ', '-', $tag) . $urlsuffix . '"';
			$tags_html[] = '<a href=' . $wholeurl . $tag_class . $style . $titlecount . '>' . $tag . '</a>' . $displaycount;
		} else {
			$tags_html[] = '<span' . $tag_class . $style . $titlecount . '>' . $tag . '</span>' . $displaycount;
		}
	}

	return doWrap($tags_html, $wraptag, $break, $class, $breakclass);
}


function tru_tags_if_tag_search($atts, $thing)
{
	$tag_searching = gps('t');
	$condition = (!empty($tag_searching))? true : false;
	return parse(EvalElse($thing, $condition));
}


function tru_tags_tag_parameter()
{
	return strip_tags(gps('t'));
}


function tru_tags_search_parameter()
{
	return strip_tags(gps('q'));
}

function tru_tags_field() {
	return 'Keywords';
}




# --- END PLUGIN CODE ---
?>
