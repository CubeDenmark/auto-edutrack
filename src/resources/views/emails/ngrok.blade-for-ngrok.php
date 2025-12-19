<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ngrok URL Update</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f5f7;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f5f7;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); max-width: 100%;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #000000; padding: 30px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left">
                                        <img src="https://ngrok.com/static/img/ngrok-white.svg" alt="Ngrok Logo" style="height: 40px; display: block;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0 0 20px 0; font-size: 24px; font-weight: 600; color: #111827;">
                                            Your Ngrok Tunnel is Ready
                                        </h1>
                                        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.5; color: #4b5563;">
                                            A new Ngrok tunnel has been established. You can access your service using the following URL:
                                        </p>
                                    </td>
                                </tr>

                                <!-- URL Box -->
                                <tr>
                                    <td style="padding: 20px; background-color: #f8fafc; border-radius: 6px; border: 1px solid #e5e7eb;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td>
                                                    <p style="margin: 0; font-family: monospace; font-size: 16px; word-break: break-all;">
                                                        <a href="{{ $ngrokUrl }}" style="color: #2563eb; text-decoration: none;" target="_blank">
                                                            {{ $ngrokUrl }}
                                                        </a>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Instructions -->
                                <tr>
                                    <td style="padding-top: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td>
                                                    <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #111827;">
                                                        Quick Tips:
                                                    </h2>
                                                    <ul style="margin: 0 0 20px 0; padding-left: 20px; color: #4b5563;">
                                                        <li style="margin-bottom: 10px;">Click the URL above to open it in your browser</li>
                                                        <li style="margin-bottom: 10px;">Copy and save this URL for your reference</li>
                                                        <li style="margin-bottom: 10px;">Share this URL with authorized team members only</li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Security Warning -->
                                <tr>
                                    <td style="padding-top: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #fff7ed; border-left: 4px solid #f97316; border-radius: 6px;">
                                            <tr>
                                                <td style="padding: 15px;">
                                                    <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #9a3412;">
                                                        <strong>Security Notice:</strong> This URL provides access to your local environment. Do not share it with unauthorized users. The tunnel will remain active until you stop the Ngrok process.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f8fafc; border-top: 1px solid #e5e7eb;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                            This is an automated message from your development environment.
                                        </p>
                                        <p style="margin: 10px 0 0 0; font-size: 14px; color: #6b7280;">
                                            For Ngrok documentation, visit
                                            <a href="https://ngrok.com/docs" style="color: #2563eb; text-decoration: none;" target="_blank">ngrok.com/docs</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>


{{-- 1.0 working --}}
{{-- <!DOCTYPE html>
<html>
<head>
    <title>Ngrok URL Update</title>
</head>
<body>
    <h2>Your new Ngrok URL:</h2>
    <p><a href="{{ $ngrokUrl }}" target="_blank">{{ $ngrokUrl }}</a></p>
</body>
</html> --}}
