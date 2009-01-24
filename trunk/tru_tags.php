<?php
//------------------------------------------------------//
// This file has been decoded with ort_plugindecode.php //
//------------------------------------------------------//
 
#$plugin['name'] = 'tru_tags.tmp';
$plugin['version'] = '1.10';
$plugin['author'] = 'Nathan Arthur';
$plugin['author_uri'] = 'http://www.truist.com/';
$plugin['description'] = 'Tagging support';
$plugin['type'] = '0';
$plugin['allow_html_help'] = '0';
 
if (!defined('txpinterface'))
	@include_once('zem_tpl.php');
 
if(0){
?>
# --- BEGIN PLUGIN HELP ---
To learn more about tru_tags, check out the "introductory article":/blog/493/trutags-a-tagging-plugin-for-textpattern and "feature list":http://www.truist.com/reference/495/trutags-feature-list.

h3. Configuration

h4. Step 1: Create a new section

tru_tags depends on the existence of a special Textpattern section named "tag," by default[1].  Create that section, using whatever settings you like.  (You won't be publishing articles to that section.)  Here's what I use[2]:

<img src="http://www.truist.com/images/2.png" height="280" width="398" alt="tag section configuration" class="diagram" />

fn1(footnote). You can use a different name, but you have to use a special attribute in some of the plugin calls to make  everything work correctly.  See below for details.

fn2(footnote). Note that I use the 'default' page - that choice may not be right for you.  This section will be shown whenever you click on a tag, to display the tag search results.  You'll want a page that has the correct layout/headers/footers.  I use my default page, with <code><txp:if_section name="tag"></code> to change the page display in this case.

h4. Step 2: Call the plugin from that section

To make tag searching and the default tag cloud work, you'll need to call <code><txp:tru_tags_handler /></code> from the page you chose in Step 1.  I replaced the default <code><txp:article /></code> with something like this:

<pre><txp:if_section name="tag">
  <txp:tru_tags_handler />
<txp:else />
  <txp:article />
</txp:if_section></pre>

h4. Step 3: Configure your article form to display tags

To make each article show a list of all the tags associated with it, put someting like this in your article form:

<pre><txp:tru_tags_if_has_tags>
  tags: <txp:tru_tags_from_article />
</txp:tru_tags_if_has_tags></pre>

h4. Step 4: Drop a custom tag cloud somewhere, if you want

If you'd like to show a tag cloud somewhere on your site (other than <code>/tag/</code>), put something like this in that page:

<pre><txp:tru_tags_cloud /></pre>

See below for lots of formatting options, including the ability to output a simple list instead of a cloud (using <code>tru_tags_list</code>).

h4. Step 5: Start tagging!

Whenever you write an article, put your tags into the Keywords field in Textpattern.  (The Keywords field hides behind the "Advanced Options" link on the left side of the "write" page.)  Tags should be separated by commas, and can have spaces[1].

You'll probably want to install Rob Sable's "rss_admin_show_adv_opts":http://www.wilshireone.com/textpattern-plugins/rss-admin-show-adv-opts, which will automatically expand the "Advanced Options" section of the "write" page, when you are writing articles.  That gives you easy access to the Keywords field.

fn1(footnote). Tags with spaces will generate urls with dashes, which will work correctly.  Tags with dashes will also work correctly.

h4. Step 6: Fancy display customization (Optional)

You can use <code>tru_tags_if_tag_search</code>, <code>tru_tags_tag_parameter</code>, and <code>tru_tags_search_parameter</code> to customize your page titles or tag search results.  See below for details.  For an example, do a tag search on this site and look at the titlebar.

If you don't like the fact that all tags are shown in lowercase you can use <code>.text-transform: capitalize.</code> in your CSS stylesheet, in conjunction with an appropriate <code>class</code> attribute on your <code>tru_tags_cloud</code> call, to capitalize the tags.

h4. Step 7: Turn on clean urls (Optional)

Clean urls are now possible with tru_tags, provided that you are willing to modify Textpattern's rewrite rules (in the .htaccess file).  But be warned - rewrite rules are complex.  I make no guarantees that this won't eat your children.  *You don't have to do this* but a lot of people want it.  This site uses it, if that helps.

These instructions are for apache 2.0 on unix/linux with textpattern installed at the root of your site.  See "this forum post":http://forum.textpattern.com/viewtopic.php?pid=103823#p103823 for details on clean urls with lighttpd.  All other platforms/servers/configurations are untested (by me).

Here's what to do:

# Add the following lines to Textpattern's .htaccess file (found in the root of your Textpattern directory), adjusting them if necessary.  These lines should be *after* the

<div class="pre">

<notextile>RewriteRule ^(.+) - [PT,L]</notextile>

</div>

and *before* the

<div class="pre">

<notextile>RewriteRule ^(.*) index.php</notextile>

</div>

Here's the bit to insert (which you may need to customize for your site):

<div class="pre">

<notextile>#This path should match the relative url of your Textpattern install</notextile>
<notextile>RewriteBase /</notextile>
<notextile>#If you're using a section other than "tag", change the following two rules accordingly</notextile>
<notextile>RewriteRule ^tag/([^/]+)$ tag/$1/ [R,L]</notextile>
<notextile>RewriteRule ^tag/(.+)/$ ?s=tag&amp;t=$1</notextile>

</div>

# *Check to make sure your site is still working.*
# Edit the tru_tags plugin (admin -> plugins, click 'Edit') and change the <code>tru_tags_use_clean_urls()</code> function to return <code>1</code>.  (It's the first function.)
## If you're planning on using non-alphanumeric characters (like '+') in your tag names, and you're using apache 2, then you also need to make <code>tru_tags_double_urlencode()</code> return <code>1</code>.  This works around a bug in apache and mod_rewrite.
# Clean urls should be working now.  Try clicking a tag link to see what url it references.
# By default, article tags will set the <code>rel="tag"</code> attribute, and the tag cloud (or list) won't.  This can be controlled with the <code>usereltag</code> attribute; see below for details.

h4. Step 8: Show a "related tags" cloud (Optional)

v1.8 (and beyond) of tru_tags has a feature that lets you show a cloud of tags that are related to the tags used in articles that are found by a tag search.

Got that?

To see an example, do a tag search on this site (click a link in the tag cloud).  When the search results come up the sidebar will have a new section in it called "Related Tags".  The cloud in that section contains all the tags used by all the articles in the search result.  It's a way to say "these other tags may be similar to the tag you just searched".

*Before you use this on your site, however,* you should read the detailed instructions carefully.  This tag can have significant performance implications for your site, which your hosting provider might not be very happy about.

To use it anyway, put something like this into the appropriate page:

<pre><txp:tru_tags_if_tag_search>
<txp:tru_tags_related_tags_from_search section="blog,reference" useoverallcounts="1" />
</txp:tru_tags_if_tag_search></pre>

If you use the <code>section</code> attribute in your main tag cloud you should also use it here.  See the documentation below for an explanation of why.

The <code>useroverallcounts</code> attribute causes the cloud to size the links according to how large they would be in the main cloud.  That's probably what you want, but it causes even more performance slowdown.  See the documentation below for all the details.


h3. Tag reference

h4. <code>tru_tags_handler</code>

This is the main function that drives tag search and shows the generic tag cloud.  It should be called from the page that is used in the 'tag' section.  It generally calls <code>doArticles()</code> (in the Textpattern code) to display tag search results, but if no tag was passed in the url it will call <code>tru_tags_cloud</code> instead.

This tag accepts most of the standard <code>txp:article</code> attributes, which will be applied during the tag search.  Note that <code>tru_tags_handler</code> *does not* support using multiple sections with the <code>section</code> attribute, when doing a tag search.  If multiple sections are passed, none are used.

*Note:* In Textpattern, the <code>limit</code> attribute is defaulted to <code>10</code>, to limit the output to 10 articles per page, and the <code>txp:older</code> and <code>txp:newer</code> tags are used to paginate the full list.  The <code>txp:older</code> and <code>txp:newer</code> tags do not work with tru_tags, but the <code>limit</code> is still used by Textpattern when it outputs the articles.  Therefore, tru_tags uses a default limit of <code>1000</code> when doing an article search.  You can override this limit by setting the <code>limit</code> attribute on <code>tru_tags_handler</code>.

This tag will also accept all of the attributes used by <code>tru_tag_cloud</code>.  See below for details.

h4. <code>tru_tags_if_has_tags</code>

This conditional tag can be used in an article form, and will evaluate its contents (e.g. "return true") if the current article has tags.

h4. <code>tru_tags_from_article</code>

This tag can be used in an article form to return a list of tags associated with the current article.  Typically (see below), each tag in the list will be a link (<code><a href=...></code>) to the tag search url for that particular tag.

This tag accepts the standard <code>wraptag</code>, <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes[1].

If you don't want to have the tags be links, you can set <code>generatelinks="0"</code> to turn the links off.

If you named your 'tag' section something other than "tag", set <code>tagsection</code> to the name of that section.  By default, this is set to <code>tag</code>.

The <code>title</code> attribute can be used to set the tooltip for the tags.

If you have clean urls turned on (see above), you can use the <code>usereltags</code> attribute to specify whether links in the list should have the <a href="http://microformats.org/wiki/reltag"><code>rel="tag"</code></a>  (used by "Technorati":http://www.technorati.com/) attribute set.  Note that this won't work if you turn off links.  Also note that tags don't show up in the RSS/Atom feeds, unless you "hack the textpattern source":http://forum.textpattern.com/viewtopic.php?pid=40907#p40907.  This is on by default.

Again if you have clean urls turned on, you can use the <code>usenofollow</code> attribute to specify whether links in the list should have the <a href="http://googleblog.blogspot.com/2005/01/preventing-comment-spam.html"><code>rel="nofollow"</code></a> attribute set.  This is off by default.

As of v1.8, you can set the <code>useoverallcounts</code> attribute to tell the tag list to render as a cloud (where each tag is sized according to its site-wide frequency).  This tag isn't sufficient on its own, however; once you set it you also need to set the rendering attributes like <code>maxpercent="200"</code>, <code>setsizes="1"</code>, and <code>setclasses="1"</code> because these are all turned off by default.  You'll also then be able to use attributes like <code>showcounts</code> and <code>sort</code>.  See the <code>tru_tags_cloud</code> documentation for details on these attributes.

*Be careful, however,* before turning this on.  This attribute causes <code>tru_tags</code> to do an extra database query for each article displayed on a page.  That extra query is equivalent to the query used to generate the overall cloud.  You may pay a performance penalty for all the extra queries.

fn1(footnote). As with all Textpattern tags, <code>class</code> is only used if you specify an appropriate <code>wraptag</code> and <code>breakclass</code> is only used if you specify an appropriate <code>break</code>.

h4. <code>tru_tags_cloud</code>

h4. <code>tru_tags_list</code>

<p><code>tru_tags_cloud</code> can be used on any page, and is generally used to generate a simple tag cloud of all the tags used on your site.  The cloud is really just a list of links, much like that generated by <code>tru_tags_from_article</code>, but with a <code>style</code> attribute set on each link to give it a font size ranging from 100% to 200%.</p>

<p><code>tru_tags_list</code> can be used on any page, and is generally used to output a bulleted list of all the tags used on your site.  By default, the tags will all have a font-size of 100%.</p>

These two tags do the exact same thing - <code>tru_tags_cloud</code> just provides different defaults to <code>tru_tags_list</code>.

<p><code>tru_tags_cloud</code> and <code>tru_tags_list</code> both set the <code>class</code> attribute of each tag, specifying two classes.  The first class groups the tags into categories, with classes of <code>tagSizeSmallest</code>, <code>tagSizeMedium</code>, and <code>tagSizeLargest</code>.  Using these, you could make the smallest and largest tags have different styles than all the others.</p>

The second class indicates the "step" of the current tag, with classes of <code>tagSize1</code>, <code>tagSize2</code>, and so on.  These give you precise control over each tag size, if you want it.

If you use these classes to create special CSS rules, you may also want to set the <code>setsizes</code> attribute, described below.

h5. Both tags accept the following attributes:

* The standard <code>wraptag</code>, <code>break</code>, <code>class</code>, and <code>breakclass</code> attributes.  <code>tru_tags_cloud</code> has a default <code>break</code> of a comma.  <code>tru_tags_list</code> has a default <code>wraptag</code> of <code>ul</code> and a default <code>break</code> of <code>li</code>.
* <code>tagsection</code>, which tells tru_tags the name of your 'tag' section.  This can be useful if you have named the section something other than "tag".  By default, this is set to <code>tag</code>.
* <code>section</code>, which tells it to limit the list to tags from the given section or sections.  For example, <code><txp:tru_tags_cloud section="blog,reference" /></code> would only show tags from the "blog" and "reference" sections.  By default, this is set to blank (to show tags from all sections).
** *Note:* if you use <code>section</code> to limit the cloud to a particular section, it won't limit the tag search feature to that section.  The tag search finds (tagged) articles from all sections, no matter what.  That's a side-effect of the use of <code>doArticles()</code>, and I don't think there's anything I can do about it.
* <code>minpercent</code> and <code>maxpercent</code>, which can be used to control the weighted font sizes in the tag cloud/list.  <code>tru_tags_cloud</code> defaults to <code>100</code> and <code>200</code>, respectively, and <code>tru_tags_list</code> defaults to <code>100</code> and <code>100</code>.
* <code>showcounts</code>, which will append a number indicating the number of times a tag has been used, to each tag in the list.  For example, you might see: <span class="pre">"life":/?s=tag&t=life [3], "tech":/?s=tag&t=tech [5]</span> in my tag cloud, if this was turned on.  This is off by default.  Use <code>1</code> or <code>true</code> to turn it on.
** This can also be used to put the counts in the <code>title</code> attribute of the links, which will make it appear in a tooltip.  Use <code>showcounts="title"</code> or <code>showcounts="both"</code> (to show it in both places) to turn it on.
* <code>countwrapchars</code>, which controls the characters used to show the tag count, if <code>showcounts</code> is turned on.  By default this is <code>[]</code>.  The first character will be put on the left side of the number, and the second character will be put on the right.  For example, a <code>countwrapchars</code> of <code>()</code> would show: <span class="pre">"life":/?s=tag&t=life (3), "tech":/?s=tag&t=tech (5)</span>
* <code>title</code> can be used to set the tooltip for the tags.
* If you have clean urls turned on (see above), you can use the <code>usereltags</code> attribute to specify whether links in the list should have the <a href="http://microformats.org/wiki/reltag"><code>rel="tag"</code></a>  (used by "Technorati":http://www.technorati.com/) attribute set.  This is off by default.
* Again if you have clean urls turned on, you can use the <code>usenofollow</code> attribute to specify whether links in the list should have the <a href="http://googleblog.blogspot.com/2005/01/preventing-comment-spam.html"><code>rel="nofollow"</code></a> attribute set.  This is off by default.
* If you don't want the tags to be links, you can set <code>generatelinks="0"</code> to turn them off.
* <code>mintagcount</code> and <code>maxtagcount</code> can be used to hide tags that only have a few articles, or that have too many.  They are defaulted to <code>0</code> and <code>1000</code>, respectively.  For example, <code>mintagcount="2"</code> would hide any tags that were only associated with a single article.  If you do this, you may want to add a link to the default tag cloud, usually found at <code>/tag/</code>.
* If you want to control the size(s) of the tags yourself (through CSS), set <code>setsizes="0"</code> to turn off the <code>style="font-size: XXX%"</code> attribute generation.  That will leave behind the default CSS classes, which you can use to control the display of your cloud.
* If you don't want the tags to have their <code>class</code> attributes set, you can set <code>setclasses="0"</code> to turn it off.
* <code>sort</code> can be used to sort the cloud by tag frequency, rather than the default of alphabetically.  Use <code>sort="count"</code> to sort by frequency in descending order, and <code>sort="count asc"</code> to sort by frequency in ascending order.

Note that you can use the attributes to make each tag do the same thing.  <code>tru_tags_cloud</code> is just a convenience function for generating a tag cloud using <code>tru_tags_list</code>.  Therefore, it's possible to have a tag cloud with tag counts showing, or have a bulleted list with variable font sizes, etc.

h4. <code>tru_tags_if_tag_search</code>

This conditional tag can be used anywhere and will evaluate its contents (e.g. "return true") if the current url indicates that there is a tag search going on.  This can be useful if you want to do something like customize the titlebar when using the tag search.

h4. <code>tru_tags_tag_parameter</code>

This tag can be used anywhere and will return the name of the current tag under search, during a tag search.  This is generally used with <code>tru_tags_if_tag_search</code>.

It accepts one parameter, <code>striphyphens</code>, which will convert all hyphens to spaces in tag names.  This is useful because <code>tru_tags</code> will convert spaces to hyphens when it does a tag search, and this undoes that conversion.

h4. <code>tru_tags_search_parameter</code>

This tag can be used anywhere and will return the text the user typed into the standard search box, during a regular search.  This tag is not specifically related to tagging, but can be handy for customizing the titlebar on search result pages.

h4. <code>tru_tags_related_tags_from_search</code>

This tag is useful on the results page of a tag search.  It generates a cloud of all the tags that are used by the articles found in that search, excluding the search tag itself.  For example, if a tag search for "life" found three articles that were tagged as follows:

# money, health, life
# money, politics, life
# life

...<code>tru_tags_related_tags_from_search</code> would generate a cloud containing "money", "health", and "politics".

This is useful for generating a "related tags" cloud, as you can see in the sidebar of this site when you do a tag search.

*Before you use this on your site, however,* be warned that it's not good for your site's performance, and it's a little bit of a hack (and therefore may break with newer versions of Textpattern).  It is implemented by completely redoing the database query that found all the articles in the first place, which means that your site is essentially performing the search twice.  Then, depending on the attributes you use, it may do a second query that is equivalent to the query that generates the "complete" tag cloud.

You may want to test this carefully if you have a hosting provider who charges you for CPU cycles.  :)

If you decide to use this tag, be sure to minic the <code>section</code> and <code>limit</code> attributes from your <code>tru_tags_cloud</code> (or <code>tru_tags_list</code>) call, along with any <code>txp:article</code> attributes that you used in your <code>tru_tags_handler</code> call.  If you don't, the set of articles found/used by this tag won't match the set that are displayed to the user.

This tag accepts the following attributes:

* All the attributes of <code>txp:article</code>
* All the attributes of <code>tru_tags_cloud</code>/<code>tru_tags_list</code>.
* <code>useoverallcounts</code>, which makes the cloud render using the frequency data for the site-wide tag cloud.
** By default, <code>tru_tags_related_tags_from_search</code> outputs a cloud where the weights are based on the frequency of the tags _in the search results_.  Using the example from above, "money" would have a weight that was double the weight of "health" and "politics".
** This attribute will change that behavior, making the tag sizes match the sizes used in the site-wide tag cloud.  In other words, this cloud will look exactly like a subset of your sitewide cloud.
** It will also add an extra database query to the mix (mentioned above), so consider performance carefully before using it.
# --- END PLUGIN HELP ---
<?php
}
# --- BEGIN PLUGIN CODE ---

#Copyright 2006 Nathan Arthur
#Released under the GNU Public License, see http://www.opensource.org/licenses/gpl-license.php for details
#This work is inspired by ran_tags by Ran Aroussi, originally found at http://aroussi.com/article/45/tagging-textpattern

#See http://www.truist.com/reference/497/trutags-usage-instructions for instructions

function tru_tags_use_clean_urls()
{
	return 0; 	#DO NOT ENABLE THIS unless you've read the instructions
}

function tru_tags_add_tags_to_feed_body()
{
	return 0;	#this will append "tags: trees, flowers, animals, etc" (with links) to the body of RSS/Atom feeds
}

#YOU ONLY NEED TO CARE ABOUT THIS FUNCTION IF YOU USE CLEAN URLS AND NON-ALPHANUMERIC CHARACTERS IN YOUR TAGS (LIKE '+')
#MM: more info at http://forum.textpattern.com/viewtopic.php?pid=123007#p123007
function tru_tags_double_urlencode()
{
	return 0;	#use this if you aren't using a broken version of apache, or if you only use letters/numbers in your tag names, or if you're not using clean urls
	#return tru_tags_use_clean_urls();	#use this if you are using a version of apache that has this problem: http://issues.apache.org/bugzilla/show_bug.cgi?id=34602
}


### PRIMARY TAG FUNCTIONS ###
#############################

function tru_tags_handler($atts) {
	$tag_parameter = tru_tags_tag_parameter();
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

	$tags_field = tru_tags_field();

	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	return parse(EvalElse($thing, $rs));
}


function tru_tags_if_tag_search($atts, $thing)
{
	$tag_parameter = tru_tags_tag_parameter();
	$condition = (!empty($tag_parameter)) ? true : false;
	return parse(EvalElse($thing, $condition));
}


function tru_tags_tag_parameter($atts = array())
{
	extract(lAtts(array('striphyphens' => 0), $atts, 0));
	$parm = strip_tags(gps('t'));
	return ($striphyphens ? str_replace('-', ' ', $parm) : $parm);
}


function tru_tags_search_parameter()
{
	return strip_tags(gps('q'));
}


function tru_tags_related_tags_from_search($atts) {
	$tag_parameter = tru_tags_tag_parameter();
	if (!empty($tag_parameter)) {
	        $tags_field = tru_tags_field();
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
			'tagsection'	=> 'tag',
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
			'title'		=> ''
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

	$tags_field = tru_tags_field();
	include_once txpath.'/publish/search.php';
	$filter = filterSearch();
	$all_tags = array();
	$rs = safe_rows("$tags_field", "textpattern", "$tags_field <> ''" . $section_clause . $filter . " and Status >= '4' and Posted < now()");
	foreach ($rs as $row) {
		$temp_array = explode(",", trim(tru_tags_strtolower($row[$tags_field])));
		$all_tags = array_merge($all_tags, tru_tags_trim_tags($temp_array));
	}

	return $all_tags;
}


function tru_tags_render_cloud($atts, $all_tags, $all_tags_for_weight) {
	global $siteurl, $path_from_root;
	extract($_SERVER);
	extract($atts);

	sort($all_tags);
	sort($all_tags_for_weight);

	$tags_weight = array_count_values($all_tags_for_weight);
	$tags_unique = array_unique($all_tags);

	foreach ($tags_unique as $key => $tag) {
		if ($tags_weight[$tag] < $mintagcount || $tags_weight[$tag] > $maxtagcount) {
			unset($tags_unique[$key]);
			unset($tags_weight[$tag]);
		}
	}

	$max = max($tags_weight);
	$min = min($tags_weight);
	$stepvalue = ($max == $min) ? 0 : ($maxpercent - $minpercent) / ($max - $min);

	foreach ($tags_weight as $tag => $weight) {
		if (!in_array($tag, $tags_unique)) {
			unset($tags_weight[$tag]);
		}
	}

	if (strpos($sort, 'count') !== false) {
		$sort_asc = (strpos($sort, 'asc') !== false);
		array_multisort($tags_weight, ($sort_asc ? SORT_ASC : SORT_DESC), $tags_unique);
	}

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

		$style = '';
		if ($setsizes)
			$style = ' style="font-size: ' . $tag_weight . '%;"';

		$tag_class = '';
		if ($setclasses) {
			$tag_class = ' class="';
			if ($tags_weight[$tag] == $min)
				$tag_class .= "tagSizeSmallest";
			else if ($tags_weight[$tag] == $max)
				$tag_class .= "tagSizeLargest";
			else
				$tag_class .= "tagSizeMedium";
			$tag_class .= ' tagSize' . ($tags_weight[$tag] + 1 - $min);
			$tag_class .= '"';
		}

		//adapted from code by gdtroiano, see http://forum.textpattern.com/viewtopic.php?pid=102875#p102875
		$titlecount = '';
		if ($title)
			$titlecount = ' title="' . $title . '"';
		$displaycount= '';
		$count = $countwrapchars{0} . $tags_weight[$tag] . $countwrapchars{1};
		if ($showcounts == 'title' || $showcounts == 'both')
			$titlecount = ' title="' . $title . $count . '"';
		if ($showcounts && $showcounts != 'title')
			$displaycount = ' ' . $count;

		if ($generatelinks) {
			$wholeurl = '"' . $urlprefix . tru_tags_urlencode(str_replace(' ', '-', $tag)) . $urlsuffix . '"';
			$tags_html[] = '<a href=' . $wholeurl . $tag_class . $style . $titlecount . '>' . $tag . '</a>' . $displaycount;
		} else if ($tag_class || $style || titlecount) {
			$tags_html[] = '<span' . $tag_class . $style . $titlecount . '>' . $tag . '</span>' . $displaycount;
		} else {
			$tags_html[] = $tag . $displaycount;
		}
	}
	return tru_tags_do_wrap($tags_html, $wraptag, $break, $class, $breakclass);
}


### OTHER SUPPORT FUNCTIONS ###
###############################

register_callback('tru_tags_atom_handler', 'atom_entry');
function tru_tags_atom_handler($event, $step) { return tru_tags_feed_handler(true); }
register_callback('tru_tags_rss_handler', 'rss_entry');
function tru_tags_rss_handler($event, $step) { return tru_tags_feed_handler(false); }

function tru_tags_feed_handler($atom) {
	$tags = tru_tags_get_tags_for_article();

	if (tru_tags_add_tags_to_feed_body()) {
		$extrabody = '';
		$FORM_NAME = 'tru_tags_feed_tags';
		if (fetch('form', 'txp_form', 'name', $FORM_NAME)) {
			$form = trim(fetch_form($FORM_NAME));
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


function tru_tags_get_tags_for_article() {
	global $thisarticle;
	extract($thisarticle);

	$tags_field = tru_tags_field();
	$rs = safe_row($tags_field, "textpattern", "ID='$thisid' AND $tags_field <> ''");
	$all_tags = explode(",", trim(tru_tags_strtolower($rs[$tags_field])));

	return tru_tags_trim_tags($all_tags);
}


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


function tru_tags_field() {
	return 'Keywords';
}


function tru_tags_fixup_query_atts($atts, $tag_parameter) {
	$keywords = explode(',', $tag_parameter);
	foreach ($keywords as $keyword) {
		if (strpos($keyword, '-') !== false) {
			$keywords[] = str_replace('-', ' ', $keyword);
		}
	}
	$atts['keywords'] = implode(',', $keywords);

	if ($atts['section'] && strpos($atts['section'], ',') !== false)
		$atts['section'] = '';

	if (is_callable("array_key_exists")) {
		if (!array_key_exists('limit', $atts))
			$atts['limit'] = '1000';
	} else {
		if (!$atts['limit'])
			$atts['limit'] = '1000';
	}
	$atts['allowoverride'] = true;

	return $atts;
}


function tru_tags_strtolower($str) {
	if (is_callable("mb_strtolower")) {
	//if (version_compare(phpversion(), "4.3.0", ">=")) {
		return mb_strtolower($str, "UTF-8");
	} else {
		return strtolower($str);
	}
}


# this works around a bug in apache and mod_rewrite that shows up if you turn on clean urls
function tru_tags_urlencode($str) {
	if (tru_tags_double_urlencode()) {
		return urlencode(urlencode($str));
	} else {
		return urlencode($str);
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
