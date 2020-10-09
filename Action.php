<?php
class ApiInOne_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $cid;
    private $mid;
    private $page;
    private $type;
    private $size;

    /**
     * ApiInOne_Action constructor.
     *
     * 获取传递的各参数
     * cid - 文章id
     * mid - 分类id
     * page - 页码
     * type - 文章类型
     * size - 分页数量
     */
    public function __construct()
    {
        $this->cid = $_REQUEST['cid'];
        $this->mid = $_REQUEST['mid'];
        $this->page = empty($_REQUEST['page'])?1:$_REQUEST['page'];
        //post-page
        $this->type = empty($_REQUEST['type'])?'post':$_REQUEST['type'];
        $this->size = empty($_REQUEST['size'])?10:$_REQUEST['size'];
    }

    /**
     * 返回带scheme的host url
     *
     * @return string Host URL
     */
    private function fetchURL(){
        if(empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
            return $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
        else
            //判断Cloudflare HTTPS
            return $_SERVER['HTTP_X_FORWARDED_PROTO']."://".$_SERVER['HTTP_HOST'];
    }

    /**
     * 获取具有迭代next()函数内部数据，返回数组
     *
     * @param $iterator Typecho_Widget 迭代器
     * @param $name String 所需参数名
     * @return array
     */
    private function fetchIterate($iterator, $name) {
        $tmp = [];
        while($iterator -> next())
            $tmp[] = $iterator->$name;
        return $tmp;
    }

    /**
     * 获取数组特定字段，返回数组
     *
     * @param $array array 数组
     * @param $name String 所需参数名
     * @return array
     */
    private function fetchArray($array, $name){
        $tmp = [];
        foreach ($array as $item)
            $tmp[] = $item[$name];
        return $tmp;
    }

    /**
     * 文章详情、文章列表
     *
     * @throws Typecho_Exception
     */
    public function archive()
    {
        //处理单个文章内容
        if (!empty($this->cid)){
            Typecho_Widget::widget('Widget_Archive', ['pageSize'=>1, 'type'=>$this->type]
                ,['cid' => $this->cid])->to($archive);

            $this->response->throwJson([
                'code' => 1,
                'data' => [
                    'cid' => $archive->cid,
                    'type' => $archive->type,
                    'title' => $archive->title,
                    'permalink' => $archive->permalink,
                    'content' => $archive->text,
                    'author' => implode(",",$this->fetchIterate($archive->author, "name")),
                    'category' => implode(",",$this->fetchArray($archive->categories, "name")),
                    'time' => date("Y/m/d H:i:s", $archive->created)
                ]]);
        }

        //文章列表
        $param = ['pageSize' => $this->size, 'page' => $this->page];

        !empty($mid) && $param['mid'] = $mid;
        switch ($this->type){
            case 'post':
                Typecho_Widget::widget('Widget_Contents_Post_Recent', $param)->to($archive);
                break;
            case 'page':
                Typecho_Widget::widget('Widget_Contents_Page_List', $param)->to($archive);
                break;
        }

        $data = [];
        while ($archive->next())
            $data[] = [
                'cid' => $archive->cid,
                'title' => $archive->title,
                'type' => $archive->type,
                'author' => implode(",",$this->fetchIterate($archive->author, "name")),
                'permalink' => $archive->permalink,
                'api_url' => $this->fetchURL()."/ApiInOne/archive?cid=$archive->cid&type=$archive->type",
                'category' => implode(",",$this->fetchArray($archive->categories, "name")),
                'time' => date("Y/m/d H:i:s", $archive->created),
                'comments_num' => $archive->commentsNum
            ];

        $this->response->throwJson(['code' => 1, 'data' => $data]);
    }

    /**
     * 分类
     *
     * @throws Typecho_Exception
     */
    public function category()
    {
        Typecho_Widget::widget('Widget_Metas_Category_List')->to($categroy);

        $data = [];
        while ($categroy->next())
            $data[] = [
                "mid" => $categroy->mid,
                "name" => $categroy->name,
                "description" => $categroy->description,
                "count" => $categroy->count,
                "order" => $categroy->order,
                "parent" => $categroy->parent,
                "levels" => $categroy->levels,
                "api_url" => $this->fetchURL()."/ApiInOne/archive?mid=$categroy->mid",
                "permalink" => $categroy->permalink,
            ];

        $this->response->throwJson(['code' => 1, 'data'=>$data]);
    }

    /**
     * 评论
     *
     * @throws Typecho_Exception
     */
    public function comment()
    {
        $param = ['pageSize' => $this->size, 'page'=>$this->page];

        !empty($this->cid) && $param['parentId'] = $this->cid;
        Typecho_Widget::widget('Widget_Comments_Recent', $param)->to($comment);

        $data = [];
        while ($comment->next())
            $data[] = [
                'cid' => $comment->cid,
                'author' => $comment->author,
                'text' => $comment->text,
                'status' => $comment->status,
                'parent' => $comment->parent,
                'time' => date("Y/m/d H:i:s",$comment->created),
                'avatar' => "https://gravatar.loli.net/avatar/".md5($comment->mail)."?s=150"
            ];

        $this->response->throwJson(['code' => 1, 'data'=>$data]);
    }

    public function action(){}
}
