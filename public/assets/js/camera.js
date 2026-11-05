const video = document.getElementById('idCameraStream');
const canvas = document.getElementById('idCanvas');
const img = document.getElementById('idCapturedImg');
const hiddenInput = document.getElementById('idPhotoBase64');
const startBtn = document.getElementById('startCamBtn');
const captureBtn = document.getElementById('captureCamBtn');
const retakeBtn = document.getElementById('retakeCamBtn');
let stream = null;

startBtn.onclick = async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: 1280, height: 720 } });
        video.srcObject = stream;
        video.style.display = 'block';
        captureBtn.style.display = 'inline-flex';
        startBtn.style.display = 'none';
    } catch (err) {
        alert('Camera permission denied or unavailable');
    }
};

captureBtn.onclick = () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
    hiddenInput.value = dataUrl;
    img.src = dataUrl;
    
    // Stop cam & toggle view
    if (stream) stream.getTracks().forEach(t => t.stop());
    video.style.display = 'none';
    img.style.display = 'block';
    captureBtn.style.display = 'none';
    retakeBtn.style.display = 'inline-flex';
};

retakeBtn.onclick = () => {
    img.style.display = 'none';
    retakeBtn.style.display = 'none';
    hiddenInput.value = '';
    startBtn.click();
};