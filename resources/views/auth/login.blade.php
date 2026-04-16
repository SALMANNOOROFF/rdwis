<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RDWIS | Login</title>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=fallback">
<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

<style>
/* GLOBAL STYLES */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    /* Background Image - Make sure this image exists in public/dist/img/ */
    background: url('{{ asset("dist/img/loginbg.png") }}') no-repeat center center fixed;
    background-size: cover;
    height: 100vh;
    overflow: hidden; /* No scrollbars */
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Dark Overlay to make text readable */
body::before {
    content: '';
    position: absolute;
    inset: 0;
    
    z-index: 1;
}

.main-wrapper {
    position: relative;
    z-index: 2;
    display: flex;
    width: 100%;
    max-width: 1400px;
    height: 100%;
    padding: 0 40px;
}

/* LEFT SIDE (BRANDING) */
.left-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
    padding-right: 50px;
}

.left-section img {
    width: 180px;
    margin-bottom: 30px;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
    animation: float 6s ease-in-out infinite;
}

.left-section h1 {
    font-size: 56px;
    font-weight: 700;
    margin: 0;
    line-height: 1.1;
    letter-spacing: -1px;
}

.left-section h2 {
    font-size: 32px;
    font-weight: 400;
    margin: 10px 0 20px 0;
    color: #e64d3d; /* Brand Color */
}

.left-section p {
    font-size: 16px;
    opacity: 0.8;
    max-width: 500px;
    line-height: 1.6;
}

/* RIGHT SIDE (GLASS LOGIN CARD) */
.right-section {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center; /* Center the card */
}

.glass-card {
    width: 100%;
    max-width: 420px;
    padding: 45px 40px;
    
    /* GLASSMORPHISM EFFECT */
    background: rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    border-radius: 20px;
    color: #fff;
}

.glass-card h3 {
    font-size: 28px;
    font-weight: 600;
    margin: 0 0 10px 0;
    text-align: center;
}

.glass-card p {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 30px 0;
    text-align: center;
}

/* FORM ELEMENTS */
.form-group {
    margin-bottom: 20px;
    position: relative;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    color: #fff;
    font-size: 15px;
    transition: 0.3s;
    outline: none;
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.form-control:focus {
    background: rgba(255, 255, 255, 0.2);
    border-color: #e64d3d;
    box-shadow: 0 0 0 4px rgba(230, 77, 61, 0.1);
}

/* Error Styling */
.alert-danger {
    background: rgba(231, 76, 60, 0.2);
    border: 1px solid rgba(231, 76, 60, 0.5);
    color: #ffccc7;
    padding: 10px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 20px;
}
.alert-danger ul {
    margin: 0;
    padding-left: 20px;
}

.password-wrapper i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.6);
    cursor: pointer;
    transition: 0.2s;
}

.password-wrapper i:hover {
    color: #fff;
}

.options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    margin-bottom: 25px;
    color: rgba(255, 255, 255, 0.8);
}

.options label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.options input[type="checkbox"] {
    accent-color: #e64d3d;
    margin-right: 8px;
    width: 16px;
    height: 16px;
}

.forget {
    color: #e64d3d;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    transition: 0.2s;
}

.forget:hover {
    color: #ff6b5b;
    text-decoration: underline;
}

.btn-login {
    width: 100%;
    padding: 15px;
    background: linear-gradient(45deg, #e64d3d, #c0392b);
    border: none;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(230, 77, 61, 0.4);
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(230, 77, 61, 0.6);
}

.admin-note {
    display: none;
    text-align: center;
    margin-top: 15px;
    font-size: 13px;
    background: rgba(230, 77, 61, 0.2);
    padding: 10px;
    border-radius: 8px;
    color: #ffccc7;
    border: 1px solid rgba(230, 77, 61, 0.3);
}

/* Floating Animation for Logo */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .main-wrapper {
        flex-direction: column;
        padding: 40px 20px;
        overflow-y: auto; /* Enable scroll on mobile */
    }
    .left-section {
        align-items: center;
        text-align: center;
        padding-right: 0;
        margin-bottom: 40px;
    }
    .left-section h1 { font-size: 40px; }
    .glass-card { max-width: 100%; }
}
</style>
<link rel="stylesheet" href="{{ asset('css/zoom-scale.css') }}">
</head>

<body>

    <div class="main-wrapper">
        
        <div class="left-section">
            <img src="{{ asset('dist/img/newonelogo.png') }}" alt="RDWIS Logo">
            <h1>WELCOME</h1>
            <h3>Welcome to the Research & Development Wing Information System.</h3> <br> <h3 style="margin-top: -30px">  SECURE | RELIABLE | EFFICIENT MANAGEMENT</h3>
        </div>

        <div class="right-section">
            <div class="glass-card">
                <h3>Sign In</h3>
                <p>Access your dashboard using your credentials</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('login.post') }}">
                    @csrf

                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>

                    <div class="form-group password-wrapper">
                        <input type="password" name="userpass" id="password" class="form-control" placeholder="Password" required>
                        <i class="fas fa-eye" onclick="togglePassword()"></i>
                    </div>

                    <div class="options">
                        <label>
                            <input type="checkbox" name="remember"> Remember Me
                        </label>
                        <span class="forget" onclick="showAdminNote()">Forgot Password?</span>
                    </div>

                    <div class="admin-note" id="adminNote">
                        <i class="fas fa-info-circle"></i> Please contact your System Administrator to reset your password.
                    </div>

                    <button type="submit" class="btn-login">Sign In</button>
                </form>
            </div>
        </div>

    </div>

<script>
    function togglePassword() {
        const pwd = document.getElementById('password');
        const icon = document.querySelector('.password-wrapper i');
        
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function showAdminNote() {
        const note = document.getElementById('adminNote');
        // Simple fade in effect
        note.style.display = 'block';
        note.style.opacity = 0;
        let opacity = 0;
        const timer = setInterval(function() {
            if (opacity >= 1) {
                clearInterval(timer);
            }
            note.style.opacity = opacity;
            opacity += 0.1;
        }, 30);
    }
</script>

<script>
(function() {
    var isFirefox = typeof InstallTrigger !== 'undefined';
    if (!isFirefox) return;
    var w = window.innerWidth;
    var scale = 1;
    if (w <= 1100) scale = 0.70;
    else if (w <= 1280) scale = 0.75;
    else if (w <= 1400) scale = 0.85;
    if (scale < 1) {
        document.body.style.transform = 'scale(' + scale + ')';
        document.body.style.transformOrigin = '0 0';
        document.body.style.width = (100 / scale) + '%';
    }
})();
</script>

</body>
</html>