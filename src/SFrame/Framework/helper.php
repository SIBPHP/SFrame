<?php
/**
 * Functions of utiles
 */


/**
 * Dump variable
 * 
 * @param mixed $var
 * @param bool $is_dump dump or print
 */
function p($var, $is_dump = false)
{
    echo '<pre>';
    if ($is_dump) {
        var_dump($var);
    } else {
        print_r($var);
    }
    exit;
}


/**
 * POST or GET
 */
function getRequest($key, $default = null)
{
    return filter_has_var(INPUT_POST, $key) ? getPost($key, $default) : getGet($key, $default);
}

/**
 * GET
 */
function getGet($key, $default = null)
{
    return filter_has_var(INPUT_GET, $key) ? filter_input(INPUT_GET, $key) : $default;
}

/**
 * POST
 */
function getPost($key, $default = null)
{
    return filter_has_var(INPUT_POST, $key) ? filter_input(INPUT_POST, $key) : $default;
}


/**
 * PUT
 */
function getPut($key, $default = null)
{
    static $content = null;
    if (null === $content) {
        $content = file_get_contents('php://input');
    }
    $params = array();
    parse_str($content, $params);
    return isset($params[$key]) ? $params[$key] : $default;
}


/**
 * Get cookie
 */
function getCookie($key, $default = null)
{
    return filter_has_var(INPUT_COOKIE, $key) ? filter_input(INPUT_COOKIE, $key) : $default;
}


/**
 * Get session
 */
function getSession($key, $default = null)
{
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}


/**
 * Get arguments in cli mode
 */
function getArgv($index, $default = null)
{
    $argv = filter_input(INPUT_SERVER, 'argv');
    return isset($argv[$index]) ? $argv[$index] : $default;
}


/**
 * Get ip address
 */
function getIp()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {  
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = '';
    }
    if (true == ($p = strpos($ip, ','))) {
        $ip = substr($ip, 0, $p);
    }
    return $ip;
}


/**
 * JS alert and back or redirect
 */
function alert($message, $url = '')
{
    $redirect = $url ? 'location.href="'. $url .'";' : 'history.go(-1);';
    echo '<!DOCTYPE HTML>
        <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript">
        alert("' . $message . '");
        '. $redirect .'
        </script>
        </head><body>
        </body></html>
    ';
    exit;
}


/**
 * Echo json/jsonp
 * Default format: {status: '', message: '', ...}
 */
function outJson($status, $message = '', $params = array())
{
    $result = array('status' => $status, 'message' => $message);
    if (!empty($params)) {
        foreach ($params as $k=>$v) {
            $result[$k] = $v;
        }
    }
    $cb = getRequest('callback', '');
    if ($cb) {
        echo $cb .'('. json_encode($result) .');';
    } else {
        echo json_encode($result);
    }
    exit;
}


/**
 * Redirect
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}


/**
 * Returns a translated string if one is found in the translation table
 */
function __($msgid)
{
    return gettext($msgid);
}

/**
 * Returns a translated string if one is found in the translation table
 */
function _e($msgid)
{
    echo gettext($msgid);
}
