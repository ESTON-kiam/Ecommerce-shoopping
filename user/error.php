<?php
http_response_code(404); // Set the HTTP status code to 404
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
            padding-top: 50px;
        }
        .error-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
        }
        h1 {
            color: #d9534f;
        }
        p {
            color: #333;
        }
        .home-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404 - Page Not Found</h1>
        <p>Sorry, the page you are looking for might have been removed or is temporarily unavailable.</p>
        <a href="/" class="home-link">Return to Home</a>
    </div>
</body>
</html>