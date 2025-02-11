<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu">

    <div class="slimscroll-menu">

        <!-- User box -->
        <div class="user-box text-center">
            <img src="<?php echo base_url('assets/images/users/user-1.jpg');?>" alt="user-img" title="Mat Helme" class="rounded-circle img-thumbnail avatar-lg">
            <div class="dropdown">
                <a href="#" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block" data-toggle="dropdown">Nowak Helme</a>
                <div class="dropdown-menu user-pro-dropdown">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-user mr-1"></i>
                        <span>My Account</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-settings mr-1"></i>
                        <span>Settings</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-lock mr-1"></i>
                        <span>Lock Screen</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-log-out mr-1"></i>
                        <span>Logout</span>
                    </a>

                </div>
            </div>
            <p class="text-muted">Admin Head</p>
            <ul class="list-inline">
                <li class="list-inline-item">
                    <a href="#" class="text-muted">
                        <i class="mdi mdi-settings"></i>
                    </a>
                </li>

                <li class="list-inline-item">
                    <a href="#" class="text-custom">
                        <i class="mdi mdi-power"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <ul class="metismenu" id="side-menu">

<!--                <li class="menu-title">Navigation</li>-->
                <li>
                    <a href="/admin/main">
                        <i class="mdi mdi-domain"></i>
                        <!--                        <i class="mdi mdi-view-dashboard"></i>-->
                        <span> 메인 </span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);">
                        <i class="fas fa-user"></i>
                        <span> 회원관리 </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="/admin/user_lists">회원리스트</a></li>
<!--                        <li><a href="#">차단 리스트</a></li>-->

                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);">
                        <i class="mdi mdi-chart-timeline"></i>
                        <span> 타임라인 </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="nav-second-level" aria-expanded="false">
                        <li><a href="/admin/timeline_lists">타임라인리스트</a></li>
                        <li><a href="/admin/timeline_declaration">신고리스트</a></li>

                    </ul>
                </li>
