<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TodoList extends Component
{
    // pagination
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name;
    
    public $search;
    
    public $editingTodoID;
    
    #[Rule('required|min:3|max:50')]
    public $editingTodoName;

    public function create(){
        // validate
        $validated = $this->validateOnly('name');
        
        // create the todo
        Todo::create($validated);
        
        // clear the input
        $this->reset('name');
        
        // send flash message
        session()->flash('success', 'Created.');

        $this->resetPage();
    }

 

    public function delete($todoID){
        try {
            Todo::findOrFail($todoID)->delete();
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Todo not found!');
            return;
        } catch (Exception $e) {
            session()->flash('error', 'Failed to delete todo!');
            return;
        }
    }

    public function toggle(Todo $todo){
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit($todoID){
        $this->editingTodoID = $todoID;
        if(isset(Todo::find($todoID)->name)){
            $this->editingTodoName = Todo::find($todoID)->name;
        }
    }

    public function cancelEdit(){
        $this->reset('editingTodoID', 'editingTodoName');
    }

    public function update(){
        $this->validateOnly('editingTodoName');
        Todo::find($this->editingTodoID)->update([
            'name' => $this->editingTodoName,
        ]);

        $this->cancelEdit();
    }

       // fix search problem, when pagination not in number 1
    public function updatedSearch(){
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.todo-list', [
            // show todos with pagination
            // 'todos' => Todo::latest()->paginate(5),

            'todos' => Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(5),
        ]);
    }
}