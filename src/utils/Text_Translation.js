const micIcon = document.getElementById('mic-icon');
const micContainer = document.querySelector('.i');
const outputText = document.getElementById('output-text');

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
const recognition = new SpeechRecognition();

recognition.continuous = false;
recognition.lang = 'en-US';
recognition.interimResults = false;

let isListening = false;

micContainer.addEventListener('click', () => {
    micIcon.classList.toggle('fa-microphone-slash');
    micIcon.classList.toggle('fa-microphone');

    if (!isListening) {
        recognition.start();
        isListening = true;
    } else {
        recognition.stop();
        isListening = false;
    }
});

recognition.onresult = (event) => {
    const transcript = event.results[0][0].transcript;
    outputText.value += transcript + " ";
};

recognition.onerror = (event) => {
    console.error('Speech recognition error:', event.error);
    recognition.stop();
    isListening = false;
    micIcon.classList.add('fa-microphone-slash');
    micIcon.classList.remove('fa-microphone');
};

recognition.onend = () => {
    isListening = false;
    micIcon.classList.add('fa-microphone-slash');
    micIcon.classList.remove('fa-microphone');
};
