<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Client;

/**
 * Addresses Controller
 *
 * @method \App\Model\Entity\Address[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AddressesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $addresses = $this->paginate($this->Addresses);

        return $this->response->withType('application/json')
        ->withStatus(201)
        ->withStringBody(json_encode([
            'success' => true,
            'data' => $addresses
        ]));
    }

    /**
     * View method
     *
     * @param string|null $id Address id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        try
        {
            $address = $this->Addresses->get($id, [
                'contain' => [],
            ]);

            return $this->response->withType('application/json')
            ->withStatus(201)
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $address
            ]));
        }
        catch (Exception $e) 
        {
            throw new \BadRequestException($e->getMessage());
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if ($this->request->is('post')) {
            
            $addressEntity = $this->Addresses->newEmptyEntity();
            $data = $this->request->getData();

            
            if(!isset($data['postal_code']))
            {
                return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'error' => true,
                    'message' => 'O campo postal_code é obrigatório.'
                ]));
            }
            else if($data['postal_code'] === '')
            {
                return $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'error' => true,
                    'message' => 'O campo postal_code está vazio.'
                ]));
            }

            $postal_code = $data['postal_code'];

            try
            {
                $http = new Client();
    
                $responseRepublica = $http->get("https://republicavirtual.com.br/web_cep.php?cep=$postal_code&formato=json")->getJson();
                
                if (isset($responseRepublica['resultado']) && $responseRepublica['resultado'] == 1) 
                {
                    $data['state'] = $responseRepublica['uf'];
                    $data['city'] = $responseRepublica['cidade'];

                    if (empty($data['street'])) {
                        $data['street'] = ($responseRepublica['tipo_logradouro'] . " " . $responseRepublica['logradouro']) ?? null;
                    }

                    if (empty($data['sublocality'])) {
                        $data['sublocality'] = $responseRepublica['bairro'] ?? null;
                    }
    
                }
                else
                {
                    $http = new Client();
                    
                    $responseVia = $http->get("https://viacep.com.br/ws/{$postal_code}/json")->getJson();
                    if (isset($responseVia['erro']) && !$responseVia['erro']) {
                        $data['state'] = $data['state'] ?? $responseVia['estado'];
                        $data['city'] = $data['city'] ?? $responseVia['localidade'];

                        if (empty($data['street'])) {
                            $data['street'] = $responseVia['logradouro'] ?? null;
                        }

                        if (empty($data['sublocality'])) {
                            $data['sublocality'] = $responseVia['bairro'] ?? null;
                        }
                    } else {
                        return $this->response->withType('application/json')
                        ->withStatus(404)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'CEP não encontrado.'
                        ]));
                    }
                }
                
                $entity = $this->Addresses->patchEntity($addressEntity, $data);

                if ($this->Addresses->save($entity)) {
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
                            'message' => 'Erro ao salvar o endereço.',
                            'data' => $entity->getErrors()
                        ]));
                }
            }
            catch (Exception $e) 
            {
                throw new \BadRequestException($e->getMessage());
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

            $addressOld = $this->Addresses->get($id, [
                'contain' => [],
            ]);
        
            $data = $this->request->getData();
        
            if (!isset($data['postal_code'])) {
                return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo postal_code é obrigatório.'
                    ]));
            }
        
            if (empty($data['postal_code'])) {
                return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => 'O campo postal_code está vazio.'
                    ]));
            }
        
            $postal_code = $data['postal_code'];
        
            try {
                $http = new Client();
        
                $responseRepublica = $http->get("https://republicavirtual.com.br/web_cep.php?cep=$postal_code&formato=json")->getJson();
        
                if (isset($responseRepublica['resultado']) && $responseRepublica['resultado'] == 1) {
                    $data['state'] = $addressOld->state ?? $responseRepublica['uf'];
                    $data['city'] = $addressOld->city ?? $responseRepublica['cidade'];
        
                    if (empty($data['street'])) {
                        $data['street'] = ($responseRepublica['tipo_logradouro'] . " " . $responseRepublica['logradouro']) ?? null;
                    }
        
                    if (empty($data['sublocality'])) {
                        $data['sublocality'] = $responseRepublica['bairro'] ?? null;
                    }
                } else {
                    $responseVia = $http->get("https://viacep.com.br/ws/{$postal_code}/json")->getJson();
        
                    if (!isset($responseVia['erro']) || !$responseVia['erro']) {
                        $data['state'] = $addressOld->state ?? $responseVia['uf'];
                        $data['city'] = $addressOld->city ?? $responseVia['localidade'];
        
                        if (empty($data['street'])) {
                            $data['street'] = $responseVia['logradouro'] ?? null;
                        }
        
                        if (empty($data['sublocality'])) {
                            $data['sublocality'] = $responseVia['bairro'] ?? null;
                        }
                    } else {
                        return $this->response->withType('application/json')
                            ->withStatus(404)
                            ->withStringBody(json_encode([
                                'error' => true,
                                'message' => 'CEP não encontrado.'
                            ]));
                    }
                }
        
                $address = $this->Addresses->patchEntity($addressOld, $data);
        
                if ($this->Addresses->save($address)) {
                    return $this->response->withType('application/json')
                        ->withStatus(200)
                        ->withStringBody(json_encode([
                            'success' => true,
                            'data' => $address
                        ]));
                } else {
                    return $this->response->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'error' => true,
                            'message' => 'Erro ao atualizar o endereço.',
                            'data' => $address->getErrors()
                        ]));
                }
            } catch (\Exception $e) {
                return $this->response->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode([
                        'error' => true,
                        'message' => $e->getMessage()
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
        $this->request->allowMethod(['post', 'delete']);
        $address = $this->Addresses->get($id);
        if ($this->Addresses->delete($address)) {
            $this->Flash->success(__('The address has been deleted.'));
        } else {
            $this->Flash->error(__('The address could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function getCsrfToken()
    {
        $this->request->allowMethod(['get']);
    
        $token = $this->request->getAttribute('csrfToken');
    
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['csrfToken' => $token]));
    }
}