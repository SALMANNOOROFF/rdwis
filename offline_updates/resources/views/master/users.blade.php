<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master User Control | RDWIS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --bg-dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f1f5f9;
            --text-dim: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(79, 172, 254, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(0, 242, 254, 0.1) 0%, transparent 40%);
        }

        .header {
            padding: 2rem;
            text-align: center;
            background: var(--glass);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--text-dim);
            letter-spacing: 1px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 3rem;
            width: 100%;
        }

        .search-box {
            margin-bottom: 2rem;
            position: relative;
        }

        .search-box input {
            width: 100%;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 1.2rem 1.5rem 1.2rem 3.5rem;
            border-radius: 15px;
            color: white;
            font-size: 1.1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
        }

        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .user-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            animation: fadeIn 0.5s ease forwards;
        }

        .user-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, var(--secondary), var(--primary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--bg-dark);
            font-size: 1.2rem;
        }

        .details h3 {
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
        }

        .details span {
            font-size: 0.9rem;
            color: var(--text-dim);
        }

        .meta {
            padding-top: 1rem;
            border-top: 1px solid var(--glass-border);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .meta-item {
            font-size: 0.85rem;
        }

        .meta-item label {
            display: block;
            color: var(--text-dim);
            font-size: 0.75rem;
            margin-bottom: 0.2rem;
        }

        .reset-btn {
            width: 100%;
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.8rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: auto;
        }

        .reset-btn:hover {
            background: var(--primary);
            color: var(--bg-dark);
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.4);
        }

        .reset-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .reset-btn.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 2rem;
            border-radius: 12px;
            background: #1e293b;
            color: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 1rem;
            transform: translateX(200%);
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 1000;
            border-left: 5px solid var(--primary);
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success { border-left-color: var(--success); }
        .toast.error { border-left-color: var(--danger); }

        @media (max-width: 768px) {
            .header h1 { font-size: 2rem; }
            .user-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>RDWIS MASTER CONTROL</h1>
        <p>USER MANAGEMENT & PASSWORD RECOVERY SYSTEM</p>
    </header>

    <div class="container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="userSearch" placeholder="Search by username, designation or area...">
        </div>

        <div class="user-grid" id="userGrid">
            @foreach($users as $user)
            <div class="user-card" data-search="{{ strtolower($user->acc_username . ' ' . $user->acc_desig . ' ' . $user->acc_untarea) }}">
                <div class="user-info">
                    <div class="avatar">{{ substr($user->acc_username, 0, 2) }}</div>
                    <div class="details">
                        <h3>{{ $user->acc_username }}</h3>
                        <span>{{ $user->acc_desig }}</span>
                    </div>
                </div>
                
                <div class="meta">
                    <div class="meta-item">
                        <label>AREA</label>
                        {{ $user->acc_untarea }}
                    </div>
                    <div class="meta-item">
                        <label>STATUS</label>
                        <span style="color: {{ $user->acc_status === 'Active' ? 'var(--success)' : 'var(--danger)' }}">
                            {{ $user->acc_status }}
                        </span>
                    </div>
                    <div class="meta-item">
                        <label>AUTH</label>
                        {{ $user->acc_auth }}
                    </div>
                    <div class="meta-item">
                        <label>LEVEL</label>
                        {{ $user->acc_level }}
                    </div>
                </div>

                <button class="reset-btn" onclick="resetPassword({{ $user->acc_id }}, this)">
                    <i class="fas fa-key"></i>
                    Reset Password
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <div id="toast" class="toast">
        <i class="fas fa-info-circle"></i>
        <span id="toastMessage"></span>
    </div>

    <script>
        // Search Logic
        const searchInput = document.getElementById('userSearch');
        const cards = document.querySelectorAll('.user-card');

        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(term)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Reset Password Logic
        async function resetPassword(accId, btn) {
            if (!confirm('Are you sure you want to reset password for this user to "12345"?')) return;

            btn.disabled = true;
            btn.classList.add('loading');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch"></i> Resetting...';

            try {
                const response = await fetch('{{ route("master.users.reset") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ acc_id: accId })
                });

                const data = await response.json();

                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Something went wrong', 'error');
                }
            } catch (error) {
                showToast('Failed to connect to server', 'error');
            } finally {
                btn.disabled = false;
                btn.classList.remove('loading');
                btn.innerHTML = originalText;
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            toast.className = 'toast ' + type + ' show';
            toastMessage.innerText = message;

            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>
