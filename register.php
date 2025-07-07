<?php 
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if terms were accepted in the modal
    if (!isset($_POST['modal_agree_terms'])) {
        $error = 'You must agree to the terms and conditions to register';
    } else {
        $userData = [
            'username' => trim($_POST['username']),
            'password' => trim($_POST['password']),
            'email' => trim($_POST['email']),
            'full_name' => trim($_POST['full_name']),
            'date_of_birth' => trim($_POST['date_of_birth']),
            'gender' => trim($_POST['gender']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            // Patient health details
            'stroke_type' => trim($_POST['stroke_type']),
            'stroke_date' => trim($_POST['stroke_date']),
            'stroke_severity' => trim($_POST['stroke_severity']),
            'affected_side' => trim($_POST['affected_side']),
            'rehabilitation_status' => trim($_POST['rehabilitation_status']),
            'medical_history' => trim($_POST['medical_history']),
            'current_medications' => trim($_POST['current_medications']),
            'allergies' => trim($_POST['allergies']),
            // Caregiver details
            'caregiver_name' => trim($_POST['caregiver_name']),
            'caregiver_relationship' => trim($_POST['caregiver_relationship']),
            'caregiver_phone' => trim($_POST['caregiver_phone']),
            'caregiver_email' => trim($_POST['caregiver_email'])
        ];
        
        if (registerUser($userData)) {
            $success = 'Registration successful! You can now login.';
        } else {
            $error = 'Registration failed. Please check your information.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Patient Registration – NeuroAid</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    html {
      scroll-behavior: smooth;
    }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f6f9f9;
    }
    header {
      background-color: white;
      border-bottom: 1px solid #ccc;
    }
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 15px 30px 5px 30px;
    }
    .top-bar-left {
      display: flex;
      align-items: center;
    }
    .top-bar-left img.logo {
      height: 40px;
      margin-right: 10px;
    }
    .top-bar-left h1 {
      color: #1D4C43;
      font-size: 32px;
      margin: 0;
      font-weight: 800;
    }
    .contact {
      font-size: 14px;
      color: #1D4C43;
      text-align: right;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 30px 20px 30px;
      border-top: 1px solid #eee;
    }
    .nav-links {
      display: flex;
      gap: 80px;
      margin-left: 100px;
    }
    nav a {
      text-decoration: none;
      color: #1D4C43;
      font-weight: bold;
      font-size: 17px;
      transition: color 0.3s, background-color 0.3s;
      padding: 6px 10px;
      border-radius: 5px;
    }
    nav a:hover {
      color: white;
      background-color: #1D4C43;
    }
    .btn-login {
      font-weight: bold;
      font-size: 17px;
      border: 2px solid #1D4C43;
      background-color: white;
      color: #1D4C43;
      border-radius: 5px;
      text-decoration: none;
      padding: 6px 10px;
    }
    .btn-login:hover {
      background-color: #1D4C43;
      color: white;
    }
    .nav-login {
      display: flex;
      margin-right: 60px;
    }
    
    /* Registration Form Styles */
    .registration-container {
      max-width: 1000px;
      margin: 2rem auto;
      padding: 2rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .form-row {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -1rem;
    }
    
    .form-group {
      flex: 1 0 calc(50% - 2rem);
      margin: 0 1rem 1.5rem;
      min-width: 250px;
    }
    
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #1D4C43;
    }
    
    label i {
      margin-right: 8px;
      color: #1D4C43;
    }
    
    input, select, textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      transition: border 0.3s;
    }
    
    input:focus, select:focus, textarea:focus {
      border-color: #1D4C43;
      outline: none;
      box-shadow: 0 0 0 3px rgba(29, 76, 67, 0.2);
    }
    
    .radio-group, .checkbox-group {
      display: flex;
      gap: 1rem;
      margin-top: 0.5rem;
    }
    
    .radio-group label, .checkbox-group label {
      display: flex;
      align-items: center;
      font-weight: normal;
      cursor: pointer;
    }
    
    .radio-group input, .checkbox-group input {
      width: auto;
      margin-right: 0.5rem;
    }
    
    .btn {
      display: inline-block;
      background: #1D4C43;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      text-align: center;
      transition: background 0.3s;
      width: 100%;
      margin: 1rem 0;
    }
    
    .btn:hover {
      background: #14332c;
    }
    
    .form-footer {
      text-align: center;
      margin-top: 1.5rem;
      color: #666;
    }
    
    .form-footer a {
      color: #1D4C43;
      text-decoration: none;
    }
    
    .form-footer a:hover {
      text-decoration: underline;
    }
    
    .alert {
      padding: 15px;
      margin-bottom: 1.5rem;
      border-radius: 5px;
      font-weight: 500;
    }
    
    .error {
      background-color: #fdecea;
      color: #e74c3c;
      border: 1px solid #f5c2c7;
    }
    
    .success {
      background-color: #edf7ed;
      color: #2ecc71;
      border: 1px solid #c3e6cb;
    }
    
    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
      animation: fadeIn 0.3s;
    }
    
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }
    
    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.2);
      width: 80%;
      max-width: 800px;
      max-height: 80vh;
      display: flex;
      flex-direction: column;
      animation: slideIn 0.3s;
    }
    
    @keyframes slideIn {
      from {transform: translateY(-50px); opacity: 0;}
      to {transform: translateY(0); opacity: 1;}
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #eee;
    }
    
    .modal-header h3 {
      color: #1D4C43;
      margin: 0;
      border: none;
      padding: 0;
      font-size: 1.5rem;
    }
    
    .close {
      color: #aaa;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    
    .close:hover {
      color: #1D4C43;
    }
    
    .modal-body {
      overflow-y: auto;
      padding-right: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .modal-body h4 {
      margin: 1.5rem 0 0.5rem;
      color: #1D4C43;
    }
    
    .modal-body p, .modal-body ol {
      margin-bottom: 1rem;
    }
    
    .modal-body ol {
      padding-left: 1.5rem;
    }
    
    .modal-footer {
      display: flex;
      justify-content: space-between;
      padding-top: 1rem;
      border-top: 1px solid #eee;
    }
    
    .terms-checkbox {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-disabled {
      background: #cccccc;
      cursor: not-allowed;
    }
    
    .btn-disabled:hover {
      background: #cccccc;
    }
    
    @media (max-width: 768px) {
      .form-group {
        flex: 1 0 100%;
      }
      
      .registration-container {
        padding: 1.5rem;
        margin: 1rem;
      }
      
      .modal-content {
        width: 95%;
        margin: 2% auto;
        padding: 1rem;
      }
      
      .modal-footer {
        flex-direction: column;
        gap: 1rem;
      }
      
      .nav-links {
        gap: 30px;
        margin-left: 30px;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="top-bar">
    <div class="top-bar-left">
      <img src="image/logo.png" alt="Logo" class="logo">
      <h1><span style="color: #1D4C43; font-weight: 600;">Neuro</span><span style="color: black; font-weight: 600;">Aid</span></h1>
    </div>
    <div class="contact">
      <span style="display: flex; align-items: center;"><img src="image/phone-icon.png" alt="Phone" style="height: 16px; margin-right: 6px;">+1 (800) 777-NEUR (6387)</span><br>
      <small>Call us for any question</small>
    </div>
  </div>
  <nav>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="about_us.php">About Us</a>
      <a href="services.php">Services</a>
      <a href="blogs.php">Blogs</a>
      <a href="team.php">Team</a>
    </div>
    <div class="nav-login">
      <a href="login.php" class="btn-login">Login</a>
    </div>
  </nav>
</header>

<div class="registration-container">
    <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
        <p class="form-footer"><a href="login.php" class="btn">Go to Login</a></p>
    <?php else: ?>
        <form id="registrationForm" method="POST" action="register.php">
            <!-- Hidden input for terms acceptance -->
            <input type="hidden" name="modal_agree_terms" id="modalAgreeTerms" value="0">
            
            <h2 style="color: #1D4C43; text-align: center; margin-bottom: 2rem;">Patient Registration</h2>
            
            <h3>Account Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required placeholder="Choose a username">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a strong password">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required placeholder="Your email address">
                </div>
                
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Your contact number">
                </div>
            </div>
            
            <h3>Personal Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name"><i class="fas fa-id-card"></i> Full Name</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Your full name">
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth"><i class="fas fa-calendar"></i> Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="Male" required> Male</label>
                        <label><input type="radio" name="gender" value="Female"> Female</label>
                        <label><input type="radio" name="gender" value="Other"> Other</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address"><i class="fas fa-home"></i> Address</label>
                    <input type="text" id="address" name="address" placeholder="Your current address">
                </div>
            </div>
            
            <h3>Stroke Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="stroke_type"><i class="fas fa-heartbeat"></i> Type of Stroke</label>
                    <select id="stroke_type" name="stroke_type" required>
                        <option value="">Select stroke type</option>
                        <option value="Ischemic Stroke">Ischemic Stroke</option>
                        <option value="Hemorrhagic Stroke">Hemorrhagic Stroke</option>
                        <option value="Transient Ischemic Attack (TIA)">Transient Ischemic Attack (TIA)</option>
                        <option value="Cryptogenic Stroke">Cryptogenic Stroke</option>
                        <option value="Brain Stem Stroke">Brain Stem Stroke</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="stroke_date"><i class="fas fa-calendar-check"></i> Date of Stroke</label>
                    <input type="date" id="stroke_date" name="stroke_date" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="stroke_severity"><i class="fas fa-exclamation-triangle"></i> Stroke Severity</label>
                    <select id="stroke_severity" name="stroke_severity" required>
                        <option value="">Select severity</option>
                        <option value="Mild">Mild</option>
                        <option value="Moderate">Moderate</option>
                        <option value="Severe">Severe</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="affected_side"><i class="fas fa-body"></i> Affected Side</label>
                    <select id="affected_side" name="affected_side" required>
                        <option value="">Select affected side</option>
                        <option value="Left">Left</option>
                        <option value="Right">Right</option>
                        <option value="Both">Both</option>
                        <option value="None">None</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="rehabilitation_status"><i class="fas fa-procedures"></i> Rehabilitation Status</label>
                    <select id="rehabilitation_status" name="rehabilitation_status" required>
                        <option value="">Select status</option>
                        <option value="Acute Phase">Acute Phase (0-4 weeks)</option>
                        <option value="Subacute Phase">Subacute Phase (1-6 months)</option>
                        <option value="Chronic Phase">Chronic Phase (6+ months)</option>
                        <option value="Preventive Care">Preventive Care</option>
                    </select>
                </div>
            </div>
            
            <h3>Medical Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="medical_history"><i class="fas fa-file-medical"></i> Medical History</label>
                    <textarea id="medical_history" name="medical_history" rows="3" placeholder="List any pre-existing conditions (e.g., hypertension, diabetes, heart disease)"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="current_medications"><i class="fas fa-pills"></i> Current Medications</label>
                    <textarea id="current_medications" name="current_medications" rows="3" placeholder="List all current medications with dosages"></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="allergies"><i class="fas fa-allergies"></i> Allergies</label>
                    <textarea id="allergies" name="allergies" rows="2" placeholder="List any medication allergies or adverse reactions"></textarea>
                </div>
            </div>
            
            <h3>Caregiver Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="caregiver_name"><i class="fas fa-user-nurse"></i> Caregiver Name</label>
                    <input type="text" id="caregiver_name" name="caregiver_name" placeholder="Primary caregiver's name">
                </div>
                
                <div class="form-group">
                    <label for="caregiver_relationship"><i class="fas fa-users"></i> Relationship</label>
                    <input type="text" id="caregiver_relationship" name="caregiver_relationship" placeholder="Relationship to patient">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="caregiver_phone"><i class="fas fa-phone-alt"></i> Caregiver Phone</label>
                    <input type="tel" id="caregiver_phone" name="caregiver_phone" placeholder="Caregiver's contact number">
                </div>
                
                <div class="form-group">
                    <label for="caregiver_email"><i class="fas fa-envelope-open"></i> Caregiver Email</label>
                    <input type="email" id="caregiver_email" name="caregiver_email" placeholder="Caregiver's email address">
                </div>
            </div>
            
             <button type="button" id="submitBtn" class="btn">Complete Registration</button>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Terms & Conditions Modal -->
<div id="termsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>NeuroAid Terms and Conditions</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <h4>1. Acceptance of Terms</h4>
            <p>By registering with NeuroAid, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you must not use our services.</p>
            
            <h4>2. Service Description</h4>
            <p>NeuroAid provides a digital platform for stroke rehabilitation management. Our services include but are not limited to exercise tracking, progress monitoring, and educational resources.</p>
            
            <h4>3. Medical Disclaimer</h4>
            <p>The content provided through NeuroAid is for informational purposes only and is not intended as medical advice. Always consult with a qualified healthcare professional before making any decisions about your health or treatment.</p>
            
            <h4>4. User Responsibilities</h4>
            <ol>
                <li>You must provide accurate and complete information during registration.</li>
                <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                <li>You agree to use the platform only for its intended purposes.</li>
                <li>You will not share your login information with unauthorized individuals.</li>
            </ol>
            
            <h4>5. Privacy Policy</h4>
            <p>Your personal and health information will be handled in accordance with our Privacy Policy. We implement industry-standard security measures to protect your data, but no system can be completely secure.</p>
            
            <h4>6. Intellectual Property</h4>
            <p>All content, trademarks, and data on the NeuroAid platform, including software, databases, text, graphics, and logos, are the property of NeuroAid or its licensors and are protected by intellectual property laws.</p>
            
            <h4>7. Limitation of Liability</h4>
            <p>NeuroAid shall not be liable for any direct, indirect, incidental, special, or consequential damages resulting from the use or inability to use our services.</p>
            
            <h4>8. Modifications to Terms</h4>
            <p>We reserve the right to modify these terms at any time. Continued use of the service after such changes constitutes your acceptance of the new terms.</p>
        </div>
        <div class="modal-footer">
            <div class="terms-checkbox">
                <input type="checkbox" id="agreeTermsCheckbox">
                <label for="agreeTermsCheckbox">I have read and agree to the Terms and Conditions</label>
            </div>
            <button class="btn btn-disabled" id="acceptTermsBtn" disabled>Accept and Continue</button>
        </div>
    </div>
</div>

<script>
    // Form submission handling
    const form = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const modalAgreeTerms = document.getElementById('modalAgreeTerms');
    
    // Modal elements
    const modal = document.getElementById("termsModal");
    const closeBtn = document.getElementsByClassName("close")[0];
    const agreeCheckbox = document.getElementById("agreeTermsCheckbox");
    const acceptTermsBtn = document.getElementById("acceptTermsBtn");
    
    // When submit button is clicked, show modal instead of submitting
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = "block";
        document.body.style.overflow = "hidden"; // Prevent scrolling
    });
    
    // Close modal when X is clicked
    closeBtn.onclick = function() {
        modal.style.display = "none";
        document.body.style.overflow = "auto";
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        }
    }
    
    // Enable/disable accept button based on checkbox
    agreeCheckbox.addEventListener('change', function() {
        acceptTermsBtn.disabled = !this.checked;
        acceptTermsBtn.classList.toggle('btn-disabled', !this.checked);
        acceptTermsBtn.classList.toggle('btn', this.checked);
    });
    
    // When terms are accepted, submit the form
    acceptTermsBtn.addEventListener('click', function() {
        if (agreeCheckbox.checked) {
            modalAgreeTerms.value = "1"; // Set the hidden field to indicate acceptance
            form.submit(); // Submit the form
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        }
    });
</script>
</body>
</html>