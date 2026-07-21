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
    if (!note) return;
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

document.addEventListener('DOMContentLoaded', function() {
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
});
