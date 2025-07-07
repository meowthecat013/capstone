
<?php

// This file should only be included, not accessed directly
if (!defined('INCLUDED_FROM_INDEX')) {
    // If accessed directly, handle AJAX submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'config.php';
        
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }
        
        // Get the submitted data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($data['mood']) || !in_array($data['mood'], ['Happy', 'Sad', 'Anxious', 'Angry', 'Tired', 'Neutral'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please select a valid mood']);
            exit;
        }
        
        if (empty($data['blood_pressure_systolic']) || empty($data['blood_pressure_diastolic']) || empty($data['heart_rate'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
            exit;
        }
        
        // Additional validation
        if ($data['blood_pressure_systolic'] < 70 || $data['blood_pressure_systolic'] > 250) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid systolic blood pressure']);
            exit;
        }
        
        if ($data['blood_pressure_diastolic'] < 40 || $data['blood_pressure_diastolic'] > 150) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid diastolic blood pressure']);
            exit;
        }
        
        if ($data['heart_rate'] < 40 || $data['heart_rate'] > 200) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid heart rate']);
            exit;
        }
        
        // Prepare data for insertion
        $today = date('Y-m-d');
        $userId = $_SESSION['user_id'];
        
        try {
            // Check if today's vitals already exist
            $stmt = $pdo->prepare("SELECT id FROM daily_vitals WHERE user_id = ? AND date = ?");
            $stmt->execute([$userId, $today]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Daily vitals already submitted today']);
                exit;
            }
            
            // Insert new vitals
            $stmt = $pdo->prepare("INSERT INTO daily_vitals 
                (user_id, date, mood, blood_pressure_systolic, blood_pressure_diastolic, heart_rate, blood_sugar, feelings)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            $success = $stmt->execute([
                $userId,
                $today,
                $data['mood'],
                $data['blood_pressure_systolic'],
                $data['blood_pressure_diastolic'],
                $data['heart_rate'],
                $data['blood_sugar'] ?? null,
                $data['feelings'] ?? ''
            ]);
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to save vitals']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // If not POST and accessed directly, redirect to user_dashboard
    header("Location: user_dashboard.php");
    exit;
}
?>

<!-- Modal CSS -->
<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(5px);
}

.modal.hidden {
    display: none;
}

.modal-content {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    position: relative;
    animation: modalSlideIn 0.4s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 25px;
    font-size: 30px;
    cursor: pointer;
    color: #666;
    transition: color 0.3s;
}

.close-modal:hover {
    color: #333;
}

.modal-header {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.modal-header .icon {
    width: 150px;
    height: 80px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    box-shadow: 0 8px 16px rgba(76, 175, 80, 0.3);
}

.modal-header .icon i {
    font-size: 35px;
    color: white;
}

.modal-header .text h3 {
    font-size: 28px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 600;
}

.modal-header .text p {
    color: #666;
    font-size: 16px;
    line-height: 1.5;
}

.form-group {
    margin-bottom: 30px;
}

.form-group label {
    display: block;
    margin-bottom: 12px;
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.mood-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 15px;
}

.mood-options input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.mood-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    border: 3px solid #e0e0e0;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f9f9f9;
    min-height: 100px;
}

.mood-option:hover {
    border-color: #4CAF50;
    background: #f0f8f0;
    transform: translateY(-2px);
}

