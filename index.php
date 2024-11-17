<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Selection</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .container {
            text-align: center;
            padding: 2rem;
        }

        h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .buttons-container {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .login-button {
            width: 280px;
            padding: 2rem;
            border: none;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.95);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .login-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .icon {
            width: 64px;
            height: 64px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        .button-text {
            font-size: 1.25rem;
            color: #1a202c;
            font-weight: 600;
        }

        .button-description {
            color: #4a5568;
            font-size: 0.9rem;
            max-width: 200px;
        }

        @media (max-width: 640px) {
            .buttons-container {
                flex-direction: column;
                gap: 1rem;
            }

            .login-button {
                width: 100%;
                padding: 1.5rem;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Our Modern Cart</h1>
        <div class="buttons-container">
            <button onclick="window.location.href='http://localhost:8000/user'" class="login-button">
                <div class="icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <span class="button-text">Customer Login</span>
                <span class="button-description">Shop and manage your orders</span>
            </button>

            <button onclick="window.location.href='http://localhost:8000/admin'" class="login-button">
                <div class="icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                </div>
                <span class="button-text">Admin Login</span>
                <span class="button-description">Manage store and inventory</span>
            </button>
        </div>
    </div>
</body>
</html>