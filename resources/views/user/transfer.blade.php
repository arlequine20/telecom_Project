@extends('layouts.app')

@section('title', 'Send Money')

@section('sidebar')
    <a class="nav-link" href="{{ route('user.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link active" href="{{ route('user.transfer') }}">
        <i class="fas fa-exchange-alt"></i> Send Money
    </a>
    <a class="nav-link" href="{{ route('user.history') }}">
        <i class="fas fa-history"></i> Transaction History
    </a>
    <a class="nav-link" href="{{ route('user.sims') }}">
        <i class="fas fa-sim-card"></i> My SIM Cards
    </a>
    <a class="nav-link" href="{{ route('user.profile') }}">
        <i class="fas fa-user"></i> My Profile
    </a>
@endsection

@section('header-title', 'Send Money')
@section('user-name', auth()->user()->name ?? 'User')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-4">Transfer funds from your SIM</h5>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('user.send') }}" id="transferForm">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="from_sim">From SIM</label>
                <select class="form-select" id="from_sim" name="from_sim" required>
                    <option value="">Select SIM</option>
                    @foreach($simCards as $sim)
                        <option value="{{ $sim->id }}">{{ $sim->phone_number }} ({{ $sim->status }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="to_phone">Recipient Phone</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="to_phone" name="to_phone" value="{{ old('to_phone', $phone ?? '') }}" required>
                    <button class="btn btn-outline-secondary" type="button" id="scanQrBtn">
                        <i class="fas fa-qrcode"></i> Scan QR
                    </button>
                </div>
                @if(isset($recipientError))
                    <div class="alert alert-warning mt-3">{{ $recipientError }}</div>
                @endif
                <small class="text-muted d-block mt-2">Scan a recipient QR code to auto-fill the phone and open the send-money form.</small>
                <div id="qrScannerPanel" class="border rounded p-3 mt-3" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Scan recipient QR code</strong>
                        <button class="btn btn-sm btn-outline-secondary" type="button" id="stopQrBtn">Close</button>
                    </div>
                    <div id="qrReader" style="width:100%; max-width:360px;"></div>
                    <div id="scanActiveMessage" class="alert alert-info mt-3" style="display:none;">
                        Point your camera at the QR code on another device or printed page.
                    </div>
                    <div id="scanResultPanel" class="alert alert-success mt-3" style="display:none;">
                        <strong>Recipient detected.</strong>
                        <p class="mb-0">Complete the amount and description fields below to send money instantly.</p>
                    </div>
                    <small class="text-muted d-block mt-2">If you are scanning from the same device screen, the camera may not read the code. Use another phone or print the QR.</small>
                </div>
                <div id="qrScannerError" class="mt-2 alert alert-warning p-2" style="display:none;"></div>
                <div id="recipientInfo" class="mt-2" style="display:none;">
                    <div class="alert alert-info p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <strong>Recipient:</strong> <span id="recipientName" class="ms-2"></span>
                                <div class="small text-muted">Phone: <span id="recipientPhone"></span></div>
                                <div class="small text-muted">Status: <span id="recipientStatus"></span></div>
                            </div>
                            <span class="badge bg-success align-self-start">Verified</span>
                        </div>
                    </div>
                </div>
                <div id="recipientError" class="mt-2 alert alert-danger p-2" style="display:none;"></div>
            </div>

            <div id="paymentPanel" style="display:none;">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h5 class="mb-2">Ready to send money</h5>
                        <p class="text-muted mb-0">Your recipient is verified. Enter the amount and a note, then confirm the transfer.</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="amount">Amount (RWF)</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    <small class="text-muted d-block mt-1">Minimum: 1 RWF</small>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2" placeholder="Enter a short note for this transfer"></textarea>
                </div>

                <div id="summarySectionContainer" style="display:none;" class="mb-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Transfer Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Amount to Send:</small>
                                    <p class="fs-5"><strong>RWF <span id="summaryAmount">0.00</span></strong></p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Transfer Fee (2%):</small>
                                    <p class="fs-5"><strong>RWF <span id="summaryFee">0.00</span></strong></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <small class="text-muted">Total You Pay:</small>
                                    <p class="fs-5"><strong class="text-danger">RWF <span id="summaryTotal">0.00</span></strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom" id="submitBtn" disabled>Send Money</button>
            </div>
        </form>
    </div>
</div>

<div id="transferData" data-phone="{{ $phone ?? '' }}" data-recipient-name="{{ $recipientName ?? '' }}" data-recipient-status="{{ $recipientStatus ?? '' }}" style="display:none;"></div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    const toPhoneInput = document.getElementById('to_phone');
    const amountInput = document.getElementById('amount');
    const recipientInfo = document.getElementById('recipientInfo');
    const recipientError = document.getElementById('recipientError');
    const recipientName = document.getElementById('recipientName');
    const recipientPhone = document.getElementById('recipientPhone');
    const recipientStatus = document.getElementById('recipientStatus');
    const scanResultPanel = document.getElementById('scanResultPanel');
    const scanActiveMessage = document.getElementById('scanActiveMessage');
    const paymentPanel = document.getElementById('paymentPanel');
    const summarySection = document.getElementById('summarySectionContainer');
    const submitBtn = document.getElementById('submitBtn');
    const transferData = document.getElementById('transferData');

    const initialPhone = transferData?.dataset.phone || '';
    const initialRecipientName = transferData?.dataset.recipientName || '';
    const initialRecipientStatus = transferData?.dataset.recipientStatus || '';
    const transferForm = document.getElementById('transferForm');
    const scanQrBtn = document.getElementById('scanQrBtn');
    const stopQrBtn = document.getElementById('stopQrBtn');
    const qrScannerPanel = document.getElementById('qrScannerPanel');
    const qrScannerError = document.getElementById('qrScannerError');

    let isValidRecipient = false;
    let lookupTimer = null;
    let qrScanner = null;

    function normalizePhone(phone) {
        return phone.replace(/\D+/g, '');
    }

    function extractPhoneFromQr(decodedText) {
        const trimmed = decodedText.trim();

        try {
            const parsed = JSON.parse(trimmed);
            if (parsed.phone || parsed.phone_number || parsed.msisdn) {
                return parsed.phone || parsed.phone_number || parsed.msisdn;
            }
        } catch (error) {
            // QR values can be plain text, tel links, URLs, or JSON.
        }

        if (trimmed.toLowerCase().startsWith('tel:')) {
            return trimmed.slice(4);
        }

        try {
            const url = new URL(trimmed);
            return url.searchParams.get('phone') || url.searchParams.get('phone_number') || url.searchParams.get('msisdn') || trimmed;
        } catch (error) {
            return trimmed;
        }
    }

    const lookupEndpoint = '{{ url("api/sim-cards/lookup/by-phone") }}';

    async function lookupRecipient(phone) {
        const normalizedPhone = normalizePhone(phone).trim();

        if (!normalizedPhone) {
            recipientInfo.style.display = 'none';
            scanResultPanel.style.display = 'none';
            recipientError.style.display = 'none';
            isValidRecipient = false;
            updateSubmitButton();
            return;
        }

        try {
            const response = await fetch(`${lookupEndpoint}/${encodeURIComponent(normalizedPhone)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            console.log('API Response status:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('API Response data:', data);
                recipientName.textContent = data.customer_name || 'Unknown Customer';
                recipientPhone.textContent = normalizedPhone;
                recipientStatus.textContent = data.status || 'Unknown';
                recipientInfo.style.display = 'block';
                recipientError.style.display = 'none';
                scanResultPanel.style.display = 'block';
                scanActiveMessage.style.display = 'none';
                isValidRecipient = true;
                updateSubmitButton();
                paymentPanel.style.display = 'block';
            } else {
                console.log('API Error status:', response.status);
                const error = await response.json();
                console.log('API Error data:', error);
                recipientInfo.style.display = 'none';
                scanResultPanel.style.display = 'none';
                recipientError.style.display = 'block';
                recipientError.textContent = 'Recipient phone number not found in the system';
                isValidRecipient = false;
                updateSubmitButton();
            }
        } catch (error) {
            console.error('Fetch error:', error);
            recipientInfo.style.display = 'none';
            scanResultPanel.style.display = 'none';
            recipientError.style.display = 'block';
            recipientError.textContent = 'Error looking up recipient. Please try again.';
            isValidRecipient = false;
            updateSubmitButton();
        }
    }

    // Lookup recipient details
    toPhoneInput.addEventListener('input', function() {
        clearTimeout(lookupTimer);
        recipientInfo.style.display = 'none';
        scanResultPanel.style.display = 'none';
        recipientError.style.display = 'none';
        isValidRecipient = false;
        updateSubmitButton();

        if (normalizePhone(this.value).length >= 6) {
            lookupTimer = setTimeout(() => lookupRecipient(this.value), 600);
        }
    });

    toPhoneInput.addEventListener('blur', function() {
        clearTimeout(lookupTimer);
        if (normalizePhone(this.value).length >= 6) {
            lookupRecipient(this.value);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        showInitialRecipient();
    });

    // Calculate fee and update summary
    amountInput.addEventListener('input', updateSummary);

    function updateSummary() {
        const amount = parseFloat(amountInput.value) || 0;
        
        if (amount > 0) {
            const fee = Math.max(amount * 0.02, 0.10);
            const total = amount + fee;

            document.getElementById('summaryAmount').textContent = amount.toFixed(2);
            document.getElementById('summaryFee').textContent = fee.toFixed(2);
            document.getElementById('summaryTotal').textContent = total.toFixed(2);

            summarySection.style.display = 'block';
        } else {
            summarySection.style.display = 'none';
        }

        updateSubmitButton();
    }

    function showInitialRecipient() {
        if (!initialPhone) {
            return;
        }

        toPhoneInput.value = initialPhone;

        if (initialRecipientName) {
            recipientName.textContent = initialRecipientName;
            recipientPhone.textContent = initialPhone;
            recipientStatus.textContent = initialRecipientStatus || 'Unknown';
            recipientInfo.style.display = 'block';
            recipientError.style.display = 'none';
            scanResultPanel.style.display = 'block';
            scanActiveMessage.style.display = 'none';
            paymentPanel.style.display = 'block';
            isValidRecipient = true;
            updateSubmitButton();
        } else if (initialPhone) {
            lookupRecipient(initialPhone);
        }
    }

    function updateSubmitButton() {
        const phone = toPhoneInput.value.trim();
        const amount = parseFloat(amountInput.value) || 0;

        paymentPanel.style.display = isValidRecipient ? 'block' : 'none';
        submitBtn.disabled = !isValidRecipient || amount <= 0 || !phone;
    }

    // Validate on form submission
    transferForm.addEventListener('submit', function(e) {
        if (!isValidRecipient) {
            e.preventDefault();
            recipientError.style.display = 'block';
            recipientError.textContent = 'Please verify the recipient phone number is correct';
        }
    });

    async function stopQrScanner(hidePanel = true) {
        if (qrScanner) {
            try {
                await qrScanner.stop();
                qrScanner.clear();
            } catch (error) {
                console.warn('Unable to stop QR scanner:', error);
            }
            qrScanner = null;
        }

        if (hidePanel) {
            qrScannerPanel.style.display = 'none';
        }
        scanActiveMessage.style.display = 'none';
    }

    scanQrBtn.addEventListener('click', async function() {
        qrScannerError.style.display = 'none';
        qrScannerPanel.style.display = 'block';
        scanResultPanel.style.display = 'none';
        scanActiveMessage.style.display = 'block';

        if (typeof Html5Qrcode === 'undefined') {
            scanActiveMessage.style.display = 'none';
            qrScannerError.style.display = 'block';
            qrScannerError.textContent = 'QR scanner library could not load. Please type the number manually.';
            return;
        }

        if (qrScanner) {
            return;
        }

        qrScanner = new Html5Qrcode('qrReader');

        try {
            await qrScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                async (decodedText) => {
                    const scannedPhone = extractPhoneFromQr(decodedText);
                    toPhoneInput.value = scannedPhone;
                    await stopQrScanner(false);
                    await lookupRecipient(scannedPhone);

                    scanActiveMessage.style.display = 'none';

                    // After a successful scan and lookup, focus the amount field
                    // and scroll the form into view so the user can complete payment.
                    try {
                        amountInput.focus();
                        amountInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        // prefill a helpful description to speed up sending
                        const desc = document.getElementById('description');
                        if (desc && !desc.value) desc.value = 'Payment via QR scan';
                    } catch (e) {
                        // ignore focus/scroll errors on older browsers
                    }
                }
            );
        } catch (error) {
            qrScanner = null;
            scanActiveMessage.style.display = 'none';
            qrScannerError.style.display = 'block';
            qrScannerError.textContent = 'Unable to open the camera. Allow camera access or type the phone number manually.';
        }
    });

    stopQrBtn.addEventListener('click', stopQrScanner);
</script>
@endsection
