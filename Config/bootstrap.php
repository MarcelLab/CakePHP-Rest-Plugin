<?php
/**
 * Rest plugin is used to extend REST capabilities of CakePHP:
 * - Custom XML/JSON error handling
 * - Default XML rendering and disabling for prefix parts (ie admin)
 *
 * @package     Plugin.Rest
 * @version     1.1
 * @copyright   Copyright (C) 2013 Marcel Publicis All rights reserved.
 * @author      Vivien Ripoche <vivien.ripoche@marcelww.com>
 */
Configure::write('Exception.renderer', 'Rest.RestExceptionRenderer');
Router::parseExtensions();