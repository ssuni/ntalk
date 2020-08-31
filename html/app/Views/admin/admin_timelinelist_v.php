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
                            <h4 class="mt-0 header-title">타임라인리스트</h4>
                            <p class="text-muted font-14 mb-3">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">타임라인</a></li>
                                <li class="breadcrumb-item"><a href="#">리스트</a></li>
                            </ol>
                            <!--                            Use one of two modifier classes to make <code>&lt;thead&gt;</code>s appear light or dark gray.-->
                            </p>
                            <!--table start div-->
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>
                                            <input type="checkbox" id="allCheck" value="timeline">
                                        </th>
                                        <th>닉네임</th>
                                        <th>아이디</th>
                                        <th>전화번호</th>
                                        <th>제목</th>
                                        <th>내용</th>
                                        <th>성별</th>
                                        <th>선호나이</th>
                                        <th>선호_지역</th>
                                        <th>선호_성별</th>

                                        <th>설정</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php if(isset($timeline)){ helper('function');?>
                                        <?php $num =  $total-($limit*($getPage-1)) ?>
                                        <?php if(count($timeline)>0){?>
                                        <?php foreach ($timeline as $lt){?>
                                            <tr>
                                                <td scope="row"><?php echo $num;?></td>
                                                <td>
                                                    <input type="checkbox" id="<?php echo $lt->t_idx;?>" name="timelineCheckBox[]" value="" >
                                                </td>
                                                <td><?php echo $lt->nickname;?></td>
                                                <td><?php echo $lt->uid;?></td>
                                                <td><?php echo hyphen_hp_number($lt->phone);?></td>
                                                <td>
                                                    <div style="width:100px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap">
                                                    <?php echo $lt->title;?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="width:150px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap">
                                                    <?php echo $lt->comment;?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    if($lt->gender == 'male'){
                                                        echo '남자';
                                                    }else if($lt->gender == 'female'){
                                                        echo '여자';
                                                    }else{
                                                        echo '전체';
                                                    }
                                                    ;?>
                                                </td>
                                                <td>
                                                    <?php

                                                        if($lt->minAge !== "-1" && $lt->maxAge == "-1"){
                                                            echo $lt->minAge.'세 이상';
                                                        }else if($lt->minAge == "-1" && $lt->maxAge !== "-1"){
                                                            echo $lt->maxAge.'세 이하';
                                                        }else if(($lt->minAge == "-1" && $lt->maxAge == "-1") || ($lt->minAge == "" && $lt->maxAge == "")) {
                                                            echo '';
                                                        }else{
                                                            echo $lt->minAge.'세~'.$lt->maxAge.'세';
                                                        }
                                                    ?>
                                                </td>
                                                <td><?php echo $lt->flocation.' '.$lt->flocation2;?></td>
                                                <td>
                                                    <?php
                                                    if($lt->fgender == 'male'){
                                                        echo '남자';
                                                    }else if($lt->fgender == 'female'){
                                                        echo '여자';
                                                    }else{
                                                        echo '';
                                                    }
                                                    ;?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-icon waves-effect waves-light btn-primary" name="timelineinfo" value="<?php echo $lt->t_idx;?>">
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
                                                <td colspan="12">데이터가 없습니다.</td>
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
                                <button type="button" id="delButton_timeline" class="btn btn-danger waves-effect width-md waves-light delete" data-title="timeline" data-content="timelineCheckBox[]" disabled>삭제</button>
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
                    <h4 class="modal-title" id="myModalLabel">타임라인</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="card-box">
                        <div class="table-responsive">

                            <table class="table table-bordered table-striped mb-0" id="userInfo" style="width: 99%">
                                <tbody>
                                <tr>
                                    <img src="https://ntalk.me/assets/images/users/user-1.jpg" alt="user-img" title="Mat Helme" class="rounded-circle img-thumbnail avatar-lg">
                                </tr>
                                <tr>
                                    <td width="30%">닉네임</td>
                                    <td width="65%" id="nickname"></td>
                                </tr>
                                <tr>
                                    <td>아이디</td>
                                    <td id="id"></td>
                                </tr>
                                <tr>
                                    <td>전화번호</td>
                                    <td id="phone">
<!--                                        <a href="#" id="timeline-phone" data-type="text" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your firstname" class="time_line editable editable-click editable-empty" style=""></a>-->
                                    </td>
                                </tr>
                                <tr>
                                    <td>선호_성별</td>
                                    <td><a href="#" id="timeline-sex" data-type="select" data-pk="1" data-value="" data-title="Select sex" data-name="fgender" class="time_line editable editable-click editable-unsaved" style="color: blue; background-color: rgba(0, 0, 0, 0);"></a></td>
                                </tr>
                                <tr>
                                    <td>선호_나이</td>
                                    <td>
                                        <div class="form-group row">
                                            <div class="col-sm-10">
                                                <input type="text" id="range_04" data-name="age" class="time_line editable editable-click editable-empty">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>선호_지역</td>
                                    <td>
                                        <a href="#" id="inline-location1" data-type="select" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your location" data-name="flocation" class="time_line editable editable-click editable-empty" style=""></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>선호_지역</td>
                                    <td>
                                        <a href="#" id="inline-location2"  data-type="select" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your location" data-name="flocation2"  class="time_line editable editable-click editable-empty" style=""></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>타이틀</td>
                                    <td>
                                        <a href="#" id="timeline-title"  data-type="text" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your location" data-name="title"  class="time_line editable editable-click editable-empty" style=""></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>내용</td>
                                    <td>
                                        <a href="#" id="timeline-comment"  data-type="textarea" data-pk="1" data-placement="right" data-placeholder="Required" data-title="Enter your location" data-name="comment"  class="time_line editable editable-click editable-empty" style=""></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>첨부이미지</td>
                                    <td>
                                        <form id="img_form" method="post" enctype="multipart/form-data" action="/admin/timelineImgUpload">
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
                    <button type="button" class="btn btn-primary waves-effect waves-light" data-value="" id="save" value="time_line">저장</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- ============================================================== -->
    <!-- End User Setting Modal -->
    <!-- ============================================================== -->

</div>
<!-- END wrapper -->
