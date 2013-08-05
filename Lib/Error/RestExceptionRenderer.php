<?php
App::uses('ExceptionRenderer', 'Error');

/**
 * RestExceptionRenderer
 * Error Renderer used to display errors in XML or JSON format,
 * it works with Router::mapResources()
 *
 * @uses ExceptionRenderer
 * @package Plugin.Rest
 * @version 1.1
 * @copyright Copyright (C) 2013 Marcel Publicis All rights reserved.
 * @author Vivien Ripoche <vivien.ripoche@marcelww.com>
 */
class RestExceptionRenderer extends ExceptionRenderer
{
    const DEFAULT_EXT = 'xml';

    /**
     * render method is called to display all CakeError errors
     * 
     * @return NULL
     */
    public function render()
    {
        if( isset($this->controller->request->params['prefix']) || 
            ! empty($this->controller->request->params['plugin']) ||
            preg_match('#^admin/#', $this->controller->request->url) ) {
                parent::render();
        } else {
            $this->controller->set(array(
                'error' => array('message' => $this->error->getMessage(), 'code' => $this->error->getCode()),
                '_serialize' => array('error')
            ));
            $this->controller->viewClass = ucfirst(isset($this->controller->request->params['ext']) ? $this->controller->request->params['ext'] : self::DEFAULT_EXT);

			      $this->controller->response->header('Content-type: ' . $this->controller->render()->type());
            $this->controller->response->send();
        }
    }

    /**
     * Get the controller instance to handle the exception.
     * Override this method in subclasses to customize the controller used.
     * This method returns the built in `CakeErrorController` normally, or if an error is repeated
     * a bare controller will be used.
     *
     * @param Exception $exception The exception to get a controller for.
     * @return Controller
     */
    protected function _getController($exception) {
          App::uses('AppController', 'Controller');
          App::uses('CakeErrorController', 'Controller');
          if (!$request = Router::getRequest(true)) {
              $request = new CakeRequest();
          }
          $response = new CakeResponse();

          if (method_exists($exception, 'responseHeader')) {
              $response->header($exception->responseHeader());
          }

          try {
              $controller = new CakeErrorController($request, $response);
          } catch (Exception $e) {
              if (!empty($controller) && $controller->Components->enabled('RequestHandler')) {
              $controller->RequestHandler->startup($controller);
              }
          }
          if (empty($controller)) {
              $controller = new Controller($request, $response);
              $controller->viewPath = 'Errors';
          }
          return $controller;
    }
}
