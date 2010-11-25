<?php
/**
 * PlantUML-Plugin: Add a toolbar button to insert a PlantUML block
 *
 * @license GPL v2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Andreone
 * @author  Willi Schönborn <w.schoenborn@googlemail.com>
 * @version 0.3
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');

class action_plugin_plantuml extends DokuWiki_Action_Plugin {
 
    /**
     * Register the event handler
     */
    function register(&$controller) {
        if ($this->getConf('button_enabled') == '1') {
            $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array());
        }
    }
 
    /**
     * Inserts the toolbar button
     */
    function insert_button(&$event, $param) {
        $event->data[] = array (
            'type' => 'format',
            'title' => $this->getLang('tooltip'),
            'icon' => '../../plugins/plantuml/icon.png',
            'open' => '<uml>',
            'close' => '</uml>',
            'sample' => PHP_EOL . PHP_EOL,
        );
    }

}

