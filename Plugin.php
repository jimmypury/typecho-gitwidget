<?php

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Git Widget For Typecho.
 *
 * @package GitWidget
 * @author Simon.H
 * @version 1.0.0
 * @link http://ywy.me
 */
class GitWidget_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('GitWidget_Plugin','parselable');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('GitWidget_Plugin','parselable');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('GitWidget_Plugin','footerjs');
    }
    
    /**
     * 内容标签替换
     * 
     * @param string $content
     * @return string
     */
    public static function parselable($content, $widget, $lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;
        if ($widget instanceof Widget_Archive) {
            // 没有标签直接返回
            if ( false === strpos( $content, '[' ) ) {
                return $content;
            }
            $tags = self::parseTag($content, 'gitwidget');
            foreach($tags as $tag) {
                //$content .= $tag[0];
                $content = self::parseAndReplaceTag($content, $tag[0]);
            }
        }
        return $content;
    }
    
    private static function parseAndReplaceTag ($content, $tag) {
        // (\w+)=(?:['"]([^\["\']+?)['"]|(\w+))
        $regex = "/(\w+)=(?:['\"]([^\[\"\']+?)['\"]|(\w+))/";
        $match = array();
        $atts = array();
        //$content .= $tag;
        preg_match_all($regex, $tag, $match, PREG_SET_ORDER);
        foreach($match as $item) {
          if(true === isset($item[3])) {
            $atts[$item[1]] = $item[3];
          }
          else {
            $atts[$item[1]] = $item[2];
          }
        }
        if(isset($atts['skip'])) {
            return $content;
        }
        //$content .= $atts['type'];
        if (isset($atts['type']) && isset($atts['url'])) {
            $regexTag = sprintf('/%s/', preg_quote($tag, '/'));
            $replace_with = '';
            if($atts['type'] === 'gitee') {
                $replace_with .=  "<script src='//gitee.com/".$atts['url']."/widget_preview'></script>";
                $replace_with .= '<style>';
                $replace_with .= Typecho_Widget::widget('Widget_Options')->plugin('GitWidget')->gitee_css;
                $replace_with .= '</style>';
            }elseif($atts['type'] === 'github'){
                $replace_with .=  "<script>var needGithubWidget=1;</script>";
                $replace_with .= '<div class="github-widget" data-repo="'.$atts['url'].'"></div>';
                $replace_with .= '<style>';
                $replace_with .= Typecho_Widget::widget('Widget_Options')->plugin('GitWidget')->github_css;
                $replace_with .= '</style>';
            }
            $content = preg_replace($regexTag, $replace_with, $content, 1);
        }
        return $content;
    }
    
    private static function parseTag($content, $tagnames = null ) {
        $regex = "/\[{$tagnames}[^\]]*?\]/";
        $match = array();
        preg_match_all($regex, $content, $match, PREG_SET_ORDER);
        return $match;
    }
    
    public static function footerjs(){
        echo '<script language="javascript">if (typeof(needGithubWidget) != "undefined") {
            var script = document.createElement("script");
            script.src = "//cdn.bootcss.com/github-repo-widget/e23d85ab8f/jquery.githubRepoWidget.min.js";
            document.body.appendChild(script);
        }</script>';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 样式表 */
        $giteecss = new Typecho_Widget_Helper_Form_Element_Textarea('gitee_css', NULL, '.pro_name a{color: #4183c4;}
.osc_git_title{background-color: #fff;}
.osc_git_box{background-color: #fff;}
.osc_git_box{border-color: #E3E9ED;}
.osc_git_info{color: #666;}
.osc_git_main a{color: #9B9B9B;}', _t('Gitee样式表'));
        $form->addInput($giteecss);
        $githubcss = new Typecho_Widget_Helper_Form_Element_Textarea('github_css', NULL, '', _t('Github样式表'));
        $form->addInput($githubcss);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}
