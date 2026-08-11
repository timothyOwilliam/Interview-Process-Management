<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Log in</title>
</head>
<body class="login-page">
    <img src="assets/NCIA-Logo-2022.png" alt="NCIA logo" width="200" height="70">

    <div class="login-section">
        <form class="login-details" action="../backend/login-validation.php" method="POST">

            <div id="error-message" style="display:none; color:red; margin-bottom:10px;"></div>
            <div id="success-message" style="display:none; color:green; margin-bottom:10px;"></div>

            <div class="email">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button class="sign-in-button">Sign in</button>

            <p class="form-link">Don't have an account? <a href="register.php">Register</a></p>

        </form>

    </div>

    <script>

        const params = new URLSearchParams(window.location.search); //window.location.search this is the captures text in the search bar
        const error = params.get('error'); //extract the value of the error //written in the PHP code
        const success = params.get('success');// for the success

        //if there is an error make the error dialog appear
        if (error) {
            const errorDiv = document.getElementById('error-message');

            errorDiv.textContent = decodeURIComponent(error);
            errorDiv.style.display = 'block';
        }
        //if the success value is true make the success error appear .
        if (success) {
            const successDiv = document.getElementById('success-message');
            successDiv.textContent = decodeURIComponent(success);
            successDiv.style.display = 'block';
        }
    </script>
</body>
</html>