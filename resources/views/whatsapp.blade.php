<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp Multi-User Sender</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background: #f2f4f6;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .btn-custom {
            border-radius: 50px;
            padding: 10px 20px;
        }
        #qr-image {
            border: 3px dashed #0d6efd;
            padding: 10px;
            background: #fff;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container py-5">

    <h2 class="text-center mb-5 fw-bold text-primary">📱 WhatsApp Multi-User Sender</h2>

    <!-- Step 1: QR Login -->
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            Step 1: Scan QR Code to Login
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <label for="session" class="form-label">User ID</label>
                    <input type="text" id="session" class="form-control" placeholder="Enter a unique  (e.g., user123)">
                </div>
                <div class="col-md-4 d-grid gap-2">
                    <button class="btn btn-primary btn-custom" onclick="getQrCode()">Get QR Code</button>
                    <button class="btn btn-danger btn-custom" onclick="logoutSession()">Logout</button>
                    
                </div>
            </div>

            <!-- Dropdown Section (Initially Hidden) -->
            <div id="dropdown-section" class="mt-4" style="display: none;">
                <label for="active-sessions" class="form-label">Active Sessions</label>
                <select id="active-sessions" class="form-select">
                    <option value="">Loading...</option>
                </select>
            </div>

            <div id="qr-section" class="text-center mt-4" style="display:none;">
                <h5 class="mb-3">Scan This QR:</h5>
                <img id="qr-image" src="" alt="QR Code" style="width:250px;">
            </div>

            <div id="login-status" class="text-center text-success mt-3 fw-bold"></div>
            <div id="all-sessions-status" class="text-center text-primary mt-3 fw-bold"></div>
        </div>
    </div>

    <!-- Step 2: Send Bulk Messages -->
    <div class="card">
        <div class="card-header bg-success text-white">
            Step 2: Upload Excel & Send Messages
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('send.bulk') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="session_send" class="form-label">User ID</label>
                    <input type="text" name="session" id="session_send" class="form-control" placeholder="Same User ID used above" required>
                </div>

                <div class="mb-3">
                    <label for="excel_file" class="form-label">Excel File (Number, Message)</label>
                    <input type="file" name="excel_file" id="excel_file" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Optional Media File (Image, Video, PDF)</label>
                    <input type="file" name="image" id="image" class="form-control">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-custom">🚀 Send Bulk Messages</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    @if(session('results'))
        <div class="card mt-5">
            <div class="card-header bg-info text-white">
                Results
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach(session('results') as $result)
                        <li class="list-group-item">{!! $result !!}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
function getQrCode() {
    let session = document.getElementById('session').value;
    if (!session) {
        alert('Please enter  first!');
        return;
    }

    axios.get('/get-qr?session=' + session)
        .then(response => {
            if (response.data.qr) {
                document.getElementById('qr-section').style.display = 'block';
                document.getElementById('qr-image').src = response.data.qr;
                document.getElementById('login-status').innerHTML = '';
            } else if (response.data.status === 'authenticated') {
                document.getElementById('login-status').innerHTML = '✅ Already logged in!';
                document.getElementById('qr-section').style.display = 'none';
            } else {
                document.getElementById('login-status').innerHTML = '⏳ Please wait, loading QR...';
                document.getElementById('qr-section').style.display = 'none';
            }
        })
        .catch(error => {
            console.error(error);
            alert('❌ Failed to get QR.');
        });
}

function logoutSession() {
    let session = document.getElementById('session').value;
    if (!session) {
        alert('Please enter User ID first!');
        return;
    }

    axios.post('http://localhost:4000/logout', { session: session })
        .then(response => {
            alert('✅ Session logged out.');
            document.getElementById('login-status').innerHTML = '';
            document.getElementById('qr-section').style.display = 'none';
            document.getElementById('qr-image').src = '';
        })
        .catch(error => {
            console.error(error);
            alert('❌ Failed to logout session.');
        });
}

function checkAllSessions() {
    axios.get('http://localhost:4000/sessions')
        .then(response => {
            let dropdown = document.getElementById('active-sessions');
            dropdown.innerHTML = ''; // Clear old options

            if (response.data.sessions && response.data.sessions.length > 0) {
                response.data.sessions.forEach(session => {
                    let option = document.createElement('option');
                    option.value = session;
                    option.text = session;
                    dropdown.appendChild(option);
                });

                // Show the dropdown section
                document.getElementById('dropdown-section').style.display = 'block';
                
                document.getElementById('all-sessions-status').innerHTML = 
                    '✅ Active sessions (' + response.data.sessions.length + ') loaded.';
            } else {
                let option = document.createElement('option');
                option.value = '';
                option.text = 'No active sessions';
                dropdown.appendChild(option);

                document.getElementById('dropdown-section').style.display = 'block';

                document.getElementById('all-sessions-status').innerHTML = '❌ No active sessions found.';
            }
        })
        .catch(error => {
            console.error(error);
            document.getElementById('all-sessions-status').innerHTML = '❌ Failed to fetch sessions.';
        });
}

// Auto-fill session fields when dropdown changes
document.getElementById('active-sessions').addEventListener('change', function() {
    let selectedSession = this.value;
    document.getElementById('session').value = selectedSession;
    document.getElementById('session_send').value = selectedSession;
});
</script>

</body>
</html>
