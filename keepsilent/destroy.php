<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="">
    <title>start-session</title>
</head>

<body>
    <header class="p30 nvy clgrey">
    </header>
    <section class="p30 lgrey">
        <h2>session telah selesai</h2>
        <p>

            <?php
    session_unset();
    session_destroy();

    header('Location: index.php'); 
    ?>

        </p>
    </section>
</body>

</html>