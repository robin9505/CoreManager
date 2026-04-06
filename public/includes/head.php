<?php
$username = $_SESSION['username'];
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CoreManager - <?php echo $pageTitle ?? 'Panel'; ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php if (isset($loadDataTables)): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<?php endif; ?>