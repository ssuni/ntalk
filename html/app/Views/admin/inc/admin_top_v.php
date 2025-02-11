<!-- Topbar Start -->
<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">

        <?php if(uri_string() !== 'admin/main'){?>
        <li class="d-none d-sm-block">
            <?php if(uri_string() == 'admin/user_lists'){?>
            <form class="app-search" action="/admin/user_lists" method="GET" style="max-width: 500px;">
            <?php }?>
            <?php if(uri_string() == 'admin/timeline_lists'){?>
            <form class="app-search" action="/admin/timeline_lists" method="GET" style="max-width: 500px;">
            <?php }?>
                <div class="app-search-box" style="width: 400px;">
                    <div class="input-group">

                        <select name="division" class="form-control" style="border-radius: 0px; margin-right: 20px;">
                            <option value="">전체</option>
                            <?php if(uri_string() == 'admin/user_lists'){?>
                                <option value="id" <?php if(isset($_GET['division'])){ echo ($_GET['division']=='id')?'selected':'';}?>>아이디</option>
                                <option value="nickname" <?php if(isset($_GET['division'])){ echo ($_GET['division']=='nickname')?'selected':'';}?>>닉네임</option>
                                <option value="location" <?php if(isset($_GET['division'])){ echo ($_GET['division']=='location')?'selected':'';}?>>지역</option>
                            <?php }?>
                            <?php if(uri_string() == 'admin/timeline_lists'){?>
                                <option value="id" <?php if(isset($_GET['division'])){ echo ($_GET['division']=='id')?'selected':'';}?>>아이디</option>
                                <option value="nickname" <?php if(isset($_GET['division'])){ echo ($_GET['division']=='nickname')?'selected':'';}?>>닉네임</option>
                                <option value="title" <?php if(isset($_GET['division'])){ echo ($_GET['division']=='title')?'selected':'';}?>>타이틀</option>
                            <?php }?>
                        </select>
                        <input type="text" class="form-control" name="keyword" style="border-radius : 0px;0px;0px;0px;" placeholder="검색어..." value="<?php echo(isset($_GET['keyword']))?$_GET['keyword']:""?>">
                        <div class="input-group-append">
                            <button class="btn" type="submit" style="border-radius : 0px;0px;0px;0px;" >
                                <i class="fe-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </li>
        <?php }?>
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="fe-bell noti-icon"></i>
                <span class="badge badge-danger rounded-circle noti-icon-badge">9</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-lg">

                <!-- item-->
                <div class="dropdown-item noti-title">
                    <h5 class="m-0">
                                    <span class="float-right">
                                        <a href="" class="text-dark">
                                            <small>Clear All</small>
                                        </a>
                                    </span>Notification
                    </h5>
                </div>

                <div class="slimscroll noti-scroll">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item active">
                        <div class="notify-icon">
                            <img src="<?php echo base_url('assets/images/users/user-1.jpg');?>" class="img-fluid rounded-circle" alt="" /> </div>
                        <p class="notify-details">Cristina Pride</p>
                        <p class="text-muted mb-0 user-msg">
                            <small>Hi, How are you? What about our next meeting</small>
                        </p>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <div class="notify-icon bg-primary">
                            <i class="mdi mdi-comment-account-outline"></i>
                        </div>
                        <p class="notify-details">Caleb Flakelar commented on Admin
                            <small class="text-muted">1 min ago</small>
                        </p>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <div class="notify-icon">
                            <img src="<?php echo base_url('assets/images/users/user-4.jpg');?>" class="img-fluid rounded-circle" alt="" /> </div>
                        <p class="notify-details">Karen Robinson</p>
                        <p class="text-muted mb-0 user-msg">
                            <small>Wow ! this admin looks good and awesome design</small>
                        </p>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <div class="notify-icon bg-warning">
                            <i class="mdi mdi-account-plus"></i>
                        </div>
                        <p class="notify-details">New user registered.
                            <small class="text-muted">5 hours ago</small>
                        </p>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <div class="notify-icon bg-info">
                            <i class="mdi mdi-comment-account-outline"></i>
                        </div>
                        <p class="notify-details">Caleb Flakelar commented on Admin
                            <small class="text-muted">4 days ago</small>
                        </p>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <div class="notify-icon bg-secondary">
                            <i class="mdi mdi-heart"></i>
                        </div>
                        <p class="notify-details">Carlos Crouch liked
                            <b>Admin</b>
                            <small class="text-muted">13 days ago</small>
                        </p>
                    </a>
                </div>

                <!-- All-->
                <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">
                    View all
                    <i class="fi-arrow-right"></i>
                </a>

            </div>
        </li>

        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <img src="<?php echo base_url('assets/images/users/user-1.jpg');?>" alt="user-image" class="rounded-circle">
                <span class="pro-user-name ml-1">
                                Nowak <i class="mdi mdi-chevron-down"></i>
                            </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                <!-- item-->
                <div class="dropdown-header noti-title">
                    <h6 class="text-overflow m-0">Welcome !</h6>
                </div>

                <!-- item-->
                <a href="javascript:void(0);" class="dropdown-item notify-item">
                    <i class="fe-user"></i>
                    <span>My Account</span>
                </a>

                <!-- item-->
                <a href="javascript:void(0);" class="dropdown-item notify-item">
                    <i class="fe-settings"></i>
                    <span>Settings</span>
                </a>

                <!-- item-->
                <a href="javascript:void(0);" class="dropdown-item notify-item">
                    <i class="fe-lock"></i>
                    <span>Lock Screen</span>
                </a>

                <div class="dropdown-divider"></div>

                <!-- item-->
                <a href="javascript:void(0);" class="dropdown-item notify-item">
                    <i class="fe-log-out"></i>
                    <span id="logout">Logout</span>
                </a>

            </div>
        </li>

        <li class="dropdown notification-list">
            <a href="javascript:void(0);" class="nav-link right-bar-toggle waves-effect">
                <i class="fe-settings noti-icon"></i>
            </a>
        </li>


    </ul>

    <!-- LOGO -->
    <div class="logo-box">
        <a href="index.html" class="logo text-center">
                        <span class="logo-lg">
                            <img src="<?php echo base_url('assets/images/logo-dark.png');?>" alt="" height="16">
                            <!-- <span class="logo-lg-text-light">Xeria</span> -->
                        </span>
            <span class="logo-sm">
                            <!-- <span class="logo-sm-text-dark">X</span> -->
                            <img src="<?php echo base_url('assets/images/logo-sm.png');?>" alt="" height="24">
                        </span>
        </a>
    </div>

    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile disable-btn waves-effect">
                <i class="fe-menu"></i>
            </button>
        </li>

        <li>
            <h4 class="page-title-main">Ntalk</h4>
        </li>

    </ul>
</div>
<!-- end Topbar -->