<?= $this->extend('Auth/layout') ?>

<?= $this->section('content') ?>
<section class="vh-lg-100 mt-5 mt-lg-0 bg-soft d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center form-bg-image">
            <p class="text-center"><a href="/" class="d-flex align-items-center justify-content-center">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Back to homepage
                </a>
            </p>
            <div class="col-12 d-flex align-items-center justify-content-center">
                <div class="signin-inner my-3 my-lg-0 bg-white shadow border-0 rounded p-4 p-lg-5 w-100 fmxw-500">
                    <div class="text-center text-md-center mb-4 mt-md-0">
                        <h1 class="mb-0 h3">Verification Account</h1>
                    </div>
                    <!-- Validation Errors and Message -->
                    <?= $this->include('Auth/messages') ?>
                        <p class="mb-4">
                            Thanks for signing up! Before getting started, could you verify your email address by clicking on
                            the link we just emailed to you? If you didn&#039;t receive the email, we will gladly send you
                            another.
                        </p>
                    <form method="post" action="<?= route_to('verification.send') ?>">
                        <?= csrf_field() ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-gray-800">Resend Verification Email</button>
                        </div>
                    </form>
                    <form method="post" action="<?= route_to('logout') ?>" class="mt-4">
                        <?= csrf_field() ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-gray-800">Logout</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>