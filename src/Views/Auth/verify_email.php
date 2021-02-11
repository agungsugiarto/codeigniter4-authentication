<?= $this->extend('Auth\layout') ?>

<?= $this->section('content') ?>
<section class="d-flex align-items-center my-5 mt-lg-4 mb-lg-5">
    <div class="container">
        <p class="text-center"><a href="/" class="text-gray-700"><i class="fas fa-angle-left me-2"></i> Back to homepage</a></p>
        <div class="row justify-content-center form-bg-image" data-background-lg="https://cdn.jsdelivr.net/npm/@themesberg/volt-bootstrap-5-dashboard@1.3.1/src/assets/img/illustrations/signin.svg">
            <div class="col-12 d-flex align-items-center justify-content-center">
                <div class="bg-white shadow-soft border rounded border-light p-4 p-lg-5 w-100 fmxw-500">
                    <div class="text-center text-md-center mb-4 mt-md-0">
                        <h1 class="mb-0 h3">Verification Account</h1>
                    </div>
                    <!-- Validation Errors and Message -->
                    <?= $this->include('Auth\messages') ?>
                    <div class="d-flex justify-content-center align-items-center mt-4">
                        <span class="fw-normal">
                            Thanks for signing up! Before getting started, could you verify your email address by clicking on
                            the link we just emailed to you? If you didn&#039;t receive the email, we will gladly send you
                            another.
                        </span>
                    </div>
                    <form method="post" action="<?= route_to('verification.send') ?>" class="mt-4">
                        <?= csrf_field() ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark">Resend Verification Email</button>
                        </div>
                    </form>
                    <form method="post" action="<?= route_to('logout') ?>" class="mt-4">
                        <?= csrf_field() ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark">Logout</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>