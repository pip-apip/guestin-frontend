<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

new #[Layout('components.layouts.app-landing')] class extends Component {
    public array $scanResult;
    public $slug;
    public $event;

    public function mount(){
        $response = Http::get(env('API_BASE_URL') . '/events/event-guests-check-in/'.$this->slug)->json();

        $guests = collect($response['data']['guests'] ?? []);

        $guests->filter(function($guest){
            return isset($guest['status']) && $guest['status'] === 'checked_in';
        })->each(function($guest){
            $this->scanResult[] = $guest;
        });

        $this->event = $response['data']['event'] ?? [];

    }


    #[On('checkin')]
    public function checkIn($code)
    {
        $response = Http::post(env('API_BASE_URL') . "/guests/check-in/1", [
            'code' => $code,
        ])->json();
        if ($response['error'] === null) {
            $this->scanResult[] = $response['data'];
            $this->dispatch('qr-success', [
                'message' => 'Check-in successful.'
            ]);
            Toaster::success('Check-in successful.');
        } else {
            $this->dispatch('qr-error', [
            'message' => $response['error'] ?? 'Check-in failed.'
             ]);
            Toaster::error($response['error'] ?? 'Check-in failed.');
        }
        return;
    }
};
?>

<div class="w-full">
    <div class="flex gap-8">
        <div class="flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-5 lg:p-8 rounded-xl shadow-xl w-full h-full max-w-xl aspect-square">
            <h1 class="text-2xl font-bold text-zinc-800 dark:text-zinc-100 mb-2 text-center">
                Scan Your QR Code
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-4 text-center">
                Turn on the switch to activate the scanner. Align the QR code inside the box.
            </p>

            <div id="qr-reader-container" wire:ignore>
                <div id="qr-reader" style="transform: scaleX(-1);" class="w-full h-auto rounded-lg overflow-hidden border border-dashed border-zinc-400 dark:border-zinc-600 mb-4">
                </div>
            </div>

            <div class="mt-4 text-green-600 dark:text-green-400 font-mono text-sm text-center">
            </div>
        </div>

        <div class="flex flex-col relative overflow-auto max-h-[575px] bg-white dark:bg-zinc-900 p-5 lg:p-8 rounded-xl shadow-xl w-full aspect-square">
            <h1 class="text-2xl font-bold text-zinc-800 dark:text-zinc-100 mb-1">
                {{ $this->event['name'] }}
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-4">
                {{ $this->event['description'] }}
            </p>
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="sticky top-0 z-10 text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Phone</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Check In</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($scanResult as $data)
                    <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $data['name'] }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $data['phone'] }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $data['status'] ? 'Checked In' : 'Not Checked In' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ date_format(new DateTime($data['check_in_time']), 'H:i:s')}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <style>
        #qr-reader video {
            width: 100% !important;
            height: auto !important;
            object-fit: cover !important;
            display: block !important;
            background: black;
        }

        #qr-reader div[style*="background: rgba(9, 9, 9"] {
            display: none !important;
        }

    </style>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrCode;
    let isScannerRunning = false;
    let lastScannedCode = "";
    let lastScannedTime = 0;
    let isProcessing = false;

    function startScanner() {
        const qrElement = document.getElementById("qr-reader");
        if (!qrElement) {
            console.warn("❌ #qr-reader belum siap.");
            return;
        }

        if (isScannerRunning) {
            console.log("Scanner sudah aktif.");
            return;
        }

        html5QrCode = new Html5Qrcode("qr-reader");

        Html5Qrcode.getCameras()
            .then((cameras) => {
                if (!cameras.length) {
                    console.error("Tidak ada kamera terdeteksi.");
                    return;
                }

                const cameraId = cameras[0].id;
                html5QrCode
                    .start(
                        cameraId, {
                            fps: 10
                            , qrbox: (vw, vh) => {
                                let minEdge = Math.min(vw, vh);
                                let size = Math.max(minEdge * 0.7, 150);
                                return {
                                    width: size
                                    , height: size,

                                };
                            }
                        , }
                        , (decodedText) => {
                            if (isProcessing) {
                                console.log("⏳ Masih memproses scan sebelumnya...");
                                return;
                            }
                            const now = Date.now();
                            if (decodedText === lastScannedCode && now - lastScannedTime < 3000) return;

                            isProcessing = true;
                            lastScannedCode = decodedText;
                            lastScannedTime = now;
                            html5QrCode.pause(true);
                            console.log("✅ Scanned:", decodedText);
                            if (window.Livewire) Livewire.dispatch("checkin", {
                                code: decodedText
                            });

                        }
                        , (errorMessage) => {}
                    )
                    .then(() => {
                        isScannerRunning = true;
                        console.log("🎥 Scanner dimulai");
                    })
                    .catch((err) => console.error("Gagal memulai kamera:", err));
            })
            .catch((err) => {
                console.error("Kesalahan akses kamera:", err);
            });
    }

    document.addEventListener("livewire:navigated", () => {
        setTimeout(startScanner, 300);
    });

    window.addEventListener("beforeunload", () => {
        if (isScannerRunning && html5QrCode) {
            // html5QrCode.stop().then(() => console.log("Scanner stopped on unload"));
        }
    });

    function setQrBoxColor(color) {
        const qrShaded = document.querySelector("#qr-shaded-region");
        if (qrShaded) {
            const corners = qrShaded.querySelectorAll("div");
            corners.forEach(corner => {
                corner.style.backgroundColor = color || "rgb(255, 255, 255)";
            });
        }
    }

    document.addEventListener('livewire:initialized', event => {
        Livewire.on('qr-error', (event) => {
            const qrRegion = document.querySelector("#qr-reader > video")?.parentElement;
            if (qrRegion) {
                qrRegion.style.border = "3px solid #ef4444"; // merah Tailwind
                // clearTimeout(qrRegion._resetTimeout);
                qrRegion._resetTimeout = setTimeout(() => {
                    qrRegion.style.border = "";
                }, 1000);
            }
            setQrBoxColor("#ef4444");
            setTimeout(() => {
                // Reset border container
                if (qrRegion) {
                    qrRegion.style.border = "";
                }
                if (html5QrCode && isScannerRunning) {
                    html5QrCode.resume();
                }
                setQrBoxColor("");

                isProcessing = false;
            }, 1000);
        });
        Livewire.on('qr-success', (event) => {
            const qrRegion = document.querySelector("#qr-reader > video")?.parentElement;
            if (qrRegion) {
                qrRegion.style.border = "3px solid #22c55e";
                qrRegion._resetTimeout = setTimeout(() => {
                    qrRegion.style.border = "";
                }, 1000);
            }
            setQrBoxColor("#22c55e");
            setTimeout(() => {
                if (qrRegion) {
                    qrRegion.style.border = "";
                }
                if (html5QrCode && isScannerRunning) {
                    html5QrCode.resume();
                }
                setQrBoxColor("");

                isProcessing = false;
                console.log("🔓 Scanner unlocked (error)");
            }, 1000);
        });
    });

</script>
@endpush
