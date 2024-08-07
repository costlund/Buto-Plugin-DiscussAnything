# Buto-Plugin-DiscussAnything

<ul>
<li>Sort newest to oldes in level one. </li>
<li>The rest oldest to newest.</li>
<li>Anchor to scroll to latest edited item.</li>
</ul>

<a name="key_0"></a>

## Settings



<a name="key_0_0"></a>

### plugin

<p>Enabled to include js.
Set email for report.
Example below uses two places.</p>
<pre><code>plugin:
  discuss:
    anything:
      enabled: true
      data:
        email:
          - me@world.com
      place:
        discussion_test:
          tag_owner: 'plugin/memb_inc/main/active_inc_id'
        discuss_proposal:
          tag_owner: 'plugin/memb_inc/main/active_inc_id'
          username: |
            select concat(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as username from memb_account where memb_inc_id='[SESSION:plugin/memb_inc/main/active_inc_id]' and account_id=d.created_by
          item_data: |
            select concat(COALESCE(vote_proposal.name, ''), ': ', COALESCE(vote_proposal.description, '')) 
            from vote_proposal 
            inner join memb_account on vote_proposal.memb_account_id=memb_account.id
            where vote_proposal.id=d.tag_item
            and memb_account.memb_inc_id='[SESSION:plugin/memb_inc/main/active_inc_id]'</code></pre>
<p>Param tag_owner could be id of an organisation if your application has one stored in session.</p>
<p>User name from table account will show author of comments.
One can override it with username parameter.</p>
<pre><code>          username: |
            select concat(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as username from memb_account where memb_inc_id='[SESSION:plugin/memb_inc/main/active_inc_id]' and account_id=d.created_by</code></pre>
<p>Add item data used in report senario.</p>
<pre><code>          item_data: |
            select concat(COALESCE(vote_proposal.name, ''), ': ', COALESCE(vote_proposal.description, '')) 
            from vote_proposal 
            inner join memb_account on vote_proposal.memb_account_id=memb_account.id
            where vote_proposal.id=d.tag_item
            and memb_account.memb_inc_id='[SESSION:plugin/memb_inc/main/active_inc_id]'</code></pre>

<a name="key_0_1"></a>

### plugin_modules

<pre><code>plugin_modules:
  discuss_anything:
    plugin: 'discuss/anything'
    settings:</code></pre>
<p>Param mysql for MySQL.</p>
<pre><code>      mysql: 'yml:/../buto_data/(any_file).yml'</code></pre>

<a name="key_1"></a>

## Widgets



<a name="key_1_0"></a>

### include

<p>Include js in html head section.</p>
<pre><code>type: widget
data:
  plugin: 'discuss/anything'
  method: include          </code></pre>

<a name="key_2"></a>

## Usage



<a name="key_2_0"></a>

### Embed discussion

<ul>
<li>Embed via ajax request.</li>
<li>Place param has in this example value discussion_test.</li>
<li>Id of div used must be the sames as place value.</li>
</ul>

<a name="key_2_1"></a>

### url

<p>Value discuss_anything must be the same as key in settings plugin_modules.</p>
<pre><code>'/discuss_anything/list?tag_place=discussion_test&amp;tag_item=[id]'</code></pre>

<a name="key_2_2"></a>

### Element test

<pre><code>type: div
attribute: 
  class: alert alert-success
  id: discussion_test
innerHTML: 'load:/discuss_anything/list?tag_place=discussion_test&amp;tag_item=x'</code></pre>

<a name="key_2_3"></a>

### Editable

<p>One could only edit post if within five minutes.</p>

<a name="key_2_4"></a>

### Answer

<p>One could only answer other post if not editable.</p>

<a name="key_3"></a>

## State



<a name="key_3_0"></a>

### Set state

<p>Set state from other plugins.</p>
<pre><code>wfPlugin::includeonce('discuss/anything');
$discuss = new PluginDiscussAnything();
$discuss-&gt;setState('discuss_proposal', wfRequest::get('id'), wfUser::getSession()-&gt;get('plugin/memb_inc/main/active_inc_id'), wfRequest::get('state'));</code></pre>

<a name="key_4"></a>

## Methods



<a name="key_4_0"></a>

### discussion_list_tree

<p>Get all discussions for an item.</p>
<pre><code>wfRequest::set('tag_owner', wfUser::getSession()-&gt;get('plugin/memb_inc/main/active_inc_id'));
wfRequest::set('tag_place', 'discuss_query');
wfRequest::set('tag_item', $query-&gt;get('id'));
wfPlugin::includeonce('discuss/anything');
$discuss = new PluginDiscussAnything();
$rs = $discuss-&gt;discussion_list_tree();</code></pre>