<!---->
<!--                <li>-->
<!--                    <a href="javascript: void(0);">-->
<!--                        <i class="mdi mdi-texture"></i>-->
<!--                        <span class="badge badge-warning float-right">7</span>-->
<!--                        <span> Forms </span>-->
<!--                    </a>-->
<!--                    <ul class="nav-second-level" aria-expanded="false">-->
<!--                        <li><a href="form-elements.html">General Elements</a></li>-->
<!--                        <li><a href="form-advanced.html">Advanced Form</a></li>-->
<!--                        <li><a href="form-validation.html">Form Validation</a></li>-->
<!--                        <li><a href="form-wizard.html">Form Wizard</a></li>-->
<!--                        <li><a href="form-fileupload.html">Form Uploads</a></li>-->
<!--                        <li><a href="form-quilljs.html">Quilljs Editor</a></li>-->
<!--                        <li><a href="form-xeditable.html">X-editable</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="javascript: void(0);">-->
<!--                        <i class="mdi mdi-view-list"></i>-->
<!--                        <span> Tables </span>-->
<!--                        <span class="menu-arrow"></span>-->
<!--                    </a>-->
<!--                    <ul class="nav-second-level" aria-expanded="false">-->
<!--                        <li><a href="tables-basic.html">Basic Tables</a></li>-->
<!--                        <li><a href="tables-datatable.html">Data Tables</a></li>-->
<!--                        <li><a href="tables-responsive.html">Responsive Table</a></li>-->
<!--                        <li><a href="tables-editable.html">Editable Table</a></li>-->
<!--                        <li><a href="tables-tablesaw.html">Tablesaw Table</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="javascript: void(0);">-->
<!--                        <i class="mdi mdi-chart-donut-variant"></i>-->
<!--                        <span> Charts </span>-->
<!--                        <span class="menu-arrow"></span>-->
<!--                    </a>-->
<!--                    <ul class="nav-second-level" aria-expanded="false">-->
<!--                        <li><a href="charts-flot.html">Flot Charts</a></li>-->
<!--                        <li><a href="charts-morris.html">Morris Charts</a></li>-->
<!--                        <li><a href="charts-chartist.html">Chartist Charts</a></li>-->
<!--                        <li><a href="charts-chartjs.html">Chartjs Charts</a></li>-->
<!--                        <li><a href="charts-other.html">Other Charts</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="calendar.html">-->
<!--                        <i class="mdi mdi-calendar"></i>-->
<!--                        <span> Calendar </span>-->
<!--                    </a>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="inbox.html">-->
<!--                        <i class="mdi mdi-email"></i>-->
<!--                        <span> Mail </span>-->
<!--                    </a>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="javascript: void(0);">-->
<!--                        <i class="mdi mdi-page-layout-sidebar-left"></i>-->
<!--                        <span class="badge badge-purple float-right">New</span>-->
<!--                        <span> Layouts </span>-->
<!--                    </a>-->
<!--                    <ul class="nav-second-level" aria-expanded="false">-->
<!--                        <li><a href="layouts-sidebar-sm.html">Small Sidebar</a></li>-->
<!--                        <li><a href="layouts-dark-sidebar.html">Dark Sidebar</a></li>-->
<!--                        <li><a href="layouts-dark-topbar.html">Dark Topbar</a></li>-->
<!--                        <li><a href="layouts-preloader.html">Preloader</a></li>-->
<!--                        <li><a href="layouts-sidebar-collapsed.html">Sidebar Collapsed</a></li>-->
<!--                        <li><a href="layouts-boxed.html">Boxed</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="javascript: void(0);">-->
<!--                        <i class="mdi mdi-google-pages"></i>-->
<!--                        <span> Pages </span>-->
<!--                        <span class="menu-arrow"></span>-->
<!--                    </a>-->
<!--                    <ul class="nav-second-level" aria-expanded="false">-->
<!--                        <li><a href="pages-starter.html">Starter Page</a></li>-->
<!--                        <li><a href="pages-login.html">Login</a></li>-->
<!--                        <li><a href="pages-register.html">Register</a></li>-->
<!--                        <li><a href="pages-recoverpw.html">Recover Password</a></li>-->
<!--                        <li><a href="pages-lock-screen.html">Lock Screen</a></li>-->
<!--                        <li><a href="pages-confirm-mail.html">Confirm Mail</a></li>-->
<!--                        <li><a href="pages-404.html">Error 404</a></li>-->
<!--                        <li><a href="pages-500.html">Error 500</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="javascript: void(0);">-->
<!--                        <i class="mdi mdi-layers"></i>-->
<!--                        <span> Extra Pages </span>-->
<!--                        <span class="menu-arrow"></span>-->
<!--                    </a>-->
<!--                    <ul class="nav-second-level" aria-expanded="false">-->
<!--                        <li><a href="extras-projects.html">Projects</a></li>-->
<!--                        <li><a href="extras-tour.html">Tour</a></li>-->
<!--                        <li><a href="extras-taskboard.html">Taskboard</a></li>-->
<!--                        <li><a href="extras-taskdetail.html">Task Detail</a></li>-->
<!--                        <li><a href="extras-profile.html">Profile</a></li>-->
<!--                        <li><a href="extras-maps.html">Maps</a></li>-->
<!--                        <li><a href="extras-contact.html">Contact list</a></li>-->
<!--                        <li><a href="extras-pricing.html">Pricing</a></li>-->
<!--                        <li><a href="extras-timeline.html">Timeline</a></li>-->
<!--                        <li><a href="extras-invoice.html">Invoice</a></li>-->
<!--                        <li><a href="extras-faq.html">FAQs</a></li>-->
<!--                        <li><a href="extras-gallery.html">Gallery</a></li>-->
<!--                        <li><a href="extras-email-templates.html">Email Templates</a></li>-->
<!--                        <li><a href="extras-maintenance.html">Maintenance</a></li>-->
<!--                        <li><a href="extras-comingsoon.html">Coming Soon</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li>-->
<!--                    <a href="javascript: void(0);">-->
<!--                        <i class="mdi mdi-share-variant"></i>-->
<!--                        <span> Multi Level </span>-->
<!--                        <span class="menu-arrow"></span>-->
<!--                    </a>-->
<!--                    <ul class="nav-second-level nav" aria-expanded="false">-->
<!--                        <li>-->
<!--                            <a href="javascript: void(0);">Level 1.1</a>-->
<!--                        </li>-->
<!--                        <li>-->
<!--                            <a href="javascript: void(0);" aria-expanded="false">Level 1.2-->
<!--                                <span class="menu-arrow"></span>-->
<!--                            </a>-->
<!--                            <ul class="nav-third-level nav" aria-expanded="false">-->
<!--                                <li>-->
<!--                                    <a href="javascript: void(0);">Level 2.1</a>-->
<!--                                </li>-->
<!--                                <li>-->
<!--                                    <a href="javascript: void(0);">Level 2.2</a>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                        </li>-->
<!--                    </ul>-->
<!--                </li>-->
            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>
<!-- Left Sidebar End -->