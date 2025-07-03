<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to {{ $company_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .credentials {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to {{ $company_name }}!</h2>
        </div>
        <div class="content">
            <p>Hello {{ $user_name }},</p>
            <p>Thank you for joining us! Your account has been successfully created.</p>
            
            <div class="credentials">
                <p><strong>Your login credentials:</strong></p>
                <p>Email: {{ $user->email }}</p>
                <p>Password: {{ $password }}</p>
            </div>
            
            <p>For security reasons, we recommend changing your password after your first login.</p>
            
            <p>You can access your account by clicking the button below:</p>
            
            <a href="{{ $login_url }}" class="button">Login Now</a>
            
            <p>If you have any questions or need assistance, please contact our support team at {{ $support_email }}.</p>
            
            <p>Best regards,<br>The {{ $company_name }} Team</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ $company_name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
