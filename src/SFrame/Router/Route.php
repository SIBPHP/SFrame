<?php namespace SFrame\Router;

/**
 * The route
 */
class Route
{
    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'index';
    
    protected $_controller_path = '';
    protected $_controller_namespace = null;
    protected $_controller = '';
    protected $_action = '';
    protected $_group_index = null;
    protected $_groups = array();
    protected $_routes = array();

    public function __construct($controller_path, $namespace = null)
    {
        $this->_controller_path = $controller_path;
        $this->_controller_namespace = $namespace;
    }
    
    
    public function getHost()
    {
        return filter_input(INPUT_SERVER, 'HTTP_HOST');
    }
    
    public function getScript()
    {
        return filter_has_var(INPUT_SERVER, 'SCRIPT_NAME') ? filter_input(INPUT_SERVER, 'SCRIPT_NAME') : filter_input(INPUT_SERVER, 'PHP_SELF');
    }
    
    public function getBase()
    {
        return rtrim(dirname($this->getScript()), '\\/');
    }
    
    public function getUri()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_URI');
    }
    
    /**
     * Get the rewrite path in the url
     */
    public function getPath()
    {
        $uri = $this->getUri();
        $script = $this->getScript();
        $base = $this->getBase();
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        if ($script && strpos($uri, $script) === 0) {
            $path = substr($uri, strlen($script));
        } elseif ($base && strpos($uri, $base) === 0) {
            $path = substr($uri, strlen($base));
        } else {
            $path = $uri;
        }
        return preg_replace('/\/+/', '/', trim($path, '/'));
    }
    
    
    /**
     * Group
     */
    public function group(array $options, $routes)
    {
        $this->_groups[] = $options;
        $this->_group_index = count($this->_groups) - 1;
        $routes($this);
        $this->_group_index = null;
    }
    
    protected function _addRoute($method, $match, $action)
    {
        $this->_routes[] = array(
            'method' => $method,
            'match' => $match,
            'action' => $action,
            'group' => $this->_group_index
        );
    }
    
    public function get($match, $action)
    {
        $this->_addRoute('GET', $match, $action);
    }
    
    public function post($match, $action)
    {
        $this->_addRoute('POST', $match, $action);
    }
    
    public function put($match, $action)
    {
        $this->_addRoute('PUT', $match, $action);
    }
    
    public function delete($match, $action)
    {
        $this->_addRoute('DELETE', $match, $action);
    }
    
    
    /**
     * Dispatch
     */
    public function dispatch()
    {
        $path = $this->getPath();
        $method = filter_has_var(INPUT_SERVER, 'REQUEST_METHOD') ? filter_input(INPUT_SERVER, 'REQUEST_METHOD') : 'GET';
        $is_routed = 0;
        
        // Match the given route first
        if (!empty($this->_routes)) {
            foreach ($this->_routes as $route) {
                if ($method == $route['method'] && true == ($is_routed = $this->_routeMatch($method, $path, $route))) {
                    break;
                }
            }
        }
        
        // If not found, then execute the default route
        if (!$is_routed) {
            $path = explode('/', $path);
            
            // The controller
            // url: test-foo-bar  -> controller: TestFooBar
            $controller = self::DEFAULT_CONTROLLER;
            if (!empty($path[0])) {
                $cts = explode('-', strtolower($path[0]));
                $controller = '';
                foreach ($cts as $part) {
                    $controller .= ucfirst($part);
                }
            }
            
            // Action
            // url: act-foo-bar  ->  action:  actFooBar
            $action = self::DEFAULT_ACTION;
            if (!empty($path[1])) {
                $acts = explode('-', strtolower($path[1]));
                $action = '';
                foreach ($acts as $part) {
                    $action .= ucfirst($part);
                }
            }
            
            // execute
            $this->_run($method, $controller, $action);
        }
    }
    
    /**
     * Match specified route
     * @param string $path
     * @param array $route
     */
    protected function _routeMatch($method, $path, $route)
    {
        $match = $route['match'];
        
        // group
        if (null !== $route['group'] && !empty($this->_groups[$route['group']])) {
            $group = $this->_groups[$route['group']];
            if (!empty($group['prefix'])) {
                $match = $group['prefix'] .'/'. $match;
            }
        }
        
        // match
        $pattern = preg_replace('/\{([\w-]+)\}/i', '([\w-]+)', $match);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^'. $pattern .'(\/?\?.*)?$/i';
        
        if (preg_match($pattern, $path, $matches)) {
            // action
            $action_match = explode('@', $route['action']);
            $controller = $action_match[0];
            $action = empty($action_match[1]) ? self::DEFAULT_ACTION : $action_match[1];
            unset($matches[0]);
            $this->_run($method, $controller, $action, $matches);
            return true;
        }
        return false;
    }
    
    
    /**
     * Run
     */
    protected function _run($method, $controller, $action, $params = array())
    {
        $this->_controller = $controller;
        $this->_action = $action;
        
        // Execute controller->action
        $controller_file = $this->_controller_path . '/' . $controller . '.php';
        if (!is_file($controller_file)) {
            throw new NotFoundException('The Controller '. $controller .' is not exists');
        }
        require_once $controller_file;
        
        // controller class new instance
        if ($this->_controller_namespace) {
            $controller_class_name = $this->_controller_namespace .'\\'. $controller;
        } else {
            $controller_class_name = $controller;
        }
        if (!class_exists($controller_class_name)) {
            throw new NotFoundException('The Controller Class '. $controller_class_name .' is invalid');
        }
        $controller_class = new $controller_class_name($this);
        
        // action call
        $action_method = strtolower($method) . ucfirst($action);
        if (!method_exists($controller_class, $action_method)) {
            throw new NotFoundException('The Action '. $action .' is invalid');
        }
        if (empty($params)) {
            $controller_class->$action_method();
        } else {
            call_user_func_array(array($controller_class, $action_method), $params);
        }
    }
    
    
    public function getController()
    {
        return $this->_controller;
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }
}
