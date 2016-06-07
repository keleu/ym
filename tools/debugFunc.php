<?php
header("Content-type: text/html; charset=utf-8");
require_once(ROOT_DIR.'/tools/ChromePhp.php');
require_once(ROOT_DIR.'/tools/kint/Kint.class.php');
require_once(ROOT_DIR.'/tools/Geohash.php');
function consoleLog($vars) {
    //include_once "Lib/Tools/ChromePhp.php";
    ChromePhp::log($vars);
}

/**
 * 输出变量的内容，通常用于调试
 *
 * @package Core
 *
 * @param mixed $vars 要输出的变量
 * @param string $label
 * @param boolean $return
 */
function dump($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label != '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) { return $content; }
    echo $content;
    return null;
}

/**
 * @desc 格式化json输出给用户,将unicode转为中文
 * @param type $json_str
 * @return string
 * @author jeff  15-6-18 下午6:57
 */
function dumpJson($json_str) {
    consoleLog(json_decode($json_str,true));
    echo "the json is below,and you can see the decode-object in console panel:\r\n<br />".preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
        create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ),
        $json_str);
}

/**
 * @desc ：将调试内容输出到文件中，在异步调用时用到,比如调试矩阵时用
 * @author jeff 2015/09/16 11:18:55
 * @param 参数类型
 * @return 返回值类型
*/
function dump2file($content) {
    $content = "\r\n================".time()."=========\r\n".$content;
    file_put_contents('jeff_log.txt', $content,FILE_APPEND);
}

/**
 * @desc ：将调试内容输出到文件中，在异步调用时用到,比如调试矩阵时用
 * @author jeff 2015/09/16 11:18:55
 * @param 参数类型
 * @return 返回值类型
*/
function dump2fileMore($content) {
    $import_data = print_r($content,1);
    file_put_contents('../testlog/debugLog.txt', '---'.date('Y-m-d H:i:s').'---'.PHP_EOL,FILE_APPEND);
    file_put_contents('../testlog/debugLog.txt', $import_data.PHP_EOL,FILE_APPEND);

    // $content = "\r\n================".time()."=========\r\n".$content;
    // file_put_contents('jeff_log.txt', $content,FILE_APPEND);
}


/**
 * 显示应用程序执行路径，通常用于调试,不能显示详细代码
 *
 * @package return 是否返回,false表示直接echo
 *
 * @return string
 */
function dump_trace($return=false)
{
    $debug = debug_backtrace();
    $lines = '';
    $index = 0;
    for ($i = 0; $i < count($debug); $i++) {
        if ($i == 0) { continue; }
        $file = $debug[$i];
        if ($file['file'] == '') { continue; }
        if (substr($file['file'], 0, strlen(FLEA_DIR)) != FLEA_DIR) {
            $line = "#<strong>{$index} {$file['file']}({$file['line']}): </strong>";
        } else {
            $line = "#{$index} {$file['file']}({$file['line']}): ";
        }
        if (isset($file['class'])) {
            $line .= "{$file['class']}{$file['type']}";
        }
        $line .= "{$file['function']}(";
        if (isset($file['args']) && count($file['args'])) {
            foreach ($file['args'] as $arg) {
                $line .= gettype($arg) . ', ';
            }
            $line = substr($line, 0, -2);
        }
        $line .= ')';
        $lines .= $line . "\n";
        $index++;
    } // for
    $lines .= "#{$index} {main}\n";
    if($return) return $lines;
    if (ini_get('html_errors')) {
        echo nl2br(str_replace(' ', '&nbsp;', $lines));
    } else {
        echo $lines;
    }
}

/**
 * @desc ：输出堆栈
 * @author jeff 2015/09/16 11:18:55
 * @param 参数类型
 * @return 返回值类型
*/
function dumpFunc() {
   _echoHead();
   echo '<div class="track">';
   __error_dump_trace();
   echo '</div>';
}

/**
 * 显示异常信息及调用堆栈,可以显示详细代码和传参
 *
 * @param
 */
function __error_dump_trace()
{

    $trace = debug_backtrace();
    $ix = count($trace)-1;
    foreach ($trace as $_k=>$point) {
        if($_k<1) continue;
        $file = isset($point['file']) ? $point['file'] : null;
        $line = isset($point['line']) ? $point['line'] : null;
        $id = md5("{$file}({$line})");
        $function = isset($point['class']) ? "{$point['class']}::{$point['function']}" : $point['function'];

        $args = array();

        if (is_array($point['args']) && count($point['args']) > 0) {
            foreach ($point['args'] as $arg) {
                switch (gettype($arg)) {
                case 'array':
                    $args[] = 'array(' . count($arg) . ')';
                    break;
                case 'resource':
                    $args[] = gettype($arg);
                    break;
                case 'object':
                    $args[] = get_class($arg);
                    break;
                case 'string':
                    if (strlen($arg) > 30) {
                        $arg = substr($arg, 0, 27) . ' ...';
                    }
                    $args[] = "'{$arg}'";
                    break;
                default:
                    $args[] = $arg;
                }
            }
        }

        $args = implode(", ", $args);
        echo <<<EOT
<hr />
<strong>Filename:</strong> <a href="javascript:switch_filedesc('{$id}');">{$file} [{$line}]</a><br />
#{$ix} {$function}($args)
<div id="{$id}" class="filedesc" style="display: none;">
ARGS:
EOT;
        dump($point['args']);

        echo "SOURCE CODE: <br />\n";
        echo __error_show_source($file, $line);
        echo "\n</div>\n";
        echo "<br />\n";
        $ix--;
    }

}

