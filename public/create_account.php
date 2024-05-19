    <?php
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }

    require_once '../config/database.php';
    require '../vendor/autoload.php';  

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fullName = $_POST['full_name'];
        $email = $_POST['email'];
        
        // Check if the email already exists in the database
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            echo "<p>Email already exists. Please use a different email address.</p>";
            exit;
        }

        // Generate a temporary username and hashed password
        $username = explode('@', $email)[0];
        $tempPassword = password_hash($username, PASSWORD_BCRYPT);

        // Generate activation link
        $activationLink = bin2hex(random_bytes(16));
        $linkExpiration = date('Y-m-d H:i:s', strtotime('+1 minute'));

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, created_at, updated_at, account_activation_link, link_expiration, status, must_change_password) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, 'blocked', 1)");
        $stmt->execute([$username, $email, $tempPassword, $fullName, 'salesperson', $activationLink, $linkExpiration]);

        // Send activation email
        $activationUrl = "http://localhost/Final-Web2023-2024/pos-system/public/verify_account.php?link=$activationLink";
        $subject = "Account Activation";
        $message = "Click the following link to activate your account: $activationUrl.\r\n This link is valid for 1 minute.";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'trankyanh202@gmail.com';
            $mail->Password = 'snpm scpr xfib pngd';  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;


            $mail->setFrom('trankyanh202@gmail.com', 'Ky Anh');
            $mail->addAddress($email);  


            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            echo "<p>Account created. An activation email has been sent to $email.<br></p>
            <p>You will be send back to the Dasboard after 2s.</p>";
            echo "<script>
                    setTimeout(function(){
                        window.location.href = 'dashboard.php';
                    }, 2000);
                </script>";
        } catch (Exception $e) {
            echo "<p>Failed to send activation email. Mailer Error: {$mail->ErrorInfo}</p>";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Account</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <style>
            .bg {
                background-color: rgb(203, 183, 183);
            }
        </style>
    </head>
    <body>
        <div class="d-flex justify-content-center align-items-center bg" style="height: 100vh;">
            <form action="" method="post" class="bg-light p-4 rounded shadow">
                <h1>Create Account</h1>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name:</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>
        </div>
    </body>
    </html>
