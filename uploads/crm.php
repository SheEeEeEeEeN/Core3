<?php
include('sidebar.php');
include('database.php');

// --- CREATE ---
if (isset($_POST['addCustomer'])) {
    $name = $_POST['customer_name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    $sql = "INSERT INTO customers (customer_name, company, email, phone, status) VALUES ('$name','$company','$email','$phone','$status')";
    $conn->query($sql);
    header("Location: crm.php");
    exit;
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM customers WHERE id=$id");
    header("Location: crm.php");
    exit;
}

// --- UPDATE ---
if (isset($_POST['updateCustomer'])) {
    $id = $_POST['id'];
    $name = $_POST['customer_name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    $sql = "UPDATE customers SET customer_name='$name', company='$company', email='$email', phone='$phone', status='$status' WHERE id=$id";
    $conn->query($sql);
    header("Location: crm.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Freight Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .table thead {
            background-color: #0f2027;
            color: white;
        }

        .page-title {
            font-weight: bold;
            color: #0f2027;
        }
    </style>
</head>

<body>

    <div class="main-content p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title">Customer Relationship Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                + Add Customer
            </button>
        </div>

        <!-- Search & Filter -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchInput" placeholder="Search customer...">
            </div>
            <div class="col-md-4">
                <select class="form-select" id="filterStatus">
                    <option value="">Filter by Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Prospect">Prospect</option>
                </select>
            </div>
        </div>

        <!-- Customer Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Last Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead><tbody id="customerTable">
<?php
$editModals = ""; // collect modals here

$result = $conn->query("SELECT * FROM customers ORDER BY id asc");
while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['customer_name']}</td>
        <td>{$row['company']}</td>
        <td>{$row['email']}</td>
        <td>{$row['phone']}</td>
        <td><span class='badge " .
            ($row['status']=="Active" ? "bg-success" : ($row['status']=="Prospect" ? "bg-info" : "bg-secondary")) .
            "'>{$row['status']}</span></td>
        <td>{$row['last_contact']}</td>
        <td>
            <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#editCustomerModal{$row['id']}'>Edit</button>
            <a href='crm.php?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this customer?\")'>Delete</a>
        </td>
    </tr>";

    // collect edit modal instead of printing it here
    $editModals .= "
    <div class='modal fade' id='editCustomerModal{$row['id']}' tabindex='-1'>
        <div class='modal-dialog'>
            <form method='post' class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title'>Edit Customer</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                </div>  
                <div class='modal-body'>
                    <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                    <div class='mb-3'><label>Name</label>
                        <input type='text' class='form-control' name='customer_name' value='" . htmlspecialchars($row['customer_name']) . "' required>
                    </div>
                    <div class='mb-3'><label>Company</label>
                        <input type='text' class='form-control' name='company' value='" . htmlspecialchars($row['company']) . "' required>
                    </div>
                    <div class='mb-3'><label>Email</label>
                        <input type='email' class='form-control' name='email' value='" . htmlspecialchars($row['email']) . "' required>
                    </div>
                    <div class='mb-3'><label>Phone</label>
                        <input type='text' class='form-control' name='phone' value='" . htmlspecialchars($row['phone']) . "' required>
                    </div>
                    <div class='mb-3'><label>Status</label>
                        <select class='form-select' name='status'>
                            <option value='Active'" . ($row['status']=="Active" ? " selected" : "") . ">Active</option>
                            <option value='Prospect'" . ($row['status']=="Prospect" ? " selected" : "") . ">Prospect</option>
                            <option value='Inactive'" . ($row['status']=="Inactive" ? " selected" : "") . ">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                    <button type='submit' name='updateCustomer' class='btn btn-primary'>Update</button>
                </div>
            </form>
        </div>
    </div>";
}
?>
</tbody>
</table>

<!-- ✅ Place all edit modals AFTER the table -->
<?php echo $editModals; ?>

            </table>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Customer Name</label><input type="text" class="form-control" name="customer_name" required></div>
                    <div class="mb-3"><label>Company</label><input type="text" class="form-control" name="company" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email" required></div>
                    <div class="mb-3"><label>Phone</label><input type="text" class="form-control" name="phone" required></div>
                    <div class="mb-3"><label>Status</label>
                        <select class="form-select" name="status">
                            <option value="Active">Active</option>
                            <option value="Prospect">Prospect</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="addCustomer" class="btn btn-primary">Add Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search filter
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#customerTable tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // Status filter
        document.getElementById('filterStatus').addEventListener('change', function() {
            let filter = this.value;
            let rows = document.querySelectorAll('#customerTable tr');
            rows.forEach(row => {
                let status = row.querySelector('td:nth-child(5)').innerText.trim();
                row.style.display = filter === '' || status === filter ? '' : 'none';
            });
        });
    </script>

</body>

</html>