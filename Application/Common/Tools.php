<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 11:10 AM
 */

namespace Common;

class Tools
{
    /**
     * 系统加密方法
     * @param string $data 要加密的字符串
     * @param string $key  加密密钥
     * @param int $expire  过期时间 单位 秒
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function think_encrypt($data, $key = '', $expire = 0) {
        $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
        $data = base64_encode($data);
        $x    = 0;
        $len  = strlen($data);
        $l    = strlen($key);
        $char = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        $str = sprintf('%010d', $expire ? $expire + time():0);

        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
        }
        return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
    }

    /**
     * 系统解密方法
     * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
     * @param  string $key  加密密钥
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function think_decrypt($data, $key = ''){
        $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
        $data   = str_replace(array('-','_'),array('+','/'),$data);
        $mod4   = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data   = base64_decode($data);
        $expire = substr($data,0,10);
        $data   = substr($data,10);

        if($expire > 0 && $expire < time()) {
            return '';
        }
        $x      = 0;
        $len    = strlen($data);
        $l      = strlen($key);
        $char   = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }else{
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }

    /**
     * 数据签名认证
     * @param  array  $data 被认证的数据
     * @return string       签名
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function data_auth_sign($data) {
        //数据类型检测
        if(!is_array($data)){
            $data = (array)$data;
        }
        ksort($data); //排序
        $code = http_build_query($data); //url编码并生成query字符串
        $sign = sha1($code); //生成签名
        return $sign;
    }

    /**
     * 格式化字节大小
     * @param  number $size      字节数
     * @param  string $delimiter 数字和单位分隔符
     * @return string            格式化后的带单位的大小
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function format_bytes($size, $delimiter = '') {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

    /**
     * 设置跳转页面URL
     * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function set_redirect_url($url){
        cookie('redirect_url', $url);
    }

    /**
     * 获取跳转页面URL
     * @return string 跳转页URL
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function get_redirect_url(){
        $url = cookie('redirect_url');
        return empty($url) ? __APP__ : $url;
    }

    /**
     * 对查询结果集进行排序
     * @access public
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sortby 排序类型
     * asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    public static function list_sort_by($list,$field, $sortby='asc') {
        if(is_array($list)){
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ( $refer as $key=> $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function list2tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
        // 创建Tree
        $tree = [];
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 将list_to_tree的树还原成列表
     * @param  array $tree  原来的树
     * @param  string $child 孩子节点的键
     * @param  string $order 排序显示的键，一般是主键 升序排列
     * @param  array  $list  过渡用的中间数组，
     * @return array        返回排过序的列表数组
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public static function tree2list($tree, $child = '_child', $order='id', &$list = []){
        if(is_array($tree)) {
            $refer = [];
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if(isset($reffer[$child])){
                    unset($reffer[$child]);
                    tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = list_sort_by($list, $order, $sortby='asc');
        }
        return $list;
    }

    /**
     * 获取节点所有父级元素
     * @param array $list 数据
     * @param int $node_id 节点id
     * @param int $pk 主键
     * @param int $pid 外键
     * @param int $root 根节点
     * @return array 父节点
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function get_list_parents($list,$node_id,$pk='id',$pid='pid',$root=0)
    {
        $i = $root;
        while($node_id!=$root){
            foreach ($list as $k => $v){
                if ($v[$pk]==$node_id){
                    $node_to_root[$i++] = $k;
                    $node_id = $v[$pid];
                }
            }
        }
        $i = $i-1;
        for($i;$i>=$root;$i--){
            $root_to_node[] = $node_to_root[$i];
            $parent_list[] = $list[$node_to_root[$i]];
        }
        return $parent_list;
    }

    /**
     * 获取有子元素的列
     */
    public static function get_p_list($tree,$child = '_child',$order='id',&$list=[]){
        foreach($tree as $k => $v){
            if($v[$child]){
                unset($v[$child]);
                $list[] = $v;
                self::get_p_list($tree[$k][$child],$child,$order,$list);
            }
        }
        return $list;
    }

