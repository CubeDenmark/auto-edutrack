<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7f7f7; color: #333333;">
    <!-- Main Table -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f7f7f7;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <!-- Email Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); max-width: 100%;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 30px 0; background-color: #4f46e5; background-image: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                            <table width="90%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <!-- Logo Placeholder - Replace with your actual logo -->
                                        <img src="https://via.placeholder.com/150x50/ffffff/4f46e5?text=YourLogo" alt="Company Logo" style="display: block; margin-bottom: 15px; max-width: 150px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <h1 style="color: #ffffff; font-size: 24px; font-weight: 600; margin: 0; padding: 0;">New Contact Message</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px 0;">
                                            You've received a new message from <strong style="color: #4f46e5;">{{ $data['name'] }}</strong>.
                                        </p>
                                    </td>
                                </tr>

                                <!-- Sender Info -->
                                <tr>
                                    <td style="padding: 0 0 20px 0;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; border-radius: 6px; border-left: 4px solid #4f46e5;">
                                            <tr>
                                                <td style="padding: 15px 20px;">
                                                    <p style="font-size: 15px; line-height: 1.5; margin: 0 0 5px 0;">
                                                        <strong style="color: #4b5563;">From:</strong> {{ $data['name'] }}
                                                    </p>
                                                    <p style="font-size: 15px; line-height: 1.5; margin: 0;">
                                                        <strong style="color: #4b5563;">Email:</strong>
                                                        <a href="mailto:{{ $data['email'] }}" style="color: #4f46e5; text-decoration: none;">{{ $data['email'] }}</a>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Message -->
                                <tr>
                                    <td style="padding: 0 0 20px 0;">
                                        <p style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 10px 0;">Message:</p>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; border-radius: 6px;">
                                            <tr>
                                                <td style="padding: 20px;">
                                                    <p style="font-size: 15px; line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $data['message'] }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- CTA Button -->
                                <tr>
                                    <td align="center" style="padding: 10px 0 20px 0;">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="background-color: #4f46e5; border-radius: 6px;">
                                                    <a href="mailto:{{ $data['email'] }}" style="display: inline-block; padding: 12px 24px; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none;">Reply to {{ $data['name'] }}</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 40px;">
                            <div style="height: 1px; background-color: #e5e7eb;"></div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <p style="font-size: 14px; color: #6b7280; margin: 0 0 10px 0;">
                                            This message was sent from your website contact form.
                                        </p>
                                        <p style="font-size: 14px; color: #6b7280; margin: 0 0 10px 0;">
                                            © 2025 Your Company. All rights reserved.
                                        </p>
                                        <p style="font-size: 14px; margin: 0;">
                                            <a href="#" style="color: #4f46e5; text-decoration: none; margin: 0 10px;">Website</a>
                                            <a href="#" style="color: #4f46e5; text-decoration: none; margin: 0 10px;">Privacy Policy</a>
                                            <a href="#" style="color: #4f46e5; text-decoration: none; margin: 0 10px;">Unsubscribe</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Email Preferences -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 100%;">
                    <tr>
                        <td align="center" style="padding: 20px 0 0 0;">
                            <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                                If you have any questions, contact us at <a href="mailto:support@yourcompany.com" style="color: #6b7280; text-decoration: underline;">support@yourcompany.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

{{-- 1.1 Working --}}
{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        h2 {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            color: #333;
        }
        .info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="email-container">
    <h2>📩 New Message from {{ $data['name'] }}</h2>

    <p><strong>Email:</strong> <span class="info">{{ $data['email'] }}</span></p>
    <p><strong>Message:</strong></p>
    <div class="info">
        {{ $data['message'] }}
    </div>

    <p class="footer">This message was sent from your website contact form.</p>
</div>

</body>
</html> --}}


{{-- Working 1.0 --}}
{{-- <h2>New Message from {{ $data['name'] }}</h2>
<p><strong>Email:</strong> {{ $data['email'] }}</p>
<p><strong>Message:</strong> {{ $data['message'] }}</p> --}}
