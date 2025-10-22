<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

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
            $response = Http::withToken("6|TEkkoRe7FFNsajYxqL0TFu25oD26JBeiHyh2VRyace4ec310")->get(env('API_BASE_URL') . '/guests');
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
            $response = Http::withToken("6|TEkkoRe7FFNsajYxqL0TFu25oD26JBeiHyh2VRyace4ec310")->post(env('API_BASE_URL') . '/guests', $this->guestData);
            $result = $response->json();

            if ($response->status() === 200) {
                session()->flash('success', $result['message'] ?? 'Guest added successfully.');
                $this->getData();
                $this->dispatch('close-modal');
            } else {
                \Log::error($result['message'] ?? 'Failed to add guest.');
                session()->flash('error', $result['message'] ?? 'Failed to add guest.');
                // Bring back the old value
                $this->guestData = array_merge($this->guestData, $this->guestData);
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            session()->flash('error', 'Failed to add guest: ' . $e->getMessage());
            // Bring back the old value
            $this->guestData = array_merge($this->guestData, $this->guestData);
        }
    }

    public function render()
    {
        return view('livewire.guest');
    }
}
