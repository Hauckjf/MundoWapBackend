<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AddressesController;
use App\Controller\WorkdaysController;
use Cake\Http\ServerRequest;
use Cake\Http\Response;

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
        try
        {

            $data = $this->request->getData();
            
            if(isset($data['date']))
            {
                $visits = $this->Visits->find()
                    ->where(['date' => $date])
                    ->contain(['Addresses'])
                    ->toArray();

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

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if ($this->request->is('post')) 
        {
            
            try
            {

                $visitsEntity = $this->Visits->newEmptyEntity();
                $data = $this->request->getData();

                if(!isset($data['visits']['date']) || !isset($data['visits']['status']) || !isset($data['visits']['forms']) || !isset($data['visits']['products']) || !isset($data['address']))
                {
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Os campos "date", "status", "forms", "products" e "address" são obrigatórios.'
                    ]));
                }
                elseif(empty($data['visits']['date']) || empty($data['visits']['forms']) || empty($data['visits']['products']) || empty($data['address']))
                {
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Os campos "date", "status", "forms", "products" e "address" não podem estar vazios.'
                    ]));
                }

                $data['visits']['duration'] = $this->getDuration($data['visits']['forms'], $data['visits']['products']);

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
                        ->toArray();

                        $visitas =  $this->Visits->find()
                        ->where(['date' => $data['visits']['date']])
                        ->contain([])
                        ->toArray();

                        $data['workdays']['id'] = $responseWorkdaysData['data'][0]['id'];
                        $data['workdays']['date'] = $data['visits']['date'];
                        $data['workdays']['visits'] = sizeof($visitas);
                        $data['workdays']['completed'] = sizeof($visitasCompletas);
                        $data['workdays']['duration'] = ($data['visits']['duration'] + $responseWorkdaysData['data'][0]['duration']);

                        $workdaysControllerPut = new WorkdaysController(
                            (new ServerRequest())
                                ->withMethod('PUT')
                                ->withParsedBody($data['workdays']),
                            new Response()
                        );
                        
                        $id = $data['workdays']['id'] ?? null;
                        
                        $responseWorkdaysPut = $workdaysControllerPut->edit($id);
                        $responseWorkdaysPut->getBody()->rewind();
                        
                        $responseWorkdaysPutData = json_decode($responseWorkdaysPut->getBody()->getContents(), true);
                    }

                    $responseAddresses = $addressesController->add();
                    $responseAddresses->getBody()->rewind();
                    $responseData = json_decode($responseAddresses->getBody()->getContents(), true);
                    
                    if (isset($responseData['error']) && $responseData['error']) {
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
                        return $this->response->withType('application/json')
                        ->withStatus(200)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $data
                        ])); 
                    }
                    
                } else {
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
        
            try {

                $visitsOld = $this->Visits->get($id, [
                    'contain' => [],
                ]);
            
                $data = $this->request->getData();
            
                $visits = $this->Visits->patchEntity($visitsOld, $data);
        
                if ($this->Visits->save($visits)) {
                    return $this->response->withType('application/json')
                        ->withStatus(200)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $visits
                        ]));
                } else {
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
