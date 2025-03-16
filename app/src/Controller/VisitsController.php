<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AddressesController;
use App\Controller\WorkdaysController;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\Datasource\ConnectionManager;

/**
 * Visits Controller
 *
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class VisitsController extends AppController
{

   /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $visits = $this->paginate($this->Visits);

        return $this->response->withType('application/json')
        ->withStatus(200)
        ->withStringBody(json_encode([
            'success' => true,
            'data' => $visits
        ]));
    }

    /**
     * View method
     *
     * @param string|null $id Address id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id)
    {
        try
        {
            if(isset($id))
            {
                
                $visits = $this->Visits->get($id, [
                    'contain' => [],
                ]);

                return $this->response->withType('application/json')
                ->withStatus(200)
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $visits
                ]));
            }
            else
            {
                return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'error' => true,
                    'message' => 'O campo id é obrigatório.'
                ]));
            }
        }
        catch (RecordNotFoundException $e) 
        {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode([
                    'error' => true,
                    'message' => 'Visita não encontrado.'
                ]));

        } catch (InvalidArgumentException $e) 
        {
            return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'error' => true,
                    'message' => $e->getMessage()
                ]));

        } catch (\Exception $e) 
        {
            return $this->response->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'error' => true,
                    'message' => 'Erro interno no servidor: ' . $e->getMessage()
                ]));
        }
    }

    public function viewByDate()
    {
        if ($this->request->is('post')) 
        {
            try
            {

                $data = $this->request->getData();
                
                if(isset($data['date']))
                {
                    $visits = $this->Visits->find()
                        ->where(['date' => $data['date']])
                        ->contain(['Addresses'])
                        ->toArray();

                    if(sizeof($visits) === 0)
                    {
                        return $this->response->withType('application/json')
                        ->withStatus(404)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'Nenhum visita cadastrada para a data.'
                        ]));
                    }

                    return $this->response->withType('application/json')
                        ->withStatus(200)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $visits
                        ]));
                }
                else
                {
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo date é obrigatório.'
                    ]));
                }
            }
            catch (RecordNotFoundException $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(404)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Visita não encontrada.'
                    ]));

            } catch (InvalidArgumentException $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => $e->getMessage()
                    ]));

            } catch (\Exception $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Erro interno no servidor: ' . $e->getMessage()
                    ]));
            }
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if ($this->request->is('post')) 
        {
            $connection = ConnectionManager::get('default');

            $connection->begin();
            try
            {

                $visitsEntity = $this->Visits->newEmptyEntity();
                $data = $this->request->getData();

                if(!isset($data['address']) || !isset($data['visits']) )
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Os campos "visits" e "address" são obrigatórios.'
                    ]));
                }
                elseif(!isset($data['visits']))
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo "visits" é obrigatório.'
                    ]));
                }
                elseif(!isset($data['address']))
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo "address" é obrigatório.'
                    ]));
                }
                elseif(!isset($data['visits']['date']) || !isset($data['visits']['forms']) || !isset($data['visits']['products']))
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Os campos "date", "forms" e "products" do "address" são obrigatórios.'
                    ]));
                }
                elseif((isset($data['visits']['completed']) && $data['visits']['completed'] !== 0 && $data['visits']['completed'] !== 1))
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo "completed" poderá ser preenchido apenas com 1(true) e 0(false).'
                    ]));
                }
                elseif(!isset($data['address']['postal_code']) || empty($data['address']['postal_code']) )
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo "postal_code" do "address" é obrigatório.'
                    ]));
                }
                
                $data['visits']['duration'] = $this->getDuration($data['visits']['forms'], $data['visits']['products']);

                if($data['visits']['duration'] > 480)
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => "Limite de horas atingido"
                    ])); 
                }

                $entity = $this->Visits->patchEntity($visitsEntity, $data['visits']);

                if ($this->Visits->save($entity)) {
                    
                    $data['address']['foreign_table'] = 'visits';
                    $data['address']['foreign_id'] = $entity->id;
                    $addressesController = new AddressesController(
                        (new ServerRequest())
                            ->withMethod('POST')
                            ->withParsedBody($data['address']),
                        new Response()
                    );
                    
                    $workdaysControllerGet = new WorkdaysController(
                        (new ServerRequest())
                        ->withParsedBody(['date' => $data['visits']['date']]),
                        new Response()
                    );

                    $responseWorkdays = $workdaysControllerGet->viewByDate($data['visits']['date']);
                    $responseWorkdays->getBody()->rewind();
                    $responseWorkdaysData = json_decode($responseWorkdays->getBody()->getContents(), true);
                    
                    if(sizeof($responseWorkdaysData['data']) === 0)
                    {
                        $data['workdays']['date'] = $data['visits']['date'];
                        $data['workdays']['visits'] = 1;
                        $data['workdays']['completed'] = $data['visits']['completed'] ?? 0;
                        $data['workdays']['duration'] = $data['visits']['duration'];

                        $workdaysControllerPost = new WorkdaysController(
                            (new ServerRequest())
                            ->withMethod('POST')
                            ->withParsedBody($data['workdays']),
                            new Response()
                        );
                        
                        $responseWorkdaysPost = $workdaysControllerPost->add();
                        
                    }
                    else
                    {
                        $visitasCompletas =  $this->Visits->find()
                        ->where(['date' => $data['visits']['date']])
                        ->where(['completed' => 1])
                        ->contain([])
                        ->count();

                        $visitas =  $this->Visits->find()
                        ->where(['date' => $data['visits']['date']])
                        ->contain([])
                        ->count();

                        $data['workdays']['id'] = $responseWorkdaysData['data'][0]['id'];
                        $data['workdays']['date'] = $data['visits']['date'];
                        $data['workdays']['visits'] = $visitas;
                        $data['workdays']['completed'] = $visitasCompletas;
                        $data['workdays']['duration'] = ($data['visits']['duration'] + $responseWorkdaysData['data'][0]['duration']);


                        if($data['workdays']['duration'] > 480)
                        {
                            $connection->rollback();
                            return $this->response->withType('application/json')
                            ->withStatus(400)
                            ->withStringBody(json_encode([
                                'error' => true,
                                'message' => "Limite de horas atingido"
                            ])); 
                        }

                        $workdaysControllerPut = new WorkdaysController(
                            (new ServerRequest())
                                ->withMethod('PUT')
                                ->withParsedBody($data['workdays']),
                            new Response()
                        );
                        
                        $id = $data['workdays']['id'] ?? null;
                        
                        $responseWorkdaysPut = $workdaysControllerPut->edit($id);
                    }

                    $responseAddresses = $addressesController->add();
                    $responseAddresses->getBody()->rewind();
                    $responseData = json_decode($responseAddresses->getBody()->getContents(), true);
                    
                    if (isset($responseData['error']) && $responseData['error']) {
                        $connection->rollback();
                        return $this->response->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'Erro ao salvar visita.',
                            'data' => $responseData['data']
                        ]));
                    }
                    else
                    {
                        $data['address'] = $responseData['data'];
                        $data['visits'] = $entity;

                        $connection->commit();
                        return $this->response->withType('application/json')
                        ->withStatus(201)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $data
                        ])); 
                    }
                    
                } else {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'Erro ao salvar o visita.',
                            'data' => $entity->getErrors()
                        ]));
                }

            }
            catch (InvalidArgumentException $e) 
            {
                $connection->rollback();
                return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => $e->getMessage()
                    ]));
    
            } 
            catch (\Exception $e) 
            {
                $connection->rollback();
                return $this->response->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Erro interno no servidor: ' . $e->getMessage()
                    ]));
            }

        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Address id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        
        if ($this->request->is(['patch', 'put'])) {
        
            $connection = ConnectionManager::get('default');

            $connection->begin();

            try 
            {
                $data = $this->request->getData();

                if (!$id) {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'ID é obrigatório.'
                        ]));
                }

                $visitsOld = $this->Visits->get($id, [
                    'contain' => ['Addresses', 'Workdays'],
                ]);
            
                if(isset($data['address']) && isset($visitsOld->toArray()['address']) && array_diff_assoc($data['address'], $visitsOld->toArray()['address']))
                {
                    $addressesController = new AddressesController(
                        (new ServerRequest())->withMethod('DELETE'),
                        new Response()
                    );
                    $addressesController->delete($visitsOld->toArray()['address']['id']);
        
                    $data['address']['foreign_table'] = 'visits';
                    $data['address']['foreign_id'] = $id;
                    $addressesControllerPost = new AddressesController(
                        (new ServerRequest())
                            ->withMethod('POST')
                            ->withParsedBody($data['address']),
                        new Response()
                    );
                    
                    $responseAddresses = $addressesControllerPost->add();
                    $responseAddresses->getBody()->rewind();
                    $responseBody = json_decode($responseAddresses->getBody()->getContents(), true);
                    if (isset($responseBody['error']) && $responseBody['error']) {
                        $connection->rollback();
                        return $this->response->withType('application/json')
                            ->withStatus(400)
                            ->withStringBody(json_encode([
                                'error' => true,
                                'message' => $responseBody['message']
                            ]));
                    }
                    else
                    {
                        $data['address'] = $responseBody['data'];
                    }
                }
                else
                {
                    $data['address'] = $visitsOld->toArray()['address'];
                }
                
                $responseWorkdaysOldData = $visitsOld->toArray()['workday'];

                if($visitsOld->toArray()['date'] !== $data['visits']['date'])
                {
                    $visitasCompletas =  $this->Visits->find()
                    ->where(['date' => $visitsOld->toArray()['date']])
                    ->where(['completed' => 1])
                    ->contain([])
                    ->count();

                    $visitas =  $this->Visits->find()
                    ->where(['date' => $visitsOld->toArray()['date']])
                    ->contain([])
                    ->count();

                    $data['workdays']['id'] = $responseWorkdaysOldData['id'];
                    $data['workdays']['date'] = $visitsOld->toArray()['date'];
                    $data['workdays']['visits'] = $visitas-1;
                    $data['workdays']['completed'] = $visitsOld->toArray()['completed'] === 1 ? ($visitasCompletas - 1) : $visitasCompletas;
                    $data['workdays']['duration'] = ($responseWorkdaysOldData['duration'] - $visitsOld->toArray()['duration']);

                    $workdaysControllerOldPut = new WorkdaysController(
                        (new ServerRequest())
                            ->withMethod('PUT')
                            ->withParsedBody($data['workdays']),
                        new Response()
                    );
                    
                    $id = $data['workdays']['id'] ?? null;
                    
                    $responseWorkdaysOldPut = $workdaysControllerOldPut->edit($id);
                }
                
                $data['visits']['duration'] = $this->getDuration($data['visits']['forms'], $data['visits']['products']);

                
                if($data['visits']['duration'] > 480 )
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => "Limite de horas atingido"
                    ])); 
                }
                if(($visitsOld->toArray()['duration'] - $responseWorkdaysOldData['duration'] ) + $data['visits']['duration'] > 480)
                {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => "Limite de horas atingido"
                    ])); 
                }

                $visits = $this->Visits->patchEntity($visitsOld, $data['visits']);
                if ($this->Visits->save($visits)) {

                    $workdaysControllerGet = new WorkdaysController(
                        (new ServerRequest())
                        ->withParsedBody(['date' => $data['visits']['date']]),
                        new Response()
                    );
                    
                    $responseWorkdays = $workdaysControllerGet->viewByDate();
                    $responseWorkdays->getBody()->rewind();
                    $responseWorkdaysData = json_decode($responseWorkdays->getBody()->getContents(), true);
                    
                    if(sizeof($responseWorkdaysData['data']) === 0)
                    {
                        $data['workdays']['date'] = $data['visits']['date'];
                        $data['workdays']['visits'] = 1;
                        $data['workdays']['completed'] = $data['visits']['completed'] ?? 0;
                        $data['workdays']['duration'] = $data['visits']['duration'];

                        $workdaysControllerPost = new WorkdaysController(
                            (new ServerRequest())
                            ->withMethod('POST')
                            ->withParsedBody($data['workdays']),
                            new Response()
                        );
                        
                        $responseWorkdaysPost = $workdaysControllerPost->add();
                        
                    }
                    else
                    {
                        $visitasCompletas =  $this->Visits->find()
                        ->where(['date' => $data['visits']['date']])
                        ->where(['completed' => 1])
                        ->contain([])
                        ->count();

                        $visitas =  $this->Visits->find()
                        ->where(['date' => $data['visits']['date']])
                        ->contain([])
                        ->count();

                        $data['workdays']['id'] = $responseWorkdaysData['data'][0]['id'];
                        $data['workdays']['date'] = $data['visits']['date'];
                        $data['workdays']['visits'] = $visitas;
                        $data['workdays']['completed'] = $visitasCompletas;
                        $data['workdays']['duration'] = ($data['visits']['duration'] + $responseWorkdaysData['data'][0]['duration']);
                      
                        if($data['workdays']['duration'] > 480)
                        {
                            $connection->rollback();
                            return $this->response->withType('application/json')
                            ->withStatus(400)
                            ->withStringBody(json_encode([
                                'error' => true,
                                'message' => "Limite de horas atingido"
                            ])); 
                        }

                        $workdaysControllerPut = new WorkdaysController(
                            (new ServerRequest())
                                ->withMethod('PUT')
                                ->withParsedBody($data['workdays']),
                            new Response()
                        );
                        
                        $id = $data['workdays']['id'] ?? null;
                        
                        $responseWorkdaysPut = $workdaysControllerPut->edit($id);
                    }
                    
                    $connection->commit();
                    return $this->response->withType('application/json')
                        ->withStatus(201)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $data
                        ]));
                } else {
                    $connection->rollback();
                    return $this->response->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'Erro ao atualizar o visita.',
                            'data' => $visits->getErrors()
                        ]));
                }
            } catch (RecordNotFoundException $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(404)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Visita não encontrada.'
                    ]));
    
            } catch (InvalidArgumentException $e) 
            {
                $connection->rollback();
                return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => $e->getMessage()
                    ]));
    
            } catch (\Exception $e) 
            {
                $connection->rollback();
                return $this->response->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Erro interno no servidor: ' . $e->getMessage()
                    ]));
            }
        }
        
    }

    /**
     * Delete method
     *
     * @param string|null $id Address id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        if ($this->request->is(['delete'])) 
        {
            try
            {

                $visits = $this->Visits->get($id);

                if ($this->Visits->delete($visits)) {
                    return $this->response->withType('application/json')
                    ->withStatus(200)
                    ->withStringBody(json_encode([
                        'success' => true,
                        'data' => 'Visita removido.'
                    ]));
                } else {
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Erro ao deletar o visita.',
                        'data' => $visits->getErrors()
                    ]));
                }
            
            }
            catch (RecordNotFoundException $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(404)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Visita não encontrado.'
                    ]));
    
            }
            catch (InvalidArgumentException $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => $e->getMessage()
                    ]));
    
            } 
            catch (\Exception $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Erro interno no servidor: ' . $e->getMessage()
                    ]));
            }
        }
    }

}   
