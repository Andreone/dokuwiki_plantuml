<?php
/**
 * PlantUML-Plugin: Add a toolbar button to insert a plantuml block
 *
 * @license	GPL v3 (http://www.gnu.org/licenses/gpl.html)
 * @author	Andreone
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_plantuml extends DokuWiki_Action_Plugin {

    /**
     * Register the event handler
     */
    function register(&$controller) {
      if($this->getConf('button_enabled') == '1')
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
    }
 
    /**
     * Inserts the toolbar button
     */
    function insert_button(& $event, $param) {
 		$event->data[] = array (
            'type' => 'format',
            'title' => htmlspecialchars($this->getLang('tooltip')),
            'icon' => '../../plugins/plantuml/'.$this->getConf('button_icon'),
            'open' => '<uml>',
            'close' => '</uml>',
            'sample' => '',
        );
    }
}