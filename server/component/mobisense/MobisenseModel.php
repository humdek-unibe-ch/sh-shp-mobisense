<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseModel.php";
require_once __DIR__ . "/../../ext/phpseclib/vendor/autoload.php";  // Load phpseclib
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

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
    public function pull_data($transactionBy) {}

    /**
     * Creates a Mobisense control panel with standard buttons
     * @param array $options Additional options for the panel
     * @return BaseStyleComponent The panel component
     */
    public function create_mobisense_panel($options = array()) {
        $default_options = array(
            "type" => "secondary",
            "is_expanded" => true,
            "is_collapsible" => true,
            "title" => "Mobisense Panel",
            "css" => "",
        );
        
        $panel_options = array_merge($default_options, $options);
        
        return new BaseStyleComponent("card", array_merge($panel_options, array(
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
                ))
            )
        )));
    }

    public function test_connection(): array
    {
        $mobisense_server_ip = $this->mobisense_settings['mobisense_server_ip'];
        $mobisense_ssh_port = $this->mobisense_settings['mobisense_ssh_port'];
        $mobisense_ssh_user = $this->mobisense_settings['mobisense_ssh_user'];
        $mobisense_db_name = $this->mobisense_settings['mobisense_db_name'];
        $mobisense_db_port = $this->mobisense_settings['mobisense_db_port'];
        $mobisense_db_user = $this->mobisense_settings['mobisense_db_user'];
        $mobisense_db_password = $this->mobisense_settings['mobisense_db_password'];
        $mobisense_local_host = $this->mobisense_settings['mobisense_local_host'];
        $mobisense_pull_data = $this->mobisense_settings['mobisense_pull_data'];
        $private_key_file = __DIR__ . '/../../../auth/ssh_key';

        $messages = [];

        try {
            // Check if private key file exists
            if (!file_exists($private_key_file)) {
                throw new Exception("SSH key file not found at: $private_key_file");
            }

            // Load SSH Private Key
            try {
                $privateKey = PublicKeyLoader::load(file_get_contents($private_key_file));
                $messages[] = "✅ SSH key loaded successfully";
            } catch (Exception $e) {
                throw new Exception("Failed to load SSH key: " . $e->getMessage());
            }

            // Establish SSH Connection
            $ssh = new SSH2($mobisense_server_ip, $mobisense_ssh_port);
            
            // Set timeout to avoid hanging
            $ssh->setTimeout(10);
            
            if (!$ssh->login($mobisense_ssh_user, $privateKey)) {
                throw new Exception("SSH Authentication Failed");
            }
            
            $messages[] = "✅ SSH connection established successfully";
            
            // Test direct database connection to the remote server through SSH
            // In this approach, we're connecting directly to the database server through the SSH tunnel
            // This is simpler and more reliable than trying to set up port forwarding
            
            // Execute a test command to verify database connectivity
            $test_cmd = "pg_isready -h $mobisense_local_host -p $mobisense_db_port -d $mobisense_db_name -U $mobisense_db_user";
            $result = $ssh->exec($test_cmd);
            
            if (strpos($result, 'accepting connections') === false) {
                throw new Exception("Database server is not accessible: $result");
            }
            
            $messages[] = "✅ Remote database server is accessible";
            
            // Test a simple query through the SSH connection
            $query_cmd = "PGPASSWORD=\"$mobisense_db_password\" psql -h $mobisense_local_host -p $mobisense_db_port -d $mobisense_db_name -U $mobisense_db_user -c \"SELECT 1 as test\" -t";
            $query_result = trim($ssh->exec($query_cmd));
            
            if ($query_result !== "1") {
                throw new Exception("Failed to execute test query: $query_result");
            }
            
            $messages[] = "✅ Successfully executed test query on PostgreSQL database";
            
            // Disconnect from SSH
            $ssh->disconnect();
            
            return [
                'success' => true,
                'messages' => $messages
            ];
            
        } catch (Exception $e) {
            // Make sure to disconnect SSH if it's connected
            if (isset($ssh) && $ssh->isConnected()) {
                $ssh->disconnect();
            }
            
            $messages[] = "❌ Error: " . $e->getMessage();
            return [
                'success' => false,
                'messages' => $messages
            ];
        }
    }
}
