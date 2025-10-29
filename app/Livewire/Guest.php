<?php

namespace App\Livewire;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Masmerise\Toaster\Toaster;

class Guest extends Component
{
    public $datas = [];
    public $eventDatas = [];
    public $addModal = true;

    public $guestData = [
        'id' => '',
        'name' => '',
        'email' => '',
        'phone' => '',
        'organization' => '',
        'event_id' => '',
        'event_name' => '',
        'status' => '',
    ];

    public function mount()
    {
        $this->getData();
        $this->getEventData();
    }

    public function getData()
    {
        try {
            $response = Http::withToken(session('token'))->get(env('API_BASE_URL') . '/guests');
            $result = $response->json();
            $this->datas = $result['data'] ?? [];
        } catch (\Exception $e) {
            $this->datas = [];
            \Log::error($e->getMessage());
        }
    }

    public function getEventData()
    {
        try {
            $response = Http::get(env('API_BASE_URL') . '/events');
            $result = $response->json();
            $this->eventDatas = $result['data'] ?? [];
        } catch (\Exception $e) {
            $this->eventDatas = [];
            \Log::error($e->getMessage());
        }
    }

    public function save()
    {
        if ($this->addModal) {
            $this->guestData['status'] = 'invited';
        }
        try {
            $response = Http::withToken(session('token'))->post(env('API_BASE_URL') . '/guests', $this->guestData)->json();
            session()->flash('success', $response['message'] ?? 'Guest added successfully.');


            if($response['message']=== 'success'){
                // $this->getData();
                Flux::modals()->close();
                $this->guestData = [
                    'id' => '',
                    'name' => '',
                    'email' => '',
                    'phone' => '',
                    'organization' => '',
                    'event_id' => '',
                    'event_name' => '',
                    'status' => '',
                ];
                Toaster::success( 'Guest added successfully.');
            }else {
                throw new \Exception( $response['error'] ?? 'Failed to add guest.');
            }
        }catch (\Exception $e) {
            Toaster::error( $e->getMessage() ?? 'Failed to add guest.');
        }
    }

    public function render()
    {
        return view('livewire.guest');
    }
}
