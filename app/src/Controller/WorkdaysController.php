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

        $this->set(compact('workdays'));
    }

    /**
     * View method
     *
     * @param string|null $id Workday id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $workday = $this->Workdays->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('workday'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $workday = $this->Workdays->newEmptyEntity();
        if ($this->request->is('post')) {
            $workday = $this->Workdays->patchEntity($workday, $this->request->getData());
            if ($this->Workdays->save($workday)) {
                $this->Flash->success(__('The workday has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The workday could not be saved. Please, try again.'));
        }
        $this->set(compact('workday'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Workday id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $workday = $this->Workdays->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $workday = $this->Workdays->patchEntity($workday, $this->request->getData());
            if ($this->Workdays->save($workday)) {
                $this->Flash->success(__('The workday has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The workday could not be saved. Please, try again.'));
        }
        $this->set(compact('workday'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Workday id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $workday = $this->Workdays->get($id);
        if ($this->Workdays->delete($workday)) {
            $this->Flash->success(__('The workday has been deleted.'));
        } else {
            $this->Flash->error(__('The workday could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
