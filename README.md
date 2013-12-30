CakePHP-Rest-Plugin
=====================

For CakePHP 2.x, It is and add on usefull for REST services, it is composed by a XML/JSON error renderer and a ready-to-use requester.

Setup
-----

You need to clone the project into a "Plugin" directory in app/Plugin.
Then, add this "CakePlugin::load" in the app bootstrap. You need to activate the plugin bootstrap:

> CakePlugin::load('Rest', array('bootstrap' => true));

Add the conponent in requested resource controller
--------------------------------------------------

> public $components = array('Rest.Rest' => array('fields' => array('firstname', 'lastname')));

You configure the public fields in the conponent declaration for the requester.

Call the requester in a specific action
---------------------------------------

> public function index() {
>     $this->Rest->requester();
> }

Now you can call the action with conditions, order, page and limit parameters encoded in JSON format.
The result will a list of records encoded in JSON or in XML (depends on the extension).

For example you can have this parameters (they respect the CakePHP convention)
{"limit":"2", "page":"1", "order":"firstname DESC", "conditions":{"firstname !=": "John"}}