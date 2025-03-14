<?php
declare(strict_types=1);

namespace App\Controller;

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

                $addressOld = $this->Workdays->get($id, [
                    'contain' => [],
                ]);
            
                $data = $this->request->getData();
            
                $workdays = $this->Workdays->patchEntity($addressOld, $data);
        
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
}
