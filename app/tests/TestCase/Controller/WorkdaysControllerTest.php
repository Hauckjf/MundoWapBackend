<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\WorkdaysController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\Fixture\FixtureManagerTrait;

/**
 * App\Controller\WorkdaysController Test Case
 *
 * @uses \App\Controller\WorkdaysController
 */
class WorkdaysControllerTest extends TestCase
{
    use IntegrationTestTrait;
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Workdays',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->getTableLocator()->get('Workdays')->deleteAll([]);
    }

    public function tearDown(): void
    {
        $this->getTableLocator()->get('Workdays')->deleteAll([]);

        parent::tearDown();
    }

    public function testCloseWorkDay(): void
    {


        $data1 = [
            'visits' => [
                'date' => '2025-03-28',
                'forms' => 10,
                'products' => 1, 
                'completed' => 0
            ],
            'address' => [
                'postal_code' => '36031010',
                'street_number' => '123',
                'complement' => 'teste'
            ],
        ];

        $data2 = [
            'visits' => [
                'date' => '2025-03-28',
                'forms' => 20,
                'products' => 1, 
                'completed' => 0
            ],
            'address' => [
                'postal_code' => '01311300',
                'street_number' => '456',
                'complement' => 'teste'
            ],
        ];

        
        $this->configRequest([
            'headers' => ['Content-Type' => 'application/json']
        ]);
        
        $this->post('/api/visits', json_encode($data1));
        $this->assertResponseCode(201);

        $this->configRequest([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->post('/api/visits', json_encode($data2));
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseCode(201);

        $this->configRequest([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->post('/api/workdays/close', json_encode(['date' => '2025-03-28']));

        $this->assertResponseCode(201);
        $response = json_decode((string)$this->_response->getBody(), true);
        
        $this->assertTrue($response['success']);

        $responseDate = $this->getTableLocator()->get('Workdays')
        ->find()
        ->where(['date' => '2025-03-28'])
        ->contain([])
        ->toArray();
        $this->assertEquals(0, ($responseDate[0]['visits'] - $responseDate[0]['completed']));

        $responseDateNew = $this->getTableLocator()->get('Workdays')
        ->find()
        ->where(['date' => '2025-03-29'])
        ->contain([])
        ->toArray();

        $this->assertEquals(2, ($responseDateNew[0]['visits'] - $responseDateNew[0]['completed']));
    }

}
