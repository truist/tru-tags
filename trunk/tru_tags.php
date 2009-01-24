<?php
//------------------------------------------------------//
// This file has been decoded with ort_plugindecode.php //
//------------------------------------------------------//
 
#$plugin['name'] = 'tru_tags';
$plugin['version'] = '0.9';
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

	<p>To learn more about tru_tags, check out the <a href="http://www.truist.com/blog/493/trutags-a-tagging-plugin-for-textpattern">introductory article</a>.</p>
	<h3>Configuration</h3>
	<p>This section details what do to after you&#8217;ve installed the plugin.  Please <a href="/stash/tru_tags-0.9.txt">click here to see the install instructions</a> if you haven&#8217;t done that yet.</p>
	<h4>Step 1: Create a new section</h4>
	<p>tru_tags depends on the existence of a special Textpattern section named &#8220;tag<sup><a href="#fn124658340440a99016bbbc">1</a></sup>,&#8221; by default.  Create that section, using whatever settings you like.  (You won&#8217;t be publishing articles to that section.)  Here&#8217;s what I use<sup><a href="#fn1555334271440a9901709f7">2</a></sup>:</p>
	<p><img src="http://www.truist.com/images/2.png" height="280" width="398" alt="tag section configuration" class="diagram" /></p>
	<p class="footnote" id="fn124658340440a99016bbbc"><sup>1</sup> You can use a different name, but you have to use a special attribute in some of the plugin calls to make  everything work correctly.  See below for details.</p>
	<p class="footnote" id="fn1555334271440a9901709f7"><sup>2</sup> Note that I use the &#8216;default&#8217; page &#8211; that choice may not be right for you.  This section will be shown whenever you click on a tag, to display the tag search results.  You&#8217;ll want a page that has the correct layout/headers/footers.  I use my default page, with <code>&lt;txp:if_section name="tag"&gt;</code> to change the page display in this case.</p>
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
	<p>If you&#8217;d like to show a tag cloud somewhere on your site (other than /tag/), put something like this, in that page:</p>
<pre>&lt;txp:tru_tags_cloud /&gt;</pre>
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
	<p>This is the main function that drives tag search, and shows the generic tag cloud.  It should be called from the page that is used in the &#8216;tag&#8217; section.  It calls <code>doArticles()</code> to display tag search results.</p>
	<h4><code>tru_tags_if_has_tags</code></h4>
	<p>This conditional tag can be used in an article form, and will evaluate its contents (e.g. &#8220;return true&#8221;) if the current article has tags.</p>
	<h4><code>tru_tags_from_article</code></h4>
	<p>This tag can be used in an article form to return a list of tags associated with the current article.  Each tag in the list will be a link (<code>&lt;a href=...&gt;</code>) to the tag search url for that particular tag.</p>
	<p>This tag accepts the standard <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes.</p>
	<p>It also accepts a <code>tagsection</code> attribute, which tells tru_tags what relative url to use to find the &#8216;tag&#8217; section.  This can be useful if you have named the section something other than &#8216;tag&#8217;.  By default, this is set to <code>tag</code>.</p>
	<h4><code>tru_tags_cloud</code></h4>
	<p>That tag can be used on any page to generate a simple tag cloud of all the tags used on your site.  The cloud is really just a list of links, much like that generated by <code>tru_tags_from_article</code>, but with a <code>style</code> attribute set on each link to give it a from size ranging from 100% to 200%.</p>
	<p>This tag accepts the standard <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes.</p>
	<p>It also accepts a <code>tagsection</code> attribute, which tells tru_tags what relative url to use to find the &#8216;tag&#8217; section.  This can be useful if you have named the section something other than &#8216;tag&#8217;.  By default, this is set to <code>tag</code>.</p>
	<p>Lastly, this tag accepts a <code>section</code> attribute, which tells it to limit the list to tags from the given section.  For example, <code>&lt;txp:tru_tags_cloud section="blog" /&gt;</code> would only show tags from the &#8220;blog&#8221; section.  By default, this is set to blank (to show tags from all sections).</p>
	<h4><code>tru_tags_if_tag_search</code></h4>
	<p>This conditional tag can be used anywhere and will evaluate its contents (e.g. &#8220;return true&#8221;) if the current url indicates that there is a tag search going on.  This can be useful if you want to do something like customize the titlebar when using the tag search.</p>
	<h4><code>tru_tags_tag_parameter</code></h4>
	<p>This tag can be used anywhere and will return the name of the current tag under search, during a tag search.  This is generally used with <code>tru_tags_if_tag_search</code>.</p>
	<h4><code>tru_tags_search_parameter</code></h4>
	<p>This tag can be used anywhere and will return whatever the user typed into the Textpattern search box, during a regular search.  This tag is not specifically related to tagging, but can be handy for customizing the titlebar (or the page) during search results.</p>


 
# --- END PLUGIN HELP ---
<?php
}
# --- BEGIN PLUGIN CODE ---


function tru_tags_field() {
	return 'Keywords';
}


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

	$url = 'http://' . $siteurl . $path_from_root . $tagsection . '?t=';

	for($i=0; $i<count($all_tags); $i++) {
		$tags_html[] = '<a href="' . $url . $all_tags[$i] . '">' . $all_tags[$i] .'</a>';
	}
	return doWrap($tags_html, $wraptag, $break, $class, $breakclass);
}


function tru_tags_cloud($atts) {
	global $siteurl, $path_from_root;
	extract($_SERVER);

	extract(lAtts(array('wraptag'       => '',
						'break'         => ', ',
						'class'         => '',
						'breakclass'	=> '',
						'tagsection'	=> 'tag',
						'section'	=> '',
						),$atts));

	$tags_field = tru_tags_field();

	$section_clause = '';
	if ($section <> '')
		$section_clause = " AND Section = '$section'";

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
	$stepvalue = ($max == $min) ? 0 : 100 / ($max - $min);

	$url = 'http://' . $siteurl . $path_from_root . $tagsection . '?t=';
	foreach ($tags_unique as $no) {
		$tag_weight = 100 + ($tags_weight[$no] - $min) * $stepvalue;
		$tags_html[] = '<a href="' . $url . $no . '" style="font-size:'. $tag_weight . '%;">' . $no .'</a>';
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



# --- END PLUGIN CODE ---
?>
