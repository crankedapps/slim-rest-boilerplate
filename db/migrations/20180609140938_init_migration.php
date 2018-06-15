<?php


use Phinx\Migration\AbstractMigration;

class InitMigration extends AbstractMigration
{
    public function change() {
        // todo items
        $table = $this->table('todo')
                ->addColumn('name', 'string')
                ->addColumn('category_id', 'integer')
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp', ['null' => true])
                ->addColumn('deleted_at', 'timestamp', ['null' => true])
                ->create();
        // categories
        $table = $this->table('categories')
                ->addColumn('name', 'text')
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp', ['null' => true])
                ->addColumn('deleted_at', 'timestamp', ['null' => true])
                ->create();
        // users
        $table = $this->table('users')
                ->addColumn('username', 'text')
                ->addColumn('password', 'text')
                ->addColumn('created_at', 'timestamp')
                ->addColumn('updated_at', 'timestamp', ['null' => true])
                ->addColumn('deleted_at', 'timestamp', ['null' => true])
                ->create();
    }
}
