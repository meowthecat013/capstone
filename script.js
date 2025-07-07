document.addEventListener('DOMContentLoaded', function() {
    // Show daily vitals modal if not submitted today
    const vitalsModal = document.getElementById('vitalsModal');
    if (vitalsModal) {
        vitalsModal.style.display = 'flex';
        
        // Close modal
        const closeModal = document.querySelector('.close-modal');
        closeModal.addEventListener('click', function() {
            vitalsModal.style.display = 'none';
        });
        
        // Submit vitals form
        const vitalsForm = document.getElementById('vitalsForm');
        vitalsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = {
                mood: document.querySelector('input[name="mood"]:checked').value,
                blood_pressure_systolic: document.getElementById('blood_pressure_systolic').value,
                blood_pressure_diastolic: document.getElementById('blood_pressure_diastolic').value,
                heart_rate: document.getElementById('heart_rate').value,
                blood_sugar: document.getElementById('blood_sugar').value,
                feelings: document.getElementById('feelings').value
            };
            
            // Send to server
            fetch('api/vitals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    vitalsModal.style.display = 'none';
                    location.reload(); // Refresh to show new vitals
                } else {
                    alert('Error saving vitals: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save vitals. Please try again.');
            });
        });
    }
    
    // Music player functionality
    const audioPlayer = new Audio();
    let currentSong = null;
    let isPlaying = false;
    
    // Play/pause button
    const playPauseBtn = document.getElementById('play-pause');
    if (playPauseBtn) {
        playPauseBtn.addEventListener('click', function() {
            if (isPlaying) {
                audioPlayer.pause();
                playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                isPlaying = false;
            } else {
                if (currentSong) {
                    audioPlayer.play();
                    playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                    isPlaying = true;
                } else {
                    // Play default song if none selected
                    playSong(1); // Assuming ID 1 is a default song
                }
            }
        });
    }
    
    // Volume control
    const volumeControl = document.getElementById('volume');
    if (volumeControl) {
        volumeControl.addEventListener('input', function() {
            audioPlayer.volume = this.value;
        });
        
        // Set initial volume
        audioPlayer.volume = volumeControl.value;
    }
    
    // Play song when clicked in suggestions
    const songItems = document.querySelectorAll('.music-suggestions li');
    songItems.forEach(item => {
        item.addEventListener('click', function() {
            const songId = this.getAttribute('data-song-id');
            playSong(songId);
        });
    });
    
    // Function to play a song by ID
    function playSong(songId) {
        fetch(`api/music.php?song_id=${songId}`)
            .then(response => response.json())
            .then(data => {
                if (data.file_path) {
                    currentSong = data;
                    audioPlayer.src = data.file_path;
                    audioPlayer.play();
                    isPlaying = true;
                    playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                    document.getElementById('current-song').textContent = `${data.title} - ${data.artist}`;
                }
            });
    }
    
    // New quote button
    const newQuoteBtn = document.getElementById('new-quote');
    if (newQuoteBtn) {
        newQuoteBtn.addEventListener('click', function() {
            fetch('api/quotes.php')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('.quote blockquote p').textContent = data.quote;
                });
        });
    }
    
    // Accessibility button
    const accessibilityBtn = document.querySelector('.accessibility-btn');
    if (accessibilityBtn) {
        accessibilityBtn.addEventListener('click', function() {
            // Toggle high contrast mode
            document.body.classList.toggle('high-contrast');
            
            // Toggle larger text
            const html = document.documentElement;
            const currentSize = window.getComputedStyle(html).fontSize;
            if (currentSize === '16px') {
                html.style.fontSize = '20px';
            } else {
                html.style.fontSize = '16px';
            }
        });
    }
});

// Global function to handle API errors
function handleApiError(error) {
    console.error('API Error:', error);
    alert('An error occurred. Please try again later.');
}