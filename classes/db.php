<?php

/**
 * db.php
 *
 * Database related functions for SMTP API.
 *
 * @author     Donat Marko
 * @copyright  2018 Donatus
 * @license    GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

class DB
{
    protected $sql;
    protected $config = null;
    
    public function __construct($sql)
    {
        $this->sql = $sql;
    }
    
    /**
     * Retrieve data about the given API key
     * @param string API key
     * @return object
     */
    public function GetAPIkeyData($apikey = '')
    {
        if ($query = $this->sql->query("SELECT * FROM apikeys WHERE apikey LIKE '$apikey'"))
        {
            while ($row = $query->fetch_assoc())
            {
                $data = new StdClass();
                $data->apikey = $row['apikey'];
                $data->name = $row['name'];
                $data->email = $row['email'];
                $data->ip = $row['ip'];
                $data->notes = $row['notes'];
                return $data;
            }
        }
        return false;
    }
    
    /**
     * Logs API usage to database.
     * @param string API key
     * @param string mail in JSON format
     * @param string response in JSON format
     */
    public function Log($apikey, $mail, $response)
    {
        global $_SERVER;
        $referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
        $agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '';
        $ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '';
        $host = isset($_SERVER["REMOTE_HOST"]) ? $_SERVER["REMOTE_HOST"] : '';

        $stmt = $this->sql->prepare("INSERT INTO log (datetime, apikey, mail, response, http_referer, http_user_agent, remote_addr, remote_host) VALUES (now(), ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss',
            $apikey,
            $mail,
            $response,
            $referer,
            $agent,
            $ip,
            $host
        );
        $stmt->execute();
    }
}

?>