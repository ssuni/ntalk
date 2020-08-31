<!-- Begin page -->
<div id="wrapper">
<!-- ============================================================== -->
<!-- Start Page Content here -->
<!-- ============================================================== -->

<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-12">
                    <div class="card-box">
                        <div class="dropdown float-right">
                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">Another action</a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">Something else</a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">Separated link</a>
                            </div>
                        </div>
                        <h4 class="mt-0 header-title">회원리스트</h4>
                        <p class="text-muted font-14 mb-3">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">회원관리</a></li>
                                <li class="breadcrumb-item"><a href="#">리스트</a></li>
                            </ol>
<!--                            Use one of two modifier classes to make <code>&lt;thead&gt;</code>s appear light or dark gray.-->
                        </p>
                        <!--table start div-->
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="thead-dark">
                                <tr>
                                    <th width="50px;">#</th>
                                    <th width="50px;">
                                        <input type="checkbox" id="allCheck" value="user" >
                                    </th>
                                    <th>닉네임</th>
                                    <th>아이디</th>
                                    <th>전화번호</th>
                                    <th>성별</th>
                                    <th>나이</th>
                                    <th>지역</th>
                                    <th>IP</th>
                                    <th>가입유형</th>
                                    <th>설정</th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php if(isset($users)){ helper('function')?>
                                    <?php $num =  $total-($limit*($getPage-1)) ?>
                                    <?php if(count($users)>0){?>
                                    <?php foreach ($users as $lt){?>
                                        <tr>
                                            <th scope="row"><?php echo $num;?></th>
                                            <td>
                                                <input type="checkbox" id="<?php echo $lt->idx;?>" name="userCheckBox[]" value="" >
                                            </td>
<!--                                            <td><a href="#custom-modal" data-animation="fadein" data-plugin="custommodal" data-overlayColor="#36404a">--><?php //echo $lt->nickname;?><!--</a></td>-->
                                            <td class="block" data="<?php echo $lt->id;?>"><?php echo $lt->nickname;?></td>
                                            <td><?php echo $lt->id;?></td>
                                            <td><?php echo hyphen_hp_number($lt->phone);?></td>
                                            <td>
                                                <?php
                                                if($lt->gender == 'male'){
                                                    echo '남자';
                                                }else if($lt->gender == 'female'){
                                                    echo '여자';
                                                }else{
                                                    echo '';
                                                }
                                                ;?>
                                            </td>
                                            <td><?php
                                                if($lt->age && $lt->age !== '-1'){
                                                    echo $lt->age.'세';
                                                }else{
                                                    echo '미지정';
                                                }
//                                                echo ($lt->age)?$lt->age.'세':'';
                                                ?>
                                            </td>
                                            <td><?php echo $lt->location.' '.$lt->location2;?></td>
                                            <td><?php echo $lt->login_ip;?></td>
                                            <td><?php
                                                    switch ($lt->type){
                                                        case 'email' : echo 'Web';
                                                            break;
                                                        case 'google' : echo '구글';
                                                            break;
                                                        case 'kakao' : echo '카카오';
                                                            break;
                                                        case 'naver' : echo '네이버';
                                                            break;
                                                        case 'facebook' : echo '페이스북';
                                                            break;
                                                        case 'ntalk' : echo '엔톡어플';
                                                            break;
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-icon waves-effect waves-light btn-primary" name="userinfo" value="<?php echo $lt->id;?>">
<!--                                                        data-toggle="modal" data-target="#myModal"-->
<!--                                                        id="del_user" value="--><?php //echo $lt->id;?><!--">-->
                                                    <i class="fe-settings noti-icon"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php $num--;?>
                                    <?php }?>
                                    <?php }else{?>
                                        <tr style="text-align: center">
                                            <td colspan="10">데이터가 없습니다.</td>
                                        </tr>
                                    <?php }?>

                                <?php }?>

                                </tbody>
                            </table>
                        </div>
                        <!--table end div-->
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-auto mr-auto">
                            <?php echo $pager->links();?>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="reset" class="btn btn-warning waves-effect width-md waves-light" data-title="reset" data-content="reset[]">SMS 인증초기화</button>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="delButton_user" class="btn btn-danger waves-effect width-md waves-light delete" data-title="user" data-content="userCheckBox[]" disabled>회원삭제</button>
                        </div>
                    </div>
                </div>

            </div> <!--class row-->

        </div> <!-- container -->

    </div> <!-- content -->

    <!-- Footer Start -->
    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    2019 &copy; JLCOMPANY
                </div>
                <div class="col-md-6">
                    <div class="text-md-right footer-links d-none d-sm-block">
                        <a href="javascript:void(0);">About Us</a>
                        <a href="javascript:void(0);">Help</a>
                        <a href="javascript:void(0);">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- end Footer -->

</div>

<!-- ============================================================== -->
<!-- End Page content -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Start User Setting Modal -->
<!-- ============================================================== -->

<div id="myModal" class="modal fade bs-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">회원관리</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="card-box">
                    <div class="table-responsive">

                        <table class="table table-bordered table-striped mb-0" id="userInfo" style="width: 99%">
                            <tbody>
                            <tr>
                                <img src="https://ntalk.me/assets/images/users/user-1.jpg" alt="user-img" title="Mat Helme" id="user_profile" class="rounded-circle img-thumbnail avatar-lg">
                            </tr>
                            <tr>
                                <td width="30%">닉네임</td>
                                <td width="65%"><a href="#" id="inline-nickname" data-type="text" data-pk="" data-title="Enter nickname" data-name="nickname" class="users_info editable editable-click editable-empty" style=""></a></td>
                            </tr>
                            <tr>
                                <td>아이디</td>
                                <td>
                                    <span id="id"></span>
<!--                                    <a href="#" id="inline-id" data-type="text" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your firstname" data-name="id" class="userinfo editable editable-click editable-empty" style="">test</a>-->
                                </td>
                            </tr>
                            <tr>
                                <td>전화번호</td>
                                <td><a href="#" id="inline-phone" data-type="text" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your firstname" class="users_info editable editable-click editable-empty" style=""></a></td>
                            </tr>
                            <tr>
                                <td>성별</td>
                                <td><a href="#" id="inline-sex" data-type="select" data-pk="1" data-value="" data-title="Select sex" data-name="gender" class="users_info editable editable-click editable-unsaved" style="color: blue; background-color: rgba(0, 0, 0, 0);"></a></td>
                            </tr>
                            <tr>
                                <td>나이</td>
                                <td><a href="#" id="inline-age" data-type="text" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your age" data-name="age" class="users_info editable editable-click editable-empty" style=""></a></td>
                            </tr>
                            <tr>
                                <td>지역</td>
                                <td>
                                    <a href="#" id="inline-location1" data-type="select" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your location" data-name="location" class="users_info editable editable-click editable-empty" style=""></a>
                                </td>
                            </tr>
                            <tr>
                                <td>지역</td>
                                <td>
                                    <a href="#" id="inline-location2"  data-type="select" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your location" data-name="location2"  class="users_info editable editable-click editable-empty" style=""></a>
                                </td>
                            </tr>
                            <tr>
                                <td>IP</td>
                                <td>
                                    <span id="ip"></span>
                                </td>
                            </tr>

                            <tr>
                                <td>프로필이미지</td>
                                <td>
                                    <form id="img_form" method="post" enctype="multipart/form-data" action="/admin/userImgUpload">
                                        <div class="container-fluid">
                                            <div class="row" id='img_list'>

                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" id="save" value="users_info">저장</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- ============================================================== -->
<!-- End User Setting Modal -->
<!-- ============================================================== -->

<!-- ============================================================== -->
<!-- Start User Block Modal -->
<!-- ============================================================== -->
    <div id="custom-modal" class="modal-demo">
        <button type="button" class="close" onclick="Custombox.modal.close();">
            <span>&times;</span><span class="sr-only">Close</span>
        </button>
        <h4 class="custom-modal-title" style="background-color:#ff5b5b">차단설정</h4>
        <div class="custom-modal-text">
<!--            <div class="form-row">-->
<!--                <div class="form-group col-md-5">-->
<!--                    <label for="inputState" class="col-form-label">HOUR</label>-->
<!--                    <select id="inputState" class="form-control">-->
<!--                        <option value="0">선택</option>-->
<!--                        --><?php //for($i = 1; $i <= 12; $i++){?>
<!--                                <option value="--><?php //echo $i;?><!--">--><?php //echo $i;?><!--시간</option>-->
<!--                        --><?php //}?>
<!--                    </select>-->
<!--                </div>-->
<!--                <div class="form-group col-md-5">-->
<!--                    <label for="inputState" class="col-form-label">DAY</label>-->
<!--                    <select id="inputState" class="form-control">-->
<!--                        <option value="0">선택</option>-->
<!--                        --><?php //for($i = 1; $i <= 30; $i++){?>
<!--                            <option value="--><?php //echo $i;?><!--">--><?php //echo $i;?><!--일</option>-->
<!--                        --><?php //}?>
<!--                    </select>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
<!-- ============================================================== -->
<!-- End User Block Modal -->
<!-- ============================================================== -->

</div>
<!-- END wrapper -->
