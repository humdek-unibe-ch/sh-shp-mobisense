<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseController.php";
/**
 * The controller class of the group insert component.
 */
class MobisenseController extends BaseController
{
    /* Private Properties *****************************************************/


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $mode)
    {
        parent::__construct($model);
        if (isset($mode) && !$this->check_acl($mode)) {
            return false;
        }
        if (isset($mode)) {
            if ($mode == PAGE_MOBISENSE_MODE_TEST_CONNECTION) {
                $res = $this->model->test_connection();
                if(!$res['success']) {
                    $this->error_msgs = $res['messages'];
                    $this->fail = true;
                }else{
                    $this->success = true;
                    $this->success_msgs = $res['messages'];
                }
            } elseif ($mode == PAGE_MOBISENSE_MODE_PULL_DATA) {
                $res = $this->model->pull_data(transactionBy_by_user);
                if(!$res['success']) {
                    $this->error_msgs = $res['messages'];
                    $this->fail = true;
                }else{
                    $this->success = true;
                    $this->success_msgs = $res['messages'];
                }
            }
        }
    }

    /**
     * Check the acl for the current user and the current page
     * @return bool
     * true if access is granted, false otherwise.
     */
    protected function check_acl($mode)
    {
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword(PAGE_MOBISENSE), SELECT)) {
            $this->fail = true;
            $this->error_msgs[] = "You don't have rights to " . $mode . " this survey";
            return false;
        } else {
            return true;
        }
    }

    /* Public Methods *********************************************************/
}
?>
