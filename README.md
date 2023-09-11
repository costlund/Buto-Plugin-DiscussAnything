# Buto-Plugin-DiscussAnything


## Settings

### plugin
Enabled to include js.
Set email for report.
Example below uses two places.
```
plugin:
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
            and memb_account.memb_inc_id='[SESSION:plugin/memb_inc/main/active_inc_id]'
```
Param tag_owner could be id of an organisation if your application has one stored in session.


### plugin_modules
```
plugin_modules:
  discuss_anything:
    plugin: 'discuss/anything'
    settings:
```
Param mysql for MySQL.
```
      mysql: 'yml:/../buto_data/(any_file).yml'
```

#### username
User name from table account will show author of comments.
One can override it with username parameter.
```
      username: |
        select concat(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as username from memb_account where memb_inc_id='[SESSION:plugin/memb_inc/main/active_inc_id]' and account_id=d.created_by
```

#### item_data
Add item data used in report senario.
```
      item_data: |
        select concat(COALESCE(vote_proposal.name, ''), ': ', COALESCE(vote_proposal.description, '')) 
        from vote_proposal 
        inner join memb_account on vote_proposal.memb_account_id=memb_account.id
        where vote_proposal.id=d.tag_item
        and memb_account.memb_inc_id='[SESSION:plugin/memb_inc/main/active_inc_id]'
```

### Include js
Include js in html head section.
```
type: widget
data:
  plugin: 'discuss/anything'
  method: include          
```

### Embed discussion
- Embed via ajax request.
- Place param has in this example value discussion_test.
- Id of div used must be the sames as place value.

#### url
Value discuss_anything must be the same as key in settings plugin_modules.
```
'/discuss_anything/list?tag_place=discussion_test&tag_item=[id]'
```

#### Element test
```
type: div
attribute: 
  class: alert alert-success
  id: discussion_test
innerHTML: 'load:/discuss_anything/list?tag_place=discussion_test&tag_item=x'
```
