<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WorkdaysFixture
 */
class WorkdaysFixture extends TestFixture
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
                'visits' => 1,
                'completed' => 1,
                'duration' => 1,
            ],
        ];
        parent::init();
    }
}
