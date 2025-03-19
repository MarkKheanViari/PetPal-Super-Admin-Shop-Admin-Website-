<?php
if (!isset($_GET['deep_link'])) {
    echo "Missing deep link!";
    exit();
}

$deep_link = $_GET['deep_link'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <script>
        setTimeout(function () {
            window.location.href = "<?php echo $deep_link; ?>";
        }, 3000); // Wait 3 seconds before redirecting
    </script>
</head>
<body>
    <p>Redirecting back to the app...</p>
    <p>If you are not redirected, <a href="<?php echo $deep_link; ?>">click here</a>.</p>
</body>
</html>
