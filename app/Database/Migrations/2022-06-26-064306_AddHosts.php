<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHosts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ipaddress' => [
                'type' => 'VARCHAR',
                'constraint' => '15',
                'null' => false
            ],
            'hostnmae' => [
                'type' => 'VARCHAR',
                'constraint' => '30',
                'null' => false,
                'unique' => true
            ],
            'updated_at' => [
                'type' => 'datetime',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->createTable('hosts');
    }

    public function down()
    {
        $this->forge->dropTable('hosts');
    }
}
