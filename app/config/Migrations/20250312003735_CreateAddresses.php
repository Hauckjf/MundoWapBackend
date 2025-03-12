<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateAddresses extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('addresses');

        $table->addPrimaryKey('id');
        
        $table->addColumn('foreign_table', 'string', [
            'limit' => 100,
            'null' => false,
        ]);

        $table->addColumn('foreign_id', 'integer', [
            'null' => false,
        ]);
        
        $table->addIndex(['foreign_table', 'foreign_id']);

        $table->addColumn('postal_code', 'string', [
            'limit' => 8,
            'null' => false,
        ]);

        $table->addColumn('state', 'string', [
            'limit' => 2,
            'null' => false,
        ]);

        $table->addColumn('city', 'string', [
            'limit' => 200,
            'null' => false,
        ]);

        $table->addColumn('sublocality', 'string', [
            'limit' => 200,
            'null' => false,
        ]);

        $table->addColumn('street', 'string', [
            'limit' => 200,
            'null' => false,
        ]);

        $table->addColumn('street_number', 'string', [
            'limit' => 200,
            'null' => false,
        ]);

        $table->addColumn('complement', 'string', [
            'limit' => 200,
            'default' => '',
            'null' => false,
        ]);

        $table->create();
    }
}