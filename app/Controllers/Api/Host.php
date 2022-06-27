<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\HostModel;

class Host extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $hostModel = new hostModel();
        $hostModel->select('ipaddress, hostname');
        $query = $hostModel->get();
        $hosts = $query->getResultArray();
        return $this->respond($hosts);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        /* get dhcp list from server and parse to json*/
        $bom = pack('H*','EFBBBF');

        # Connect to server and generate dhcp list as a csv file
        $key = \phpseclib3\Crypt\PublicKeyLoader::load(file_get_contents($_ENV['SSH_KEY']), $password = false);
        $ssh = new \phpseclib3\Net\SSH2($_ENV['SSH_DHCP_SERVER']);
        $ssh->login($_ENV['SSH_USER'], $key);
        $result = $ssh->exec('powershell.exe -command "Get-DHCPServerv4scope |  Get-DHCPServerv4Lease | ConvertTo-CSV -notype | out-file -encoding UTF8  -filepath dhcp.csv');

        # Download the dhcp csv file
        $key = \phpseclib3\Crypt\PublicKeyLoader::load(file_get_contents($_ENV['SSH_KEY']), $password = false);
        $sftp = new \phpseclib3\Net\SFTP($_ENV['SSH_DHCP_SERVER']);
        $sftp->login($_ENV['SSH_USER'], $key);
        $raw_csv = $sftp->get('dhcp.csv');

        # remove utf-8 BOM's from csv file
        $filtered_csv = preg_replace("/^$bom/", '', $raw_csv);

        # split csv file into individale lines as an array
        $csv = explode("\n", $filtered_csv);

        $header = array_shift($csv);
        $hosts = [];
        foreach($csv as $line) {
            $row = str_getcsv(strtolower($line), ",");
            $ip = $row[0];
            if (count($row)>6) {
                if ($row[8]) {
                    $host = explode(".", $row[8])[0];
                } else {
                    $host = 'Unknown';
                }
                if (strtolower($host)=="bad_address") {
                    $host = "Unknown";
                }
                $hosts[] = ['ipaddress'=>$ip, 'hostname'=>$host];
            }
        }

        // stubing in database
        $hostModel = new HostModel();
        $hostModel->transStart();
        $hostModel->truncate();
        $hostModel->insertBatch($hosts);
        $hostModel->transComplete();

        if ($hostModel->transStatus() === false) {
            // generate an error... or use the log_message() function to log your error
        }

        return $this->respondNoContent('Hosts Updated: ' . sizeof($csv));
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        //
    }
}
