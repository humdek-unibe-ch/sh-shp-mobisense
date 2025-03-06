<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseModel.php";

/**
 * This class is used to prepare all API calls related to Mobisense
 */
class MobisenseModel extends BaseModel
{

    /* Private Properties *****************************************************/

    /**
     * The settings for the Mobisense instance
     */
    private $mobisense_settings;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $params)
    {     
        parent::__construct($services);
        $this->mobisense_settings = $this->db->fetch_page_info(SH_MODULE_MOBISENSE);
    }

    /* Private Methods *********************************************************/

    /* Public Methods *********************************************************/


    /**
     * Pull all Mobisense data for all the users
     * @param string $transactionBy
     * Who initiated the action
     */
    public function pull_data_all_users($transactionBy)
    {
        
    }
}
