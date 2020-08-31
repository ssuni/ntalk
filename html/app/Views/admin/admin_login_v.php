<div class="home-btn d-none d-sm-block">
    <a href="index.html"><i class="fas fa-home h2 text-dark"></i></a>
</div>

<div class="account-pages mt-5 mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="text-center">
                    <a href="index.html">
                        <span><img src="<?php echo base_url('assets/images/logo-light.png');?>" alt="" height="22"></span>
                    </a>
                    <p class="text-muted mt-2 mb-4">엔톡 관리자</p>
                </div>
                <div class="card">

                    <div class="card-body p-4">

                        <div class="text-center mb-4">
                            <h4 class="text-uppercase mt-0">관리자 로그인</h4>
                        </div>

<!--                        <form action="#">-->

                            <div class="form-group mb-3">
                                <label for="emailaddress">아이디</label>
                                <input class="form-control" autocomplete="off"  type="email" id="loginId" name="id" placeholder="아이디를 입력하세요.">
                            </div>

                            <div class="form-group mb-3">
                                <label for="password">패스워드</label>
                                <input class="form-control" type="password" id="password" name="password" required="" placeholder="비밀번호를 입력하세요.">
                            </div>

                            <div class="form-group mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="checkbox-signin" checked>
                                    <label class="custom-control-label" for="checkbox-signin">Remember me</label>
                                </div>
                            </div>

                            <div class="form-group mb-0 text-center">
                                <button class="btn btn-primary btn-block" type="submit" id="loginSubmit"> 로그인 </button>
                            </div>

<!--                        </form>-->

                    </div> <!-- end card-body -->
                </div>
                <!-- end card -->

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <p> <a href="pages-recoverpw.html" class="text-muted ml-1"><i class="fa fa-lock mr-1"></i>Forgot your password?</a></p>
                        <p class="text-muted">Don't have an account? <a href="pages-register.html" class="text-dark ml-1"><b>Sign Up</b></a></p>
                    </div> <!-- end col -->
                </div>
                <!-- end row -->

            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</div>
<!-- end page -->