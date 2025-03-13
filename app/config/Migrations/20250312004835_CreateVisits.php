<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateVisits extends AbstractMigration
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
        $table = $this->table('visits');
     
        $table->addPrimaryKey('id');

        $table->addColumn('date', 'date', [
            'null' => false,
        ]);
        
        $table->addIndex(['date']);

        $table->addColumn('completed', 'integer', [
            'null' => false,
            'default' => '0'
        ]);
       
        $table->addColumn('forms', 'integer', [
            'null' => false,
        ]);
  
        $table->addColumn('products', 'integer', [
            'null' => false,
        ]);
     
        $table->addColumn('duration', 'integer', [
            'null' => false,
            'default' => '0'
        ]);

        $table->create();
    }
}
