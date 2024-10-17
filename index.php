<?php
// Xác định trang hiện tại dựa trên tham số 'page' trong URL Ví dụ: index.php?page=dashboard.
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // Mặc định là dashboard
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web IoT BTL 2024</title>

    <!-- bootstrap -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous" />
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"></script>

    <!-- main css -->
    <link rel="stylesheet" href="./css/style.css" />

    <!-- chartjs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

    <!-- flatpickr.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- jQuery & DataTable -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.min.css">
    <script charset= "utf8" src = "https://cdn.datatables.net/2.1.4/js/dataTables.min.js"></script>

    <!-- gg fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet" />

  <!-- font awesome -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/fontawesome.min.css" integrity="sha512-B46MVOJpI6RBsdcU307elYeStF2JKT87SsHZfRSkjVi4/iZ3912zXi45X5/CBr/GbCyLx6M1GQtTKYRd52Jxgw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
  <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/> -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" /> -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script defer src="https://use.fontawesome.com/releases/v5.15.0/js/all.js"></script>

  

</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand <?php echo ($page == 'profile') ? 'active' : ''; ?>" href="index.php?page=dashboard" >B21DCAT134</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
          <ul class="nav nav-pills me-auto mb-2 mb-lg-0">
            <li class="nav-item <?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
              <a class="nav-link" id="dashboard" aria-current="page" href="index.php?page=dashboard" >Dashboard</a>
            </li>
            <li class="nav-item <?php echo ($page == 'data-sensor') ? 'active' : ''; ?>">
              <a class="nav-link" id="dashboard" aria-current="page" href="index.php?page=dashboard" >Data sensor</a>
            </li>
            <li class="nav-item <?php echo ($page == 'action-history') ? 'active' : ''; ?>">
              <a class="nav-link" id="dashboard" aria-current="page" href="index.php?page=dashboard" >Action History</a>
            </li>
            <li class="nav-item <?php echo ($page == 'profile') ? 'active' : ''; ?>">
              <a class="nav-link" id="dashboard" aria-current="page" href="index.php?page=dashboard" >Profile</a>
            </li>
          </ul>
          <span class="navbar-text <?php echo ($page == 'dashboard') ? 'active' : ''; ?>" href="index.php?page=dashboard" >Hệ thống IoT - Theo dõi và điều khiển thiết bị</span>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php
        // Include trang dựa vào giá trị của biến $page
        if ($page == 'dashboard') {
            include 'views/dashboard.php';
        } elseif ($page == 'data_sensor') {
            include 'views/data_sensor.php';
        } elseif ($page == 'action_history') {
            include 'views/action_history.php';
        } elseif ($page == 'profile') {
            include 'views/profile.php';
        }
        ?>
    </div>
    
    <script src="./js/scripts.js"></script>
</body>
</html>
