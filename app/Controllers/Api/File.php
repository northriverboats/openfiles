<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FileModel;
use App\Models\HostModel;

class File extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $fileModel = new FileModel();
        $fileModel->select('computer, file, share, user, type');
        $query = $fileModel->get();
        $files = $query->getResultArray();
        return $this->respond($files);
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

        return $this->respondNoContent('Open Files Updated: ' . sizeof($rows));
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
