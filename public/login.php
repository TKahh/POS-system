<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- <link rel="stylesheet" href="assets/css/styles.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <section class="vh-70 gradient-custom">
        <div class="container py-5 h-70">
            <div class="row d-flex justify-content-center align-items-center h-70">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-dark text-white" style="border-radius: 1rem;">
                        <div class="card-body p-5 text-center">

                            <div class="mb-md-2 mt-md-4 pb-5">

                                <h2 class="fw-bold mb-5 text-uppercase">Login</h2>

                                <?php
                                session_start();
                                if (isset($_SESSION['error'])) {
                                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                                    unset($_SESSION['error']);
                                }
                                ?>
                                
                                <form action="login_handler.php" method="post">
                                    <div data-mdb-input-init class="form-outline form-white mb-4">
                                        <input type="text" id="typeusername" name="username" class="form-control form-control-lg" required />
                                        <label class="form-label" for="typeusername">Username</label>
                                    </div>

                                    <div data-mdb-input-init class="form-outline form-white mb-4">
                                        <input type="password" id="typePasswordX" name="password" class="form-control form-control-lg" required/>
                                        <label class="form-label" for="typePasswordX">Password</label>
                                    </div>
                                    <button data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>
                                </form>
                            </div>
                            <p class="text-white-50 ">Please enter your login and password!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
