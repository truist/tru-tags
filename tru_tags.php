<?php
//------------------------------------------------------//
// This file has been decoded with ort_plugindecode.php //
//------------------------------------------------------//
 
#$plugin['name'] = 'tru_tags';
$plugin['version'] = '1.1';
$plugin['author'] = 'Nathan Arthur';
$plugin['author_uri'] = 'http://truist.com/';
$plugin['description'] = 'Tagging support with full integration';
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
	<p>tru_tags depends on the existence of a special Textpattern section named &#8220;tag,&#8221; by default<sup><a href="#fn12476053534411b8237b03e">1</a></sup>.  Create that section, using whatever settings you like.  (You won&#8217;t be publishing articles to that section.)  Here&#8217;s what I use<sup><a href="#fn19069737924411b8237fe5d">2</a></sup>:</p>
	<p><img src="http://www.truist.com/images/2.png" height="280" width="398" alt="tag section configuration" class="diagram" /></p>
	<p class="footnote" id="fn12476053534411b8237b03e"><sup>1</sup> You can use a different name, but you have to use a special attribute in some of the plugin calls to make  everything work correctly.  See below for details.</p>
	<p class="footnote" id="fn19069737924411b8237fe5d"><sup>2</sup> Note that I use the &#8216;default&#8217; page &#8211; that choice may not be right for you.  This section will be shown whenever you click on a tag, to display the tag search results.  You&#8217;ll want a page that has the correct layout/headers/footers.  I use my default page, with <code>&lt;txp:if_section name="tag"&gt;</code> to change the page display in this case.</p>
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
	<p>Whenever you write an article, put your tags into the Keywords field in Textpattern.  (The Keywords field hides behind the &#8220;Advanced Options&#8221; link on the left side of the &#8220;write&#8221; page.)  Tags should be separated by commas, and can have spaces.  Be careful not to leave spaces at the beginning and end of tags.  Here&#8217;s a good example:</p>
	<p><img src="http://www.truist.com/images/3.png" height="110" width="118" alt="tags with spaces, but not next to the commas" class="diagram" /></p>
	<p>...and a bad one:</p>
	<p><img src="http://www.truist.com/images/4.png" height="112" width="119" alt="tags with spaces after the commas (WRONG)" class="diagram" /></p>
	<p>You&#8217;ll probably want to install Rob Sable&#8217;s <a href="http://www.wilshireone.com/textpattern-plugins/rss-admin-show-adv-opts">rss_admin_show_adv_opts</a>, which will automatically expand the &#8220;Advanced Options&#8221; section of the &#8220;write&#8221; page, when you are writing articles.  That gives you easy access to the Keywords field.</p>
	<h4>Step 6: Fancy display customization</h4>
	<p>You can use <code>tru_tags_if_tag_search</code>, <code>tru_tags_tag_parameter</code>, and <code>tru_tags_search_parameter</code> to customize your page titles or tag search results.  See below for details.  See the titlebar of <a href="http://www.truist.com/">truist.com</a> for an example.</p>
	<h3>Tag reference</h3>
	<h4><code>tru_tags_handler</code></h4>
	<p>This is the main function that drives tag search, and shows the generic tag cloud.  It should be called from the page that is used in the &#8216;tag&#8217; section.  It calls <code>doArticles()</code> (in the Textpattern code) to display tag search results.</p>
	<h4><code>tru_tags_if_has_tags</code></h4>
	<p>This conditional tag can be used in an article form, and will evaluate its contents (e.g. &#8220;return true&#8221;) if the current article has tags.</p>
	<h4><code>tru_tags_from_article</code></h4>
	<p>This tag can be used in an article form to return a list of tags associated with the current article.  Each tag in the list will be a link (<code>&lt;a href=...&gt;</code>) to the tag search url for that particular tag.</p>
	<p>This tag accepts the standard <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes.</p>
	<p>It also accepts a <code>tagsection</code> attribute, which tells tru_tags what relative url to use to find the &#8216;tag&#8217; section.  This can be useful if you have named the section something other than &#8216;tag&#8217;.  By default, this is set to <code>tag</code>.</p>
	<h4><code>tru_tags_cloud</code></h4>
	<h4><code>tru_tags_list</code></h4>
	<p>These two tags do the exact same thing &#8211; <code>tru_tags_cloud</code> just provides different defaults to <code>tru_tags_list</code>.</p>
<p><code>tru_tags_cloud</code> can be used on any page, and is generally used to generate a simple tag cloud of all the tags used on your site.  The cloud is really just a list of links, much like that generated by <code>tru_tags_from_article</code>, but with a <code>style</code> attribute set on each link to give it a font size ranging from 100% to 200%.</p>
<p><code>tru_tags_list</code> can be used on any page, and is generally used to output a bulleted list of all the tags used on your site.  By default, the tags will all have a font-size of 100%.</p>
	<h5>Both tags accept the following attributes:</h5>
	<ul>
		<li>The standard <code>wraptag</code>, <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes.  <code>tru_tags_cloud</code> has a default <code>break</code> of a comma.  <code>tru_tags_list</code> has a default <code>wraptag</code> of <code>ul</code> and a default <code>break</code> of <code>li</code>.</li>
		<li><code>tagsection</code>, which tells tru_tags what relative url to use to find the &#8216;tag&#8217; section.  This can be useful if you have named the section something other than &#8216;tag&#8217;.  By default, this is set to <code>tag</code>.</li>
		<li><code>section</code>, which tells it to limit the list to tags from the given section or sections.  For example, <code>&lt;txp:tru_tags_cloud section="blog,reference" /&gt;</code> would only show tags from the &#8220;blog&#8221; and &#8220;reference&#8221; sections.  By default, this is set to blank (to show tags from all sections).
	<ul>
		<li><strong>Note:</strong> if you use <code>section</code> to limit the cloud to a particular section, it won&#8217;t limit the tag search feature to that section.  The tag search finds (tagged) articles from all sections, no matter what.  That&#8217;s a side-effect of the use of <code>doArticles()</code>, and I don&#8217;t think there&#8217;s anything I can do about it.</li>
	</ul></li>
		<li><code>minpercent</code> and <code>maxpercent</code>, which can be used to control the weighted font sizes in the tag cloud/list.  <code>tru_tags_cloud</code> defaults to <code>100</code> and <code>200</code>, respectively, and <code>tru_tags_list</code> defaults to <code>100</code> and <code>100</code>.</li>
		<li><code>showcounts</code>, which will append a number indicating the number of times a tag has been used, to each tag in the list.  For example, you might see: <span class="pre"><a href="/?s=tag&#38;t=life">life</a> [3], <a href="/?s=tag&#38;t=tech">tech</a> [5]</span> in my tag cloud, if this was turned on.  This is off by default.  Use <code>1</code> or <code>true</code> to turn it on.</li>
		<li><code>countwrapchars</code>, which controls the characters used to show the tag count, if <code>showcounts</code> is turned on.  By default this is <code>[]</code>.  The first character will be put on the left side of the number, and the second character will be put on the right.  For example, a <code>countwrapchars</code> of <code>()</code> would show: <span class="pre"><a href="/?s=tag&#38;t=life">life</a> (3), <a href="/?s=tag&#38;t=tech">tech</a> (5)</span></li>
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


function tru_tags_handler($atts) {
	extract($_GET);
	if (!isset($t)) {
		return tru_tags_cloud(0);
	}
	else {
		$atts['keywords'] = strip_tags($t);
		return doArticles($atts, true);
	}
}


function tru_tags_if_has_tags($atts, $thing) {
	global $thisarticle;
	extract($thisarticle);

	$tags_field = tru_tags_field();

	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	if ($rs) {
		return parse($thing);
	}
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
						),$atts));

	$tags_field = tru_tags_field();

	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	$all_tags = explode(",", trim(strtolower($rs[$tags_field])));
	sort($all_tags);
	$all_tags = array_unique($all_tags);

	$url = 'http://' . $siteurl . $path_from_root . '?s=' . $tagsection . '&amp;t=';

	for($i=0; $i<count($all_tags); $i++) {
		$tags_html[] = '<a href="' . $url . $all_tags[$i] . '">' . $all_tags[$i] .'</a>';
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
				'countwrapchars'	=> '[]'),
			$atts);

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
				'countwrapchars'	=> '[]'),
			$atts);
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
		$all_tags = array_merge($all_tags, explode(",", trim(strtolower($row[$tags_field]))));
	}
	sort($all_tags);

	$tags_weight = array_count_values($all_tags);
	$tags_unique = array_unique($all_tags);

	$max = max($tags_weight);
	$min = min($tags_weight);
	$stepvalue = ($max == $min) ? 0 : ($maxpercent - $minpercent) / ($max - $min);

	$url = 'http://' . $siteurl . $path_from_root . '?s=' . $tagsection . '&amp;t=';

	foreach ($tags_unique as $tag) {
		$tag_weight = $minpercent + ($tags_weight[$tag] - $min) * $stepvalue;
		//adapted from code by gdtroiano, see http://forum.textpattern.com/viewtopic.php?pid=102875#p102875
		$count = $showcounts ? ' ' . $countwrapchars{0} . $tags_weight[$tag] . $countwrapchars{1} : '';
		$tags_html[] = '<a href="' . $url . $tag . '" style="font-size:'. $tag_weight . '%;">' . $tag .'</a>' . $count;
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
