<?php

namespace App\Livewire;

use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Http;

class Event extends Component
{
    public $datas = [];
    public $addModal = true;

    public $editData = [
        'name' => '',
        'description' => '',
        'location' => '',
        'date' => '',
        'start_time' => '',
        'end_time' => '',
        'status' => '',
    ];

    public function mount()
    {
        $this->getData();
    }

    public function getData()
    {
        try {
            $response = Http::get(env('API_BASE_URL') . '/events');
            $result = $response->json();
            $this->datas = $result['data'] ?? [];
        } catch (\Exception $e) {
            $this->datas = [];
        }
    }

    public function edit($name)
    {
        // dd($name);
        $this->addModal = false;

        try {
            $response = Http::get(env('API_BASE_URL') . '/events/' . urlencode($name));
            $result = $response->json();
            $this->editData = $result['data'] ?? [];
        } catch (\Exception $e) {
            $this->editData = [];
        }

        // dd($this->editData);

        $this->dispatch('openEditModal');
    }

    #[On('resetAddModal')]
    public function resetAddModal()
    {
        $this->addModal = true;
        Flux::closeModal('form-event-modal');
    }

    public function render()
    {
        return view('livewire.event');
    }
}
