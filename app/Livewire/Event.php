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
    public $searchQuery = '';
    public $searchStatus = '';
    public $filterSort = 'name';
    public $filterOrder = 'asc';

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

    public function updatedSearchQuery()
    {
        $this->filter();
    }

    public function updatedSearchStatus()
    {
        $this->filter();
    }

    public function updatedFilterSort()
    {
        $this->filter();
    }

    public function updatedFilterOrder()
    {
        $this->filter();
    }

    public function filter()
    {
        $search = '';
        if(!empty($this->searchQuery)){
            $search = $this->searchQuery;
        }else if(!empty($this->searchStatus)){
            $search = $this->searchStatus;
        }
        $params = [
            'search' => $search,
            'sort_by' => $this->filterSort,
            'order' => $this->filterOrder,
        ];

        try {
            $response = Http::get(env('API_BASE_URL') . '/events', $params);
            $result = $response->json();
            $this->datas = $result['data'] ?? [];
        } catch (\Exception $e) {
            $this->datas = [];
        }
    }

    public function render()
    {
        return view('livewire.event');
    }
}
