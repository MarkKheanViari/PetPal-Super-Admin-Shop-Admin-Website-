<?php
$order_id = $_GET['order_id'] ?? 'Unknown';

$deep_link = "intent://payment_success?order_id=$order_id#Intent;scheme=myapplication;package=com.example.myapplication;end";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <script>
        window.onload = function() {
            var deepLink = "<?php echo $deep_link; ?>";

            // Open deep link in a new tab
            var a = document.createElement("a");
            a.href = deepLink;
            a.style.display = "none";
            document.body.appendChild(a);
            a.click();

            // Fallback if deep link doesn't work
            setTimeout(function() {
                window.location.href = "https://10.40.70.46/thankyou";
            }, 5000);
        };
    </script>
</head>
<body>
    <h2>Processing Payment...</h2>
    <p>If you are not redirected, <a href="<?php echo $deep_link; ?>">click here</a>.</p>
</body>
</html>