/**
 * 显示文件源代码
 *
 * @param string $file
 * @param int $line
 * @param int $prev
 * @param int $next
 *
 * @return string
 */
function __error_show_source($file, $line, $prev = 10, $next = 10)
{
    if (!(file_exists($file) && is_file($file))) {
        return '';
    }

    $data = file($file);
    $count = count($data) - 1;

    //count which lines to display
    $start = $line - $prev;
    if ($start < 1) {
        $start = 1;
    }
    $end = $line + $next;
    if ($end > $count) {
        $end = $count + 1;
    }

    //displaying
    $out = '<table cellspacing="0" cellpadding="0">';

    for ($x = $start; $x <= $end; $x++) {
        $out .= "  <tr>\n";
        if ($line != $x) {
            $out .= "    <td class=\"line-num\">";
        } else {
            $out .= "    <td class=\"line-num-break\">";
        }
        $out .= str_repeat('&nbsp;', (strlen($end) - strlen($x)) + 1);
        $out .= $x;
        $out .= "&nbsp;</td>\n";

        $out .= "    <td class=\"source\">&nbsp;";
        $out .= nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($data[$x - 1])));
        $out .= "</td>\n  </tr>\n";
    }
    $out .= "</table>\n";
    return $out;
}

/**
 * 显示指定文件的连接
 *
 * @param string $filename
 */
function __error_filelink($filename)
{
    $path = realpath($filename);
    if ($path) {
        echo $path;
    } else {
        echo $filename;
    }
}

/**
 * 输出按照模版要求格式化以后的代码
 *
 * @param string $code
 */
function __error_highlight_string($code)
{
    $text = str_replace(array("\r", "\n"), array('', '\n'), addslashes($code));
    $code = str_replace("<br />", "\n", highlight_string($code, true));

    echo <<<EOT
<pre>
[<a href="#" onclick="copytextToClipboard('{$text}'); return false;">Copy To Clipboard</a>]

{$code}
</pre>
EOT;
}

function _echoHead() {
    $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        h1 {
            font-size: 24px;
            font-weight: bold;
            color: #6699cc;
        }
        h2 {
            font-size: 14px;
            font-weight: bold;
            margin: 0px;
            padding: 0px;
            margin-bottom: 8px;
        };
        code, pre {
            color:#4444AA;
            font-size: 12px;
        }
        pre {
            margin-left: 12px;
            border-left: 1px solid #CCCCCC;
            padding-left: 20px;
        }
        a {
            color: #3366CC;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .tip {
            background: #eeffee;
            padding: 10px;
            border: 1px solid #ccddcc;
        }
        .tip h2 {
            color: #006600;
        }
        .error {
            background: #ffeeee;
            padding: 10px;
            border: 1px solid #ddcccc;
        }
        .error h2 {
            color: #FF3300;
        }
        .track {
            font-family:Verdana, Arial, Helvetica, sans-serif;
            font-size: 12px;
            background-color: #FFFFCC;
            padding: 10px;
            border: 1px solid #FF9900;
        }
        .filedesc {
            margin-left: 16px;
            color: #666666;
        }
        .line-num {
            font-size: 12px;
            vertical-align: top;
        }
        .line-num-break {
            font-size: 12px;
            font-weight: bold;
            color: white;
            background-color: red;
            vertical-align: top;
        }
        .source {
            font-size: 12px;
            vertical-align: top;
        }
        </style>
        <script language="javascript" type="text/javascript">
        function switch_filedesc(id)
        {
            var el = document.getElementById(id);
            if (el.style.display == "none") {
                el.style.display = "block";
            } else {
                el.style.display = "none";
            }
        }
        function copytextToClipboard(txt)
        {
            if (window.clipboardData) {
                window.clipboardData.clearData();
                window.clipboardData.setData("Text", txt);
            }
        }
        </script>';
    echo $html;
}

// test function 后期将必要的方法作为php的拓展包安装或者改写为地理定位支持工具
if( !function_exists('geohash_encode') )
{
    function geohash_encode($lng, $lat)
    {
        return Geohash::encode($lng, $lat);
    }
}

if( !function_exists('geohash_decode') )
{

    function geohash_decode($hash)
    {
        return Geohash::decode($hash);
    }
}
if( !function_exists('geohash_neighbors') )
{

    function geohash_neighbors($hash)
    {
        return Geohash::geohash_neighbors($hash);
    }
}
if( !function_exists('get_distance') )
{
    function get_distance($lat1, $lng1, $lat2, $lng2)
    {
        return Distance::get_distance( $lat1, $lng1, $lat2, $lng2);
    }
}

