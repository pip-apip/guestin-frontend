<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;

new #[Layout('components.layouts.app-landing')] class extends Component {
    public string $scanResult = '';

    #[On('checkin')]
    public function checkIn($code)
    {
        $baseUrl = env('API_BASE_URL');
        $response = Http::post("$baseUrl/guests/check-in/1", [
            'code' => $code,
        ]);

        if ($response->successful()) {
            $this->scanResult = $response->json('data.name');
        } else {
            $this->scanResult = '❌ Check-in failed.';
        }
        return;
    }
};
?>

<div class="w-full max-w-7xl">
    <div class="grid grid-cols-2 gap-4">
        <div
            class="flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-5 lg:p-8 rounded-xl shadow-xl w-full h-full max-w-xl aspect-square">
            <h1 class="text-2xl font-bold text-zinc-800 dark:text-zinc-100 mb-2 text-center">
                Scan Your QR Code
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-4 text-center">
                Turn on the switch to activate the scanner. Align the QR code inside the box.
            </p>

            <div id="qr-reader-container" wire:ignore>
                <div id="qr-reader"
                    class="w-full h-auto rounded-lg overflow-hidden border border-dashed border-zinc-400 dark:border-zinc-600 mb-4">
                </div>
            </div>

            <div class="mt-4 text-green-600 dark:text-green-400 font-mono text-sm text-center">
                {{ $scanResult }}
            </div>
        </div>

        <div
            class="flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-5 lg:p-8 rounded-xl shadow-xl w-full max-w-xl aspect-square">
            <!-- Optional: additional content -->
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrCode;
        let lastScannedCode = "";
        let lastScannedTime = 0;
        let isScannerRunning = false;

        function startScanner() {
            if (isScannerRunning) return;

            const qrElement = document.getElementById("qr-reader");
            if (!qrElement) {
                console.warn("QR reader DOM not found");
                return;
            }

            html5QrCode = new Html5Qrcode("qr-reader");

            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length) {
                    const cameraId = cameras[0].id;

                    html5QrCode.start(
                        cameraId, {
                            fps: 10,
                            qrbox: 250
                        },
                        qrCodeMessage => {
                            const now = Date.now();
                            if (qrCodeMessage === lastScannedCode && now - lastScannedTime < 3000) return;

                            lastScannedCode = qrCodeMessage;
                            lastScannedTime = now;

                            console.log("Scanned:", qrCodeMessage);

                            // 🔥 Fire Livewire event
                            Livewire.dispatch('checkin', {
                                code: qrCodeMessage
                            });

                            // 🛑 Stop scanner safely
                            html5QrCode.stop().then(() => {
                                isScannerRunning = false;
                                console.log("Scanner stopped.");

                                // ✅ Restart after short delay
                                setTimeout(() => {
                                    startScanner();
                                }, 3000);
                            }).catch(err => {
                                console.error("Stop scanner error:", err);
                            });
                        },
                        errorMessage => {
                            // Optional: handle scan error
                        }
                    ).then(() => {
                        isScannerRunning = true;
                    }).catch(err => {
                        console.error("Scanner start error:", err);
                    });
                }
            }).catch(err => {
                console.error("Camera error:", err);
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            startScanner();
        });
    </script>
@endpush
