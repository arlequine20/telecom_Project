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
                <input type="text" class="form-control" id="to_phone" name="to_phone" required>
                <div id="recipientInfo" class="mt-2" style="display:none;">
                    <div class="alert alert-info p-2 mb-0">
                        <strong>Recipient:</strong> <span id="recipientName" class="ms-2"></span>
                        <small class="d-block text-muted">Status: <span id="recipientStatus"></span></small>
                    </div>
                </div>
                <div id="recipientError" class="mt-2 alert alert-danger p-2" style="display:none;"></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="amount">Amount (RWF)</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                <small class="text-muted d-block mt-1">Minimum: 1 RWF</small>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
            </div>

            <!-- Summary Section -->
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
        </form>
    </div>
</div>

<script>
    const toPhoneInput = document.getElementById('to_phone');
    const amountInput = document.getElementById('amount');
    const recipientInfo = document.getElementById('recipientInfo');
    const recipientError = document.getElementById('recipientError');
    const recipientName = document.getElementById('recipientName');
    const recipientStatus = document.getElementById('recipientStatus');
    const summarySection = document.getElementById('summarySectionContainer');
    const submitBtn = document.getElementById('submitBtn');
    const transferForm = document.getElementById('transferForm');

    let isValidRecipient = false;
    let lookupTimer = null;

    function normalizePhone(phone) {
        return phone.replace(/\D+/g, '');
    }

    const lookupEndpoint = '{{ url("api/sim-cards/lookup/by-phone") }}';

    async function lookupRecipient(phone) {
        const normalizedPhone = normalizePhone(phone).trim();

        if (!normalizedPhone) {
            recipientInfo.style.display = 'none';
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
                recipientStatus.textContent = data.status || 'Unknown';
                recipientInfo.style.display = 'block';
                recipientError.style.display = 'none';
                isValidRecipient = true;
                updateSubmitButton();
            } else {
                console.log('API Error status:', response.status);
                const error = await response.json();
                console.log('API Error data:', error);
                recipientInfo.style.display = 'none';
                recipientError.style.display = 'block';
                recipientError.textContent = 'Recipient phone number not found in the system';
                isValidRecipient = false;
                updateSubmitButton();
            }
        } catch (error) {
            console.error('Fetch error:', error);
            recipientInfo.style.display = 'none';
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

    function updateSubmitButton() {
        const phone = toPhoneInput.value.trim();
        const amount = parseFloat(amountInput.value) || 0;

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
</script>
@endsection