    /**
     * 获取节点排序
     * @param array $tree 数据
     * @param int $node_id 节点id
     * @param int $start 起始值
     * @param string $sort_name 排序字段下标
     * @param array $p
     * @return array 带节点排序的数据
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function get_tree_node_sort($tree,$start=0,$sort_name='_node_sort',&$p=[]){
        $start++;
        foreach ($tree as $k => $v) {
            $p[$k] = $v;
            $p[$k][$sort_name] = $start-1;
            if ($v['_child']) {
                self::get_tree_node_sort($v['_child'],$start,$sort_name,$p[$k]['_child']);
            }
        }
        return $p;
    }

    /**
     * 获取list数组单个字段值
     * @param array $list 数组
     * @param int $key 索引键
     * @param int $search 索引值
     * @param int $field 查询键
     * @return int|string 查询值
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function get_list_field($list,$key,$search,$field){
        foreach ($list as $k => $v){
            if($v[$key]==$search){
                return $v[$field];
            }
        }
    }

    /**
     * 字符串转换为数组，主要用于把分隔符调整到第二个参数
     * @param  string $str  要分割的字符串
     * @param  string $glue 分割符
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function str2arr($str, $glue = ','){
        return explode($glue, $str);
    }

    /**
     * 数组转换为字符串，主要用于把分隔符调整到第二个参数
     * @param  array  $arr  要连接的数组
     * @param  string $glue 分割符
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function arr2str($arr, $glue = ','){
        return implode($glue, $arr);
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    public static function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }

    /**
     * 生成目录
     * @access public
     * @param string $path 目录位置与名称
     * @return void
     * @author wodrow <451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function createDir($path)
    {
        return is_dir($path) or (createDir(dirname($path)) and mkdir($path, 0777));
    }

    /**
     * 图片裁剪处理函数
     * @param  string $img_url  图片位置 must
     * @param  string $save_path 处理完毕的保存路径 must
     * @param  string $save_name 处理完毕的保存名称 must
     * @param  array $crop 裁剪参数
     * @param  array $thumb 缩略参数
     * @return array
     * @author wodrow <451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function cutImage($img_url,$save_path,$save_name,$crop=['x1'=>0,'y1'=>0,'w'=>200,'h'=>200],$thumb=['w'=>200,'h'=>200])
    {
        $image = new \Think\Image(\Think\Image::IMAGE_GD,$img_url); // GD库
        createDir($save_path);
        $save_url = $save_path.$save_name;
        $image->crop($crop['w'],$crop['h'],$crop['x1'],$crop['y1'])->thumb($thumb['w'],$thumb['h'])->save($save_url);
        return $save_url;
    }

    /**
     * 获取当前页面完整URL地址
     */
    public static function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    /**
     * gbk转utf8
     * @param array $arr 要编码的数组(一维数组)
     * @return array 编码后的数组
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function gbk2utf8($arr,$dimension=1){
        if($dimension == 1){
            $keys = array_keys($arr);
            foreach($keys as $k => $v){
//                $x[$v] = mb_convert_encoding($arr[$v],'utf-8','gbk');
                $x[$v] = iconv ("GBK", "UTF-8", $arr[$v]);
            }
            return $x;
        }else{
            $dimension--;
            foreach($arr as $k => $v){
                $y[$k] = self::gbk2utf8($v,$dimension);
            }
            return $y;
        }
    }

    /**
     * utf8转gbk
     * @param array $arr 要编码的数组(一维数组)
     * @return array 编码后的数组
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function utf82gbk($arr,$dimension=1){
        if($dimension == 1){
            $keys = array_keys($arr);
            foreach($keys as $k => $v){
//                $x[$v] = mb_convert_encoding($arr[$v],'gbk','utf-8');
                $x[$v] = iconv ("UTF-8", "GBK", $arr[$v]);
            }
            return $x;
        }else{
            $dimension--;
            foreach($arr as $k => $v){
                $y[$k] = self::utf82gbk($v,$dimension);
            }
            return $y;
        }
    }

    /**
     * 格式化手机号
     * @param string $mobile 手机号
     * @return string 加密手机号
     */
    public static function substr_mobile($mobile){
        return substr($mobile,0,3)."********";
    }

    /**
     * 获取后缀
     */
    public static function get_extend($file_name)
    {
        $extend  = explode ("."  ,  $file_name);
        $va = count ($extend) - 1;
        return   $extend[$va];
    }

