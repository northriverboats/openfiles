<?php

namespace App\Controllers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Controller;
use App\Models\FileModel;
use App\Models\HostModel;

class Tools extends Controller
{

    public function message($to = 'World')
    {
        CLI::write("Hello ${to}!");
    }

    public function getOpenFilesList() {
        // convert db to json
    }

    public function updateDhcpList() {
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

        // $this->respondCreated();
        return json_encode(['status'=>'ok']);
    }

    public function updateFilesList()
    {
        $skip_list = ['hr', 'payroll', 'cis', 'hroffice'];

        $share_names = [
            'acad' => 'V:\\',
            'common' => 'X:\\',
            'commercial' => 'N:\\',
            'scans' => 'J:\\',
            'costing' => 'K:\\',
            'marketing' => 'M:\\',
            'almar' => 'O:\\',
            'production' => 'P:\\',
            'recreation' => 'R:\\',
            'photos' => 'U:\\',
        ];

        $hostModel = new HostModel();

        $key = \phpseclib3\Crypt\PublicKeyLoader::load(file_get_contents($_ENV['SSH_KEY']), $password = false);
        $sftp = new \phpseclib3\Net\SFTP($_ENV['SSH_FILE_SERVER']);
        $sftp->login($_ENV['SSH_USER'], $key);
        $files = $sftp->get('files.csv');
        $rows = str_getcsv($files, "\n");
        $keys = array_shift($rows);
        $key = str_getcsv($keys);

        $files =[];

        foreach($rows as $row) {
            $original = str_getcsv($row);
            $state = "default";
            $item = ["user"=>"", "computer"=> "", "share"=>"", "file"=>"", "type"=>""];
            foreach($original as $segment) {
                switch($state) {
                    case "default":
                        $state = "ip";
                        break;
                    case "ip":
                        $state = "user";
                        $hostModel->where('ipaddress', $segment);
                        $query = $hostModel->get();
                        $hostInfo = $query->getResult();
                        $item["computer"] = $hostInfo ? $hostInfo[0]->hostname : 'Unknown';
                        break;
                    case "user":
                        $item["user"] = substr($segment, 11);
                        $state = "share";
                        break;
                    case "share":
                        $explode = explode("\\", $segment);
                        if ($explode[0] != "D:") {
                            break;
                        }
                        if ($explode[0] == "D:" and $explode[2] == "Shares" and !in_array($explode[3], $skip_list)) {
                            $state = "skip1";
                            $item["share"] = $explode[3];
                            $item['type'] = str_contains($explode[sizeof($explode)-1], '.') ? "file" : "folder";
                        }
                        break;
                    case "skip1":
                        $state = "skip2";
                        break;
                    case "skip2":
                        $state = "skip3";
                        break;
                    case "skip3":
                        $state = "file";
                        break;
                    case "file":
                        $item["file"] = $share_names[$explode[3]] . $segment;
                        $state = "skip4";
            
                }
            }
            $files[] = $item;
        }
        // stubing in database
        $fileModel = new FileModel();
        $fileModel->transStart();
        $fileModel->truncate();
        $fileModel->insertBatch($files);
        $fileModel->transComplete();

        // $this->respondCreated();
        return json_encode(['status'=>'ok']);
    }
}