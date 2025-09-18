<?php
include("darkmode.php");
include 'connection.php';
include('session.php');
requireRole('admin');

// Upload Document
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["uploadfile"])) {
    $title    = $conn->real_escape_string($_POST['docstitle']);
    $doc_type = $conn->real_escape_string($_POST['doc_type']);

    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = basename($_FILES["uploadfile"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $targetFilePath)) {
        $conn->query("INSERT INTO e_doc (title, doc_type, filename, status) 
                      VALUES ('$title', '$doc_type', '$fileName', 'Pending Review')");

        // Record activity
        $module = "E-Documentation";
        $activity = "Uploaded document: $title";
        $status = "Pending Review";
        $conn->query("INSERT INTO admin_activity (`module`, `activity`, `status`, `date`) 
                      VALUES ('$module', '$activity', '$status', NOW())");

        $_SESSION['alert'] = ['title' => 'Success!', 'text' => 'Document uploaded successfully.', 'icon' => 'success'];
    } else {
        $_SESSION['alert'] = ['title' => 'Error!', 'text' => 'Failed to upload document.', 'icon' => 'error'];
    }

    header("Location: E-Doc.php");
    exit;
}

// Edit Document
if (isset($_POST['save'])) {
    $editId = intval($_POST['edit_id']);
    $title = $_POST['docstitle'];
    $docType = $_POST['doc_type'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE e_doc SET title=?, doc_type=?, status=? WHERE id=?");
    if (!$stmt) die("SQL error: " . $conn->error);

    $stmt->bind_param("sssi", $title, $docType, $status, $editId);

    if ($stmt->execute()) {
        // Record activity
        $module = "E-Documentation";
        $activity = "Edited document: $title";
        $conn->query("INSERT INTO admin_activity (`module`, `activity`, `status`, `date`) 
                      VALUES ('$module', '$activity', '$status', NOW())");

        $_SESSION['alert'] = ['title' => 'Saved!', 'text' => 'Document updated successfully.', 'icon' => 'success'];
    } else {
        $_SESSION['alert'] = ['title' => 'Error!', 'text' => 'Failed to save changes.', 'icon' => 'error'];
    }

    header("Location: E-Doc.php");
    exit;
}

// Delete Document
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $conn->query("SELECT filename, title FROM e_doc WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $filePath = "uploads/" . $row['filename'];
        if (file_exists($filePath)) unlink($filePath); // remove file

        $conn->query("DELETE FROM e_doc WHERE id=$id");

        // Record activity
        $module = "E-Documentation";
        $activity = "Deleted document: " . $row['title'];
        $status = "Deleted";
        $conn->query("INSERT INTO admin_activity (`module`, `activity`, `status`, `date`) 
                      VALUES ('$module', '$activity', '$status', NOW())");
    }
    header("Location: E-Doc.php");
    exit;
}

// Fetch all documents
$sql = "SELECT * FROM e_doc ORDER BY uploaded_on DESC";
$result = $conn->query($sql);

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Documentation & Compliance Manager</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --text-light: #f8f9fa;
            --text-dark: #212529;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --border-radius: 0.35rem;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
            background-color: var(--secondary-color);
            color: var(--text-dark);
        }

        body.dark-mode {
            --secondary-color: var(--dark-bg);
            background-color: var(--secondary-color);
            color: var(--text-light);
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #2c3e50;
            color: white;
            padding: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            transform: translateX(0);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar .logo {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .logo img {
            max-width: 100%;
            height: auto;
        }

        .system-name {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar a {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid white;
        }

        .admin-feature {
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Main Content */
        .content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
        }

        .content.expanded {
            margin-left: 0;
        }

        /* Header */
        .header {
            background-color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dark-mode .header {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .hamburger {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .system-title {
            color: var(--primary-color);
            font-size: 1rem;
        }

        /* Upload Documents */
        .upload-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .dark-mode .upload-section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .uploadform {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .upload input,
        .upload select {
            width: 390px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-top: 0.5rem;
            background-color: white;
        }

        .dark-mode .upload input,
        .dark-mode .upload select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .upload .choose-file {
            display: inline-block;
            width: 390px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-top: 0.5rem;
            background-color: white;
            color: #444;
            cursor: pointer;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Dark mode */
        .dark-mode .upload .choose-file {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .btn {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-upload {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-upload:hover {
            background-color: #3a5bc7;
        }


        /* Search Documents*/
        .searchfilter {
            position: relative;
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .dark-mode .searchfilter {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .searchform {
            display: flex;
        }

        .search-titleortype input {
            width: 600px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin: 0.5rem 0.8rem 0 0;
        }

        .dark-mode .search-titleortype input {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .filter-select select {
            width: 400px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin: 0.5rem 0.8rem 0 0;
            background-color: white;
        }

        .dark-mode .filter-select select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .btn-search {
            background-color: var(--primary-color);
            color: white;
            width: 170px;
            margin-top: 0.5rem;
        }

        /* Table Section */
        .table-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .dark-mode .table-section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        table {
            width: 100%;
            margin-top: 1rem;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .dark-mode th,
        .dark-mode td {
            border-bottom-color: #3a4b6e;
        }

        thead {
            background-color: var(--primary-color);
            color: white;
        }

        .download {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            background-color: #1a629dff;
            color: white;
            text-decoration: none;
        }

        .download:hover {
            background-color: #0476d3ff;
        }

        .save,
        .edit {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            background-color: #60c452ff;
            color: white;
            text-decoration: none;
        }

        .save,
        .edit:hover {
            background-color: #29d812ff;
        }

        .cancel,
        .delete {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            background-color: #c03838ff;
            color: white;
            text-decoration: none;
        }

        .cancel,
        .delete:hover {
            background-color: #e50d0dff;
        }

        .title,
        .doc_type,
        .doc_status {
            width: 150px;
            padding: 0.2rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: white;
        }

        .dark-mode td input,
        .dark-mode td select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        /* Make SweetAlert smaller */
        .swal-small {
            width: 400px !important;
            font-size: 0.85rem !important;
            padding: 0.5rem !important;
        }

        .swal-small .swal2-title {
            font-size: 1rem !important;
        }

        .swal-small .swal2-html-container {
            font-size: 0.85rem !important;
        }


        /* Theme Toggle */
        .theme-toggle-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .theme-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-color);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="rem.png" alt="SLATE Logo">
        </div>
        <div class="system-name">CORE TRANSACTION 3</div>
        <a href="admin.php">Dashboard</a>
        <a href="CRM.php">Customer Relationship Management</a>
        <a href="CSM.php">Contract & SLA Monitoring</a>
        <a href="E-Doc.php" class="active">E-Documentations & Compliance Manager</a>
        <a href="BIFA.php">Business Intelligence & Freight Analytics</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1>E-Documentation & Compliance Manager</h1>
            </div>
            <div class="theme-toggle-container">
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="adminThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="upload-section">
            <h3>Upload New Documents</h3>
            <form action="E-Doc.php" method="POST" enctype="multipart/form-data">
                <div class="uploadform">
                    <div class="upload">
                        <input type="text" id="docstitle" name="docstitle" placeholder="Document Title" required>
                    </div>
                    <div class="upload">
                        <select id="doc_type" name="doc_type" required>
                            <option value="">Select Document Type</option>
                            <option value="Bill of Lading">Bill of Lading</option>
                            <option value="Invoice">Invoice</option>
                            <option value="Customs Clearance">Customs Clearance</option>
                            <option value="Compliance Certificate">Compliance Certificate</option>
                        </select>
                    </div>
                    <div class="upload">
                        <label for="uploadfile" class="choose-file">
                            Choose File: <span id="file-name">No file chosen</span>
                        </label>
                        <input type="file" id="uploadfile" name="uploadfile" required hidden>
                    </div>
                </div>
                <button type="submit" class="btn btn-upload">Upload Document</button>
            </form>
        </div>

        <!-- Search & Filter -->
        <div class="searchfilter">
            <h3>Search & Filter Documents</h3>
            <div class="searchform">
                <div class="search-titleortype">
                    <input type="text" id="search" placeholder="Search by Title or Type">
                </div>
                <div class="filter-select">
                    <select id="doc-status">
                        <option value="">Filter by Compliance Status</option>
                        <option value="Compliant">Compliant</option>
                        <option value="Pending Review">Pending Review</option>
                        <option value="Expired">Expired</option>
                    </select>
                </div>
                <div class="search-btn">
                    <button type="button" id="searchBtn" class="btn btn-search">Search</button>
                </div>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="table-section">
            <h3>Document Records</h3>
            <table id="employeesTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Filename</th>
                        <th>Uploaded On</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="DocumentsRecord">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            if ($editId === intval($row['id'])) {
                                echo "<tr>
                <form method='POST' action='E-Doc.php'>
                  <td><input type='text' class='title' name='docstitle' value='" . htmlspecialchars($row['title']) . "' required></td>
                  <td>
                    <select class='doc_type' name='doc_type' required>
                      <option value='Bill of Lading' " . ($row['doc_type'] == 'Bill of Lading' ? 'selected' : '') . ">Bill of Lading</option>
                      <option value='Invoice' " . ($row['doc_type'] == 'Invoice' ? 'selected' : '') . ">Invoice</option>
                      <option value='Customs Clearance' " . ($row['doc_type'] == 'Customs Clearance' ? 'selected' : '') . ">Customs Clearance</option>
                      <option value='Compliance Certificate' " . ($row['doc_type'] == 'Compliance Certificate' ? 'selected' : '') . ">Compliance Certificate</option>
                    </select>
                  </td>
                  <td>{$row['filename']}</td>
                  <td>{$row['uploaded_on']}</td>
                  <td>
                    <select class='doc_status' name='status'>
                      <option value='Pending Review' " . ($row['status'] == 'Pending Review' ? 'selected' : '') . ">Pending Review</option>
                      <option value='Compliant' " . ($row['status'] == 'Compliant' ? 'selected' : '') . ">Compliant</option>
                      <option value='Expired' " . ($row['status'] == 'Expired' ? 'selected' : '') . ">Expired</option>
                    </select>
                  </td>
                  <td>
                    <input type='hidden' name='edit_id' value='{$row['id']}'>
                    <button type='submit' name='save' class='save'>Save</button>
                    <a href='E-Doc.php' class='cancel'>Cancel</a>
                  </td>
                </form>
              </tr>";
                            } else {
                                echo "<tr>
                <td>{$row['title']}</td>
                <td>{$row['doc_type']}</td>
                <td>{$row['filename']}</td>
                <td>{$row['uploaded_on']}</td>
                <td>{$row['status']}</td>
                <td>
                  <a class='download' href='uploads/{$row['filename']}' download>Download</a>  
                  <a class='edit' href='?edit={$row['id']}'>Edit</a>  
                  <a class='delete' href='?delete={$row['id']}'>Delete</a>
                </td>
              </tr>";
                            }
                        }
                    } else {
                        echo "<tr><td colspan='6'>No documents found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <script>
            initDarkMode("adminThemeToggle", "adminDarkMode");

            // Sidebar Toggle
            document.getElementById('hamburger').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
                document.getElementById('mainContent').classList.toggle('expanded');
            });

            // File chooser
            const fileInput = document.getElementById("uploadfile");
            const fileName = document.getElementById("file-name");
            fileInput.addEventListener("change", function() {
                fileName.textContent = this.files.length > 0 ? this.files[0].name : "No file chosen";
            });

            // Search & Filter
            document.getElementById("searchBtn").addEventListener("click", function() {
                const searchValue = document.getElementById("search").value.toLowerCase();
                const statusFilter = document.getElementById("doc-status").value;
                const rows = document.querySelectorAll("#DocumentsRecord tr");

                rows.forEach(row => {
                    const title = row.cells[0]?.innerText.toLowerCase() || "";
                    const type = row.cells[1]?.innerText.toLowerCase() || "";
                    const status = row.cells[4]?.innerText || "";
                    row.style.display = (title.includes(searchValue) || type.includes(searchValue)) && (!statusFilter || status === statusFilter) ? "" : "none";
                });
            });

            // SweetAlert Delete Confirmation
            document.addEventListener("DOMContentLoaded", function() {
                const deleteLinks = document.querySelectorAll(".delete");

                deleteLinks.forEach(link => {
                    link.addEventListener("click", function(e) {
                        e.preventDefault(); // stop default action
                        const url = this.getAttribute("href");

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This document will be permanently deleted.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#c03838ff',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!',
                            customClass: {
                                popup: 'swal-small'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = url; // proceed with delete
                            }
                        });
                    });
                });
            });

            <?php if (isset($_SESSION['alert'])):
                $alert = $_SESSION['alert'];
                unset($_SESSION['alert']); ?>
                Swal.fire({
                    title: '<?= $alert['title'] ?>',
                    text: '<?= $alert['text'] ?>',
                    icon: '<?= $alert['icon'] ?>',
                    confirmButtonColor: '#4e73df',
                    customClass: {
                        popup: 'swal-small'
                    }
                });
            <?php endif; ?>
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>