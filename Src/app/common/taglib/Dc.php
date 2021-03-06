<?php
namespace app\common\taglib;
use think\template\TagLib;
use think\facade\Config;
use think\facade\Db;
use app\index\model\Article as ArticleModel;
use app\index\model\Category as CategoryModel;

class Dc extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'article'     => ['attr' => 'name,fields,catid,islink,isreco,ishot,istop,iscomment,limit,orderby,iscache,isthumb', 'close' => 1], //文章
        'category'    => ['attr' => 'name,fields,type,modelid,parentid,child,ismenu,limit,orderby,iscache', 'close' => 1],//分类
        'get'      => ['attr' => 'name,fields,datatable,where,limit,orderby,iscache', 'close' => 1],//万能标签
        'block'      => ['attr' => 'marker,iscache,is_strip,allow_html,is_decode', 'close' => 0],//碎片标签
        'showpage'      => ['attr' => 'total,limit,show_style,css_style', 'close' => 0],//分页标签
    ];

    /**
     * 获取文章列表
     */
    public function tagArticle($tag, $content)
    {
        $tag['limit'] = empty($tag['limit']) ? "0,5" : attr_to_variable($tag['limit']); //显示多少个,默认5
        $tag['catid'] = empty($tag['catid']) ? 0 : attr_to_variable($tag['catid']); // catid是分类ID，多个中间用,分隔
        $tag['islink'] = empty($tag['islink']) ? -1 : attr_to_variable($tag['islink']); //是否外部链接
        $tag['isreco'] = empty($tag['isreco']) ? -1 : attr_to_variable($tag['isreco']); //是否推荐
        $tag['ishot'] = empty($tag['ishot']) ? -1 : attr_to_variable($tag['ishot']); //是否热点
        $tag['istop'] = empty($tag['istop']) ? -1 : attr_to_variable($tag['istop']); //是否置顶
        $tag['status'] = empty($tag['status']) ? 9 : attr_to_variable($tag['status']); //文章状态：0无效，1正在审核，2退稿，9通过
        $tag['iscomment'] = empty($tag['iscomment']) ? -1 : attr_to_variable($tag['iscomment']); //是否允许评论
        $tag['orderby'] = empty($tag['orderby']) ? "inputtime desc,id desc" : attr_to_variable($tag['orderby']); //排序，格式：id desc,inputtime asc
        $tag['fields'] = empty($tag['fields']) ? "" : attr_to_variable($tag['fields']); //查询的字段，格式：id,title
        $tag['iscache'] = empty($tag['iscache']) ? 1 : attr_to_variable($tag['iscache']); //是否启用缓存，1=缓存
        $tag['isthumb'] = empty($tag['isthumb']) ? 0 : attr_to_variable($tag['isthumb']); //是否带缩略图，1=带缩略图
        //$name = $tag['name']; // name是必填项，这里不做判断了

        $parse = '<?php ';
        $parse .= '$__LIST__ = template_get_article(\''.json_encode($tag).'\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['name'] . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 获取分类列表
     */
    public function tagCategory($tag, $content)
    {
        $tag['limit'] = empty($tag['limit']) ? "0,5" : attr_to_variable($tag['limit']); //显示多少个,默认5
        $tag['parentid'] = empty($tag['parentid']) ? 0 : attr_to_variable($tag['parentid']); // parentid是父分类ID，多个中间用,分隔
        $tag['type'] = empty($tag['type']) ? -1 : attr_to_variable($tag['type']); //分类类型，0=内部栏目，1=单网页，2=外部链接
        $tag['modelid'] = empty($tag['modelid']) ? -1 : attr_to_variable($tag['modelid']); //模型ID，0=系统，1=文章，2=下载，3=图片，可自定义
        $tag['child'] = empty($tag['child']) ? -1 : attr_to_variable($tag['child']); //是否存在子栏目，1=存在
        $tag['ismenu'] = empty($tag['ismenu']) ? -1 : attr_to_variable($tag['ismenu']); //是否菜单显示，1 显示
        $tag['orderby'] = empty($tag['orderby']) ? "listorder asc,catid asc" : attr_to_variable($tag['orderby']); //排序，格式：listorder asc,catid asc
        $tag['fields'] = empty($tag['fields']) ? "" : attr_to_variable($tag['fields']); //查询的字段，格式：catid,catname
        $tag['iscache'] = empty($tag['iscache']) ? 1 : attr_to_variable($tag['iscache']); //是否启用缓存，1=缓存
        //$name = $tag['name']; // name是必填项，这里不做判断了

        $parse = '<?php ';
        $parse .= '$__LIST__ = template_get_category(\''.json_encode($tag).'\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['name'] . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }


    /**
     * 获取任意数据表数据，万能标签
     */
    public function tagGet($tag, $content)
    {
        $tag['limit'] = empty($tag['limit']) ? "0,5" : attr_to_variable($tag['limit']); //显示多少个,默认5
        $tag['orderby'] = empty($tag['orderby']) ? "" : attr_to_variable($tag['orderby']); //排序，格式：listorder asc,catid asc
        $tag['where'] = empty($tag['where']) ? "" : attr_to_variable($tag['where']); //排序，格式：listorder asc,catid asc
        //$tag['where_var'] = empty($tag['where_var']) ? "" : attr_to_variable($tag['where_var']); //where传入参数，json格式，如：{\"id\":56,\"title\":\"caozha\"}
        $tag['datatable'] = empty($tag['datatable']) ? "" : attr_to_variable($tag['datatable']); //查询的数据表
        $tag['fields'] = empty($tag['fields']) ? "" : attr_to_variable($tag['fields']); //查询的字段
        $tag['iscache'] = empty($tag['iscache']) ? 1 : attr_to_variable($tag['iscache']); //是否启用缓存，1=缓存
        //$name = $tag['name']; // name是必填项，这里不做判断了

        $parse = '<?php ';
        $parse .= '$__LIST__ = template_get(\''.json_encode($tag).'\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['name'] . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }


    /**
     * 获取碎片数据
     */
    public function tagBlock($tag, $content)
    {
        $tag['marker'] = empty($tag['marker']) ? "" : attr_to_variable($tag['marker']); //查询的标识符
        $tag['iscache'] = empty($tag['iscache']) ? 1 : attr_to_variable($tag['iscache']); //是否启用缓存，1=缓存
        $tag['is_strip'] = empty($tag['is_strip']) ? 0 : attr_to_variable($tag['is_strip']); //是否过滤HTML标记，1=过滤
        $tag['allow_html'] = empty($tag['allow_html']) ? "" : attr_to_variable($tag['allow_html']); //当过滤HTML标记时，是否保留HTML标记，为空则全部过滤，不为空请填写具体HTML标记，如<br>
        $tag['is_decode'] = empty($tag['is_decode']) ? 0 : attr_to_variable($tag['is_decode']); //HTML实体是否转换为字符，1=转换

        $parse = '<?php ';
        $parse .= '$__BLOCK_DATA__ = template_getBlock(\''.json_encode($tag).'\');';
        $parse .= ' ?>';
        $parse .= '{$__BLOCK_DATA__|raw}';
        return $parse;
    }

    /**
     * 获取分页数据
     */
    public function tagShowpage($tag, $content)
    {
        $tag['total'] = empty($tag['total']) ? 0 : attr_to_variable($tag['total']); //数据总数
        $tag['limit'] = empty($tag['limit']) ? 10 : attr_to_variable($tag['limit']); //每页数量
        $tag['show_style'] = empty($tag['show_style']) ? 1 : attr_to_variable($tag['show_style']); //分页显示样式
        $tag['css_style'] = empty($tag['css_style']) ? 1 : attr_to_variable($tag['css_style']); //CSS显示样式

        $parse = '<?php ';
        $parse .= '$__Showpage_DATA__ = template_getShowpage(\''.json_encode($tag).'\');';
        $parse .= ' ?>';
        $parse .= '{$__Showpage_DATA__|raw}';
        return $parse;
    }

}