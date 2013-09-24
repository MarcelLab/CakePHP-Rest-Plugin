<?php
/**
 * RestComponent
 * The component put XML format by default and disable it for admin pages.
 * It offers also a requester to get data with parameters.
 *
 * @uses Component, RequestHandler
 * @package Plugin.Rest
 * @version 1.1
 * @copyright Copyright (C) 2013 Marcel Publicis All rights reserved.
 * @author Vivien Ripoche <vivien.ripoche@marcelww.com>
 */
class RestComponent extends Component {

    const DEFAULT_EXT = 'xml';
    const DEFAULT_LIMIT = 50;
    const DEFAULT_JSONP_CALLBACK = 'callback';
    const MAX_LIMIT = 500;
    private static $_authorizedParameters = array('conditions', 'order', 'page', 'limit', 'joins');
    private $_controller = null;
    private $_settings = null;
    private $_requestData = null;
    private $_authorizedExt = array('xml', 'json', 'jsonp');

    public $components = array('RequestHandler');

    /**
     * __construct 
     * 
     * @param mixed $collection 
     * @param mixed $settings 
     * @return NULL
     */
    public function __construct($collection, $settings) {
        $this->_settings = $settings;
    }
    /**
     * startup is a Component callback which is dispatched just after the 
     * beforeFilter in the Controller
     * 
     * @param Object $controller
     * @return NULL
     */
    public function startup($controller) {
        $ext = isset($controller->request->params['ext']) ? $controller->request->params['ext'] : self::DEFAULT_EXT;
        if(!in_array($ext, $this->_authorizedExt)) throw new CakeException (sprintf('The extension "%s" is not supported', $ext));
        $this->_controller = $controller;
        $this->_requestData = $this->getRequestData();
        if(! isset($controller->request->params['prefix']) &&
              empty($controller->request->params['plugin']) &&
            ! preg_match('#^admin/#', $controller->request->url) ) {
            if(!$this->isJSONP()) {
                $controller->viewClass = ucfirst($ext);
            }
        }
    }

    /**
     * requester can get resource data with further parameters as:
     *  - conditions
     *  - order
     *  - page
     *  - limit
     * 
     * @return NULL
     */
    public function requester() {
        $parameters = array('recursive' => -1);
        foreach(self::$_authorizedParameters as $parameterName) {
            if(isset($this->_requestData[$parameterName])) $parameters[$parameterName] = $this->_requestData[$parameterName];
        }
        $parameters['fields'] = isset($this->_settings['fields']) ? $this->_settings['fields'] : array();
        if(!isset($parameters['limit'])) {
            $parameters['limit'] = self::DEFAULT_LIMIT;
        } else if($parameters['limit'] > self::MAX_LIMIT) {
            $parameters['limit'] = self::MAX_LIMIT;
        }
        $result = $this->_controller->{$this->_controller->modelClass}->find('all', $parameters);
        $this->setData($result);
    }

    /**
     * setData sets and formats data for a service output
     * 
     * @param array $data 
     * @param int $count 
     * @return NULL
     */
    public function setData($data, $count = null) {
        $data = array(
            'result'     => $data,
            'service'    => $this->_controller->params['action'],
            '_serialize' => array('result', 'service')
        );
        if($count) {
            $data['count'] = $count;
            $data['_serialize'][] = 'count';
        }
        if($this->isJSONP()) {
            $this->displayJSONP($data);
        } else $this->_controller->set($data);
    }

    /**
     * getRequestData gets data from php://input or from data GET paramater in 
     * the JSONP case
     * 
     * @return array
     */
    public function getRequestData() {
        $requestData = $this->_controller->request->input('json_decode', true);
        if($this->isJSONP() && isset($this->_controller->request->query['data'])) {
            $requestData = json_decode(urldecode($this->_controller->request->query['data']), true);
        }
        return $requestData;
    }

    /**
     * isJSONP checks if the extension is "jsonp"
     * 
     * @return NULL
     */
    public function isJSONP() {
        $ext = isset($this->_controller->request->params['ext']) ? $this->_controller->request->params['ext'] : self::DEFAULT_EXT;
        return $ext == 'jsonp';
    }

    /**
     * displayJSONP echoes then data wrapped in a JS function call
     * 
     * @param mixed $data 
     * @return NULL
     */
    public function displayJSONP($data) {
        unset($data['_serialize']);
        $callback = isset($this->_controller->request->query['callback']) ? $this->_controller->request->query['callback'] : self::DEFAULT_JSONP_CALLBACK;
        echo sprintf('%s(%s);', $callback, json_encode($data));
        $this->_controller->autoRender = false;
    }
}
