<?php
session_start();

unset($_SESSION['checkout_summary']);
unset($_SESSION['checkout_nama_pembeli']);
unset($_SESSION['checkout_jumlah_dibayarkan']);
unset($_SESSION['checkout_total_harga']);


header("Location: index.php");
exit();