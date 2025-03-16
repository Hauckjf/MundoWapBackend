<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * VisitsFixture
 */
class VisitsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'date' => '2025-03-12',
                'completed' => 1,
                'forms' => 1,
                'products' => 1,
                'duration' => 1,
            ],
        ];
        parent::init();
    }
}
