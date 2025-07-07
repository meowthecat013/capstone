<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NeuroAid - Admin | CareGiver Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <style>
    :root {
      --primary: #1D4C43;
      --white: #ffffff;
      --light-bg: #f8f9fa;
      --gray: #ced4da;
      --text-dark: #212529;
      --shadow: rgba(0, 0, 0, 0.08);
      --highlight: #31A06A;
      --hover-bg: #D3E2D9;
      --hover-text: #228C3E;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    body { background: var(--light-bg); color: var(--text-dark); }
    .header {
      position: fixed; top: 0; left: 0; right: 0; height: 60px; background: var(--white);
      border-bottom: 1px solid var(--gray); display: flex; justify-content: space-between;
      align-items: center; padding: 0 30px; z-index: 1000; box-shadow: 0 2px 4px var(--shadow);
    }
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo img { height: 40px; }
    .logo span { font-size: 20px; font-weight: bold; }
    .logo span span { color: var(--primary); }
    .datetime { font-size: 14px; font-weight: bold; color: var(--primary); }
    .layout { display: flex; margin-top: 60px; }
    .sidebar {
      width: 240px; background: var(--white); padding: 20px; border-right: 1px solid var(--gray);
      height: calc(100vh - 60px); position: fixed; top: 60px; left: 0; overflow-y: auto;
    }
    .search-wrapper { position: relative; margin-bottom: 25px; }
    .search-wrapper input {
      width: 100%; padding: 8px 12px 8px 34px; border: 1px solid var(--gray); border-radius: 6px;
    }
    .search-wrapper i {
      position: absolute; top: 50%; left: 10px; transform: translateY(-50%); color: #777;
    }
    .menu-section {
      font-size: 12px; color: #777; margin: 20px 0 12px; padding-bottom: 4px;
      border-bottom: 1px solid var(--gray); text-transform: uppercase;
    }
    .menu-btn {
      display: flex; align-items: center; gap: 10px; background: none; border: none;
      width: 100%; padding: 10px 16px; margin-bottom: 8px; border-radius: 999px; text-align: left;
      cursor: pointer; color: var(--text-dark); text-decoration: none; position: relative; transition: 0.3s ease;
    }
    .menu-btn i { width: 20px; }
    .menu-btn:hover, .menu-btn.active {
      background-color: var(--hover-bg); color: var(--hover-text); font-weight: 600;
    }
    .menu-btn:hover::before, .menu-btn.active::before {
      content: ''; position: absolute; left: 6px; top: 50%; transform: translateY(-50%);
      height: 60%; width: 6px; background-color: var(--highlight); border-radius: 4px;
    }
    .logout { margin-top: 30px; }
    .main { margin-left: 240px; padding: 30px; width: calc(100% - 240px); min-height: calc(100vh - 60px); }
    .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .main-header h1 { font-size: 24px; color: var(--primary); }
    .btn-manage {
      background-color: var(--highlight); color: white; padding: 8px 16px; border: none;
      border-radius: 6px; cursor: pointer;
    }
    .btn-manage:hover { background-color: #268a54; }
    .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
    .search-bar input, .search-bar select {
      padding: 8px; border-radius: 6px; border: 1px solid var(--gray);
    }
    .search-bar button {
      background-color: var(--highlight); color: white; padding: 8px 16px; border: none;
      border-radius: 6px; cursor: pointer;
    }
    .search-bar button:hover { background-color: #228c3e; }
    table {
      width: 100%; border-collapse: collapse; background-color: var(--white); border-radius: 8px;
      overflow: hidden; box-shadow: 0 0 8px rgba(0,0,0,0.05);
    }
    table th, table td {
      padding: 14px 16px; text-align: left; border-bottom: 1px solid #eee;
    }
    table th { background-color: #f1f1f1; color: var(--primary); }
    .status-active {
      background-color: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px;
      font-size: 13px; display: inline-block;
    }
    tbody tr { cursor: pointer; transition: background 0.18s; }
    tbody tr:hover { background: #f5f9f7; }
    /* Modal styles */
    .modal-bg {
      display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.15); z-index: 2000; justify-content: center; align-items: center;
    }
    .modal-bg.active { display: flex; }
    .modal-dialog {
      background: #fff; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.13);
      max-width: 780px; width: 95%; padding: 28px 32px 22px 32px; position: relative; animation: fadeIn 0.2s;
    }
    @keyframes fadeIn {
      from { transform: translateY(40px) scale(0.97); opacity: 0;}
      to { transform: translateY(0) scale(1); opacity: 1;}
    }
    .modal-close {
      position: absolute; top: 18px; right: 22px; font-size: 22px; color: #333;
      cursor: pointer; background: none; border: none; z-index: 10;
    }
    .modal-title {
      font-size: 1.5em; font-weight: bold; color: #1D4C43; margin-bottom: 18px;
      letter-spacing: 0.5px; border-bottom: 2px solid #31A06A; padding-bottom: 7px;
    }
    .modal-section { margin-bottom: 18px; }
    .modal-section-label {
      font-size: 1.05em; font-weight: 600; margin-bottom: 7px; color: #1D4C43;
      display: flex; align-items: center; gap: 7px;
    }
    .modal-section-label .bar {
      width: 5px; height: 22px; background: #31A06A; border-radius: 3px; display: inline-block;
    }
    .modal-section-content {
      padding: 13px 18px; background: #f8f9fa; border-radius: 8px;
      font-size: 1em; color: #222; margin-bottom: 8px;
    }
    .modal-info-row {
      display: flex; gap: 20px; margin-top: 12px;
    }
    .modal-info-box {
      flex: 1; background: #f8f9fa; border-radius: 8px; padding: 16px 18px;
      font-size: 1em; color: #222; border: 1px solid #e4e4e4;
    }
    .modal-info-box strong { color: #1D4C43; font-weight: 600; }
    .modal-footer { text-align: right; margin-top: 18px; }
    .modal-footer button {
      background: #495057; color: #fff; border: none; padding: 8px 24px;
      border-radius: 6px; font-size: 1em; cursor: pointer; font-weight: 500; transition: background 0.18s;
    }
    .modal-footer button.green { background: #31A06A; margin-right: 10px; }
    .modal-footer button.green:hover { background: #228c3e; }
    .modal-footer button:hover { background: #222; }
    /* Manage Modal */
    .modal-form-row {
      display: flex; gap: 22px; margin-bottom: 18px;
    }
    .modal-form-col { flex: 1; }
    .modal-form-group { margin-bottom: 16px; }
    .modal-form-group label {
      display: block; font-weight: 500; margin-bottom: 5px; color: #1D4C43;
    }
    .modal-form-group input, .modal-form-group textarea {
      width: 100%; padding: 9px 12px; border-radius: 6px; border: 1px solid #ced4da;
      font-size: 1em; background: #fff; color: #222;
    }
    .modal-form-group textarea { resize: vertical; min-height: 44px; }
    @media (max-width: 700px) {
      .main { padding: 10px; }
      .modal-dialog { padding: 18px 6px 16px 6px; }
      .modal-info-row, .modal-form-row { flex-direction: column; gap: 10px; }
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    <div class="logo">
      <img src="image/logo.png" alt="NeuroAid Logo">
      <span>Neuro<span>Aid</span> - Admin</span>
    </div>
    <div class="datetime">Sunday, May 20, 2025 - 2:17:42 PM</div>
  </div>

  <!-- LAYOUT -->
  <div class="layout">
    <!-- SIDEBAR -->
    <div class="sidebar">
      <div class="search-wrapper">
        <input type="text" placeholder="Search..." />
        <i class="fas fa-search"></i>
      </div>
      <div class="menu-section">Admin Tools</div>
      <a href="#" class="menu-btn"><i class="fas fa-home"></i>Dashboard</a>
      <a href="#" class="menu-btn"><i class="fas fa-user"></i>Patient Management</a>
      <a href="#" class="menu-btn"><i class="fas fa-heartbeat"></i>Monitoring</a>
      <a href="#" class="menu-btn"><i class="fas fa-comments"></i>Chat</a>
      <a href="#" class="menu-btn active"><i class="fas fa-hands-helping"></i>CareGiver Management</a>
      <a href="#" class="menu-btn"><i class="fas fa-file-alt"></i>Content Manager</a>
      <a href="#" class="menu-btn"><i class="fas fa-exclamation-circle"></i>Feedback & Issues</a>
      <div class="menu-section">Settings</div>
      <a href="#" class="menu-btn"><i class="fas fa-user-cog"></i>Settings</a>
      <div class="logout">
        <a href="#" class="menu-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>
    <!-- MAIN CONTENT -->
    <div class="main">
      <div class="main-header">
        <h1>CareGiver Management</h1>
        <button class="btn-manage" id="manageBtn">+ Manage</button>
      </div>
      <form class="search-bar">
        <input type="text" placeholder="Search by user email">
        <select>
          <option>All Statuses</option>
          <option>Active</option>
          <option>Inactive</option>
        </select>
        <button type="submit">Search</button>
      </form>
      <table id="caregiver-table">
        <thead>
          <tr>
            <th>CareGiver</th>
            <th>Caregiver type</th>
            <th>Company/Agency</th>
            <th>Status</th>
            <th>Age</th>
            <th>Birthday</th>
          </tr>
        </thead>
        <tbody>
          <tr data-name="Pedro Amerikano" data-id="20200" data-company="Care PH" data-type="Full Time" data-age="25"
              data-patient-id="10100" data-patient-name="Juan Pinoy" data-stroke-type="Ischemic Stroke" data-patient-age="58"
              data-status="Active" data-contact="09123456789" data-vital="BP: 120\nHR: 150\nBS: 100">
            <td>Pedro Amerikano</td>
            <td>Full Time</td>
            <td>Care PH</td>
            <td><span class="status-active">Active</span></td>
            <td>25</td>
            <td>01/01/1980</td>
          </tr>
          <!-- Add more rows as needed, with appropriate data- attributes -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- CareGiver Details Modal -->
  <div class="modal-bg" id="modal-bg">
    <div class="modal-dialog">
      <button class="modal-close" id="modal-close" aria-label="Close">&times;</button>
      <div class="modal-title">CareGiver Details</div>
      <div class="modal-section">
        <div class="modal-section-label"><span class="bar"></span>CareGiver name</div>
        <div class="modal-section-content" id="modal-caregiver-name">Pedro Amerikano</div>
      </div>
      <div class="modal-info-row">
        <div class="modal-info-box">
          <div class="modal-section-label"><span class="bar"></span>Basic Information</div>
          <div><strong>CareGiver ID:</strong> <span id="modal-caregiver-id">20200</span></div>
          <div><strong>Company:</strong> <span id="modal-caregiver-company">Care PH</span></div>
          <div><strong>CareGiver Type:</strong> <span id="modal-caregiver-type">Full Time</span></div>
          <div><strong>Age:</strong> <span id="modal-caregiver-age">25</span></div>
        </div>
        <div class="modal-info-box">
          <div class="modal-section-label"><span class="bar"></span>Patient Details</div>
          <div><strong>Patient ID:</strong> <span id="modal-patient-id">10100</span></div>
          <div><strong>Patient Name:</strong> <span id="modal-patient-name">Juan Pinoy</span></div>
          <div><strong>Stroke Type:</strong> <span id="modal-stroke-type">Ischemic Stroke</span></div>
          <div><strong>Age:</strong> <span id="modal-patient-age">58</span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="modal-close-btn">Close</button>
      </div>
    </div>
  </div>

  <!-- Manage Care Giver Modal -->
  <div class="modal-bg" id="manage-modal-bg">
    <div class="modal-dialog">
      <button class="modal-close" id="manage-modal-close" aria-label="Close">&times;</button>
      <div class="modal-title">Manage Care Giver</div>
      <form id="manage-form" autocomplete="off">
        <div class="modal-form-row">
          <div class="modal-form-col">
            <div class="modal-form-group">
              <label for="manage-caregiver">Care Giver</label>
              <input type="text" id="manage-caregiver" name="caregiver" value="Pedro Amerikano" />
            </div>
            <div class="modal-form-group">
              <label for="manage-type">CareGiver Type:</label>
              <input type="text" id="manage-type" name="type" value="Full Time" />
            </div>
            <div class="modal-form-group">
              <label for="manage-age">Age</label>
              <input type="number" id="manage-age" name="age" value="25" />
            </div>
            <div class="modal-form-group">
              <label for="manage-company">Company</label>
              <input type="text" id="manage-company" name="company" value="Care PH" />
            </div>
            <div class="modal-form-group">
              <label for="manage-status">Status</label>
              <input type="text" id="manage-status" name="status" value="Active" />
            </div>
          </div>
          <div class="modal-form-col">
            <div class="modal-form-group">
              <label for="manage-patient-name">Patient Name</label>
              <input type="text" id="manage-patient-name" name="patient_name" value="Juan Pinoy" />
            </div>
            <div class="modal-form-group">
              <label for="manage-vital">Vital Record</label>
              <textarea id="manage-vital" name="vital" rows="4">BP: 120
HR: 150
BS: 100</textarea>
            </div>
            <div class="modal-form-group">
              <label for="manage-contact">Contact Number</label>
              <input type="text" id="manage-contact" name="contact" value="09123456789" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="green">Edit</button>
          <button type="button" id="manage-modal-close-btn">Close</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // CareGiver Details Modal logic
    const modalBg = document.getElementById('modal-bg');
    const modalClose = document.getElementById('modal-close');
    const modalCloseBtn = document.getElementById('modal-close-btn');

    function setModalData(row) {
      document.getElementById('modal-caregiver-name').textContent = row.dataset.name;
      document.getElementById('modal-caregiver-id').textContent = row.dataset.id;
      document.getElementById('modal-caregiver-company').textContent = row.dataset.company;
      document.getElementById('modal-caregiver-type').textContent = row.dataset.type;
      document.getElementById('modal-caregiver-age').textContent = row.dataset.age;
      document.getElementById('modal-patient-id').textContent = row.dataset['patientId'];
      document.getElementById('modal-patient-name').textContent = row.dataset['patientName'];
      document.getElementById('modal-stroke-type').textContent = row.dataset['strokeType'];
      document.getElementById('modal-patient-age').textContent = row.dataset['patientAge'];
    }

    document.querySelectorAll('#caregiver-table tbody tr').forEach(row => {
      row.addEventListener('click', function() {
        setModalData(this);
        modalBg.classList.add('active');
      });
    });

    function closeModal() { modalBg.classList.remove('active'); }
    modalClose.addEventListener('click', closeModal);
    modalCloseBtn.addEventListener('click', closeModal);
    modalBg.addEventListener('click', function(e) { if (e.target === modalBg) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === "Escape") closeModal(); });

    // Manage Modal logic
    const manageModalBg = document.getElementById('manage-modal-bg');
    const manageModalClose = document.getElementById('manage-modal-close');
    const manageModalCloseBtn = document.getElementById('manage-modal-close-btn');
    const manageBtn = document.getElementById('manageBtn');

    function openManageModal() {
      // Optionally, prefill with blank or default values
      document.getElementById('manage-caregiver').value = "Pedro Amerikano";
      document.getElementById('manage-type').value = "Full Time";
      document.getElementById('manage-age').value = "25";
      document.getElementById('manage-company').value = "Care PH";
      document.getElementById('manage-status').value = "Active";
      document.getElementById('manage-patient-name').value = "Juan Pinoy";
      document.getElementById('manage-vital').value = "BP: 120\nHR: 150\nBS: 100";
      document.getElementById('manage-contact').value = "09123456789";
      manageModalBg.classList.add('active');
    }
    manageBtn.addEventListener('click', openManageModal);
    function closeManageModal() { manageModalBg.classList.remove('active'); }
    manageModalClose.addEventListener('click', closeManageModal);
    manageModalCloseBtn.addEventListener('click', closeManageModal);
    manageModalBg.addEventListener('click', function(e) { if (e.target === manageModalBg) closeManageModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === "Escape") closeManageModal(); });

    // Prevent form submit default for demo
    document.getElementById('manage-form').addEventListener('submit', function(e) {
      e.preventDefault();
      alert('Edit submitted!');
      closeManageModal();
    });
  </script>
</body>
</html>