.mood-option i {
    font-size: 35px;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.mood-option span {
    font-size: 14px;
    font-weight: 500;
    color: #666;
}

.mood-options.invalid {
    border: 2px solid #e74c3c;
    border-radius: 10px;
    padding: 5px;
}

input[type="radio"]:checked + .mood-option {
    border-color: #4CAF50;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    transform: scale(1.05);
}

input[type="radio"]:checked + .mood-option span {
    color: white;
}

input[type="radio"]:checked + .mood-option i {
    color: white;
    transform: scale(1.1);
}

.mood-happy i { color: #FFC107; }
.mood-sad i { color: #2196F3; }
.mood-anxious i { color: #FF9800; }
.mood-angry i { color: #F44336; }
.mood-tired i { color: #9C27B0; }
.mood-neutral i { color: #607D8B; }

.input-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.input-group input {
    flex: 1;
}

.input-group span {
    font-size: 18px;
    font-weight: bold;
    color: #666;
}

input[type="number"], input[type="text"], textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 16px;
    transition: border-color 0.3s;
    background: #f9f9f9;
}

input[type="number"]:focus, input[type="text"]:focus, textarea:focus {
    outline: none;
    border-color: #4CAF50;
    background: white;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

textarea {
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
}

.btn {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border: none;
    padding: 18px 40px;
    border-radius: 25px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    width: 100%;
    margin-top: 20px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.helper-text {
    font-size: 14px;
    color: #888;
    margin-top: 5px;
    font-style: italic;
}

.success-message, .error-message {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: none;
}

.success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.loading {
    display: none;
    text-align: center;
    padding: 20px;
}

.spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #4CAF50;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .modal-content {
        padding: 30px 20px;
        margin: 20px;
    }
    .mood-options {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .mood-options {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Daily Vitals Modal -->
<div class="modal hidden" id="vitalsModal">
    <div class="modal-content">
        <span class="close-modal" id="closeModal">&times;</span>
        
        <div class="modal-header">
            <div class="icon">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="text">
                <h3>Daily Health Check</h3>
                <p>Help us understand how you're feeling today. This information helps us provide better care and recommendations.</p>
            </div>
        </div>

        <div class="success-message" id="successMessage">
            <i class="fas fa-check-circle"></i> Thank you! Your health information has been recorded successfully.
        </div>

        <div class="error-message" id="errorMessage">
            <i class="fas fa-exclamation-triangle"></i> <span id="errorText">Something went wrong. Please try again.</span>
        </div>

        <div class="loading" id="loadingState">
            <div class="spinner"></div>
            <p>Saving your information...</p>
        </div>

        <form id="vitalsForm">
            <div class="form-group">
                <label>How are you feeling today? <span style="color: red;">*</span></label>
                <div class="mood-options" id="moodOptions">
                    <input type="radio" name="mood" id="mood-happy" value="Happy">
                    <label for="mood-happy" class="mood-option mood-happy">
                        <i class="fas fa-smile"></i>
                        <span>Happy</span>
                    </label>
                    
                    <input type="radio" name="mood" id="mood-sad" value="Sad">
                    <label for="mood-sad" class="mood-option mood-sad">
                        <i class="fas fa-sad-tear"></i>
                        <span>Sad</span>
                    </label>
                    
                    <input type="radio" name="mood" id="mood-anxious" value="Anxious">
                    <label for="mood-anxious" class="mood-option mood-anxious">
                        <i class="fas fa-flushed"></i>
                        <span>Anxious</span>
                    </label>
                    
                    <input type="radio" name="mood" id="mood-angry" value="Angry">
                    <label for="mood-angry" class="mood-option mood-angry">
                        <i class="fas fa-angry"></i>
                        <span>Angry</span>
                    </label>
                    
                    <input type="radio" name="mood" id="mood-tired" value="Tired">
                    <label for="mood-tired" class="mood-option mood-tired">
                        <i class="fas fa-tired"></i>
                        <span>Tired</span>
                    </label>
                    
                    <input type="radio" name="mood" id="mood-neutral" value="Neutral">
                    <label for="mood-neutral" class="mood-option mood-neutral">
                        <i class="fas fa-meh"></i>
                        <span>Neutral</span>
                    </label>
                </div>
                <small class="error-message" id="moodError" style="display:none;color:red;">Please select a mood</small>
            </div>
            
            <div class="form-group">
                <label for="blood_pressure">Blood Pressure (mmHg) <span style="color: red;">*</span></label>
                <div class="input-group">
                    <input type="number" id="blood_pressure_systolic" name="blood_pressure_systolic" placeholder="120" required min="70" max="250">
                    <span>/</span>
                    <input type="number" id="blood_pressure_diastolic" name="blood_pressure_diastolic" placeholder="80" required min="40" max="150">
                </div>
                <div class="helper-text">Normal range: 90-120 / 60-80 mmHg</div>
            </div>
            
            <div class="form-group">
                <label for="heart_rate">Heart Rate (bpm) <span style="color: red;">*</span></label>
                <input type="number" id="heart_rate" name="heart_rate" placeholder="72" required min="40" max="200">
                <div class="helper-text">Normal range: 60-100 bpm</div>
            </div>
            
            <div class="form-group">
                <label for="blood_sugar">Blood Sugar (mg/dL) - Optional</label>
                <input type="number" step="0.1" id="blood_sugar" name="blood_sugar" placeholder="110" min="50" max="500">
                <div class="helper-text">Normal range: 80-130 mg/dL</div>
            </div>
            
            <div class="form-group">
                <label for="feelings">Additional Notes</label>
                <textarea id="feelings" name="feelings" rows="3" placeholder="Describe how you're feeling or any symptoms you're experiencing..."></textarea>
                <div class="helper-text">Optional: Share any additional health information</div>
            </div>
            
            <button type="submit" class="btn" id="submitBtn">
                <i class="fas fa-save"></i> Submit Daily Check
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('vitalsModal');
    const form = document.getElementById('vitalsForm');
    const closeBtn = document.getElementById('closeModal');
    const submitBtn = document.getElementById('submitBtn');
    const loadingState = document.getElementById('loadingState');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');

    // Close modal events
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // Escape key to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Form submission
    form.addEventListener('submit', handleSubmit);

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    async function handleSubmit(e) {
        e.preventDefault();
        
        // Validate mood selection
        const moodSelected = document.querySelector('input[name="mood"]:checked');
        if (!moodSelected) {
            document.getElementById('moodError').style.display = 'block';
            document.getElementById('moodOptions').classList.add('invalid');
            document.getElementById('moodOptions').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        } else {
            document.getElementById('moodError').style.display = 'none';
            document.getElementById('moodOptions').classList.remove('invalid');
        }
        
        const formData = new FormData(form);
        const data = {
            mood: formData.get('mood'),
            blood_pressure_systolic: parseInt(formData.get('blood_pressure_systolic')),
            blood_pressure_diastolic: parseInt(formData.get('blood_pressure_diastolic')),
            heart_rate: parseInt(formData.get('heart_rate')),
            blood_sugar: formData.get('blood_sugar') ? parseFloat(formData.get('blood_sugar')) : null,
            feelings: formData.get('feelings') || ''
        };

        // Validate required fields
        if (!data.mood || !data.blood_pressure_systolic || !data.blood_pressure_diastolic || !data.heart_rate) {
            showError('Please fill in all required fields.');
            return;
        }

        // Validate ranges
        if (data.blood_pressure_systolic < 70 || data.blood_pressure_systolic > 250) {
            showError('Systolic blood pressure should be between 70-250 mmHg.');
            return;
        }

        if (data.blood_pressure_diastolic < 40 || data.blood_pressure_diastolic > 150) {
            showError('Diastolic blood pressure should be between 40-150 mmHg.');
            return;
        }

        if (data.heart_rate < 40 || data.heart_rate > 200) {
            showError('Heart rate should be between 40-200 bpm.');
            return;
        }

        try {
            setLoading(true);
            const response = await submitData(data);
            
            if (response.success) {
                showSuccess();
                // Close modal after 2 seconds and reload page
                setTimeout(() => {
                    closeModal();
                    window.location.reload();
                }, 2000);
            } else {
                showError(response.error || 'Failed to save your information.');
            }
        } catch (error) {
            console.error('Submission error:', error);
            showError('Network error. Please check your connection and try again.');
        } finally {
            setLoading(false);
        }
    }

    async function submitData(data) {
        const response = await fetch('vitals.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    function setLoading(isLoading) {
        if (isLoading) {
            loadingState.style.display = 'block';
            form.style.display = 'none';
            submitBtn.disabled = true;
        } else {
            loadingState.style.display = 'none';
            form.style.display = 'block';
            submitBtn.disabled = false;
        }
    }

    function showSuccess() {
        hideMessages();
        successMessage.style.display = 'block';
    }

    function showError(message) {
        hideMessages();
        document.getElementById('errorText').textContent = message;
        errorMessage.style.display = 'block';
    }

    function hideMessages() {
        successMessage.style.display = 'none';
        errorMessage.style.display = 'none';
    }
});
</script>