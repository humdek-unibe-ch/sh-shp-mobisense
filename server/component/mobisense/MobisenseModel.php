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
 * MobisenseModel class
 * 
 * Handles the business logic for the Mobisense plugin, including SSH connections,
 * database operations, and UI component generation.
 * 
 * @package    SelfHelp
 * @subpackage Mobisense
 */
class MobisenseModel extends BaseModel
{

    /**
     * @var array Mobisense configuration settings
     */
    private $mobisense_settings;

    /**
     * Constructor for the MobisenseModel
     * 
     * @param array $services An associative array holding the different available services.
     * @param array $params Additional parameters for the model
     */
    public function __construct($services, $params)
    {
        parent::__construct($services);
        $this->mobisense_settings = $this->db->fetch_page_info(SH_MODULE_MOBISENSE);
    }

    /**
     * Pulls data from the Mobisense database
     * 
     * @param string $transactionBy User identifier for the transaction
     * @return array Array containing query results and status messages
     */
    public function pull_data($transactionBy) {
        $mobisense_server_ip = $this->mobisense_settings['mobisense_server_ip'];
        $mobisense_ssh_port = $this->mobisense_settings['mobisense_ssh_port'];
        $mobisense_ssh_user = $this->mobisense_settings['mobisense_ssh_user'];
        $mobisense_db_name = $this->mobisense_settings['mobisense_db_name'];
        $mobisense_db_port = $this->mobisense_settings['mobisense_db_port'];
        $mobisense_db_user = $this->mobisense_settings['mobisense_db_user'];
        $mobisense_db_password = $this->mobisense_settings['mobisense_db_password'];
        $mobisense_local_host = $this->mobisense_settings['mobisense_local_host'];
        $private_key_file = __DIR__ . '/../../../auth/ssh_key';

        $messages = [];
        $success = true;
        $data = [];

        try {
            // Check if SSH key exists and is readable
            if (!file_exists($private_key_file)) {
                throw new Exception("SSH key file not found at: $private_key_file");
            }
            if (!is_readable($private_key_file)) {
                throw new Exception("SSH key file is not readable: $private_key_file");
            }
            $messages[] = "SSH key file found and is readable";

            // Initialize SSH connection
            $ssh = new SSH2($mobisense_server_ip, $mobisense_ssh_port);
            $privateKey = PublicKeyLoader::load(file_get_contents($private_key_file));
            
            // Attempt SSH login
            if (!$ssh->login($mobisense_ssh_user, $privateKey)) {
                throw new Exception("SSH authentication failed");
            }
            $messages[] = "SSH connection established successfully";

            // Execute the query and get results in CSV format for easy parsing
            $query = "select * from last_upload";
            $command = "PGPASSWORD='$mobisense_db_password' psql -h $mobisense_local_host -p $mobisense_db_port -U $mobisense_db_user -d $mobisense_db_name -c \"\\copy ($query) to stdout with csv header\"";
            $result = $ssh->exec($command);
            
            if ($ssh->getExitStatus() === 0) {
                $messages[] = "The data was pulled successfully!";
                
                // Parse CSV result into array
                $lines = explode("\n", trim($result));
                if (count($lines) > 0) {
                    $headers = str_getcsv($lines[0]);
                    foreach (array_slice($lines, 1) as $line) {
                        if (trim($line) !== '') {
                            $row = array_combine($headers, str_getcsv($line));
                            $data[] = $row;
                        }
                    }
                }
                
                // For now, just dump the data
                // var_dump($data);
            } else {
                throw new Exception("Query execution failed: " . $result);
            }

        } catch (Exception $e) {
            $success = false;
            $messages[] = "Error: " . $e->getMessage();
        }

        return array(
            'success' => $success,
            'messages' => $messages,
            'data' => $data
        );
    }

    /**
     * Creates a Mobisense control panel with standard buttons
     * 
     * @param array $options Additional options for customizing the panel
     *                      Supported options:
     *                      - type: string Panel type (default: "secondary")
     *                      - is_expanded: bool Whether panel is expanded (default: true)
     *                      - is_collapsible: bool Whether panel can collapse (default: true)
     *                      - title: string Panel title (default: "Mobisense Panel")
     *                      - css: string Additional CSS classes (default: "")
     * @return BaseStyleComponent The configured panel component
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

    /**
     * Tests the connection to the Mobisense server and database
     * 
     * This method performs a series of connection tests:
     * 1. Checks if the SSH key file exists and is readable
     * 2. Attempts to establish an SSH connection to the server
     * 3. Tests the PostgreSQL database connection
     * 
     * @return array Associative array containing:
     *               - success: bool Whether all connection tests passed
     *               - messages: array List of status messages from each test
     * @throws Exception If SSH key file is missing or unreadable
     */
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