    /**
     * 调试输出|调试模式下
     * @param  mixed $test 调试变量
     * @param  int $style 模式
     * @param  int $stop 是否停止
     * @return void       浏览器输出
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function _vp($test,$stop=0,$style=0)
    {
        switch ($style) {
            case 0:
                echo "<pre>";
                var_dump($test);
                echo "</pre>";
                break;

            case 1:
                echo "<pre>";
                var_dump($test);
                echo "<hr/>";
                for($i=0;$i<100;$i++){
                    echo $i."<hr/>";
                }
                echo "</pre>";
                break;

            case 2:
                $x = "##".date('Y-m-d H:i:s',time())."\r\r```\r".var_export($test, true)."\r```\r";
                file_put_contents(C("DOCUMENT_ROOT").RUNTIME_PATH.'/VPFILE.md',$x);
                break;

            case 3:
                $x = "##".date('Y-m-d H:i:s',time())."\r\r```\r".var_export($test, true)."\r```\r";
                file_put_contents(C("DOCUMENT_ROOT").RUNTIME_PATH.'/VPFILE.md',$x,FILE_APPEND);
                break;
        }
        if ($stop!=0) {
            exit("<hr/>");
        }
    }

    // 获取文件夹大小
    public static function getDirSize($dir)
    {
        $handle = opendir($dir);
        $sizeResult = 0;
        while (false!==($FolderOrFile = readdir($handle)))
        {
            if($FolderOrFile != "." && $FolderOrFile != "..")
            {
                if(is_dir("$dir/$FolderOrFile"))
                {
                    $sizeResult += getDirSize("$dir/$FolderOrFile");
                }
                else
                {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }
        closedir($handle);
        return $sizeResult;
    }

    // 单位自动转换函数
    public static function getRealSize($size)
    {
        $kb = 1024;         // Kilobyte
        $mb = 1024 * $kb;   // Megabyte
        $gb = 1024 * $mb;   // Gigabyte
        $tb = 1024 * $gb;   // Terabyte

        if($size < $kb)
        {
            return $size." B";
        }
        else if($size < $mb)
        {
            return round($size/$kb,2)." KB";
        }
        else if($size < $gb)
        {
            return round($size/$mb,2)." MB";
        }
        else if($size < $tb)
        {
            return round($size/$gb,2)." GB";
        }
        else
        {
            return round($size/$tb,2)." TB";
        }
    }

    /**
     * @path 路径，支持相对和绝对
     * @absolute 返回的文件数组，是否包含完整路径
     */
    public static function get_files($path, $absolute=1) {
        $files = array();
        $_path = realpath($path);
        if (!file_exists($_path)) return false;
        if (is_dir($_path)) {
            $list = scandir($_path);
            foreach ($list as $v) {
                if ($v == '.' || $v == '..') continue;
                $_paths = $_path.'/'.$v;
                if (is_dir($_paths)) {
                    //递归
                    $files = array_merge($files, get_files($_paths,$absolute));
                } else {
                    $files[] = $absolute>0 ? $_paths : $v;
                }
            }
        } else {
            if (!is_file($_path)) return false;
            $files[] = $_path;
        }
        return $files;
    }

    /**
     * 对2维数组或者多维数组排序
     * @param $arrays
     * @param $sort_key
     * @param int $sort_order
     *    SORT_ASC - 默认，按升序排列。(A-Z)
     *    SORT_DESC - 按降序排列。(Z-A)
     * @param int $sort_type
     *    SORT_REGULAR - 默认。将每一项按常规顺序排列。
     *    SORT_NUMERIC - 将每一项按数字顺序排列。
     *    SORT_STRING - 将每一项按字母顺序排列
     * @return array|bool
     */
    public static function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
        if(is_array($arrays)){
            foreach ($arrays as $array){
                if(is_array($array)){
                    $key_arrays[] = $array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
        return $arrays;
    }

    /**
     * 根据指定的键对数组排序
     *
     * @endcode
     *
     * @param array $array 要排序的数组
     * @param string $keyname 排序的键
     * @param int $dir 排序方向
     *
     * @return array 排序后的数组
     */
    public static function sortByCol($array, $keyname, $dir = SORT_ASC)
    {
        return self::sortByMultiCols($array, array($keyname => $dir));
    }

    /**
     * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
     *
     * 用法：
     * @code php
     * $rows = ArrayHelper::sortByMultiCols($rows, array(
     *     'parent' => SORT_ASC,
     *     'name' => SORT_DESC,
     * ));
     * @endcode
     *
     * @param array $rowset 要排序的数组
     * @param array $args 排序的键
     *
     * @return array 排序后的数组
     */
    public static function sortByMultiCols($rowset, $args)
    {
        $sortArray = array();
        $sortRule = '';
        foreach ($args as $sortField => $sortDir) {
            foreach ($rowset as $offset => $row) {
                $sortArray[$sortField][$offset] = $row[$sortField];
            }
            $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
        }
        if (empty($sortArray) || empty($sortRule)) {
            return $rowset;
        }
        eval('array_multisort(' . $sortRule . '$rowset);');
        return $rowset;
    }

    /**
    +-----------------------------------------------------------------------------------------
     * 删除目录及目录下所有文件或删除指定文件
    +-----------------------------------------------------------------------------------------
     * @param str $path   待删除目录路径
     * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
    +-----------------------------------------------------------------------------------------
     * @return bool 返回删除状态
    +-----------------------------------------------------------------------------------------
     */
    public static function delDirAndFile($path, $delDir = FALSE) {
        if (is_array($path)) {
            foreach ($path as $subPath)
                delDirAndFile($subPath, $delDir);
        }
        if (is_dir($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (false !== ( $item = readdir($handle) )) {
                    if ($item != "." && $item != "..")
                        is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
                }
                closedir($handle);
                if ($delDir)
                    return rmdir($path);
            }
        } else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
        clearstatcache();
    }
}