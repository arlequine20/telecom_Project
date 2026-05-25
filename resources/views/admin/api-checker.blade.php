@extends('layouts.app')

@section('title', 'API Checker | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'API Checker')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-link fa-2x mb-3" style="color: var(--primary)"></i>
            <h3>{{ $apiStatus['public_count'] + $apiStatus['protected_count'] }}</h3>
            <p class="text-muted mb-0">Documented endpoints</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-unlock fa-2x mb-3" style="color: var(--success)"></i>
            <h3>{{ $apiStatus['public_count'] }}</h3>
            <p class="text-muted mb-0">Public endpoints</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-shield-alt fa-2x mb-3" style="color: var(--warning)"></i>
            <h3>{{ $apiStatus['protected_count'] }}</h3>
            <p class="text-muted mb-0">Token endpoints</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-key fa-2x mb-3" style="color: {{ $apiStatus['token_table_ready'] ? 'var(--success)' : 'var(--danger)' }}"></i>
            <h3>{{ $apiStatus['token_table_ready'] ? 'Ready' : 'Run migration' }}</h3>
            <p class="text-muted mb-0">Token storage</p>
        </div>
    </div>
</div>

@if(!$apiStatus['sanctum_installed'] || !$apiStatus['token_table_ready'])
    <div class="alert alert-warning">
        <strong>Token API setup needs attention.</strong>
        Sanctum installed: {{ $apiStatus['sanctum_installed'] ? 'yes' : 'no' }}.
        Token table ready: {{ $apiStatus['token_table_ready'] ? 'yes' : 'no' }}.
        Run <code>php artisan migrate</code> if the token table is not ready.
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Bearer Token</h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearTokenBtn">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">API base URL</label>
                    <input type="text" class="form-control" id="baseUrlInput" value="{{ $apiStatus['base_url'] }}">
                </div>
                <textarea class="form-control font-monospace" id="tokenInput" rows="3" placeholder="Paste token here"></textarea>
                <div class="row g-2 mt-3">
                    <div class="col-md-5">
                        <input type="email" class="form-control" id="loginEmail" value="{{ auth()->user()->email ?? '' }}" placeholder="Email">
                    </div>
                    <div class="col-md-5">
                        <input type="password" class="form-control" id="loginPassword" placeholder="Password">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-primary-custom text-white" id="loginBtn">
                            <i class="fas fa-sign-in-alt"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Login fills the token automatically when credentials are valid.</small>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Endpoints</h5>
                <div class="accordion" id="endpointAccordion">
                    @foreach($endpoints as $groupIndex => $group)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $groupIndex }}">
                                <button class="accordion-button {{ $groupIndex === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $groupIndex }}">
                                    {{ $group['group'] }}
                                </button>
                            </h2>
                            <div id="collapse{{ $groupIndex }}" class="accordion-collapse collapse {{ $groupIndex === 0 ? 'show' : '' }}" data-bs-parent="#endpointAccordion">
                                <div class="accordion-body p-2">
                                    @foreach($group['items'] as $endpoint)
                                        <button type="button"
                                                class="btn w-100 text-start border mb-2 endpoint-btn"
                                                data-method="{{ $endpoint['method'] }}"
                                                data-path="{{ $endpoint['path'] }}"
                                                data-auth="{{ $endpoint['auth'] ? '1' : '0' }}"
                                                data-body='@json($endpoint['body'] ?? new stdClass())'
                                                data-description="{{ $endpoint['description'] }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>{{ $endpoint['name'] }}</strong>
                                                <span class="badge bg-{{ $endpoint['auth'] ? 'dark' : 'success' }}">{{ $endpoint['auth'] ? 'TOKEN' : 'PUBLIC' }}</span>
                                            </div>
                                            <span class="text-muted font-monospace small">{{ $endpoint['method'] }} {{ $endpoint['path'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Method</label>
                        <select class="form-select" id="methodInput">
                            <option>GET</option>
                            <option>POST</option>
                            <option>PUT</option>
                            <option>PATCH</option>
                            <option>DELETE</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Path</label>
                        <input type="text" class="form-control font-monospace" id="pathInput" value="/auth/me">
                    </div>
                </div>
                <p class="text-muted mt-3 mb-2" id="endpointDescription">Select an endpoint or build a custom request.</p>
                <div class="mb-3">
                    <label class="form-label">JSON body</label>
                    <textarea class="form-control font-monospace" id="bodyInput" rows="10">{}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary-custom text-white" id="sendBtn">
                        <i class="fas fa-paper-plane"></i> Send Request
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="formatBodyBtn">
                        <i class="fas fa-align-left"></i> Format JSON
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Response</h5>
                    <span class="badge bg-secondary" id="statusBadge">Idle</span>
                </div>
                <pre class="bg-dark text-white p-3 rounded mb-0" id="responseOutput" style="min-height: 320px; white-space: pre-wrap;">Select an endpoint and send a request.</pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const tokenInput = document.getElementById('tokenInput');
const baseUrlInput = document.getElementById('baseUrlInput');
const methodInput = document.getElementById('methodInput');
const pathInput = document.getElementById('pathInput');
const bodyInput = document.getElementById('bodyInput');
const responseOutput = document.getElementById('responseOutput');
const statusBadge = document.getElementById('statusBadge');
const description = document.getElementById('endpointDescription');

tokenInput.value = localStorage.getItem('telecom_api_token') || '';

function setStatus(text, className) {
    statusBadge.className = `badge ${className}`;
    statusBadge.textContent = text;
}

function formatJson(value) {
    if (!value.trim()) {
        return '{}';
    }

    return JSON.stringify(JSON.parse(value), null, 2);
}

document.querySelectorAll('.endpoint-btn').forEach((button) => {
    button.addEventListener('click', () => {
        methodInput.value = button.dataset.method;
        pathInput.value = button.dataset.path;
        description.textContent = button.dataset.description;
        bodyInput.value = JSON.stringify(JSON.parse(button.dataset.body), null, 2);
    });
});

document.getElementById('formatBodyBtn').addEventListener('click', () => {
    try {
        bodyInput.value = formatJson(bodyInput.value);
    } catch (error) {
        setStatus('Invalid JSON', 'bg-danger');
    }
});

document.getElementById('clearTokenBtn').addEventListener('click', () => {
    tokenInput.value = '';
    localStorage.removeItem('telecom_api_token');
});

document.getElementById('loginBtn').addEventListener('click', async () => {
    methodInput.value = 'POST';
    pathInput.value = '/auth/login';
    bodyInput.value = JSON.stringify({
        email: document.getElementById('loginEmail').value,
        password: document.getElementById('loginPassword').value
    }, null, 2);

    await sendRequest(true);
});

document.getElementById('sendBtn').addEventListener('click', () => sendRequest(false));

async function sendRequest(isLoginRequest) {
    const method = methodInput.value;
    const baseUrl = baseUrlInput.value.replace(/\/$/, '');
    const path = pathInput.value.startsWith('/') ? pathInput.value : `/${pathInput.value}`;
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json'
    };

    if (tokenInput.value.trim()) {
        headers.Authorization = `Bearer ${tokenInput.value.trim()}`;
    }

    const options = { method, headers };

    if (!['GET', 'HEAD'].includes(method)) {
        try {
            options.body = formatJson(bodyInput.value);
        } catch (error) {
            setStatus('Invalid JSON', 'bg-danger');
            responseOutput.textContent = error.message;
            return;
        }
    }

    setStatus('Sending', 'bg-warning text-dark');
    responseOutput.textContent = 'Waiting for API response...';

    try {
        const response = await fetch(`${baseUrl}${path}`, options);
        const text = await response.text();
        let payload = text;

        try {
            payload = JSON.stringify(JSON.parse(text), null, 2);
        } catch (error) {
            payload = text || '(empty response)';
        }

        setStatus(`${response.status} ${response.statusText}`, response.ok ? 'bg-success' : 'bg-danger');
        responseOutput.textContent = payload;

        if (isLoginRequest && response.ok) {
            const parsed = JSON.parse(text);
            if (parsed.token) {
                tokenInput.value = parsed.token;
                localStorage.setItem('telecom_api_token', parsed.token);
            }
        }
    } catch (error) {
        setStatus('Network error', 'bg-danger');
        responseOutput.textContent = error.message;
    }
}
</script>
@endsection
