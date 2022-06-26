<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],            
            'computer' => [
                'type' => 'VARCHAR',
                'constraint' => '30',
                'null' => false
            ],
            'file' => [
                'type' => 'VARCHAR',
                'constraint' => '2048',
                'null' => false,
                'unique' => true
            ],
            'share' => [
                'type' => 'VARCHAR',
                'constraint' => '16',
                'null' => false
            ],
            'user' => [
                'type' => 'VARCHAR',
                'constraint' => '32',
                'null' => false,
                'unique' => true
            ],
            'updated_at' => [
                'type' => 'datetime',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('files');
    }

    public function down()
    {
        $this->forge->dropTable('files');
    }
}
