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
     * The constructor creates an instance of the hooks.
     * @param object $services
     *  The service handler instance which holds all services
     * @param object $params
     *  Various params
     */
    public function __construct($services, $params = array())
    {
        parent::__construct($services, $params);
    }

    /* Private Methods *********************************************************/

    /**
     * Output select Rserve panel with its button functionality
     * @return object
     * Return instance of BaseStyleComponent -> select style
     */
    private function outputMobisensePanel()
    {
        return new BaseStyleComponent("card", array(
            "type" => "secondary",
            "is_expanded" => true,
            "is_collapsible" => true,
            "title" => "Mobisense Panel",
            "children" => array(
                new BaseStyleComponent("button", array(
                    "label" => "Test Connection",
                    "url" => $this->get_link_url(PAGE_MOBISENSE, array(
                        "mode" => PAGE_MOBISENSE_MODE_TEST_CONNECTION,
                    )),
                    "type" => "secondary",
                    "css" => "mr-3 btn-sm"
                )),
                new BaseStyleComponent("button", array(
                    "label" => "Pull Data",
                    "url" => $this->get_link_url(PAGE_MOBISENSE, array(
                        "mode" => PAGE_MOBISENSE_MODE_PULL_DATA,
                    )),
                    "type" => "secondary",
                    "css" => "mr-3 btn-sm"
                )),
                // new BaseStyleComponent("button", array(
                //     "label" => "Create  New R Script",
                //     "url" => $this->get_link_url("moduleRMode", array("mode" => INSERT)),
                //     "type" => "secondary",
                //     "css" => "mr-3 btn-sm"
                // ))
            )
        ));
    }

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
            $selectField = $this->outputMobisensePanel();
            if ($selectField && $res) {
                $children = $res->get_view()->get_children();
                $children[] = $selectField;
                $res->get_view()->set_children($children);
            }
        }
        return $res;
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
