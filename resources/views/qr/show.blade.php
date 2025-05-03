<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Bulk Messaging</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .qr-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            text-align: center;
            border: 2px solid #ddd;
            padding: 10px;
            border-radius: 10px;
            background-color: #fff;
        }
        .qr-fixed img {
            max-width: 180px;
            border-radius: 10px;
            border: 2px solid #ccc;
        }
        .content-with-padding { padding-right: 240px; margin-top: 40px; }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #0d6efd;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            color: white;
        }
        .header-container h2 { margin: 0; font-size: 2rem; font-weight: 600; }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            font-weight: bold;
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }
        .card {
            border-radius: 12px;
            box-shadow: 0px 6px 12px rgba(0,0,0,0.1);
            background-color: #ffffff;
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .form-control, .btn-primary, .btn-success {
            border-radius: 8px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            font-weight: 600;
            padding: 10px 20px;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #0a58ca;
            transform: scale(1.05);
        }
        .btn-success {
            background-color: #28a745;
            border: none;
            font-weight: 600;
            padding: 8px 20px;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: scale(1.05);
        }
        .alert ul { margin-bottom: 0; }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>

<!-- QR Code Box -->
<div class="qr-fixed">
    <div id="qr-container">
        @if($qr)
            <img src="{{ $qr }}" alt="QR Code for WhatsApp" id="qr-img">
            <p class="small text-muted mt-2" id="qr-message">Scan to login</p>
        @else
            <div class="spinner-border text-primary" role="status" id="qr-spinner"></div>
            <p class="text-danger mt-2" id="qr-message">{{ $error ?? 'Loading QR Code...' }}</p>
        @endif
    </div>

    <!-- Refresh Button -->
    <button onclick="location.reload()" class="btn btn-outline-primary btn-sm mt-2">🔄 Refresh</button>
</div>

<!-- Main Content -->
<div class="container mt-5 content-with-padding">
    <div class="header-container mb-4">
        <h2>WhatsApp Messaging System</h2>
        <button onclick="confirmLogout()" class="logout-btn">🚪 Logout</button>
    </div>

    <div class="card">
        <div class="card-header">
            Send Bulk WhatsApp Messages (Excel + Optional Media)
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session('results'))
                <div class="alert alert-info">
                    <ul>
                        @foreach(session('results') as $result)
                            <li>{!! $result !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('bulk-image-excel.send') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="excel_file" class="form-label">Upload Excel File (.xlsx / .csv)</label>
                    <input type="file" name="excel_file" class="form-control" required>
                    @error('excel_file') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Optional Image/Video/Doc (Max: 16MB)</label>
                    <input type="file" name="image" class="form-control">
                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">📤 Send Messages</button>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let qrImg = document.getElementById('qr-img');
    let qrMessage = document.getElementById('qr-message');
    let qrSpinner = document.getElementById('qr-spinner');

    function fetchQR() {
        axios.get('/get-qr?session=user1234')  // Adjust session if dynamic
            .then(response => {
                if (response.data.qr) {
                    if (qrImg) {
                        qrImg.src = response.data.qr;
                        qrImg.style.display = 'block';
                    }
                    qrSpinner.style.display = 'none';
                    qrMessage.innerText = 'Scan to login';
                } else if (response.data.status === 'authenticated') {
                    qrImg.style.display = 'none';
                    qrSpinner.style.display = 'none';
                    qrMessage.innerText = '✅ Already logged in!';
                } else {
                    qrImg.style.display = 'none';
                    qrSpinner.style.display = 'block';
                    qrMessage.innerText = '⏳ Waiting for QR...';
                }
            })
            .catch(error => {
                console.error('Error fetching QR:', error);
                qrSpinner.style.display = 'none';
                qrMessage.innerText = '⚠ Server connection failed';
            });
    }

    setInterval(fetchQR, 3000);

    // 🔥 SweetAlert logout confirmation
    function confirmLogout() {
        Swal.fire({
            title: 'Are you sure you want to logout?',
            text: "This will disconnect your WhatsApp session.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('bulk.logout') }}";
            }
        })
    }
</script>

</body>
</html>
