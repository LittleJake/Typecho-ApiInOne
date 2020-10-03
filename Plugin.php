<?php
/**
 * Typecho多功能API插件，建议在cloudflare添加URL规则全部缓存降低数据库压力。
 * 
 * @package ApiInOne
 * @author LittleJake
 * @version 1.0.0
 * @link https://blog.littlejake.net
 *
 */
class ApiInOne_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     */
    public static function activate(){
        //添加路由
	    Helper::addRoute('archive_route', '/ApiInOne/archive', 'ApiInOne_Action', 'archive');
	    Helper::addRoute('category_route', '/ApiInOne/category', 'ApiInOne_Action', 'category');
	    Helper::addRoute('comment_route', '/ApiInOne/comment', 'ApiInOne_Action', 'comment');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     */
    public static function deactivate(){
        //删除路由
	    Helper::removeRoute('archive_route');
	    Helper::removeRoute('comment_route');
	    Helper::removeRoute('category_route');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

}
