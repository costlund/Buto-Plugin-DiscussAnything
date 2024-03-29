function PluginDiscussAnything(){
  this.data = {item: {}};
  this.list = function(data){
    $.getJSON( "/"+data.class+"/list_data?tag_place="+data.place+"&tag_item="+data.item+"", function( json ) {
      document.getElementById('discussion_'+data.place).innerHTML = '';
      PluginWfDom.render(json, 'discussion_'+data.place);
      if(PluginDiscussAnything.data.item.id){
        /**
         * 
         */
        if(data.discuss_anything_id){
          /**
           * Update if new post level one.
           */
          location.href='#'+PluginDiscussAnything.data.item.id;
        }
      }
     });    
  }
  this.editDiscussion = function(btn){
    if(btn.getAttribute('data-editable')=='0'){
      alert('Note editable!');
    }else{
      PluginWfBootstrapjs.modal({id: 'modal_discuss_anything_edit_discussion', url: '/'+btn.getAttribute('data-class')+'/discussion_edit/tag_place/'+btn.getAttribute('data-place')+'/tag_item/'+btn.getAttribute('data-item')+'/id/'+btn.getAttribute('data-id')+'', lable: btn.innerHTML});
    }
  }
  this.updateDiscussion = function(data){
    this.data.item = data;
    PluginMemb_incMain.closeModal('modal_discuss_anything_edit_discussion');
    PluginDiscussAnything.list({class: data.class, place: data.tag_place, item: data.tag_item, discuss_anything_id: data.discuss_anything_id});
  }
  this.createDiscussion = function(btn){
    PluginWfBootstrapjs.modal({id: 'modal_discuss_anything_edit_discussion', url: '/'+btn.getAttribute('data-class')+'/discussion_edit/tag_place/'+btn.getAttribute('data-place')+'/tag_item/'+btn.getAttribute('data-item')+'?discuss_anything_id='+btn.getAttribute('data-discuss_anything_id'), lable: btn.innerHTML});
  }
  this.deleteDiscussion = function(btn){
    PluginWfBootstrapjs.confirm({content: btn.getAttribute('title'), method: function(){PluginDiscussAnything.deleteDiscussion_confirmed();}, data: btn });
  }
  this.deleteDiscussion_confirmed = function(btn){
    if(PluginWfBootstrapjs.confirm_data.ok){
      var btn = PluginWfBootstrapjs.confirm_data.data;
      $.get( '/'+btn.getAttribute('data-class')+'/discussion_delete/tag_place/'+btn.getAttribute('data-place')+'/tag_item/'+btn.getAttribute('data-item')+'/id/'+btn.getAttribute('data-id'), function( data ) {
        PluginMemb_incMain.closeModal('modal_discuss_anything_edit_discussion');
        PluginDiscussAnything.list({class: btn.getAttribute('data-class'), place: btn.getAttribute('data-place'), item: btn.getAttribute('data-item')});
      });
    }
  }
  this.viewLike = function(btn){
    PluginWfBootstrapjs.modal({id: 'modal_discuss_anything_like', url: '/'+btn.getAttribute('data-class')+'/like/tag_place/'+btn.getAttribute('data-place')+'/tag_item/'+btn.getAttribute('data-item')+'/id/'+btn.getAttribute('data-id'), lable: btn.innerHTML, size: 'sm'});
  }
  this.setLike = function(btn){
    $.get( '/'+btn.getAttribute('data-class')+'/like_set/tag_place/'+btn.getAttribute('data-place')+'/tag_item/'+btn.getAttribute('data-item')+'/id/'+btn.getAttribute('data-id')+'/like_value/'+btn.getAttribute('data-like')+'?like_id='+btn.getAttribute('data-like_id'), function( data ) {
      PluginMemb_incMain.closeModal('modal_discuss_anything_like');
      PluginDiscussAnything.list({class: btn.getAttribute('data-class'), place: btn.getAttribute('data-place'), item: btn.getAttribute('data-item')});
    });
  }
  this.reportDiscussion = function(btn){
    PluginWfBootstrapjs.modal({id: 'modal_discuss_anything_report_discussion', url: '/'+btn.getAttribute('data-class')+'/discussion_report/tag_place/'+btn.getAttribute('data-place')+'/tag_item/'+btn.getAttribute('data-item')+'/id/'+btn.getAttribute('data-id'), lable: btn.innerHTML});
  }
  this.discussionReportCapture = function(){
    PluginMemb_incMain.closeModal('modal_discuss_anything_report_discussion');
  }
}
var PluginDiscussAnything = new PluginDiscussAnything();

