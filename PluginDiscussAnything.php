<?php
class PluginDiscussAnything{
  private $settings;
  private $plugin;
  private $mysql;
  private $i18n = null;
  private $tag_place;
  private $state = null;
  function __construct() {
    $this->settings = wfPlugin::getModuleSettings('discuss/anything', true);
    $this->plugin = wfPlugin::getPluginSettings('discuss/anything', true);
    /**
     * 
     */
    $user = wfUser::getSession();
    $this->tag_place = wfRequest::get('tag_place');
    if($this->plugin->get("place/$this->tag_place/tag_owner")){
      wfRequest::set('tag_owner', $user->get($this->plugin->get("place/$this->tag_place/tag_owner")));
    }else{
      wfRequest::set('tag_owner', $user->get('theme_data/theme'));
    }
    /**
     * 
     */
    wfPlugin::includeonce('wf/mysql');
    $this->mysql =new PluginWfMysql();
    /**
     * 
     */
    $this->mysql->open($this->settings->get('mysql'));
    /**
     * 
     */
    wfPlugin::includeonce('i18n/translate_v1');
    $this->i18n = new PluginI18nTranslate_v1();
    $this->i18n->path = '/plugin/discuss/anything/i18n';
    /**
     * 
     */
    wfPlugin::includeonce('wf/yml');
    $this->state = $this->db_discussion_state_one(wfRequest::getAll());
  }
  public function page_list(){
    $script = "PluginDiscussAnything.list({class: '".wfGlobals::get('class')."', place: '".wfRequest::get('tag_place')."', item: '".wfRequest::get('tag_item')."'})";
    $element = wfDocument::getElementFromFolder(__DIR__, __FUNCTION__);
    $element->setByTag(array('script' => $script), 'script');
    $element->setByTag($this->state->get(), 'state');
    $element->setByTag(array('id' => 'discussion_'.wfRequest::get('tag_place')), 'div');
    wfDocument::renderElement($element);
  }
  public function page_list_data(){
    /**
     * 
     */
    if($this->state->get('state_removed')){
      exit(json_encode(array()));
    }
    /**
     * 
     */
    $tree = $this->discussion_list_tree();
    $created_by = $this->getCreatedBy();
    $answer_disable = false;
    $element = array();
    $btn_create = wfDocument::getElementFromFolder(__DIR__, 'btn_create');
    /**
     * i18n
     */
    $btn_create->setByTag(array(
      'Add a comment.' => $this->i18n->translateFromTheme('Add a comment.')
      ,'Comment' => $this->i18n->translateFromTheme('Comment')
    ), 'i18n');
    /**
     * 
     */
    $btn_create->setByTag(wfGlobals::get(), 'globals');
    $btn_create->setByTag(wfRequest::getAll(), 'get');
    if($this->state->get('state_active')){
      $element[] = $btn_create->get();
    }
    if($tree->get('level_1')){
      foreach ($tree->get('level_1') as $key => $value) {
        $item = new PluginWfArray($value);
        $element[] = $this->getDiscussion($item, $created_by, 1, $answer_disable);
        if($item->get('level_2') && !$answer_disable){
          foreach ($item->get('level_2') as $key2 => $value2) {
            $item2 = new PluginWfArray($value2);
            $element[] = $this->getDiscussion($item2, $created_by, 2);
            if($item2->get('level_3')){
              foreach ($item2->get('level_3') as $key3 => $value3) {
                $item3 = new PluginWfArray($value3);
                $element[] = $this->getDiscussion($item3, $created_by, 3, false, $item2);
              }
            }
          }
        }
      }
    }else{
      $element[] = wfDocument::createHtmlElement('i', 'Det finns inga diskussioner.');
    }
    exit(json_encode($element));
  }
  public function make_links($text, $class='', $target='_blank'){
      return preg_replace('!((http\:\/\/|ftp\:\/\/|https\:\/\/)|www\.)([-a-zA-Zа-яА-Я0-9\~\!\@\#\$\%\^\&\*\(\)_\-\=\+\\\/\?\.\:\;\'\,]*)?!ism', 
      '<a class="'.$class.'" href="//$3" target="'.$target.'">$1$3</a>', 
      $text);
  }  
  public function formatText($txt){
    $txt = wfPhpfunc::str_replace("\n", "<br>", $txt);
    $txt = $this->make_links($txt);
    return $txt;
  }
  public function formatDate($str){
    if(!wfPhpfunc::strlen($str)){
      return null;
    }elseif(wfPhpfunc::strlen($str)==10){
      return wfPhpfunc::substr($str, 2, 2).substr($str, 5, 2).substr($str, 8, 2);
    }elseif(wfPhpfunc::strlen($str)==19){
      return wfPhpfunc::substr($str, 2, 2).substr($str, 5, 2).substr($str, 8, 2).' '.substr($str, 11, 5);
    }else{
      return $str;
    }
  }
  private function getDiscussion($item, $created_by, $level = 1, $answer_disable = false, $item_above = null){
    /**
     * anchor
     */
    $item->set('anchor', '#'.$item->get('id'));
    /**
     * i18n
     * Not in usage since (240202).
     */
    $i18n = array('minutes' => 'minutes');
    foreach($i18n as $k => $v){
      $i18n[$k] = $this->i18n->translateFromTheme($v);
    }
    /**
     * 
     */
    $item->set('text', $this->formatText($item->get('text')));
    /**
     * If editable and not author.
     */
    if($item->get('created_by')!=$created_by && $item->get('editable')){
      $discussion = wfDocument::getElementFromFolder(__DIR__, 'discussion_none');
      return $discussion->get();
    }
    /**
     * 
     */
    $discussion = wfDocument::getElementFromFolder(__DIR__, 'discussion');
    $discussion->setByTag($i18n, 'i18n');
    /**
     * create_discuss_anything_id
     */
    $item->set('create_discuss_anything_id', $item->get('id'));
    if($level==3){
      $item->set('create_discuss_anything_id', $item_above->get('id'));
    }
    /**
     * 
     */
    $discussion->setByTag($item->get());
    /**
     * bg color if creator.
     */
    $card_class = new PluginWfArray();
    $card_class->set('class', 'card mb-1');
    if($item->get('created_by')==$created_by){
      $card_class->set('class', 'card mb-1 text-bg-secondary');
    }
    $discussion->setByTag($card_class->get(), 'card');
    /**
     * 
     */
    if($item->get('created_by')!=$created_by && $item->get('editable')){
      // return null;
    }
    /**
     * Well.
     */
    $well_style = '';
    if($level==2){
      $well_style = 'margin-left:30px';
    }elseif($level==3){
      $well_style = 'margin-left:60px';
    }
    $discussion->setByTag(array('style' => $well_style), 'well');
    /**
     * 
     */
    $discussion->setByTag($item->get());
    /**
     * Button edit.
     */
    if($item->get('created_by')==$created_by && $item->get('editable') && $this->state->get('state_active')){
      $btn_edit_style = 'visibility:visible;';
    }else{
      $btn_edit_style = 'visibility:hidden;';
    }
    $discussion->setByTag(array('style' => $btn_edit_style), 'btn_edit');
    $discussion->setByTag(wfGlobals::get(), '_globals');
    /**
     * Button create.
     */
    if($answer_disable || $item->get('editable') || !$this->state->get('state_active')){
      $btn_create_style = 'display:none;';
    }else{
      $btn_create_style = 'display:;';
    }
    $discussion->setByTag(array('style' => $btn_create_style), 'btn_create');
    /**
     * Button Like.
     */
    $btn_like_octicons = new PluginWfArray();
    $btn_like_octicons->set('src', '/plugin/icons/octicons/build/svg/thumbsup.svg');
    $btn_like_octicons->set('class', 'octicons_disabled');
    if(!$this->state->get('state_active')){
      $btn_like_style = 'display:none;';
      $btn_like_class = '';
    }else if($level==1){
      $btn_like_style = 'display:;';
      $btn_like_class = 'btn btn-secondary btn-sm';
      /**
       * 
       */
      if($item->get('like_value')==1){
        $btn_like_class = 'btn btn-primary btn-sm';
      }elseif($item->get('like_value')==2){
        $btn_like_class = 'btn btn-warning btn-sm';
      }
      /**
       * 
       */
      if($item->get('like_value')==1){
        $btn_like_octicons->set('class', '');
      }elseif($item->get('like_value')==2){
        $btn_like_octicons->set('src', '/plugin/icons/octicons/build/svg/thumbsdown.svg');
        $btn_like_octicons->set('class', '');
      }
    }else{
      $btn_like_style = 'display:none;';
      $btn_like_class = '';
    }
    $discussion->setByTag($btn_like_octicons->get(), 'btn_like_octicons');
    /**
     * 
     */
    $discussion->setByTag(array('style' => $btn_like_style, 'class' => $btn_like_class), 'btn_like');
    /**
     * Likes.
     */
    $likes_style = 'display:none';
    if($level==1){
      $likes_style = 'display:;font-size:smaller';
    }
    $discussion->setByTag(array('style' => $likes_style), 'likes');
    /**
     * 
     */
    return $discussion->get();
  }
  private function getCreatedBy(){
    $user = wfUser::getSession();
    return $user->get('user_id');
  }
  public function sql($key){
    $sql = new PluginWfYml(__DIR__.'/mysql/sql.yml', $key);
    return $sql;
  }
  private function db_discussion_list(){
    $sql = $this->sql('discussion_list');
    /**
     * full_name
     */
    if(!$this->plugin->get("place/$this->tag_place/username")){
      $sql->set('sql', str_replace("'_username_'", "select username from account where id=d.created_by", $sql->get('sql')));
    }else{
      $sql->set('sql', str_replace("'_username_'", $this->plugin->get("place/$this->tag_place/username"), $sql->get('sql')));
    }
    /**
     * 
     */
    $sql->setByTag($this->settings->get());
    $this->mysql->execute($sql->get());
    $rs = $this->mysql->getMany();
    foreach($rs as $k => $v){
      if(!$v['full_name']){
        $rs[$k]['full_name'] = '(ej kvar)';
      }
      if(!$rs[$k]['discuss_anything_id']){
        $rs[$k]['like_result_text'] = '('.$rs[$k]['likes'].'/'.$rs[$k]['dislikes'].')';
      }else{
        $rs[$k]['like_result_text'] = '';
      }
    }
    return $rs;
  }
  private function db_discussion_one(){
    $sql = $this->sql('discussion_one');
    /**
     * full_name
     */
    if(!$this->plugin->get("place/$this->tag_place/username")){
      $sql->set('sql', str_replace("'_username_'", "select username from account where id=d.created_by", $sql->get('sql')));
    }else{
      $sql->set('sql', str_replace("'_username_'", $this->plugin->get("place/$this->tag_place/username"), $sql->get('sql')));
    }
    if($this->plugin->get("place/$this->tag_place/item_data")){
      $sql->set('sql', str_replace("'_item_data_'", $this->plugin->get("place/$this->tag_place/item_data"), $sql->get('sql')));
    }
    $sql->setByTag($this->settings->get());
    $this->mysql->execute($sql->get());
    $rs = $this->mysql->getOne();
    return $rs;
  }
  private function db_discussion_update($id){
    $sql = $this->sql('discussion_update');
    $this->mysql->execute($sql->get());
    return null;
  }
  private function db_discussion_create(){
    $sql = $this->sql('discussion_create');
    $this->mysql->execute($sql->get());
  }
  private function db_discussion_delete($id){
    $sql = $this->sql('discussion_delete');
    $this->mysql->execute($sql->get());
    return null;
  }
  private function db_like_get(){
    $sql = $this->sql('like_get');
    $this->mysql->execute($sql->get());
    $rs = $this->mysql->getOne();
    if(!$rs->get('id')){
      $rs->set('id', null);
    }
    return $rs;
  }
  private function db_like_update(){
    $sql = $this->sql('like_update');
    $this->mysql->execute($sql->get());
    return null;
  }
  private function db_like_delete(){
    $sql = $this->sql('like_delete');
    $this->mysql->execute($sql->get());
    return null;
  }
  private function db_like_insert(){
    wfRequest::set('like_id', wfCrypt::getUid());
    $sql = $this->sql('like_insert');
    $this->mysql->execute($sql->get());
    return null;
  }
  private function db_discussion_state_one($data){
    $sql = $this->sql('discussion_state_one');
    $sql->setByTag($data);
    $this->mysql->execute($sql->get());
    $rs = $this->mysql->getOne(array('sql' => $sql->get()));
    if(!$rs->get('id')){
      $rs->set('state_active', 1);
    }
    return $rs;
  }
  private function db_discussion_state_insert($data){
    $sql = $this->sql('discussion_state_insert');
    $sql->setByTag($data);
    $this->mysql->execute($sql->get());
    return null;
  }
  private function db_discussion_state_set($data){
    $sql = $this->sql('discussion_state_set');
    $sql->setByTag($data);
    $this->mysql->execute($sql->get());
    return null;
  }
  public function setState($tag_place, $tag_item, $tag_owner, $state){
    /**
     * 
     */
    $data = array('tag_place' => $tag_place, 'tag_item' => $tag_item, 'tag_owner' => $tag_owner, 'state' => $state);
    /**
     * 
     */
    $temp = $this->db_discussion_state_one($data);
    if(!$temp->get('id')){
      $this->db_discussion_state_insert($data);
    }
    /**
     * 
     */
    $this->db_discussion_state_set($data);
    /**
     * 
     */
    return null;
  }
  public function discussion_list_tree(){
    /**
     * Data.
     */
    $discussion_list = array();
    foreach ($this->db_discussion_list() as $key => $value) {
      $discussion_list[$value['id']] = $value;
    }
    $tree = new PluginWfArray();
    /**
     * First.
     */
    foreach ($discussion_list as $key => $value) {
      $item = new PluginWfArray($value);
      if(!$item->get('discuss_anything_id')){
        $tree->set('level_1/'.$item->get('id').'', $item->get());
      }
    }
    /**
     * Second.
     */
    if($tree->get('level_1')){
      foreach ($tree->get('level_1') as $key => $value) {
        $item = new PluginWfArray($value);
        foreach ($discussion_list as $key2 => $value2) {
          $item2 = new PluginWfArray($value2);
          if($item2->get('discuss_anything_id')==$key){
            $tree->set('level_1/'.$key.'/level_2/'.$key2, $item2->get());
          }
        }
      }
    }
    /**
     * Third.
     */
    if($tree->get('level_1')){
      foreach ($tree->get('level_1') as $key => $value) {
        $item = new PluginWfArray($value);
        if(!$item->get('level_2')){
          continue;
        }
        foreach ($item->get('level_2') as $key2 => $value2) {
          foreach ($discussion_list as $key3 => $value3) {
            $item3 = new PluginWfArray($value3);
            if($item3->get('discuss_anything_id')==$key2){
              $tree->set('level_1/'.$key.'/level_2/'.$key2.'/level_3/'.$key3, $item3->get());
            }
          }
        }
      }
    }
    /**
     * Sort level_1
     */
    $level_1 = new PluginWfArray($tree->get('level_1'));
    $level_1->sort('created_at', true);
    $tree->set('level_1', $level_1->get());
    /**
     * 
     */
    return $tree;
  }
  public function widget_include(){
    $widget = PluginWfEmbed::getWidgetEmbedJs(__CLASS__);
    wfDocument::renderElement(array($widget));
  }
  public function page_discussion_edit(){
    $form = wfDocument::getElementFromFolder(__DIR__, 'discussion_edit', 'form');
    $form->setByTag(array('url' =>  '/'.wfGlobals::get('class').'/discussion_capture'));
    $widget = wfDocument::createWidget('form/form_v1', 'render', $form->get());
    wfDocument::renderElement(array($widget));
  }
  public function form_render_discussion($form){
    $form = new PluginWfArray($form);
    if(wfRequest::get('id')){
      $rs = $this->db_discussion_one();
      $form->setByTag(wfGlobals::get(), '_globals');
      $form->setByTag($rs->get());
    }else{
      $form->setByTag(array('tag_place' => wfRequest::get('tag_place'), 'tag_item' => wfRequest::get('tag_item'), 'discuss_anything_id' => wfRequest::get('discuss_anything_id')), 'rs', true);
    }
    return $form->get();
  }
  public function form_validate_discussion($form){
    $form = new PluginWfArray($form);
    if($form->get('items/id/post_value')){
      $discussion = $this->db_discussion_one();
      if(!$discussion->get('editable')){
        $form->set('items/text/is_valid', false);
        $form->set('items/text/errors/', $this->i18n->translateFromTheme('To old to edit.'));
      }
    }
    return $form->get();
  }
  public function page_discussion_capture(){
    $form = wfDocument::getElementFromFolder(__DIR__, 'discussion_edit', 'form');
    $widget = wfDocument::createWidget('form/form_v1', 'capture', $form->get());
    wfDocument::renderElement(array($widget));
  }
  public function form_capture_discussion(){
    if(!wfRequest::get('id')){
      wfRequest::set('id', wfCrypt::getUid());
      $this->db_discussion_create();
    }
    $this->db_discussion_update(wfRequest::get('id'));
    wfRequest::set('class', wfGlobals::get('class'));
    $json = json_encode(wfRequest::getAll());
    return array("PluginDiscussAnything.updateDiscussion($json);");
  }
  public function page_discussion_delete(){
    $discussion = $this->db_discussion_one();
    if($discussion->get('editable')){
      $this->db_discussion_delete(wfRequest::get('id'));
      exit('Delte success.');
    }else{
      exit('Delte not success.');
    }
  }
  public function page_like(){
    $element = wfDocument::getElementFromFolder(__DIR__, 'like');
    $discussion = $this->db_discussion_one();
    if($discussion->get('id')){
      $like = $this->db_like_get($discussion->get('id'));
      $element->setByTag($like->get());
    }
    $element->setByTag(wfGlobals::get(), '_globals');
    $element->setByTag(wfRequest::getAll(), 'get');
    wfDocument::renderElement($element->get());
  }
  public function page_like_set(){
    $value = wfRequest::get('like_value');
    if($value != '1' && $value != '2'){
      exit('Error in value.');
    }
    $discussion = $this->db_discussion_one();
    if($discussion->get('id')){
      $like = $this->db_like_get($discussion->get('id'));
      if($like->get('id')){
        if($like->get('value')==$value){
          $this->db_like_delete();
        }else{
          $this->db_like_update();
        }
      }else{
        $this->db_like_insert();
      }
    }
    exit('set like');
  }
  public function page_discussion_report(){
    /**
     * 
     */
    $discussion = $this->db_discussion_one();
    $user = wfUser::getSession();
    /**
     * 
     */
    $form = wfDocument::getElementFromFolder(__DIR__, 'discussion_report', 'form');
    $form->setByTag(array('url' =>  '/'.wfGlobals::get('class').'/discussion_report_capture'));
    $form->setByTag($discussion->get(), 'discussion');
    $form->setByTag($user->get(), 'user');
    $form->setByTag(wfRequest::getAll());
    $widget = wfDocument::createWidget('form/form_v1', 'render', $form->get());
    wfDocument::renderElement(array($widget));
  }
  public function page_discussion_report_capture(){
    /**
     * 
     */
    if(!wfConfig::get('plugin/discuss/anything/data/email')){
      throw new Exception(__CLASS__.'::'.__FUNCTION__.' says: Config param plugin/discuss/anything/data/email does not exist!');
    }
    $form = wfDocument::getElementFromFolder(__DIR__, 'discussion_report', 'form');
    /**
     * 
     */
    $form->set('capture/data/subject', $form->get('capture/data/subject').' ('.wfServer::getServerName().')');
    /**
     * 
     */
    $form->setByTag(wfConfig::get('plugin/discuss/anything/data'), 'mailqueue');
    /**
     * 
     */
    $widget = wfDocument::createWidget('form/form_v1', 'capture', $form->get());
    wfDocument::renderElement(array($widget));
  }
}