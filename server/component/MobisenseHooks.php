<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../component/BaseHooks.php";
require_once __DIR__ . "/../../../../component/style/BaseStyleComponent.php";
require_once __DIR__ . "/mobisense/MobisenseModel.php";

/**
 * The class to define the hooks for the plugin.
 */
class MobisenseHooks extends BaseHooks
{
    /* Constructors ***********************************************************/

    /**
     * The mobisenseModel 
     */
    private $mobisenseModel;

    /**
     * The constructor creates an instance of the hooks.
     * @param object $services
     *  The service handler instance which holds all services
     * @param object $params
     *  Various params
     */
    public function __construct($services, $params = array())
    {
        parent::__construct($services, $params);
        $this->mobisenseModel = new MobisenseModel($services, -1);
    }

    /* Private Methods *********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Return a BaseStyleComponent object
     * @param object $args
     * Params passed to the method
     * @return object
     * Return a BaseStyleComponent object
     */
    public function outputFieldPanel($args)
    {
        $field = $this->get_param_by_name($args, 'field');
        $res = $this->execute_private_method($args);
        if ($field['name'] == 'mobisense_panel') {
            $selectField = $this->get_mobisense_panel();
            if ($selectField && $res) {
                $children = $res->get_view()->get_children();
                $children[] = $selectField;
                $res->get_view()->set_children($children);
            }
        }
        return $res;
    }

    public function get_mobisense_panel()
    {
        return $this->mobisenseModel->create_mobisense_panel();
    }

    /**
     * Get the plugin version
     */
    public function get_plugin_db_version($plugin_name = 'mobisense')
    {
        return parent::get_plugin_db_version($plugin_name);
    }
}
?>
