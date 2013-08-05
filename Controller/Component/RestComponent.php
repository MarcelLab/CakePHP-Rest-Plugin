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
    const MAX_LIMIT = 500;
    private static $_authorizedParameters = array('conditions', 'order', 'page', 'limit');
    private $_controller = null;
    private $_settings = null;
    private $_requestData = null;

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
        $this->_controller = $controller;
        $this->_requestData = $controller->request->input('json_decode', true);
        if(! isset($controller->request->params['prefix']) &&
              empty($controller->request->params['plugin']) &&
            ! preg_match('#^admin/#', $controller->request->url) ) {
            $ext = isset($controller->request->params['ext']) ? $controller->request->params['ext'] : self::DEFAULT_EXT;
            $controller->viewClass = ucfirst($ext);
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
     * setData set and format data for a service output
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
        $this->_controller->set($data);
    }
}
