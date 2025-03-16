<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\VisitsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\VisitsController Test Case
 *
 * @uses \App\Controller\VisitsController
 */
class VisitsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Visits',
    ];
    
    public function setUp(): void
    {
        parent::setUp();

        $this->getTableLocator()->get('Visits')->deleteAll([]);
    }

    public function tearDown(): void
    {
        $this->getTableLocator()->get('Visits')->deleteAll([]);

        parent::tearDown();
    }


    public function testAddVisitWithValidData(): void
    {
        $data = [
            'visits' => [
                'date' => '2025-03-19',
                'forms' => 2,
                'products' => 2
            ],
            'address' => [
                'postal_code' => '36031010',
                'sublocality' => '',
                'street' => '',
                'street_number' => '123',
                'complement' => 'teste',
            ],
        ];

        $this->configRequest([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->post('/api/visits', json_encode($data));

        $this->assertResponseCode(201);

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals(40, $response['data']['visits']['duration']);
        
    }

    public function testCreateVisitAndUpdateExistingWorkday(): void
    {
        $data1 = [
            'visits' => [
                'date' => '2025-03-21',
                'forms' => 1,
                'products' => 1
            ],
            'address' => [
                'postal_code' => '36031010',
                'street_number' => '123',
                'complement' => 'teste',
            ],
        ];
        
        $this->configRequest([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->post('/api/visits', json_encode($data1));
        $responseVisit1 = json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseCode(201);
        $this->assertEquals(20, $responseVisit1['data']['visits']['duration']);
        $data2 = [
            'visits' => [
                'date' => '2025-03-21',
                'forms' => 1,
                'products' => 1
            ],
            'address' => [
                'postal_code' => '01311300',
                'sublocality' => 'Bela Vista',
                'street' => 'Avenida Paulista',
                'street_number' => '1500'
            ]
        ];

        $this->configRequest([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->post('/api/visits', json_encode($data2));
        $this->assertResponseCode(201);
        $responseVisit2 = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals(20, $responseVisit2['data']['visits']['duration']);

        $this->configRequest([
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->assertEquals(40, $responseVisit2['data']['workdays']['duration']);
        $this->assertResponseCode(201);
    }

    public function testListVisitsByDate(): void
    {

        $data = [
            'visits' => [
                'date' => '2025-03-21',
                'forms' => 1,
                'products' => 1
            ],
            'address' => [
                'postal_code' => '01311300',
                'sublocality' => 'Bela Vista',
                'street' => 'Avenida Paulista',
                'street_number' => '1500'
            ]
        ];


        $this->post('/api/visits', $data);
        $this->assertResponseCode(201);
        $responsePost = json_decode((string)$this->_response->getBody(), true)['data'];

        $date = ['date' => '2025-03-21'];

        $this->post('/api/visits/date', $date);
        $this->assertResponseCode(200);
        
        $responseDate = json_decode((string)$this->_response->getBody(), true)['data'];
        $this->assertEquals(20, $responseDate[0]['duration']);
    }

    public function testCreateVisitExceedingDailyLimit(): void
    {
        $data = [
            'visits' => [
                'date' => '2025-03-22',
                'forms' => 32,
                'products' => 7
            ],
            'address' => [
                'postal_code' => '01001000',
                'street_number' => '100'
            ]
        ];

        $this->post('/api/visits', $data);
        
        $responsePost = json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseCode(400);
        $this->assertResponseContains('Limite de horas atingido');
    }


}
