<?php

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
require_once __DIR__ . "/../../../../../component/BaseModel.php";
require_once __DIR__ . "/../../ext/phpseclib/vendor/autoload.php";

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
     * @var string Path to the SSH private key file
     */
    private $private_key_file;

    /**
     * Constructor for the MobisenseModel
     * 
     * @param array $services An associative array holding the different available services.
     * @param array $params Additional parameters for the model
     */
    public function __construct($services, $params)
    {
        parent::__construct($services, $params);
        $this->mobisense_settings = $this->db->fetch_page_info(SH_MODULE_MOBISENSE);
        $this->private_key_file = __DIR__ . '/../../../auth/ssh_key';
    }

    /**
     * Adds a timestamped message to the messages array
     * 
     * @param array &$messages Reference to messages array
     * @param string $message Message to add
     * @return void
     */
    private function add_message(&$messages, $message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $messages[] = "[$timestamp] $message";
    }

    /**
     * Establishes an SSH connection to the Mobisense server
     * 
     * @param array $messages Reference to an array for storing status/error messages
     * @return SSH2|false SSH connection object on success, false on failure
     */
    private function connect_ssh(&$messages)
    {
        try {
            // Check if SSH key exists and is readable
            if (!file_exists($this->private_key_file)) {
                throw new Exception("SSH key file not found at: {$this->private_key_file}");
            }
            if (!is_readable($this->private_key_file)) {
                throw new Exception("SSH key file is not readable: {$this->private_key_file}");
            }
            $this->add_message($messages, "SSH key file found and is readable");

            // Initialize SSH connection
            $ssh = new SSH2(
                $this->mobisense_settings['mobisense_server_ip'],
                $this->mobisense_settings['mobisense_ssh_port']
            );
            $privateKey = PublicKeyLoader::load(file_get_contents($this->private_key_file));

            // Attempt SSH login
            if (!$ssh->login($this->mobisense_settings['mobisense_ssh_user'], $privateKey)) {
                throw new Exception("SSH authentication failed");
            }
            $this->add_message($messages, "SSH connection established successfully");

            return $ssh;
        } catch (Exception $e) {
            $this->add_message($messages, "Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Executes a PostgreSQL command via SSH
     * 
     * @param SSH2 $ssh SSH connection object
     * @param string $command PostgreSQL command to execute
     * @param array $messages Reference to an array for storing status/error messages
     * @return string|false Command output on success, false on failure
     */
    private function execute_postgres_command($ssh, $command, &$messages)
    {
        try {
            $pgCommand = "PGPASSWORD='{$this->mobisense_settings['mobisense_db_password']}' " .
                "psql -h {$this->mobisense_settings['mobisense_local_host']} " .
                "-p {$this->mobisense_settings['mobisense_db_port']} " .
                "-U {$this->mobisense_settings['mobisense_db_user']} " .
                "-d {$this->mobisense_settings['mobisense_db_name']} " .
                "-c \"$command\"";

            $result = $ssh->exec($pgCommand);

            if ($ssh->getExitStatus() !== 0) {
                throw new Exception("PostgreSQL command execution failed: " . $result);
            }

            return $result;
        } catch (Exception $e) {
            $this->add_message($messages, "Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Executes a PostgreSQL query and returns results in CSV format
     * 
     * @param SSH2 $ssh SSH connection object
     * @param string $query SQL query to execute
     * @param array $messages Reference to an array for storing status/error messages
     * @return array|false Parsed query results on success, false on failure
     */
    private function execute_postgres_query($ssh, $query, &$messages)
    {
        try {
            $command = "\\copy ($query) to stdout with csv header";
            $result = $this->execute_postgres_command($ssh, $command, $messages);

            if ($result === false) {
                return false;
            }

            // Parse CSV result into array
            $data = [];
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

            return $data;
        } catch (Exception $e) {
            $this->add_message($messages, "Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pulls data from the Mobisense database
     * 
     * @param string $transactionBy User identifier for the transaction
     * @return array Array containing query results and status messages
     */
    public function pull_data($transactionBy)
    {
        if (!$this->mobisense_settings['mobisense_pull_data']) {
            return [
                'success' => false,
                'messages' => ['Mobisense pull data is disabled'],
                'data' => []
            ];
        }
        $messages = [];
        $success = true;
        $data = [];

        // Establish SSH connection
        $ssh = $this->connect_ssh($messages);
        if ($ssh === false) {
            return [
                'success' => false,
                'messages' => $messages,
                'data' => []
            ];
        }

        $sql_user_codes = "SELECT u.id AS id_users, vc.`code`
                        FROM users u
                        INNER JOIN validation_codes vc ON (u.id = vc.id_users)";
        $user_codes = $this->db->query_db($sql_user_codes);

        // Format codes for IN clause - each code wrapped in single quotes and comma-separated
        $formatted_codes = [];
        foreach ($user_codes as $row) {
            $formatted_codes[] = "'" . $row['code'] . "'";
        }

        $codes_string = implode(', ', $formatted_codes);

        // Execute query
        $sql_postgres = "SELECT lu.userid, COALESCE(to_char(to_timestamp(c.last_insert / 1e9), 'YYYY-MM-DD HH24:MI:SS'), 'none') AS coordinates_last_insert, COALESCE((to_char(NOW() - to_timestamp(c.last_insert / 1e9), 'HH24:MI:SS')), 'none') AS coordinates_time_difference, COALESCE(CAST(EXTRACT(DAY FROM (NOW() - to_timestamp(c.last_insert / 1e9))) AS TEXT), 'none') AS coordinates_days_difference, COALESCE(CAST(ROUND(EXTRACT(EPOCH FROM (NOW() - to_timestamp(c.last_insert / 1e9))) / 60) AS TEXT), 'none') AS coordinates_minutes_difference, to_char(lu.last_upload_day_time, 'YYYY-MM-DD HH24:MI:SS') AS last_upload_insert, to_char(NOW() - lu.last_upload_day_time, 'HH24:MI:SS') AS upload_time_difference, EXTRACT(DAY FROM (NOW() - lu.last_upload_day_time)) AS upload_days_difference, ROUND(EXTRACT(EPOCH FROM (NOW() - lu.last_upload_day_time)) / 60) AS upload_minutes_difference, CASE WHEN COALESCE(p.recording, TRUE) THEN 1 ELSE 0 END AS recording FROM (SELECT userid, MAX(day_time) AS last_upload_day_time FROM public.last_upload WHERE userid IN ($codes_string) GROUP BY userid) lu FULL OUTER JOIN (SELECT userid, MAX(timestamp) AS last_insert FROM public.coordinates WHERE userid IN ($codes_string) GROUP BY userid) c ON c.userid = lu.userid LEFT JOIN LATERAL (SELECT p.recording FROM pauses p WHERE p.userid = lu.userid ORDER BY p.timestamp DESC LIMIT 1) p ON TRUE";
        $data = $this->execute_postgres_query($ssh, $sql_postgres, $messages);

        if ($data !== false) {
            $this->add_message($messages, "The data was pulled successfully!");

            // Create a mapping of codes to id_users for quick lookup
            $code_to_id_map = [];
            foreach ($user_codes as $row) {
                $code_to_id_map[$row['code']] = $row['id_users'];
            }

            // Track which users have data from PostgreSQL
            $processed_user_ids = [];

            // Process the data to add id_users and remove userid
            $processed_data = [];
            foreach ($data as $row) {
                // Add id_users based on the code mapping
                if (isset($row['userid']) && isset($code_to_id_map[$row['userid']])) {
                    $id_users = $code_to_id_map[$row['userid']];
                    $row['id_users'] = $id_users;
                    $processed_user_ids[] = $id_users; // Track that we've processed this user
                    // Remove the userid column
                    unset($row['userid']);
                }
                $processed_data[] = $row;
            }

            // Add entries for users without PostgreSQL data
            foreach ($user_codes as $user) {
                $id_users = $user['id_users'];
                if (!in_array($id_users, $processed_user_ids)) {
                    // Create a record with default values for users without PostgreSQL data
                    $default_record = [
                        'id_users' => $id_users,
                        'coordinates_last_insert' => null,
                        'coordinates_time_difference' => null,
                        'coordinates_days_difference' => -1,
                        'coordinates_minutes_difference' => -1,
                        'last_upload_insert' => null,
                        'upload_time_difference' => null,
                        'upload_days_difference' => -1,
                        'upload_minutes_difference' => -1,
                        'recording' => -1
                    ];
                    $processed_data[] = $default_record;
                }
            }

            $data = $processed_data;
            foreach ($data as $row) {
                $success = $this->user_input->save_data($transactionBy, DATA_TABLE_MOBISENSE_LAST_UPDATE, $row);
                if (!$success) {
                    $this->add_message($messages, "Failed to save data to database for user " . $row['id_users']);
                }
            }
        } else {
            $success = false;
        }

        $this->transaction->add_transaction(
            transactionTypes_insert,
            $transactionBy,
            isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null,
            PAGE_MOBISENSE,
            null,
            "",
            "Manual pull Mobisense Data for all users"
        );

        return [
            'success' => $success,
            'messages' => $messages,
            'data' => $data ?: []
        ];
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
    public function create_mobisense_panel($options = array())
    {
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
        $messages = [];
        $success = true;

        // Establish SSH connection
        $ssh = $this->connect_ssh($messages);
        if ($ssh === false) {
            return [
                'success' => false,
                'messages' => $messages
            ];
        }

        // Test database connection with a simple command
        $result = $this->execute_postgres_command($ssh, '\\q', $messages);
        if ($result !== false) {
            $this->add_message($messages, "Database connection test successful");
        } else {
            $success = false;
        }

        return [
            'success' => $success,
            'messages' => $messages
        ];
    }
}
