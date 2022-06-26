<?php

namespace App\Controllers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Controller;

class Tools extends Controller
{

    public function message($to = 'World')
    {
        CLI::write("Hello ${to}!");
    }

    public function dhcp() {
        /* get dhcp list from server and parse to json*/
        $json = array();
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

        foreach($csv as $line) {
            $row = str_getcsv(strtolower($line), ",");
            $ip = substr(str_pad($row[0], 20, " "), 0, 16);
            if (count($row)>6) {
                if ($row[8]) {
                    $host = explode(".", $row[8])[0];
                } else {
                    $host = 'Unknown';
                }
                if (strtolower($host)=="bad_address") {
                    $host = "Unknown";
                }
                echo $ip ." " . $host, "\n";
            }
        }

        return ;


        # extract first row and use for keys/column names
        $header = array_shift($csv);
        $keys = str_getcsv(strtolower($header), ",");

        # convert each valid line of the csv file into key/value
        foreach($csv as $line) {
            $row = str_getcsv($line, ",");
            if (count($row) > 1 ) {
                $name = strtoupper(explode(".", $row[8])[0]);
                $row[8] = $name ? $name : "UNKNOWN";
                if ($row[8] == 'BAD_ADDRESS') {
                    $row[8] = "UNKNOWN";
                }
                $json[$row[8]] = array_combine($keys, $row);
            }
        }

        # save dhcp as json
        file_put_contents("../writable/uploads/dhcp.json", json_encode($json));

        return json_encode(['status'=>'ok']);
    }

    public function files() {
        if (! file_exists("../writable/uploads/dhcp.json")) {
            $this->dhcp();
        }
        $dhcp_json = file_get_contents('../writable/uploads/dhcp.json');
        $dhcp = json_decode($dhcp_json, true);
        $keys = array_keys($dhcp);





        // file_put_contents("../writable/uploads/openfiles.json", json_encode($json));

        return json_encode(['status'=>'ok']);
    }

    public function sftp()
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

        $json = array();

        $key = \phpseclib3\Crypt\PublicKeyLoader::load(file_get_contents($_ENV['SSH_KEY']), $password = false);
        $sftp = new \phpseclib3\Net\SFTP($_ENV['SSH_SERVER']);
        $sftp->login($_ENV['SSH_USER'], $key);
        $files = $sftp->get('files.csv');
        $rows = str_getcsv($files, "\n");
        $keys = array_shift($rows);
        $key = str_getcsv($keys);

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
                        $item["computer"] = "unknown";
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
            $json[] = $item;
            /*
            if ($item["type"]=='file' ) {
                echo str_pad($item["user"],12, " ") .str_pad($item["type"], 8, " ") .str_pad($item["share"], 12, " ") .$item["file"]."\n";
                # echo $output . "\n\n";
            }
            */
        }
        return json_encode($json);
    }

}