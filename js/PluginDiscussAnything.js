function PluginDiscussAnything(){
  this.list = function(data){
    $.getJSON( "/"+data.class+"/list_data?tag_place="+data.place+"&tag_item="+data.item+"", function( json ) {
      document.getElementById('discussion_'+data.place).innerHTML = '';
      PluginWfDom.render(json, 'discussion_'+data.place);
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
    PluginMemb_incMain.closeModal('modal_discuss_anything_edit_discussion');
    PluginDiscussAnything.list({class: data.class, place: data.tag_place, item: data.tag_item});
  }
  this.createDiscussion = function(btn){
    PluginWfBootstrapjs.modal({id: 'modal_discuss_anything_edit_discussion', url: '/'+btn.getAttribute('data-class')+'/discussion_edit/tag_place/'+btn.getAttribute('data-place')+'/tag_item/'+btn.getAttribute('data-item')+'?discuss_anything_id='+btn.getAttribute('data-discuss_anything_id'), lable: btn.innerHTML});
  }
  this.deleteDiscussion = function(btn){
    if(confirm(PluginI18nJson_v1.i18n('Are_you_sure?'))){
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

