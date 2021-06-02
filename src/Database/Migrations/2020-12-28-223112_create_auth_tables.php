<?php

namespace Fluent\Auth\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        /**
         * Users table.
         */
        $this->forge->addField([
            'id'                => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'             => ['type' => 'varchar', 'constraint' => 255],
            'username'          => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'password'          => ['type' => 'varchar', 'constraint' => 255],
            'email_verified_at' => ['type' => 'datetime', 'null' => true],
            'remember_token'    => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_at'        => ['type' => 'datetime', 'null' => true],
            'updated_at'        => ['type' => 'datetime', 'null' => true],
            'deleted_at'        => ['type' => 'datetime', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('username');

        $this->forge->createTable('users', true);

        /**
         * Password reset table.
         */
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'      => ['type' => 'varchar', 'constraint' => 255],
            'token'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => false],
            'updated_at' => ['type' => 'datetime', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('auth_password_resets', true);

        /**
         * Access tokens table.
         */
        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'name'         => ['type' => 'varchar', 'constraint' => 255],
            'token'        => ['type' => 'varchar', 'constraint' => 64],
            'last_used_at' => ['type' => 'datetime', 'null' => true],
            'scopes'       => ['type' => 'text', 'null' => true],
            'created_at'   => ['type' => 'datetime', 'null' => true],
            'updated_at'   => ['type' => 'datetime', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('token');
        $this->forge->createTable('auth_access_tokens', true);
    }

    //--------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('auth_password_resets', true);
        $this->forge->dropTable('auth_access_tokens', true);
    }
}
