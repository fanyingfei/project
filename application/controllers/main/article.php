<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class article extends MY_Controller  {
    private $type = 6; //默认为6
    private $table_name = 'article';
    private $tags_data = array(
                                            4=>array('感言','废话','童年','微小说','文学'),
                                            6=>array('恐怖/惊悚','感动/伤感','历史','怪奇','人生','温暖','励志'),
                                            7=>array('web前端','php','ios','mysql','linux','c/c++','java','android')
                                        );

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

    public function __construct() {
        parent :: __construct();
        $this->assign('is_show',1);
        $this->load->model('model_article');
        parent :: $order_data['最冷'] = 'scan asc';
    }

    public function zzs($p = 0){
        $this->set_type_value(__FUNCTION__);
        $this->assign('body','body-article');
        $this->assign('title','渣渣说－搞东搞西');
        $this->assign('keywords','渣渣说');
        $this->assign('description','渣渣说');
        $is_show = 0;
        if(!empty($_SESSION['email']) && $_SESSION['email'] == '1602515264@qq.com') $is_show = 1;
        $this->assign('is_show',$is_show);
        $this->article_list($p);
    }

    public function tale($p = 0){
        $this->set_type_value(__FUNCTION__);
        $this->assign('body','body-article');
        $this->assign('title','故事－搞东搞西');
        $this->assign('keywords','故事,恐怖,惊悚,情感,励志,怪奇');
        $this->assign('description','嘘~来看故事啦');
        $this->article_list($p);
    }

    public function cxy($p = 0){
        $this->set_type_value(__FUNCTION__);
        $this->assign('body','body-article');
        $this->assign('title','程序猿－搞东搞西');
        $this->assign('keywords','程序员,程序猿,码农,代码,博客,php,web,ios');
        $this->assign('description','这是专属程序员的技术博客');
        $this->article_list($p);
    }

    /*
    * 神话详情
    */
    public function detail($time,$param){
        $id = intval(get_detail_id($param));
        if(empty($id)) parent :: error_msg('你要找的内容不见啦！');
        $detail = $this->model_article->detail($id);
        if(empty($detail)) parent :: error_msg('你要找的内容不见啦！');
        $this->scan_record($id);
     //   $detail['content'] = strip_tags($detail['content'],'<img><br>');
        $detail['create_time'] = substr($detail['create_time'] , 0 , 10);
        $description = mb_substr(str_replace(array('"','\'',' '),'',strip_tags($detail['content'])), 0, 100, 'gbk');
        $this->assign('data',$detail);
        $this->assign('body','body-detail');
        $this->assign('title',$detail['title'].'－搞东搞西');
        $this->assign('keywords',$detail['tags'].' 搞东搞西');
        $this->assign('description',empty($description) ? $detail['title'] : $description);

        $this->native_display('main/header.html');
        $this->native_display('main/detail.html');
        $this->native_display('main/footer.html');
    }

    /*
     * 列表
     */
    public function article_list($p = 0)
    {
        $limit = 10;
        $p = intval($p);
        $is_random = 0;
        $this->load->library('page');
        $where = 'where status = 1 and type = '.$this->type;
        $tags = empty($_COOKIE['tags']) ? '' : $_COOKIE['tags'] ;
        if(!empty($tags)) $where .= " and tags like '%$tags%' ";
        $search = empty($_COOKIE['search']) ? '' : $_COOKIE['search'] ;
        if(!empty($search)) $where .= " and name like '%$search%'  or title like '%$search%'  or tags like '%$search%' ";

        $order_by_data = parent::$order_data;
        $cookie_order_by  = empty($_COOKIE['order_by']) || empty($order_by_data[$_COOKIE['order_by']]) ? '' : $order_by_data[$_COOKIE['order_by']];

        if($cookie_order_by == 'random') $is_random = 1;

        if($is_random == 1){
            $key_res = $this->model_article->data_key($where);
            $count = count($key_res) - 1;
            $random_data = array();
            while(count($random_data)<$limit){
                $rand =mt_rand(0 , $count);
                $random_data[] = $key_res[$rand]['id'];
            }
            $random_data = array_unique($random_data);
            $list = $this->model_article->data_random_list($random_data , $limit , $where);
            $page = '<div onclick="window.location.href=window.location.href"><a>再随一次</a></div>';
        }else {
            //得到总数
            $count = $this->model_article->data_count($where);
            $total_page = ceil($count/$limit);
            if(empty($p) || $p > $total_page) $p = $total_page;
            $order_by = empty($cookie_order_by) ? '' : ' order by '.$cookie_order_by;
            //得到数据
            $list = $this->model_article->data_list($total_page - $p, $limit, $where, $order_by);
            //生成页码
            $page = get_page($count,$limit,$total_page - $p + 1);
        }
        //得到头像
        $user_res =  parent :: get_user_avatar($list);
        if(!empty($user_res)){
            $user_avatar = my_array_column($user_res , 'avatar' , 'user_id');
            $user_time = my_array_column($user_res , 'create_time' , 'user_id');
        }

        foreach($list as &$v){
            $v['user_sn'] = '';
            $v['con_id'] = $v['art_id'];
            $v['u_name'] = empty($v['user_id']) ? md5($v['email']) : $v['name'];
            $v['tags'] = explode(' ' , $v['tags']);
            if(!empty($v['user_id'])){
                $time = empty($user_time[$v['user_id']]) ? '' : $user_time[$v['user_id']];
                $v['user_sn'] = get_user_sn($v['user_id'] , $time);
            }
            $v['detail_url'] = get_detail_url($v['art_id'],$v['create_time']);
            $v['year'] = substr($v['create_time'], 0 , 7);
            $v['day'] = substr($v['create_time'], 8 , 2);
            $v['create_time'] = change_time($v['create_time']);
        }

        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->assign('page',$page);
        $this->assign('type',$this->type);
        $this->assign('order_by', parent :: $order_data);
        $this->assign('tags',$this->tags_data[$this->type]);

        $this->display('article.html');
    }

    /*
     * 用户提交内容
     */
    public function save(){
        //是否拉入黑名单
        $this->load->model('model_black');
        $this->load->model('model_users');
        $res = $this->model_black->find_one();
        if($res) splash('error','你已被拉入黑名单');

        $data['type'] = intval($_REQUEST['type']);
        if($data['type'] == 4){
            //渣渣说
            $_REQUEST['content'] = strip_tags($_REQUEST['content'],'<p><br>');
        }
        $data['tags'] = trim(strip_tags($_REQUEST['tags']));
        if(empty($data['tags'])) splash('error','请添加标签');

        $data['content'] = $content = trim($_REQUEST['content']);
        if(empty($content)) splash('error','请填写内容');

        $title = trim(strip_tags($_REQUEST['title']));
        if(empty($title)) splash('error','请填写标题');
        $data['title'] = $title;

        if(is_login()){
            //已登陆用户
            $data['user_id'] = $_SESSION['user_id'];
            $data['name'] = $_SESSION['name'];
            $data['email'] = $_SESSION['email'];
        }else{
            $data['name'] = strip_tags(trim($_REQUEST['name']));
            //已经注册过的昵称不能用
            $res_by_name = $this->model_users->get_user_by_name($data['name']);
            if(!empty($res_by_name)) splash('error','该昵称已被注册，仅本人登陆后可用');

            $data['name'] = strip_tags(trim($_REQUEST['name']));
            $data['email'] = strip_tags(trim($_REQUEST['email']));
            valid_name($data['name']);
            valid_email($data['email']);
        }

        $res  = $this->model_article->save($data);
        if($res){
            splash('success','提交成功，审核后自动发布');
        }else{
            splash('error','提交失败');
        }
    }

    /*
     * 记录浏览
     */
    public function scan_record($id){
        $this->load->model('model_record');
        $type = -1;  //文章详情浏览专用
        $res = $this->model_record->is_has($id, $type);
        if($res){
            $data = array('type'=>$type ,'row_id'=>$id);
            $list  = $this->model_record->save($data);
            $this->model_article->update_scan($id);
        }
    }

    /*
     * 文章内容点赞
     */
    public function article_record(){
        parent :: record($this->table_name);
    }

    public function error(){
        parent :: error_msg();
    }

    public function set_type_value($fun){
        $this->type = parent :: $all_type_data[$fun];
        $tags = empty($_COOKIE['tags']) ? '' : '['. $_COOKIE['tags'].']' ;
        $this->assign('menu',parent :: $all_type_name[$this->type].' '.$tags);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */