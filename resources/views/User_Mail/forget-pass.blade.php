<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    
    <title></title>
    
       
</head>
<body style="margin:0;padding:0;">
    
                            
                                        <p>
                                        Hello  {{ $user ? $user['name'] : '' }},</h1>
                                        <p>
                                            We've received a request to reset the password.
                                            </p>
                                        <p>
                                            You can reset your password by clicking the button below:
                                        </p>

                                        <p>
                                            <a href="{{'http://localhost:8000/cust-forgot-password/'.$user['token']}}" class="btn"> Reset your password</a>
                                        </p>


                                        <p>
                                            Thank
                                            you, </p>
                                        <p>
                                           Car Space Rental </p>
                                    
</body>
</html>