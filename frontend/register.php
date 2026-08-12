<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="assets/favicon.ico">
    <title>Register</title>
</head>
<body class="login-page">
    <img src="assets/NCIA-Logo-2022.png" alt="NCIA logo" width="200" height="70">

    <div class="login-section">
        <h2 class="form-title">Applicant Registration</h2>
        <form class="login-details" action="../backend/register-validation.php" method="POST">

            <div id="error-message" style="display:none; color:red; margin-bottom:10px;"></div>

            <div>
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="John Doe" required>
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="john@example.com" required>
            </div>

            <div>
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="+254 700 000000" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button class="sign-in-button">Register</button>

            <p class="form-link">Already have an account? <a href="login.php">Sign in</a></p>
        </form>
    </div>

    <script>
        const params = new URLSearchParams(window.location.search);
        const error = params.get('error');
        if (error) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = decodeURIComponent(error);
            errorDiv.style.display = 'block';
        }
    </script>
</body>
</html>
