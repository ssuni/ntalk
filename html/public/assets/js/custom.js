requirejs.config({
    baseUrl: "/assets/js",
    paths: {
        custom_modal: './custom_modal'
    }
});
requirejs([
    "custom_modal",
    ], function(custom_modal) {
    //This function is called after custom_modal.js has loaded.
    $(".block").on('click', function (e) {
        custom_modal.viewModal(this);
    })
    $(document).on('change',"#hour",function(e){
        custom_modal.onChangeModal($("#hour"))
    })
    $(document).on('change',"#day",function(e){
        custom_modal.onChangeModal($("#day"))
    })
    $(document).on('click','#block_save',function(e){
        var id = $("#block_id").val();
        var hour = $("#hour").val();
        var day = $("#day").val();
        alert(id)
        custom_modal.block_save(id,hour,day);
    })
});


$(function(){
    var response;
    //check box
    var allCheck = function(v){
        if($("#allCheck").prop("checked")) {
            //해당화면에 전체 checkbox들을 체크해준다
            $("input[type=checkbox]").prop("checked",true);
            $("#delButton_"+v).attr('disabled',false);
            // 전체선택 체크박스가 해제된 경우
        } else {
            //해당화면에 모든 checkbox들의 체크를해제시킨다.
            $("input[type=checkbox]").prop("checked",false);
            $("#delButton_"+v).attr('disabled',true);
        }
    }
    //i button selector , v input selector
    var checkboxChk = function(i,v){
        console.log(i)
        console.log($("#delButton_"+i))
        console.log($("#allCheck").prop("checked"))
        if($("#allCheck").prop("checked")){
            $("#allCheck").prop("checked",false);
        }else{
            if($("input[name='"+v+"']:checked").length) {
                $("#delButton_"+i).attr('disabled', false);
                if ($("input[name='"+v+"']:checked").length == $("input[name='"+v+"']").length) {
                    $("#allCheck").prop("checked", true);
                    $("#delButton_"+i).attr('disabled', false);
                }
            }else{
                $("#delButton_"+i).attr('disabled', true);
            }
        }
    }

    var userInfo = function(r){
        var id = r;
        $.post('/admin/getUser',{'id':id},function(e){
            response = JSON.parse(e)
            console.log(response)
            $('#inline-nickname').attr('data-pk',response.idx)
            $('#inline-phone').attr('data-pk',response.idx)
            $('#inline-sex').attr('data-pk',response.idx)
            $('#inline-age').attr('data-pk',response.idx)
            $('#inline-location1').attr('data-pk',response.idx)
            $('#inline-location2').attr('data-pk',response.idx)

            $('#inline-nickname').editable('setValue', response.nickname)
            $('#id').text(response.id)
            $('#inline-phone').editable('setValue', response.phone)
            if(response.gender) {
                $('#inline-sex').editable('setValue', response.gender)
            }
            $('#inline-age').editable('setValue', response.age)
            $('#inline-location1').editable('setValue', response.location)

            $('#inline-location2').editable({source:response.locationArr})
            $('#inline-location2').editable('setValue', response.location2)
            $('#ip').text(response.login_ip)

            var html = "";
            html += '<div class="col-md-6">';
            html += '   <div class="card-box">';

            if(response.f_tempname) {
                $('#user_profile').attr('src', 'https://files.ntalk.me/profile/' + response.file_url_thumb + response.f_tempname)
                html += '<input type="file" class="dropify" name="img[]" id="profile" data-default-file="https://files.ntalk.me/profile/' + response.file_url_thumb + response.f_tempname+'" id="'+response.id+'"></input>'
            }else{
                $('#user_profile').attr('src', 'https://ntalk.me/assets/images/users/user-1.jpg')
                html += '<input type="file" class="dropify" name="img[]" id="profile" data-default-file="" id="'+response.id+'"></input>'
            }
            html += '   </div>';
            html += '</div>';

            $("#img_list").empty()
            $("#img_list").append(html)
            $(".dropify").dropify({
                messages:{default:"Drag and drop a file here or click",
                    replace:"Drag and drop or click to replace",
                    remove:"Remove",
                    error:"Ooops, something wrong appended."
                },error:{
                    fileSize:"The file size is too big (1M max)."
                }
            });
            var drEvent = $('.dropify').dropify();

            if($('#profile').attr('data-default-file')) {
                drEvent.on('dropify.afterClear', function (event, element) {
                    var url = element.settings.defaultFile;
                    var id = $("#id").html();
                    var delUrl = url.replace('https://files.ntalk.me', '');

                    Swal.fire({
                        title: '삭제 하시겠습니까?',
                        text: "삭제 후 되돌릴수 없습니다!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        // cancelButtonColor: '#d33',
                        cancelButtonText: '취소',
                        confirmButtonText: '삭제'
                    }).then((result) => {
                        console.log(result.dismiss)
                        if (result.dismiss !== 'cancel') {
                            //Ajax
                            $.post('/admin/delete_profile',{'id' : id ,'delUrl' : delUrl},function(res){
                                console.log(res)
                               if(res == "success"){
                                   alert();
                                   statusAlert(200,'삭제완료','/admin/user_lists');
                               }
                            });
                        } else {
                            var drEvent = $('.dropify').dropify();

                            console.log(drEvent)

                            drEvent = drEvent.data('dropify');
                            drEvent.settings['defaultFile'] = 'https://files.ntalk.me' + delUrl;
                            drEvent.destroy();
                            drEvent.init();
                        }
                    })
                });
            }

            $('#myModal').modal('show');
        })
    }

    var timelineInfo = function(r){
        var id = r;
        $.post('/admin/getTimeLineWithUser',{'id':id},function(e){
            response = JSON.parse(e)
            console.log(response)
            $('#nickname').text(response.nickname)
            $('#nickname').attr('data-pk',response.t_idx)
            $('#id').text(response.uid)
            $('#phone').text(response.phone)
            // $('#timeline-phone').attr('data-pk',response.t_idx)
            $('#timeline-sex').attr('data-pk',response.t_idx)
            $('#timeline-minage').attr('data-pk',response.t_idx)
            $('#timeline-maxage').attr('data-pk',response.t_idx)
            $('#inline-location1').attr('data-pk',response.t_idx)
            $('#inline-location2').attr('data-pk',response.t_idx)

            $('#inline-nickname').editable('setValue', response.nickname)
            $('#inline-id').editable('setValue', response.id)
            $('#timeline-phone').editable('setValue', response.phone)
            if(response.fgender) {
                $('#timeline-sex').editable('setValue', response.fgender)
            }
            $("#timeline-title").editable('setValue',response.title)
            $("#timeline-comment").editable('setValue',response.comment)

            if(response.minAge == "-1"){
                response.minAge = "19"
            }
            if(response.maxAge == "-1"){
                response.maxAge = "60"
            }

            $("#range_04").ionRangeSlider({
                type:"double",
                min:19,
                max:60,
                from:response.minAge,
                to:response.maxAge,
                onFinish: function (data) {
                    // Called then action is done and mouse is released
                    $('#range_04').editable('setValue', [data.from,data.to])
                }
            })

            $('#inline-location1').editable('setValue', response.flocation)
            $('#inline-location2').editable({source:response.locationArr})
            $('#inline-location2').editable('setValue', response.flocation2)


            var html = "";
            // if(response.files.length){
            //     fileLength = response.files.length;
            // }else{
                fileLength = 5;
            // }
            for(var i=0; i<fileLength; i++){
                html += '<div class="col-md-6">';
                html += '   <div class="card-box">';
                if(response.files[i]) {
                    html += '       <input type="file" class="dropify" name="img[]" id="img'+i+'" data-pk="'+response.files[i].f_idx+'" data-default-file="https://files.ntalk.me/timeline/' + response.files[i].file_url_thumb + response.files[i].f_tempname + '"></input>';
                }else{
                    html += '       <input type="file" class="dropify" name="img[]" id="img'+i+'" data-default-file=""></input>';
                }
                html += '   </div>';
                html += '</div>';
            }
            $("#img_list").empty()
            $("#img_list").append(html)
            $(".dropify").dropify({
                messages:{default:"Drag and drop a file here or click",
                    replace:"Drag and drop or click to replace",
                    remove:"Remove",
                    error:"Ooops, something wrong appended."
                },error:{
                    fileSize:"The file size is too big (1M max)."
                }
            });
            var drEvent = $('.dropify').dropify();
            drEvent.on('dropify.afterClear', function(event, element){
                var id = $("#id").html();
                var f_idx = $(this).attr('data-pk');
                var url = element.settings.defaultFile;
                var delUrl = url.replace('https://files.ntalk.me', '');
                if(element.settings.defaultFile !== ""){
                    Swal.fire({
                        title: '삭제 하시겠습니까?',
                        text: "삭제 후 되돌릴수 없습니다!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        // cancelButtonColor: '#d33',
                        cancelButtonText: '취소',
                        confirmButtonText: '삭제'
                    }).then((result) => {
                        var id = $(element['element'])[0]['id'];
                        if (result.dismiss !== 'cancel') {
                            //Ajax
                            $.post('/admin/delete_timeline_img',{'id' : id ,'f_idx' : f_idx},function(res){
                                console.log(res)
                                if(res == "success"){
                                    statusAlert(200,'삭제완료','/admin/user_lists');
                                }
                            });
                        } else {
                            var drEvent = $('#'+id).dropify();
                            console.log(drEvent);
                            drEvent = drEvent.data('dropify');
                            drEvent.settings['defaultFile'] = url;
                            drEvent.destroy();
                            drEvent.init();
                        }
                    })
                }else{
                    alert('false');
                }

                // alert('File deleted');
            });
            $("#save").attr('data-value',response.idx);
            $('#myModal').modal('show');
        })
    }

    var saveTable = function(selector){
        if(selector == "time_line" || selector == "users_info"){
            var form = $('#img_form')[0];
            var formData = new FormData(form);
            var fileLength = $('input[type="file"]').length;
            formData.append('id',$("#id").html())

            if(selector == "users_info"){
                formData.append('img_division',1)
            }else if(selector == "time_line"){
                formData.append('img_division',2)
            }else{
                formData.append('img_division',0)
            }

            var upfile = false;
            for(var i = 0; i < fileLength; i++){
                if($('input[type="file"]').get(i).files.length == 1){
                    console.log($('input[type="file"]').get(i).files[0])
                    upfile = true;
                }
            }
            if(upfile){
             $.ajax({
                 url: '/admin/imgUpload',
                 enctype: 'multipart/form-data',
                 processData: false,
                 contentType: false,
                 data: formData,
                 type: 'POST',
                 cache: false,
                 timeout: 600000,
                 success: function(res){
                     console.log(res)
                     if(selector == "time_line"){
                         if(res == 'false') {
                             statusAlert(404, '타임라인 이미지 업로드 개수 초과')
                         }
                     }
                     // debugger;
                     var result = JSON.parse(res)
                     if(selector == "users_info") {
                         $('#user_profile').attr('src',result['files'][0]['thumb'])
                     }
                 }
                });
            }
          //  console.log(formData)
            if(selector == "users_info"){
                var pk = $("#inline-nickname").attr('data-pk');
            }else if(selector == "time_line"){
                var pk = $("#nickname").attr('data-pk');
            }else{
                var pk = $("#timeline-phone").attr('data-pk');
            }
            $("."+selector).editable('submit',{
                url: "/admin/"+selector+"_update",
                data: {pk: pk},
                success: function(params,config){
                    // console.log(JSON.parse(params))
                    console.log(params)
                    if(params == 'true') {
                        console.log(params)
                        if(selector == "users_info") {
                            statusAlert(200, '수정완료', '/admin/user_lists');
                            $("#myModal").modal('hide');
                        }else if(selector == 'time_line'){
                            statusAlert(200, '수정완료', '/admin/timeline_lists');
                            $("#myModal").modal('hide');
                        }
                        setTimeout(function() {
                            // $("#myModal").modal('hide');
                        }, 500);
                    }else if(params == "ageFail"){
                        statusAlert(404,'나이설정 오류');
                    }else{
                        statusAlert(406,'서버오류','/admin/user_lists');
                    }
                },
                error: function(error){
                    console.log(error)
                }
            })
        }else if(selector == 'users_block'){
            alert('')
        }
    }

    var statusAlert = function(status,message,url,focus){
        var icon = ""
        switch (status) {
            case 200: icon = 'success';
                break;
            case 403: icon = 'info'
                break;
            case 404: icon = 'warning'
                break;
            case 406: icon = 'error'
                break;
        }

        Swal.fire({
            title:message,
            confirmButtonText:'확인',
            icon:icon
            // text:'Your file has been deleted.'
        }).then((result)=>{
            if(status == 200 || status == 403){
                location.href = url
            }else if(status == 406){
                location.reload()
            }else{
                setTimeout(function() {
                    $("#"+focus).val("")
                    $("#"+focus).focus()
                }, 500);
            }

        })
    }

    var sms_verification = function(){
        $.get('/admin/sms_verification',function(){
            statusAlert(403,'SMS 인증횟수 전체 초기화','/admin/user_lists');
        })
    }

    $("#password").keydown(function(e){
        if(e.keyCode == 13){
            $("#loginSubmit").trigger('click')
        }
    })

    $("#loginSubmit").on('click',function(){
        var id = $("#loginId").val();
        var password = $("#password").val();
        if(id == ""){
            statusAlert(404,'아이디를 입력하세요.','');
            return false;
        }
        if(password == ""){
            statusAlert(404,'비밀번호를 입력하세요.','');
            return false;
        }
        $.post('/admin/do_login',{'id':id , 'password':password},function(res){
            response = JSON.parse(res);
            switch (response['status']) {
                case 200: statusAlert(response['status'],response['message'],response['url'])
                    break;
                case 404: statusAlert(response['status'],response['message'],'',"password")
                    break;
                default : statusAlert(response['status'],response['message'],'')
                    break;
            }
        })
    })

    $("#logout").on('click',function(){
        $.get('/admin/logout',function(){
            statusAlert(403,'로그아웃','/admin/login');
        })
    })

    $(".delete").on('click',function(e){
        var title = $(this)[0]['dataset']['title']
        var content = $(this)[0]['dataset']['content']

        Swal.fire({
            title: '삭제 하시겠습니까?',
            text: "삭제 후 되돌릴수 없습니다!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            // cancelButtonColor: '#d33',
            cancelButtonText: '취소',
            confirmButtonText: '삭제'
        }).then((result) => {
            //체크박스 체크값 추출
            var arrIdx = []
            $("input[name='"+content+"']:checked").each(function() {
                var idx = $(this).attr('id');
                arrIdx.push(idx);
            });
            if (result.value) {
                $.post('/admin/delete_'+title,{'idx':arrIdx},function(res){
                    console.log(title)
                    console.log(res)
                })
                Swal.fire({
                    title:'삭제완료!',
                    confirmButtonText:'확인',
                    icon:'success',
                    // text:'Your file has been deleted.'
                }).then((result)=>{
                    if(result.value){
                        location.reload()
                    }
                })
            }
        })

    })
    $("#delUser_button").on('click',function(e){
        Swal.fire({
            title: '삭제 하시겠습니까?',
            text: "삭제 후 되돌릴수 없습니다!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            // cancelButtonColor: '#d33',
            cancelButtonText: '취소',
            confirmButtonText: '삭제'
        }).then((result) => {
            //체크박스 체크값 추출
            var users = []
            $("input[name='userCheckBox[]']:checked").each(function() {
                var idx = $(this).attr('id');
                users.push(idx);
            });
            console.log(users);
            if (result.value) {
                $.post('/admin/userDelete',{'idx':users},function(res){
                    console.log(res)
                })
                Swal.fire({
                    title:'삭제완료!',
                    confirmButtonText:'확인',
                    icon:'success',
                    text:'Your file has been deleted.'
                }).then((result)=>{
                    if(result.value){
                        location.reload()
                    }
                })
            }
        })
    })

    $("button[name='userinfo']").on('click',function (e) {
        userInfo($(this).val());
    })

    $("button[name='timelineinfo']").on('click',function (e) {
        timelineInfo($(this).val());
    })

    $("#save").on('click',function(e){
        var selector = $(this).val();
        saveTable(selector);
    })
    $("#block_save").on('click',function(e){
        var selector = $(this).val();
        saveTable(selector);
    })

    $("#allCheck").on('click',function(e){
        allCheck($(this).val());
    })

    $("input[name='userCheckBox[]']").on('click',function(e){
        checkboxChk('user','userCheckBox[]');
    })
    $("input[name='timelineCheckBox[]']").on('click',function(e){
        checkboxChk('timeline','timelineCheckBox[]');
    })
    // $('#myModal').on('hidden.bs.modal', function (e) {
    //     setTimeout(function() {
    //         location.reload();
    //     }, 3000);
    // })

    $('#timeline_save').on('click',function(){
        console.log($("#time_line_form").serialize())
        var option = {
            url : '/admin/time_line_update',
            type : "post",
            data : $("#time_line_form").serialize(),
            success : function(data) {
                alert(data);
            }
        };
        $("#time_line_form").ajaxSubmit(option);
    })

    $('#reset').on('click',function(){
        sms_verification();
    })

})
