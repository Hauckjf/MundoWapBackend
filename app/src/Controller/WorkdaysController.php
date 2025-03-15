<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Date;

/**
 * Workdays Controller
 *
 * @method \App\Model\Entity\Workday[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class WorkdaysController extends AppController
{
     /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $workdays = $this->paginate($this->Workdays);

        return $this->response->withType('application/json')
        ->withStatus(201)
        ->withStringBody(json_encode([
            'success' => true,
            'data' => $workdays
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
                
                $workdays = $this->Workdays->get($id, [
                    'contain' => [],
                ]);

                return $this->response->withType('application/json')
                ->withStatus(201)
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $workdays
                ]));
            }
            else
            {
                if(!isset($id))
                {
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo id é obrigatório.'
                    ]));
                }
            }
        }
        catch (RecordNotFoundException $e) 
        {
            return $this->response->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode([
                    'error' => true,
                    'message' => 'Dia útil não encontrado.'
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
                $workdays = $this->Workdays->find()
                    ->where(['date' => $data['date']])
                    ->contain([])
                    ->toArray();

                return $this->response->withType('application/json')
                    ->withStatus(200)
                    ->withStringBody(json_encode([
                        'success' => true,
                        'data' => $workdays
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
                    'message' => 'Dia útil não encontrado.'
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

                $workdaysEntity = $this->Workdays->newEmptyEntity();
                $data = $this->request->getData();

                $entity = $this->Workdays->patchEntity($workdaysEntity, $data);

                if ($this->Workdays->save($entity)) {
                    return $this->response->withType('application/json')
                        ->withStatus(201)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $data
                        ]));
                } else {
                    return $this->response->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'Erro ao salvar o dia útil.',
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

                $workdayOld = $this->Workdays->get($id, [
                    'contain' => [],
                ]);
            
                $data = $this->request->getData();
            
                $workdays = $this->Workdays->patchEntity($workdayOld, $data);
        
                if ($this->Workdays->save($workdays)) {
                    return $this->response->withType('application/json')
                        ->withStatus(200)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $workdays
                        ]));
                } else {
                    return $this->response->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'Erro ao atualizar o dia útil.',
                            'data' => $workdays->getErrors()
                        ]));
                }
            } catch (RecordNotFoundException $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(404)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Dia útil não encontrado.'
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

                $workdays = $this->Workdays->get($id);

                if ($this->Workdays->delete($workdays)) {
                    return $this->response->withType('application/json')
                    ->withStatus(200)
                    ->withStringBody(json_encode([
                        'success' => true,
                        'data' => 'Dia útil removido.'
                    ]));
                } else {
                    return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Erro ao deletar o dia útil.',
                        'data' => $workdays->getErrors()
                    ]));
                }
            
            }
            catch (RecordNotFoundException $e) 
            {
                return $this->response->withType('application/json')
                    ->withStatus(404)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'Dia útil não encontrado.'
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

    public function close()
    {
        try
        {

            $data = $this->request->getData();


            $step = 1;

            if(isset($data['date']))
            {
                $visitsController = new VisitsController(
                    (new ServerRequest())
                    ->withMethod('POST')
                    ->withParsedBody(['date' => $data['date']]),
                    new Response()
                );
                
                $responseVisits = $visitsController->viewByDate();
                
                $responseVisits->getBody()->rewind();
                $responseData = json_decode($responseVisits->getBody()->getContents(), true);

                foreach($responseData['data'] as $visits)
                {
                    if(!$visits['completed'])
                    {
                        $newDate = new Date($visits['date']);
                        
                        $newDate = $newDate->addDay($step);

                        $body['visits'] = $visits;
                        $body['visits']['date'] = $newDate->format('Y-m-d');
                        $visitsController = new VisitsController(
                            (new ServerRequest())
                            ->withMethod('PUT')
                            ->withParsedBody($body),
                            new Response()
                        );

                        $responseVisits = $visitsController->edit($visits['id']); 
                        $responseVisits->getBody()->rewind();
                        $responseEdit = json_decode($responseVisits->getBody()->getContents(), true);
                        while(isset($responseEdit['message']) && $responseEdit['message'] === 'Limite de horas atingido')
                        {
                            $step++;
                            $newDate = new Date($visits['date']);
                        
                            $newDate = $newDate->addDay($step);
    
                            $body['visits'] = $visits;
                            $body['visits']['date'] = $newDate->format('Y-m-d');
                            $visitsController = new VisitsController(
                                (new ServerRequest())
                                ->withMethod('PUT')
                                ->withParsedBody($body),
                                new Response()
                            );

                            $responseVisits = $visitsController->edit($visits['id']); 
                            $responseVisits->getBody()->rewind();
                            $responseEdit = json_decode($responseVisits->getBody()->getContents(), true);
                        }
                        $return[$visits['id']] = $responseEdit;
                    }
                }

                return $this->response->withType('application/json')
                    ->withStatus(200)
                    ->withStringBody(json_encode([
                        'success' => true,
                        'data' => $return
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
                    'message' => 'Dia útil não encontrado.'
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
