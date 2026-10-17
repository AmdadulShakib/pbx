<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo app('translator')->get('Admin Login'); ?></title>
    <link href="<?php echo e(asset('assets/admin/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/admin/admin-ui.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/vendor/toast/toastr.min.css')); ?>" rel="stylesheet">
</head>
<body class="admin-body admin-login d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-5 col-xl-4">
                <div class="card login-card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="login-brand-icon mx-auto mb-3">AP</div>
                            <h1 class="h4 mb-1"><?php echo app('translator')->get('Welcome back'); ?></h1>
                            <p class="text-muted mb-0"><?php echo app('translator')->get('Sign in to continue'); ?></p>
                        </div>
                        <form method="post" action="<?php echo e(route('admin.login')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control form-control-lg" id="admin-email" placeholder=" " value="<?php echo e(old('email')); ?>" required autofocus>
                                <label for="admin-email"><?php echo app('translator')->get('Email'); ?></label>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" name="password" class="form-control form-control-lg" id="admin-password" placeholder=" " required>
                                <label for="admin-password"><?php echo app('translator')->get('Password'); ?></label>
                                <button type="button" class="login-password-toggle" data-password-toggle data-target="admin-password" aria-label="<?php echo app('translator')->get('Toggle password'); ?>">
                                    <span class="icon-eye" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </span>
                                    <span class="icon-eye-off" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6 0-10-7-10-7a21.82 21.82 0 0 1 5.06-5.94"></path>
                                            <path d="M1 1l22 22"></path>
                                            <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c6 0 10 7 10 7a21.82 21.82 0 0 1-3.17 4.32"></path>
                                            <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember"><?php echo app('translator')->get('Remember me'); ?></label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg"><?php echo app('translator')->get('Sign In'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('admin.partials.toast-messages', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script src="<?php echo e(asset('assets/admin/js/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/toast/toastr.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/admin-ui.js')); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
                var target = toggle.getAttribute('data-target');
                var input = target ? document.getElementById(target) : null;
                if (!input) return;
                toggle.addEventListener('click', function () {
                    var isText = input.getAttribute('type') === 'text';
                    input.setAttribute('type', isText ? 'password' : 'text');
                    toggle.classList.toggle('is-on', !isText);
                });
            });
        });
    </script>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\AdminPanelBuilder\resources\views/admin/auth/login.blade.php ENDPATH**/ ?>