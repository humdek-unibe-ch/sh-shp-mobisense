<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseView.php";
require_once __DIR__ . "/../../../../../component/style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class MobisenseView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * Script id, 
     * if it is > 0  edit/delete script page     
     */
    private $sid;

    /**
     * The mode type of the form EDIT, DELETE, INSERT, VIEW     
     */
    private $mode;

    /**
     * the current selected script
     */
    private $script;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $mode)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_mobisense_alerts.php";
        $button_panel = $this->model->create_mobisense_panel(array("css" => "m-3"));
        $button_panel->output_content();
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Render the alert message.
     */
    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

}
?>
