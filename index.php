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
  <link rel="stylesheet" href="css\fontawesome\fontawesome-free-6.6.0-web\css\all.min.css">




  
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand <?php echo ($page == 'profile') ? 'active' : ''; ?>" href="index.php?page=profile" >B21DCAT134</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
          <ul class="nav nav-pills me-auto mb-2 mb-lg-0">
            <li class="nav-item ">
              <a class="nav-link <?php echo ($page == 'dashboard') ? 'active' : ''; ?> " id="dashboard" aria-current="page" href="index.php?page=dashboard" >Dashboard</a>
            </li>
            <li class="nav-item ">
              <a class="nav-link <?php echo ($page == 'data-sensor') ? 'active' : ''; ?>" id="data-sensor" aria-current="page" href="index.php?page=data-sensor" >Data sensor</a>
            </li>
            <li class="nav-item">
              <a class="nav-link  <?php echo ($page == 'action-history') ? 'active' : ''; ?>" id="action-history" aria-current="page" href="index.php?page=action-history" >Action History</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo ($page == 'profile') ? 'active' : ''; ?>" id="profile" aria-current="page" href="index.php?page=profile" >Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo ($page == 'bai5') ? 'active' : ''; ?>" id="bai5" aria-current="page" href="index.php?page=bai5" >Bài 5</a>
            </li>
          </ul>
          <span class="navbar-text">Hệ thống IoT - Theo dõi và điều khiển thiết bị</span>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php
        // Include trang dựa vào giá trị của biến $page
        if ($page == 'dashboard') {
            include 'views/dashboard.php';
        } elseif ($page == 'data-sensor') {
            include 'views/data-sensor.php';
        } elseif ($page == 'action-history') {
            include 'views/action-history.php';
        } elseif ($page == 'profile') {
            include 'views/profile.php';
        } elseif ($page == 'bai5'){
            include 'views/bai5.php';
        }
        ?>
    </div>
    
    <script>
      // Truyền biến $page từ PHP sang biến currentPage ở JavaScript
      var currentPage = "<?php echo $page; ?>";
    </script>

    <script src="./js/scripts.js"></script>
</body>
</html>
