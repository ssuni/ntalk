(function () {
    //회원가입, 문자인증, 로그인, 아이디/패스워드 찾기
    var _Join = null,
        idOverlap = null,
        nickOverlap = null,
        phone_value = null,
        register = null,
        refreshIntervalId = null,
        find_id = null,
        DOCUMENT = $(document),
        SMS = $("#sms_confirm"),
        first_request = SMS.find(".first_request"),
        second_request = SMS.find(".second_request"),
        REGISTER = $("#s_register"),
        ADDREG = $("#addRegister"),
        first_select = REGISTER.find("#s_Additional_information #first"),
        form_submit = REGISTER.find("#member_register"),
        FIND = $("#findId"),
        find_content = FIND.find("#findIdContent"),
        FIND_P = $("#findPassword"),
        pass_content = FIND_P.find("#passContent"),
        TIMELIST = $("#timeLineContainer"),
        TIMEPRO = $("#popupProfile");
    _Join = (function () {
        return {
            phoneNumberConfirm: function (phone, input_number, this_val, a, b, c, d, e, f, find_text) {
                console.log(phone);
                $.ajax({
                    type: "GET",
                    url: "/api/oauth/sms_request/",
                    data: phone,
                    success: function (data) {
                        console.log(data);

                        //인증요청 유효시간
                        var fiveMinutes = 180 * 1,
                            display = $("#time"),
                            textDisplay = $("#sms_confirm .sms_content .first_request div label div .timer_text");
                        if (refreshIntervalId != null) {
                            clearInterval(refreshIntervalId);
                            refreshIntervalId = null;
                        }
                        //if (this_val == "인증요청") {
                        var timer = fiveMinutes,
                            minutes,
                            seconds;

                        refreshIntervalId = setInterval(function () {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);

                            minutes = minutes < 10 ? "0" + minutes : minutes;
                            seconds = seconds < 10 ? "0" + seconds : seconds;

                            if (find_text == "아이디 찾기" || find_text == "비밀번호 재설정") {
                                display.text(minutes + ":" + seconds);
                            } else {
                                textDisplay.text("남은시간 ");
                                display.text(minutes + ":" + seconds);
                            }

                            if (--timer < 0) {
                                alert("인증요청 시간이 초과되었습니다.");
                                clearInterval(refreshIntervalId);
                            }
                        }, 1000);
                        if (this_val == "인증번호 재전송") {
                            alert("인증번호가 재전송 되었습니다.");
                        } else {
                            alert("인증번호가 전송 되었습니다.");
                        }
                        //}
                        console.log(location);
                        console.log($(location).attr("href"));
                        console.log($(location).attr("href") == "https://ntalk.me/auth/sms");
                        console.log(
                            $(location)
                            .attr("href")
                            .indexOf("sms")
                        );

                        if (
                            $(location)
                            .attr("href")
                            .indexOf("sms") != -1
                        ) {
                            if (this_val != "인증번호 재전송") {
                                /*  a.removeClass('nonDisplay'); */
                                a.addClass("nonDisplay");
                                b.removeClass("nonDisplay");
                                c.removeClass("nonDisplay");
                                /*  c.removeClass('nonDisplay');
                                 d.addClass('nonDisplay');
                                 e.addClass('nonDisplay'); */
                                f.val(input_number);
                            } else {
                                f.val(input_number);
                            }
                        } else if (find_text == "아이디 찾기") {
                            a.addClass("nonDisplay");
                            b.removeClass("nonDisplay");
                            c.val(input_number);
                        } //else if ($(location).attr("href") == "https://ntalk.me/auth/find_pass") {
                        else if (find_text == "비밀번호 재설정") {
                            a.addClass("nonDisplay");
                            b.removeClass("nonDisplay");
                        }
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        var error_message = JSON.parse(request.responseText);
                        if (request.status == 403) {
                            alert(error_message.message);
                        } else if (request.status == 400) {
                            alert(error_message.message);
                        } else if (request.status == 500) {
                            console.log(error_message.message);
                            alert("사전 정의되어 있지 않은 오류가 발생하였습니다.");
                        } else if (request.status == 406) {
                            alert(error_message.message);
                        }
                    }
                });
            },
            confirmRequest: function (request_value, url) {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: request_value,
                    success: function (data) {
                        var data_val = data.data,
                            user_list = data_val.userlist,
                            pw_text = DOCUMENT.find("#pwHeader .findpwTitle").text();

                        console.log(data);
                        console.log(user_list);

                        phone_value = request_value.phone;

                        if (refreshIntervalId != null) {
                            clearInterval(refreshIntervalId);
                            refreshIntervalId = null;
                        }
                        _Join.setCookie("phone_value", phone_value, 1);
                        if (
                            url == "/api/oauth/sms_confirm" &&
                            $(location)
                            .attr("href")
                            .indexOf("sms") != -1
                        ) {
                            /* location.href = '/auth/register?phone=' + request_value.phone; */

                            location.href = "/auth/register";
                        } else if (url == "/api/oauth/find_id") {
                            console.log(user_list.length);
                            $("#idcontent .resultContent").removeClass("nonDisplay");
                            $("#idcontent .requestContent").addClass("nonDisplay");
                            $("#idBtn .submitSet").removeClass("nonDisplay");
                            $("#idBtn .requestSet").addClass("nonDisplay");
                            $("#idHeader .findIdTitle span").text("내 아이디 안내");
                            for (var i = 0; i < user_list.length; i++) {
                                var str = "",
                                    className = ["oneId", "twoId", "threeId"],
                                    snsType = user_list[i].type,
                                    find_id = user_list[i].email;
                                if (snsType != "kakao" && snsType != "google" && snsType != "naver" && snsType != "facebook") {
                                    snsType = "appORwep";
                                }
                                str += '<div class="' + className[i] + '">';
                                str += '<span class="snsImg ' + snsType + '"></span>';
                                str += '<div class="findUserId">' + find_id + "</div>";
                                str += "</div>";
                                $("#idcontent .resultContent .resultId").append(str);
                            }
                            /* console.log(DOCUMENT.find('#findIdLayer'));
                            var content_h = DOCUMENT.find('#findIdLayer').children().height(),
                                layer_h = DOCUMENT.find('#findIdLayer').height();
                            console.log(content_h);
                            console.log(layer_h);
                            if (layer_h < content_h) {
                                DOCUMENT.find('#findIdLayer').height(content_h);
                                console.log(DOCUMENT.find('#findIdLayer').height(content_h));
                            } else {
                                DOCUMENT.find('#findIdLayer').height(layer_h);
                                console.log(DOCUMENT.find('#findIdLayer').height(layer_h));
                            } */
                            if (user_list.length == 1) {
                                DOCUMENT.find("#findIdLayer").height("317px");
                            } else if (user_list.length == 2) {
                                DOCUMENT.find("#findIdLayer").height("384px");
                            } else if (user_list.length == 3) {
                                DOCUMENT.find("#findIdLayer").height("434px");
                            }
                        } else if (url == "/api/oauth/sms_confirm" && pw_text == "비밀번호 재설정") {
                            DOCUMENT.find("#idBtn .requestContainer").addClass("nonDisplay");
                            DOCUMENT.find("#idBtn .subBtn").addClass("nonDisplay");
                            DOCUMENT.find("#pwcontent .requestContent").addClass("nonDisplay");
                            DOCUMENT.find("#idBtn .sub_reset").removeClass("nonDisplay");
                            DOCUMENT.find("#pwcontent .resultContent").removeClass("nonDisplay");
                            DOCUMENT.find("#findPwLayer").css("height", "286px;");
                        }
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        var error_message = JSON.parse(request.responseText);
                        if (request.status == 404) {
                            alert(error_message.message);
                        } else if (request.status == 500) {
                            console.log(error_message.message);
                            alert("사전에 정의되어 있지 않은 오류가 발생하였습니다.");
                        } else if (request.status == 400) {
                            alert(error_message.message);
                        } else if (request.status == 406) {
                            alert(error_message.message);
                        }
                    }
                });
            },
            find_pass: function (request_value, url) {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: request_value,
                    success: function (data) {
                        //alert("비밀번호가 변경 되었습니다.");
                        location.href = "https://ntalk.me/auth";
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        var error_message = JSON.parse(request.responseText);
                        if (request.status == 404) {
                            alert(error_message.message);
                        } else if (request.status == 500) {
                            console.log(error_message.message);
                            alert("사전에 정의되어 있지 않은 오류가 발생하였습니다.");
                        } else if (request.status == 400) {
                            alert(error_message.message);
                        } else if (request.status == 406) {
                            alert(error_message.message);
                        }
                    }
                });
            },
            locationHTML: function (locationData) {
                var html = "",
                    locationData_langth = locationData.length;

                html += '<div class="first_location">';
                html += '<select id="first" name="location1" title="1차 지역선택">';
                html += '<option value="">상위 지역</option>';
                for (var i = 0; i < locationData_langth; i++) {
                    html += "<option value=" + locationData[i].name + ">" + locationData[i].name + "</option>";
                }
                html += "</select>";
                html += "</div>";
                html += '<div class="second_location">';
                html += '<select id="second" name="location2" title="2차 지역선책">';
                html += '<option value="" class="noDel">하위 지역</option>';
                html += "</select>";
                html += "</div>";

                return html;
            },
            locationSecond: function (locationData) {
                var select_value = ADDREG.find("#s_Additional_information #first option:selected").val(),
                    select_index = ADDREG.find("#s_Additional_information #first option:selected").index() - 1,
                    second_select = ADDREG.find("#second"),
                    secondData_length = locationData[select_index].childs.length;

                second_select.prop("disabled", false);
                ADDREG.find("#second")
                    .children()
                    .not(".noDel")
                    .remove();
                for (var i = 0; i < secondData_length; i++) {
                    var html = "";

                    html += "<option value=" + locationData[select_index].childs[i] + ">" + locationData[select_index].childs[i] + "</option>";

                    ADDREG.find("#second").append(html);
                }
            },
            overlap_ajax: function (confirm_val, name) {
                $.ajax({
                    type: "GET",
                    url: "/api/oauth/overlap",
                    data: confirm_val,
                    success: function (data) {
                        console.log(data);
                        console.log(data.message);
                        alert("사용 가능한 " + name + " 입니다.");
                        if (data.code == 200 && data.message == "사용 가능한 아이디 입니다.") {
                            idOverlap = "check";
                        } else if (data.code == 200 && data.message == "사용 가능한 닉네임 입니다.") {
                            nickOverlap = "check";
                        }
                        console.log(nickOverlap);
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        var error_message = JSON.parse(request.responseText);
                        if (request.status == 409) {
                            alert("사용중인 " + name + " 입니다.");
                        } else if (request.status == 400) {
                            alert(error_message.message);
                        }
                    }
                });
            },
            getParameterValue: function (val) {
                val = val.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
                var regex = new RegExp("[\\?&]" + val + "=([^&#]*)"),
                    result = regex.exec(location.search);
                return result === null ? "" : decodeURIComponent(result[1].replace(/\+/g, " "));
            },
            checkPassword: function (password, id) {
                var password_adr = REGISTER.find("#password"),
                    password_length = password.length;

                /*  var pw_regExp = /^[A-Za-z0-9+]{8,16}$/; */
                var pw_regExp = /^[A-Za-z0-9+~!@#$%^&*()_+<>?:{}]{8,16}$/;

                console.log(password_length);
                console.log(password);
                if (pw_regExp.test(password) == false) {
                    alert("비밀번호는 영문/숫자/특수문자를 허용하며,\n최소 8자 최대 16자까지 가능합니다.");
                    password_adr.val("").focus();
                    return false;
                }
                /* if (password_length < 8 || password_length > 14) {
                    alert('8자리 이상 14자리 이하로 입력해 주세요.');
                    password_adr.val('').focus();
                    return false;
                } */

                /*if (!/^[a-zA-Z0-9]{8,12}$/u.test(password)) {
                    alert('8자리 이상 입력해 주세요.');
                    password_adr.val('').focus();
                    return false;
                }*/
                /*if (!/^[a-zA-Z0-9]{8,12}$/u.test(password)) {
                    alert('8자리 이상 입력해 주세요.');
                    password_adr.val('').focus();
                    return false;
                }*/
                /*if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^*+=-])(?=.*[0-9]).{8,12}$/.test(password)) {
                    alert('숫자/영대문자/영문자/특수문자 조합으로 8자리 이상 사용해야 합니다.');
                    password_adr.val('').focus();
                    return false;
                }
                var checkNumber = password.search(/[0-9]/g);
                var checkEnglish = password.search(/[a-z]/ig);
                if (checkNumber < 0 || checkEnglish < 0) {
                    alert("숫자와 영문자를 혼용하여야 합니다.");
                    password_adr.val('').focus();
                    return false;
                }
                if (/(\w)\1\1\1/.test(password)) {
                    alert('같은 문자를 4번 이상 사용하실 수 없습니다.');
                    password_adr.val('').focus();
                    return false;
                }
                if (password.search(id) > -1) {
                    alert("비밀번호에 아이디가 포함되었습니다.");
                    password_adr.val('').focus();
                    return false;
                }*/
                return true;
            },
            live_hyphen: function (text) {
                var key = event.charCode || event.keyCode || 0,
                    $text = text;
                if (key !== 8 && key !== 9) {
                    if ($text.val().length === 3) {
                        $text.val($text.val() + "-");
                    }
                    if ($text.val().length === 8) {
                        $text.val($text.val() + "-");
                    }
                }
                return key == 8 || key == 9 || key == 46 || (key >= 48 && key <= 57) || (key >= 96 && key <= 105);
            },
            location_json: function () {
                $.ajax({
                    timeout: 3000,
                    type: "GET",
                    url: "/json/administrative_region.json",
                    dataType: "json"
                }).done(function (data) {
                    var locationData = data.data;

                    ADDREG.find("#s_Additional_information").append(_Join.locationHTML(locationData));

                    ADDREG.find("#first").change(function () {
                        //1차 지역 선택시

                        ADDREG.find("#first option:selected").attr("selected", "selected");
                        ADDREG.find("#first").css("color", "#000000");
                        ADDREG.find("#first option:selected")
                            .siblings()
                            .attr("selected", false);
                        if (ADDREG.find("#first option:selected").text() == "상위 지역") {
                            ADDREG.find("#s_Additional_information #second .noDel").prop("selected", "selected");
                            ADDREG.find("#s_Additional_information #second .noDel")
                                .siblings()
                                .prop("selected", false);
                            ADDREG.find("#second")
                                .children()
                                .not(".noDel")
                                .remove();
                            ADDREG.find("#first").css("color", "rgba(141, 141, 141, 0.5)");
                            ADDREG.find("#second").css("color", "rgba(141, 141, 141, 0.5)");
                        } else {
                            _Join.locationSecond(locationData);
                        }
                        /* var phone = _Join.getParameterValue('phone'); // 주소창 핸드폰 번호 input에 추가
                        ADDREG.find('.s_joinContainer label input[type=hidden]').val(phone); */
                    });
                    ADDREG.find("#second").change(function () {
                        // 2차 지역 선택시
                        ADDREG.find("#second option:selected").attr("selected", "selected");
                        ADDREG.find("#second").css("color", "#000000");
                        ADDREG.find("#second option:selected")
                            .siblings()
                            .attr("selected", false);
                        if (ADDREG.find("#second option:selected").text() == "하위 지역") {
                            ADDREG.find("#second").css("color", "rgba(141, 141, 141, 0.5)");
                        } else {
                            ADDREG.find("#second").css("color", "#000000");
                        }
                    });
                });
            },
            readURL: function (input) {
                console.log(input);
                console.log(input.files);
                if (input.files && input.files[0]) {
                    var reader = new FileReader(); //파일을 읽기 위한 FileReader객체 생성
                    console.log(reader);
                    reader.onload = function (e) {
                        //파일 읽어들이기를 성공했을때 호출되는 이벤트 핸들러
                        $("#blah").attr("src", e.target.result); //이미지 Tag의 SRC속성에 읽어들인 File내용을 지정 //(아래 코드에서 읽어들인 dataURL형식)
                    };
                    reader.readAsDataURL(input.files[0]); //File내용을 읽어 dataURL형식의 문자열로 저장
                }
            },
            checkExtension: function (fileName, fileSize) {
                var regex = new RegExp("(.*?).(exe|sh|gif|zip|alz)$");
                var maxSize = 10485760; //10MB

                if (fileSize >= maxSize) {
                    alert("파일 사이즈 초과");
                    $("#profile").val(""); //파일 초기화
                    return false;
                }

                if (regex.test(fileName)) {
                    alert("업로드 불가능한 파일이 있습니다.");
                    $("#profile").val(""); //파일 초기화
                    return false;
                }
                return true;
            },
            preview: function (arr) {
                arr.forEach(function (f) {
                    //파일명이 길면 파일명...으로 처리
                    var fileName = f.name;
                    if (fileName.length > 10) {
                        fileName = fileName.substring(0, 7) + "...";
                    }

                    //div에 이미지 추가
                    /* var str = '<div style="display: inline-flex; padding: 10px;"><li>';
                    str += '<span>' + fileName + '</span><br>'; */

                    //이미지 파일 미리보기
                    if (f.type.match("image.*")) {
                        var reader = new FileReader(); //파일을 읽기 위한 FileReader객체 생성
                        reader.onload = function (e) {
                            //파일 읽어들이기를 성공했을때 호출되는 이벤트 핸들러
                            //str += '<button type="button" class="delBtn" value="'+f.name+'" style="background: red">x</button><br>';
                            /* str += '<img src="' + e.target.result + '" title="' + f.name + '" width=210 height=210 />';
                            str += '</li></div>';
                            $(str).appendTo('#preview'); */
                            $("#s_joinContainer > div.imgUpdate > img").prop("src", e.target.result);
                            $("#s_joinContainer > div.imgUpdate > img").css("objectFit", "cover");
                            $("#s_joinContainer > div.imgUpdate > label").addClass("nonDisplay");
                            $("#s_joinContainer > div.imgUpdate > div").removeClass("nonDisplay");
                            /* $('#s_joinContainer > div.imgUpdate > div span').css({
                                transform: 'rotate(45deg)',
                                transitionDuration: '3s'
                            }); */
                            $("#s_joinContainer > div.imgUpdate > div span").addClass("imgRotate");
                        };
                        reader.readAsDataURL(f);
                    } else {
                        //str += '<img src="/resources/img/fileImg.png" title="' + f.name + '" width=210 height=210 />';
                        //$(str).appendTo('#preview');
                        $("#s_joinContainer > div.imgUpdate > img").prop("src", "/resources/img/fileImg.png");
                    }
                    //$('#s_joinContainer > div.imgUpdate > img').css('display', 'none');
                });
            },
            setCookie: function (cookieName, value, exdays) {
                var exdate = new Date();
                exdate.setDate(exdate.getDate() + exdays);
                var cookieValue = escape(value) + (exdays == null ? "" : "; expires=" + exdate.toGMTString());
                document.cookie = cookieName + "=" + cookieValue;
            },
            deleteCookie: function (cookieName) {
                var expireDate = new Date();
                expireDate.setDate(expireDate.getDate() - 1);
                document.cookie = cookieName + "= " + "; expires=" + expireDate.toGMTString();
            },
            getCookie: function (cookieName) {
                cookieName = cookieName + "=";
                var cookieData = document.cookie;
                var start = cookieData.indexOf(cookieName);
                var cookieValue = "";
                if (start != -1) {
                    start += cookieName.length;
                    var end = cookieData.indexOf(";", start);
                    if (end == -1) end = cookieData.length;
                    cookieValue = cookieData.substring(start, end);
                }
                return unescape(cookieValue);
            },
            reverse_nondisplay: function () {
                $("#s_essentialContainer > div.registerTitle").removeClass("nonDisplay");
                $("#s_essentialContainer > div:nth-child(2)").removeClass("nonDisplay");
                $("#s_essentialContainer > div.genderTitle").addClass("nonDisplay");
                $("#s_essentialContainer > div:nth-child(4)").addClass("nonDisplay");
                $("#member_register > div.s_idContainer").removeClass("nonDisplay");
                $("#member_register > div.s_pwContainer").removeClass("nonDisplay");
                $("#nextBtn").removeClass("nonDisplay");
                $("#member_register > div.s_genderContainer").addClass("nonDisplay");
                $("#firstCreate").addClass("nonDisplay");
            },
            findId_html: function () {
                var html = "";

                html += '<div id="findIdBack">';
                html += '<div id="findIdLayer">';
                html += '<div id="idHeader">';
                html += '<div class="findIdTitle">';
                html += "<span>아이디 찾기</span>";
                html += "</div>";
                html += '<div class="closeBtn">';
                html += "<span></span>";
                html += "</div></div>";
                html += '<div id="idcontent">';
                html += '<div class="requestContent">';
                html += '<div class="phoneNum">';
                html += '<div class="phoneTitle">';
                html += "<span>휴대폰 번호</span>";
                html += "</div>";
                html += '<div class="phoneContent">';
                html += "<label>";
                html += '<input type="text" id="idPhone" autocomplete="off" name="phone" placeholder="- 없이 번호를 입력해주세요" maxlength="13" minlength="8">';
                html += "</label>";
                html += '<input type="hidden" name="phone" id="findIdPhone">';
                html += "</div></div>";
                html += '<div class="confirmNum">';
                html += '<div class="confirmTitle">';
                html += "<span>인증번호</span>";
                html += "</div>";
                html += '<div class="confirmContent">';
                html += "<label>";
                html += '<input type="text" id="idconfirm" autocomplete="off" name="phone" placeholder="인증번호를 입력해 주세요" maxlength="16" minlength="8">';
                html += '<div class="time_contanier">';
                html += '<div class="time" id="time"></div>';
                html += "</div></label></div></div></div>";
                html += '<div class="resultContent nonDisplay">';
                html += '<div class="resultId">';
                /*                 html += '<div class="oneId">';
                                html += '<span class="snsImg kakao"></span>';
                                html += '<div class="findUserId">sdfsdf</div>';
                                html += '</div>';
                 */
                /*html += '<div class="twoId nonDisplay">';
                html += '<span class="snsImg"></span>';
                html += '<span class="findUserId"></span>';
                html += '</div>';
                html += '<div class="threeId nonDisplay">';
                html += '<span class="snsImg"></span>';
                html += '<span class="findUserId"></span>';
                html += '</div>';*/
                html += "</div>";
                html += '<div class="resultText">';
                html += "<span>이 아이디로 로그인을 하시겠습니까?<br>";
                html += "<span>비밀번호가 기억나지 않으시면 재설정 해주세요</span>";
                html += "</div></div></div>";
                html += '<div id="idBtn">';
                html += '<div class="requestSet">';
                html += '<div class="requestContainer">';
                html += '<div class="requestBtn">인증요청</div>';
                html += '<div class="requestReBtn nonDisplay">인증번호 재전송</div>';
                html += "</div>";
                html += '<div class="subBtn">아이디 찾기</div>';
                html += "</div>";
                html += '<div class="submitSet nonDisplay">';
                html += '<div class="loginBtn">로그인하기</div>';
                html += '<div class="pwFindBtn">비밀번호 재설정</div>';
                html += "</div>";
                html += "</div></div></div>";

                $("#mainContent").before(html);
            },
            pw_reset_html: function () {
                var html = "";
                html += '<div id="findPwBack">';
                html += '<div id="findPwLayer">';
                html += '<div id="pwHeader">';
                html += '<div class="findpwTitle">';
                html += "<span>비밀번호 재설정</span>";
                html += "</div>";
                html += '<div class="closeBtn">';
                html += "<span></span>";
                html += "</div>";
                html += "</div>";
                html += '<div id="pwcontent">';
                html += '<div class="requestContent">';
                html += '<div class="userId">';
                html += '<div class="idTitle">';
                html += "<span>아이디</span>";
                html += "</div>";
                html += '<div class="idContent">';
                html += "<label>";
                html += '<input type="text" id="pw_id" autocomplete="off" name="id" placeholder="아이디를 입력하세요">';
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += '<div class="phoneNum">';
                html += '<div class="phoneTitle">';
                html += "<span>휴대폰 번호</span>";
                html += "</div>";
                html += '<div class="phoneContent">';
                html += "<label>";
                html += '<input type="text" id="pwPhone" autocomplete="off" name="phone" placeholder="- 없이 번호를 입력해주세요" maxlength="16" minlength="8">';
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += '<div class="confirmNum">';
                html += '<div class="confirmTitle">';
                html += "<span>인증번호</span>";
                html += "</div>";
                html += '<div class="confirmContent">';
                html += "<label>";
                html += '<input type="text" id="pwconfirm" autocomplete="off" name="certification" placeholder="인증번호를 입력해 주세요" maxlength="6">';
                html += '<div class="time_contanier">';
                html += '<div class="time" id="time"></div>';
                html += "</div>";
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
                html += '<div class="resultContent nonDisplay">';
                html += '<div class="newPwContainer">';
                html += '<div class="newPwTitle">';
                html += "<span>새 비밀번호</span>";
                html += "</div>";
                html += '<div class="newPwContent">';
                html += "<label>";
                html += '<input type="password" id="newPw" autocomplete="off" maxlength="16" minlength="8" name="password" placeholder="변경할 비밀번호를 입력하세요">';
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += '<div class="newPwConfirmContainer">';
                html += '<div class="newPwConfirmTitle">';
                html += "<span>비밀번호 확인</span>";
                html += "</div>";
                html += '<div class="newPwConfirmContent">';
                html += "<label>";
                html += '<input type="password" id="newPwConfirm" autocomplete="off" maxlength="16" minlength="8" name="password_confirm" placeholder="변경할 비밀번호를 다시 한번 입력하세요">';

                html += "</label>";
                html += "<div id='new_pw_result'><span></span></div>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
                html += '<div id="idBtn">';
                html += '<div class="requestContainer">';
                html += '<div class="requestBtn">인증요청</div>';
                html += '<div class="requestReBtn nonDisplay">인증번호 재전송</div>';
                html += "</div>";
                html += '<div class="subBtn">비밀번호 재설정</div>';
                html += '<div class="sub_reset nonDisplay">재설정하기</div>';
                html += "</div>";
                html += "</div>";
                html += "</div>";

                $("#mainContent").before(html);
            }
        };
    })(); //회원가입 관련 함수
    _Join.location_json(); // 지역 선택 json 처리 후 append
    console.log(_Join.getCookie("phone"));
    console.log(_Join.getCookie("register"));
    /********************************************로그인 페이지 이벤트 ******************************************************/

    $("#loginSection").on("keyup", "#psw", function () {
        var id_adr = $("#user"),
            psw_adr = $("#psw");
        console.log(psw_adr.val());
        if (id_adr.val() != "" && psw_adr.val().length >= 8) {
            $("#login_submit").addClass("background_pink");
            $("#login_submit").css("cursor", "pointer");
            $("#login_submit").prop("disabled", false);
        } else {
            $("#login_submit").removeClass("background_pink");
            $("#login_submit").css("cursor", "default");
            $("#login_submit").prop("disabled", true);
        }
    });

    /************************************* 회원가입 아이디 비번 입력시 엔터키 submit 이벤트 막기  **********************************/
    $("#member_register").keydown(function (event) {
        // 회원가입 아이디 비번 입력시 엔터키 submit 이벤트 막기
        if (event.keyCode == "13") {
            if (window.event) {
                event.preventDefault();
                return;
            }
        }
    });

    /**************************************************** 파일 미리보기 *********************************************************/
    $("#profile").change(function (e) {
        /* var _thisInput = $('#profile');
        console.log(_thisInput);
        _Join.readURL(_thisInput); */
        $("#preview").empty();

        var files = e.target.files;
        var arr = Array.prototype.slice.call(files);

        //업로드 가능 파일인지 체크
        for (var i = 0; i < files.length; i++) {
            if (!_Join.checkExtension(files[i].name, files[i].size)) {
                return false;
            }
        }

        _Join.preview(arr);
    });

    /********************************************** 회원가입 이벤트, 비밀번호 유효성 검사 ************************************/
    $("#navibar > div.mainLoginContainer > div.loginPanel > div:nth-child(1) > a").on("click", function () {
        window.onbeforeunload = function (e) {
            $.ajax({
                type: "POST",
                url: "https://ntalk.me/api/oauth/session_del",
                success: function (res) {
                    console.log(res);
                }
            });
        };
    });
    $("#membershipJoin > a").on("click", function () {
        window.onbeforeunload = function (e) {
            $.ajax({
                type: "POST",
                url: "https://ntalk.me/api/oauth/session_del",
                success: function (res) {
                    console.log(res);
                }
            });
        };
    });
    /* if ($(location).attr('href').indexOf("register") != -1) {
        window.onbeforeunload = function (e) {
            $.ajax({
                type: 'POST',
                url: 'https://ntalk.me/api/oauth/session_del',
                success: function (res) {
                    console.log(res);
                }
            })
        }
    } */
    /*  else if ($(location).attr('href').indexOf("sms") != -1) {
            window.onbeforeunload = function (e) {
                $.ajax({
                    type: 'POST',
                    url: 'https://ntalk.me/api/oauth/session_del',
                    success: function (res) {
                        console.log(res);
                    }
                })
            }
        } */
    $("#s_register")
        .on("click", "#nextBtn", function () {
            // 가입화면 필수입력창 다음 클릭 이벤트
            var id_regExp = /^[A-Za-z0-9+]{6,10}$/,
                /* id_regExp = /^[a-zA-Z][a-zA-Z0-9]{5,9}$/, */
                /* id_regExp = /^[a-zA-Z](?=.{0,9}[0-9])[0-9a-zA-Z]{5,9}$/, */
                id_val = REGISTER.find(".s_idContainer #id").val(),
                first_psw = $("#password").val(),
                second_psw = $("#pw_confirm_result").val();

            if ($("#snsType").val() == "") {
                if (id_regExp.test(id_val) == false) {
                    alert("아이디 형식이 유효하지 않습니다.\n영문과 숫자만 허용 / 최소6자 최대10자로 생성 하세요.");
                    event.preventDefault();
                    _Join.reverse_nondisplay();
                    return;
                } else if (idOverlap == null) {
                    alert("아이디 중복검사를 해주세요.");
                    event.preventDefault();
                    _Join.reverse_nondisplay();
                    return;
                } else if ((first_psw == "" && second_psw == "") || (first_psw == "" && second_psw != "") || (first_psw != "" && second_psw == "")) {
                    alert("비밀번호가 모두 입력되지 않았습니다.");
                    event.preventDefault();
                    _Join.reverse_nondisplay();
                    return;
                }
            } else {
                if (id_val.indexOf("@") != -1) {
                    idOverlap = "check";
                }
            }
            $("#s_essentialContainer > div.registerTitle").addClass("nonDisplay");
            $("#s_essentialContainer > div:nth-child(2)").addClass("nonDisplay");
            $("#s_essentialContainer > div.genderTitle").removeClass("nonDisplay");
            $("#s_essentialContainer > div:nth-child(4)").removeClass("nonDisplay");
            $("#member_register > div.s_idContainer").addClass("nonDisplay");
            $("#member_register > div.s_pwContainer").addClass("nonDisplay");
            $("#nextBtn").addClass("nonDisplay");
            $("#member_register > div.s_genderContainer").removeClass("nonDisplay");
        })
        /* .on('click', '.s_genderContainer #nextBtn', function () { //성별 선택 다음 클릭 이벤트
                $('#s_essentialContainer > div.genderTitle').addClass('nonDisplay');
                $('#s_essentialContainer > div:nth-child(4)').addClass('nonDisplay');
                $('#member_register > div.s_genderContainer').addClass('nonDisplay');
                $('#firstCreate').removeClass('nonDisplay');
                $('#s_essentialContainer').css('marginTop', '144px');
            }) */
        .on("click", "#member_register .s_genderContainer .maleGender div div label", function () {
            //성별 클릭 이벤트
            var femaleChecked = $("#female").is(":checked");
            if (femaleChecked == false) {
                $(this)
                    .find("img")
                    .prop("src", "//ntalk.me/img/s_man.png");
                $(this)
                    .parent("div")
                    .siblings("span")
                    .css("color", "#2a2a2a");
            } else {
                $("#member_register .s_genderContainer .femaleGender div div label img").prop("src", "//ntalk.me/img/s_girl_g.png");
                $("#member_register .s_genderContainer .femaleGender div span").css("color", "#cbcbcb");
                $(this)
                    .find("img")
                    .prop("src", "//ntalk.me/img/s_man.png");
                $(this)
                    .parent("div")
                    .siblings("span")
                    .css("color", "#2a2a2a");
            }
        })
        .on("click", "#member_register .s_genderContainer .femaleGender div div label", function () {
            //성별 클릭 이벤트
            var maleChecked = $("#male").is(":checked");
            if (maleChecked == false) {
                $(this)
                    .find("img")
                    .prop("src", "//ntalk.me/img/s_girl.png");
                $(this)
                    .parent("div")
                    .siblings("span")
                    .css("color", "#2a2a2a");
            } else {
                $("#member_register .s_genderContainer .maleGender div div label img").prop("src", "//ntalk.me/img/s_man_g.png");
                $("#member_register .s_genderContainer .maleGender div span").css("color", "#cbcbcb");
                $(this)
                    .find("img")
                    .prop("src", "//ntalk.me/img/s_girl.png");
                $(this)
                    .parent("div")
                    .siblings("span")
                    .css("color", "#2a2a2a");
            }
        });
    $("#addRegister")
        .on("mouseenter mouseleave", "#firstCreate .startNtalk", function () {
            // 프로필 작성 화면 마우스오버 이벤트
            $(this).toggleClass("backColor");
        })
        .on("mouseenter mouseleave", "#selectBtn .completeBtn", function () {
            //추가정보 마우스오버 이벤트
            $(this).toggleClass("backColor");
        })
        .on("mouseenter mouseleave", "#submitBtn .skipBtn", function () {
            //프로필사진 마우스오버 이벤트
            $(this).toggleClass("backColor");
        })
        .on("click", "#addProfile", function () {
            // 프로필 만들기 클릭 이벤트
            $("#addProfileSection").addClass("nonDisplay");
            $("#addInputSection").removeClass("nonDisplay");
        })
        .on("click", "#selectBtn .nextBtn", function () {
            // 추가정보 다음 클릭 이벤트
            if (nickOverlap == null) {
                if ($("#nickname").val() == "") {
                    $("#s_Additional_information").addClass("nonDisplay");
                    $("#s_joinContainer").removeClass("nonDisplay");
                } else {
                    alert("닉네임 중복확인을 해주세요");
                }
            } else {
                $("#s_Additional_information").addClass("nonDisplay");
                $("#s_joinContainer").removeClass("nonDisplay");
            }
        })
        .on("click", "#selectBtn .completeBtn", function () {
            //추가정보 건너뛰기 클릭 이벤트
            var nick_adr = $("#nickname"),
                age_adr = $("#age"),
                first_select = $("#first option:selected"),
                second_select = $("#second option:selected");

            nick_adr.val("");
            age_adr.val("");
            first_select.val("");
            second_select.val("");
            $("#s_Additional_information").addClass("nonDisplay");
            $("#s_joinContainer").removeClass("nonDisplay");
        })
        .on("click", "#s_joinContainer .imgUpdate div span", function () {
            // 프로필 사진 미리보기 삭제
            $("#s_joinContainer > div.imgUpdate > img").prop("src", "//ntalk.me/img/people_base_back.svg");
            $("#s_joinContainer > div.imgUpdate > img").css("objectFit", "fill");
            $("#profile").val("");
            $("#s_joinContainer > div.imgUpdate > label")
                .removeClass("nonDisplay")
                .addClass("imgRotate_reverse");
            $("#s_joinContainer > div.imgUpdate > div").addClass("nonDisplay");
        })
        .on("submit", "#register_add", function () {
            // 추가정보 (추가내용/프로필사진) 업데이트
            $("#cookieId").val(_Join.getCookie("register"));
            console.log($("#cookieId").val());
            if ($("#cookieId").val() == "") {
                alert("페이지 정보가 초기화 되었습니다.\n메인 페이지로 이동 됩니다.");
                event.preventDefault();
                location.href = "https://ntalk.me/";
            } else {
                if ($("#nickname").val() != "") {
                    if (nickOverlap == null) {
                        alert("닉네임 중복검사를 해주세요.");
                        event.preventDefault();
                        $("#s_Additional_information").removeClass("nonDisplay");
                        $("#s_joinContainer").addClass("nonDisplay");
                        return;
                    }
                }
            }
            //$('#cookieId').val('');
            _Join.deleteCookie("register");
        });

    // 전체 동의 클릭 이벤트
    $("#termsContainer").on("change", "#allterms", function () {
        if ($("#allterms").is(":checked")) {
            $("#useterms").prop("checked", true);
            $("#pinfoterms").prop("checked", true);
            $("#minorsterms").prop("checked", true);
        } else {
            $("#useterms").prop("checked", false);
            $("#pinfoterms").prop("checked", false);
            $("#minorsterms").prop("checked", false);
        }
    }).on('click', 'div div .detail', function () { // 이용약관 동의(필수) 자세히 클릭 이벤트
        var select_terms = $(this).parent().parent().attr('class'),
            html = '';
        console.log(select_terms);
        html += '<div id="terms_back">';
        html += '<div id="terms_wrap">';
        html += '<div class="closeBtn"><span></span></div>';
        html += '<div id="terms_layer">';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        $('#mainContent').before(html);
        if (select_terms == 'termsuse') {
            $('#terms_layer').load('/inc/terms.html');
        } else if (select_terms == 'termsinfo') {
            $('#terms_layer').load('/inc/privacy.html');
        } else if (select_terms == 'termsminors') {
            $('#terms_layer').load('/inc/youth_protection.html');
        }
        $(window).load(function () {
            var do_width = $(window).width(),
                layer_width = $("#terms_layer").width(),
                real_width = (do_width - layer_width) / 2;

            $("#terms_layer").css("left", real_width);
            $("#terms_back").css({
                width: $(window).width(),
                height: $('#terms_layer').height()
            });
        });
    });
    DOCUMENT.on('click', '#terms_wrap > div.closeBtn > span', function () { // 이용약관 상세페이지 닫기 버튼 이벤트
        $('#terms_back').remove();
    });
    $("#termsBtn").on("click", "button", function () {
        // 약관 동의 후 문자인증 이동
        var one_terms = $("#allterms").is(":checked"),
            two_terms = $("#useterms").is(":checked"),
            three_terms = $("#pinfoterms").is(":checked"),
            four_terms = $("#minorsterms").is(":checked");
        if (two_terms == true && three_terms == true && four_terms == true) {
            location.href = "/auth/sms";
        } else {
            alert("필수 약관 동의 후 가입 가능합니다.");
        }
    });
    form_submit.submit(function (event) {
        //회원가입 가입요청
        var startNtalk = $("#firstCreate > button.startNtalk").text(),
            addProfile = $("#firstCreate > button.addProfile").text();
        console.log($(this));
        console.log(startNtalk);
        console.log(addProfile);
        var form_data = form_submit.serialize(),
            password_first = REGISTER.find(".s_pwContainer #password").val(),
            password_second = REGISTER.find(".s_pwContainer #pw_confirm_result").val(),
            id_val = REGISTER.find(".s_idContainer #id").val(),
            radio_check = false,
            radio_adr = REGISTER.find(".s_genderContainer div div input[name=gender]"),
            radio_length = REGISTER.find(".s_genderContainer div div input[name=gender]").length;
        for (var i = 0; i < radio_length; i++) {
            if (radio_adr[i].checked == true) {
                radio_check = true;
            }
        }
        console.log(phone_value);
        $("#phone").val(_Join.getCookie("phone_value"));
        register = $("#id").val();
        _Join.setCookie("register", register, 1);
        console.log(register);
        console.log($("#phone").val());
        console.log(radio_check);
        var id_regExp = /^([a-z]{1})?([a-z0-9+]{6,10})\$/;
        console.log(/^([a-z]{1})?([a-z0-9+]{6,10})\$/.test(id_val));
        if (password_first == "" || password_second == "" || id_val == "" || radio_check == false) {
            alert("필수 입력란을 입력해 주세요.");
            event.preventDefault();
            _Join.reverse_nondisplay();
            return;
        } else if (password_first !== password_second) {
            alert("비밀번호를 확인해 주세요.");
            event.preventDefault();
            _Join.reverse_nondisplay();
            return;
        } else if ($("#phone").val() == "") {
            /* else if (idOverlap == null) {
                   alert("아이디 중복검사를 해주세요.");
                   event.preventDefault();
                   _Join.reverse_nondisplay();
                   return;
               }  */
            var whether_id = $("#hidden_id").val();
            _Join.setCookie("register", whether_id, 1);
            if (whether_id != "") {
                alert("로그인 정보가 존재합니다.\n프로필 업데이트를 진행해주시기 바랍니다.");
                event.preventDefault();
                location.href = "https://ntalk.me/auth/register_additional";
                $("#cookieId").val(_Join.getCookie("register"));
            } else {
                alert("페이지 정보가 초기화 되었습니다.\n회원가입을 다시 진행해주시기 바랍니다.");
                event.preventDefault();
                location.href = "https://ntalk.me/auth/terms";
            }
        }
        /*  else if (id_regExp.test(id_val) == false && $('#snsType').val() == '') {
                    alert("아이디 형식이 유효하지 않습니다.");
                    event.preventDefault();
                    _Join.reverse_nondisplay();
                    return
                } */
        _Join.deleteCookie("phone_value");
        console.log(_Join.getCookie("phone_value"));
        /*  else if (nickOverlap == null) {
                    alert("닉네임 중복검사를 해주세요.");
                    event.preventDefault();
                    return
                } */
    });
    pass_content.find("#passwordRequest").submit(function (event) {
        // 비밀번호 초기화 요청
        var form_data = pass_content.find("#passwordRequest").serialize(),
            password_first = pass_content.find("#password").val(),
            password_second = pass_content.find("#pw_confirm_result").val();

        if (password_first !== password_second) {
            alert("비밀번호를 확인해 주세요");
            event.preventDefault();
            return;
        }
    });
    REGISTER.on("click", "#s_essentialContainer .s_idContainer label input[type=button]", function () {
        //아이디 중복확인
        var id_val = REGISTER.find("#s_essentialContainer .s_idContainer label input").val(),
            division_val = REGISTER.find("#s_essentialContainer .s_idContainer label input[type=hidden]").val(),
            confirm_val = {
                id: id_val,
                division: division_val
            },
            id_name = "아이디",
            id_regExp = /^[A-Za-z0-9+]{6,10}$/;

        if (id_regExp.test(id_val) == false) {
            alert("아이디 형식이 유효하지 않습니다.\n영문과 숫자만 허용 / 최소6자 최대10자로 생성 하세요.");
        } else {
            _Join.overlap_ajax(confirm_val, id_name);
        }
    });
    ADDREG.on("click", ".s_nickContainer label input[type=button]", function () {
        // 닉네임 중복 검사
        var nick_val = ADDREG.find(".s_nickContainer label input[type=text]").val(),
            division_val = ADDREG.find(".s_nickContainer label input[type=hidden]").val(),
            confirm_val = {
                id: nick_val,
                division: division_val
            },
            nick_name = "닉네임";

        _Join.overlap_ajax(confirm_val, nick_name);
    });
    second_request.on("click", ".request_input input", function () {
        //전화인증 번호 요청
        var input_number = SMS.find("#phone")
            .val()
            .replace(/-/gi, ""),
            phone = {
                phone: input_number
            },
            this_val = $(this).val(),
            find_text = "",
            /* val_1 = SMS.find('.confirm_number')
            val_2 = SMS.find('.re_number'),
            val_3 = SMS.find('.send_number'),
            val_4 = SMS.find('.phone_number'),
            val_5 = SMS.find('.p_info_text'),
            val_6 = SMS.find('.confirm_number input[type=hidden]'); */
            val_1 = SMS.find(".request_input input[type=button]"),
            val_2 = SMS.find(".re_number"),
            val_3 = SMS.find(".first_request div label div"),
            val_4 = "",
            val_5 = "",
            val_6 = SMS.find(".confirm_number input[type=hidden]");

        _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6, find_text);
    });
    second_request
        .on("click", ".re_number input", function () {
            //전화인증 번호 재전송
            var input_number = SMS.find("#phone")
                .val()
                .replace(/-/gi, ""),
                phone = {
                    phone: input_number
                },
                this_val = $(this).val(),
                find_text = "",
                /* val_1 = SMS.find('.confirm_number'),
            val_2 = SMS.find('.re_number'),
            val_3 = SMS.find('.send_number'),
            val_4 = SMS.find('.phone_number'),
            val_5 = SMS.find('.p_info_text'),
            val_6 = SMS.find('.confirm_number input[type=hidden]'); */
                val_1 = SMS.find(".request_input input[type=button]"),
                val_2 = SMS.find(".re_number"),
                val_3 = SMS.find(".first_request div label div"),
                val_4 = "",
                val_5 = "",
                val_6 = SMS.find(".confirm_number input[type=hidden]");

            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6, find_text);
        })
        .on("click", ".send_number input", function () {
            // 인증 확인 요청
            var request_number = second_request.find("#certification").val(),
                phone_number = second_request.find(".confirm_number input[type=hidden]").val(),
                request_value = {
                    phone: phone_number,
                    certification: request_number
                },
                url = "/api/oauth/sms_confirm";

            _Join.confirmRequest(request_value, url);
        });
    REGISTER.find("#password").change(function () {
        // 회원가입 비밀번호 유효성 검사
        var password_val = REGISTER.find("#password").val(),
            id_val = REGISTER.find("#id").val();

        _Join.checkPassword(password_val, id_val);
    });
    REGISTER.find("#pw_confirm_result").change(function () { // 회원 가입 비밀번호 확인 이벤트
        var password_length = $("#pw_confirm_result").val().length,
            password_adr = $("#pw_confirm_result"),
            fpassword_val = REGISTER.find("#password").val(),
            password_val = $("#pw_confirm_result").val();

        if (password_length < 8 || password_length > 16) {
            alert("8자리 이상 16자리 이하로 입력해 주세요.");
            password_adr.val("").focus();
            return false;
        }
        /* else if (password_val != fpassword_val) {
                   alert("패스워드를 확인해 주세요");
                   password_adr.val("").focus();
                   return false;
               } */
    });
    REGISTER.find("#pw_confirm_result").keyup(function () { // 회원가입 비밀번호 확인 타이핑 이벤트
        var password_length = $("#pw_confirm_result").val().length,
            password_adr = $("#pw_confirm_result"),
            fpassword_val = REGISTER.find("#password").val(),
            password_val = $("#pw_confirm_result").val();

        if (password_val != fpassword_val) {
            $("#pw_result span").text("비밀번호가 일치하지 않습니다");
        } else {
            $("#pw_result span").text("");
        }
    });
    pass_content.find("#password").change(function () {
        //아이디 찾기 비밀번호 유효성 검사
        var password_val = pass_content.find("#password").val(),
            id_val = pass_content.find("#id").val();

        _Join.checkPassword(password_val, id_val);
    });
    ADDREG.find("#age").on("keyup", function () {
        // 나이 숫자만 입력 받기
        $(this).val(
            $(this)
            .val()
            .replace(/[^0-9]/g, "")
        );
    });
    ADDREG.find("#age").on("change", function () {
        // 나이 숫자만 입력 받기
        if ($(this).val() != "") {
            if ($(this).val() < "19") {
                alert("19세 이상 60세 이하로 입력하세요.");
                $(this).val("");
            } else if ($(this).val() > "60") {
                alert("19세 이상 60세 이하로 입력하세요.");
                $(this).val("");
            }
        }
    });

    //****************************************** 핸드폰번호 입력 자동 하이픈 *************************************************//

    first_request.find(".phone_number label input").keyup(function (event) {
        var this_text = $(this);
        _Join.live_hyphen(this_text);
    }); //휴대전화인증 하이픈 자동입력
    DOCUMENT.on("keydown", "#idPhone", function (event) {
        var this_text = $(this);
        _Join.live_hyphen(this_text);
    }); //아이디 찾기 핸드폰 번호 하이픈
    DOCUMENT.on("keydown", "#pwPhone", function (event) {
        var this_text = $(this);
        _Join.live_hyphen(this_text);
    }); //비번 찾기 하이픈

    //********************************************** 아이디 찾기 ****************************************************//
    $(window).resize(function () {
        console.log($(document).width());
        $("#findIdBack").css({
            width: $(window).width(),
            height: $(window).height()
        });
        var do_width = $(window).width(),
            layer_width = $("#findIdLayer").width(),
            real_width = (do_width - layer_width) / 2;
        console.log(real_width);
        $("#findIdLayer").css("left", real_width);
    });
    $("#navibar").on("click", ".mainLoginContainer .loginPanel div:nth-child(2)", function () {
        _Join.findId_html();
        var do_width = $(window).width(),
            layer_width = $("#findIdLayer").width(),
            real_width = (do_width - layer_width) / 2;

        $("#findIdLayer").css("left", real_width);
        $("#findIdBack").css({
            width: $(window).width(),
            height: $(window).height()
        });
    });

    // 아이디 찾기 핸드폰 인증번호 요청
    DOCUMENT.on("keyup", "#idPhone", function () {
            // 아이디찾기 휴대폰 번호 입력 시 이벤트(번호만 입력가능)
            //$(this).val($(this).val().replace(/^[\d-\s]+$/, ""));
            var regExp = /^[0-9]*$/;
            var inputText = "";
            for (var i = 0; i < $(this).val().length; i++) {
                var splitStr = $(this)
                    .val()
                    .substring(i, i + 1);
                if (regExp.test(splitStr) || splitStr == "-") {
                    inputText += splitStr;
                } else {
                    break;
                }
            }

            $(this).val(inputText);

            /* if (inputText.length >= 13) {
                    $("#idBtn .requestSet .requestContainer .requestBtn").css("backgroundColor", "#4a4a4a");
                    $("#idBtn .requestSet .requestContainer .requestBtn").prop("disabled", false);
                } else {
                    $("#idBtn .requestSet .requestContainer .requestBtn").css("backgroundColor", "#aeaeae");
                    $("#idBtn .requestSet .requestContainer .requestBtn").prop("disabled", true);
                } */
        })
        .on("keyup", "#idconfirm", function () {
            // 아아디 찾기 인증번호 입력시 이벤트
            var regExp = /^[0-9]*$/;
            var inputText = "";
            for (var i = 0; i < $(this).val().length; i++) {
                var splitStr = $(this)
                    .val()
                    .substring(i, i + 1);
                if (regExp.test(splitStr)) {
                    inputText += splitStr;
                } else {
                    break;
                }
            }

            $(this).val(inputText);

            /* if (inputText.length >= 6) {
                $("#idBtn .requestSet .requestContainer .requestReBtn").css({
                    backgroundColor: "#aeaeae",
                    cursor: "default"
                });
                $("#idBtn .requestSet .requestContainer .requestReBtn").prop("disabled", true);
            } else {
                $("#idBtn .requestSet .requestContainer .requestReBtn").css({
                    backgroundColor: "#4a4a4a",
                    cursor: "pointer"
                });
                $("#idBtn .requestSet .requestContainer .requestReBtn").prop("disabled", false);
            } */
        })
        .on("click", "#idBtn .requestSet .requestContainer .requestBtn", function () {
            //아이디 찾기 핸드폰 번호 인증요청
            var input_number = $("#idPhone")
                .val()
                .replace(/-/gi, ""),
                phone = {
                    phone: input_number,
                    division: "1"
                },
                find_text = $(this)
                .parents("#idBtn")
                .siblings("#idHeader")
                .children()
                .text(),
                this_val = $(this).val(),
                val_1 = DOCUMENT.find("#idBtn .requestSet .requestContainer .requestBtn"),
                val_2 = DOCUMENT.find("#idBtn .requestSet .requestContainer .requestReBtn.nonDisplay"),
                val_3 = DOCUMENT.find("#findIdPhone"),
                val_4 = "",
                val_5 = "",
                val_6 = "";
            console.log(find_text);
            console.log($(this).parents("#idHeader"));
            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6, find_text);
        })
        .on("click", "#idHeader > div.closeBtn > span", function () {
            // 아이디 찾기 레이어 팝업 닫기
            $("#findIdBack").remove();
        })
        .on("click", "#idBtn .requestSet .requestContainer .requestReBtn", function () {
            // 아이디 찾기 인증번호 재요청
            var input_number = $("#findIdPhone").val(),
                phone = {
                    phone: input_number,
                    division: "1"
                },
                find_text = $(this)
                .parents("#idBtn")
                .siblings("#idHeader")
                .children()
                .text(),
                this_val = $(this).text(),
                val_1 = DOCUMENT.find("#idBtn .requestSet .requestContainer .requestBtn"),
                val_2 = DOCUMENT.find("#idBtn .requestSet .requestContainer .requestReBtn.nonDisplay"),
                val_3 = DOCUMENT.find("#findIdPhone"),
                val_4 = "",
                val_5 = "",
                val_6 = "";
            console.log(this_val);
            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6, find_text);
        })
        .on("click", "#idBtn .requestSet .subBtn", function () {
            //아이디 찾기 인증 확인 요청
            var request_number = $("#idconfirm").val(),
                phone_number = $("#findIdPhone").val(),
                request_value = {
                    phone: phone_number,
                    certification: request_number
                },
                url = "/api/oauth/find_id";

            _Join.confirmRequest(request_value, url);
            console.log(DOCUMENT.find("#findIdLayer").height());
        })
        .on("click", "#idcontent .resultContent .resultId div", function () {
            // 찾은 아이디 선택 이벤트
            $(this).addClass("id_selected");
            $(this)
                .children(".findUserId")
                .removeClass("id_selected");
            $(this)
                .siblings()
                .removeClass("id_selected");
            $(this)
                .siblings()
                .children(".findUserId")
                .removeClass("id_selected");
            $("#idBtn .submitSet .loginBtn").css("backgroundColor", "#4a4a4a");
            var select_text = $(this).text();
            console.log(select_text);
            _Join.setCookie("find_id", select_text, 1);
            console.log(_Join.getCookie("find_id"));
            /* _Join.deleteCookie("find_id");
            console.log(_Join.getCookie("find_id")); */
        })
        .on("click", "#idBtn .submitSet .pwFindBtn", function () {
            // 아이디 선택 후 비밀번호 재설정 클릭시 이벤트
            var select_class = $("#idcontent .resultContent .resultId .id_selected span").attr("class");
            console.log(select_class);
            if (select_class == "snsImg appORwep") {
                $("#findIdBack").remove();
                _Join.pw_reset_html();
                var do_width = $(window).width(),
                    layer_width = $("#findPwLayer").width(),
                    real_width = (do_width - layer_width) / 2;

                $("#findPwLayer").css("left", real_width);
                $("#findPwBack").css({
                    width: $(window).width(),
                    height: $(window).height()
                });
                DOCUMENT.find("#pw_id").val(_Join.getCookie("find_id"));
            } else {
                alert("SNS 계정은 해당 사이트에서 비밀번호를 변경하셔야 합니다.");
            }
        })
        .on("click", "#idBtn .submitSet .loginBtn", function () {
            // 아이디 선책후 로그인 클릭 이벤트
            console.log($(this).prop("class"));
            var select_class = $("#idcontent .resultContent .resultId .id_selected span").attr("class");
            console.log(select_class);
            if (select_class == "snsImg appORwep") {
                location.href = "https://ntalk.me/auth";
            } else if (select_class == "snsImg google") {
                location.href = "https://ntalk.me/api/oauth_social/googleLogin";
            } else if (select_class == "snsImg naver") {
                location.href = "https://ntalk.me/api/oauth_social/naverLogin";
            } else if (select_class == "snsImg kakao") {
                location.href = "https://ntalk.me/api/oauth_social/kakaoLogin";
            } else if (select_class == "snsImg facebook") {
                location.href = "https://ntalk.me/api/oauth_social/facebookLogin";
            } else {
                return;
            }
        });
    $(window).load(function () {
        if ($(location).attr("href") == "https://ntalk.me/auth" && _Join.getCookie("find_id") != "") {
            console.log(_Join.getCookie("find_id"));
            var find_memory_id = _Join.getCookie("find_id");
            $("#mainContent #warp #loginSection form #memberInfo .id_text #user").val(find_memory_id);
            $("#user").val(find_memory_id);
            console.log(find_memory_id);
            _Join.deleteCookie("find_id");
            console.log(_Join.getCookie("find_id"));
        }
    });
    //*********************************************************** 비밀번호 재설정 **********************************************************//
    /*****비밀번호 재설정 팝업 사이즈 이벤트 *****/
    $(window).resize(function () {
        console.log($(document).width());
        $("#findPwBack").css({
            width: $(window).width(),
            height: $(window).height()
        });
        var do_width = $(window).width(),
            layer_width = $("#findPwLayer").width(),
            real_width = (do_width - layer_width) / 2;
        console.log(real_width);
        $("#findPwLayer").css("left", real_width);
    });
    /*******비밀번호 재설정 팝업 이벤트 ************/
    $("#navibar").on("click", ".mainLoginContainer .loginPanel div:nth-child(3)", function () {
        _Join.pw_reset_html();
        var do_width = $(window).width(),
            layer_width = $("#findPwLayer").width(),
            real_width = (do_width - layer_width) / 2;

        $("#findPwLayer").css("left", real_width);
        $("#findPwBack").css({
            width: $(window).width(),
            height: $(window).height()
        });
    });
    DOCUMENT.on("click", "#pwHeader > div.closeBtn", function () {
            // 비밀번호 재설정 팝업 닫기 이벤트
            $("#findPwBack").remove();
        })
        .on("keyup", "#pwPhone", function () {
            var regExp = /^[0-9]*$/;
            var inputText = "";
            for (var i = 0; i < $(this).val().length; i++) {
                var splitStr = $(this)
                    .val()
                    .substring(i, i + 1);
                if (regExp.test(splitStr) || splitStr == "-") {
                    inputText += splitStr;
                } else {
                    break;
                }
            }

            $(this).val(inputText);

            if (inputText.length >= 13) {
                $("#idBtn .requestContainer .requestBtn").css("backgroundColor", "#4a4a4a");
                $("#idBtn .requestContainer .requestBtn").prop("disabled", false);
            } else {
                $("#idBtn .requestContainer .requestBtn").css("backgroundColor", "#aeaeae");
                $("#idBtn .requestContainer .requestBtn").prop("disabled", true);
            }
        })
        .on("keyup", "#pwconfirm", function () {
            // 비밀번호 재설정 인증번호 입력 이벤트
            var regExp = /^[0-9]*$/;
            var inputText = "";
            for (var i = 0; i < $(this).val().length; i++) {
                var splitStr = $(this)
                    .val()
                    .substring(i, i + 1);
                if (regExp.test(splitStr)) {
                    inputText += splitStr;
                } else {
                    break;
                }
            }

            $(this).val(inputText);

            if (inputText.length >= 6) {
                $("#idBtn .requestContainer .requestReBtn").css({
                    backgroundColor: "#aeaeae",
                    cursor: "default"
                });
                $("#idBtn .requestContainer .requestReBtn").prop("disabled", true);
            } else {
                $("#idBtn .requestContainer .requestReBtn").css({
                    backgroundColor: "#4a4a4a",
                    cursor: "pointer"
                });
                $("#idBtn .requestContainer .requestReBtn").prop("disabled", false);
            }
        })
        .on("change", "#newPw", function () {
            //비밀번호 재설정 새 비밀번호 입력 완료 이벤트
            var psw_length = $("#newPw").val().length,
                psw_adr = $("#newPw");

            if (psw_length < 8 || psw_length > 16) {
                alert("8자리 이상 16자리 이하로 입력해 주세요.");
                psw_adr.val("").focus();
                return false;
            }
        })
        .on("keyup", "#newPw", function () {
            //비밀번호 재설정 새비밀번호 입력 이벤트
            var regExp = /^[A-Za-z0-9~!@#$%^&*()_+|<>?:{}]*$/;
            var inputText = "";
            for (var i = 0; i < $(this).val().length; i++) {
                var splitStr = $(this)
                    .val()
                    .substring(i, i + 1);
                if (regExp.test(splitStr)) {
                    inputText += splitStr;
                } else {
                    break;
                }
            }

            $(this).val(inputText);
        })
        .on("keyup", "#newPwConfirm", function () {
            // 비밀번호 재설정 새비밀번호 확인 입력 이벤트
            var regExp = /^[A-Za-z0-9~!@#$%^&*()_+|<>?:{}]*$/;
            var inputText = "",
                newpw = $("#newPw").val();
            for (var i = 0; i < $(this).val().length; i++) {
                var splitStr = $(this)
                    .val()
                    .substring(i, i + 1);
                if (regExp.test(splitStr)) {
                    inputText += splitStr;
                } else {
                    break;
                }
            }

            $(this).val(inputText);

            if (inputText.length >= 8) {
                $("#idBtn .sub_reset").css({
                    backgroundColor: "#f71581",
                    cursor: "pointer"
                });
                $("#idBtn .sub_reset").prop("disabled", false);
            } else {
                $("#idBtn .sub_reset").css({
                    backgroundColor: "#aeaeae",
                    cursor: "default"
                });
                $("#idBtn .sub_reset").prop("disabled", true);
            }
            if (inputText != newpw) {
                $("#new_pw_result span").text("비밀번호가 일치하지 않습니다");
            } else {
                $("#new_pw_result span").text("");
            }
        })
        .on("click", "#idBtn .requestContainer .requestBtn", function () {
            //비밀번호 핸드폰 인증 요청
            var input_number = $("#pwPhone").val(),
                phone_number = input_number.replace(/-/gi, ""),
                phone = {
                    phone: phone_number,
                    division: "2"
                },
                this_val = $(this).text(),
                find_text = $("#pwHeader .findpwTitle").text(),
                val_1 = DOCUMENT.find("#idBtn .requestContainer .requestBtn"),
                val_2 = DOCUMENT.find("#idBtn .requestContainer .requestReBtn"),
                val_3 = "",
                val_4 = "",
                val_5 = "",
                val_6 = "";
            console.log(input_number);
            console.log(phone_number);
            console.log(phone);
            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6, find_text);
        })
        .on("click", "#idBtn .subBtn", function () {
            //비밀번호 핸드폰 인증 확인
            var request_number = $("#pwconfirm").val(),
                input_number = $("#pwPhone").val(),
                phone_number = input_number.replace(/-/gi, ""),
                request_value = {
                    phone: phone_number,
                    certification: request_number
                },
                url = "/api/oauth/sms_confirm";

            _Join.confirmRequest(request_value, url);
        })
        .on("click", "#idBtn .requestContainer .requestReBtn", function () {
            // 비밀번호 재설정 인증번호 재전송
            var input_number = $("#pwPhone").val(),
                phone_number = input_number.replace(/-/gi, ""),
                phone = {
                    phone: phone_number,
                    division: "2"
                },
                this_val = $(this).text(),
                find_text = "",
                val_1 = DOCUMENT.find("#idBtn .requestContainer .requestBtn"),
                val_2 = DOCUMENT.find("#idBtn .requestContainer .requestReBtn"),
                val_3 = "",
                val_4 = "",
                val_5 = "",
                val_6 = "";

            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6, find_text);
        })
        .on("click", "#idBtn > div.sub_reset", function () {
            //비밀번호 변경 하기
            var pw_id = $("#pw_id").val(),
                pw_phone = $("#pwPhone")
                .val()
                .replace(/-/gi, ""),
                pw_confirm = $("#pwconfirm").val(),
                pw_new = $("#newPw").val(),
                pw_newConfirm = $("#newPwConfirm").val(),
                request_value = {
                    id: pw_id,
                    phone: pw_phone,
                    certification: pw_confirm,
                    password: pw_new,
                    password_confirm: pw_newConfirm
                },
                url = "/api/oauth/find_pass";
            if (pw_new != pw_newConfirm) {
                alert("비밀번호를 확인해 주세요");
            } else {
                _Join.find_pass(request_value, url);
            }
        });

    /**********************************************************************************************/
    /****************************** 아이디 저장 ***************************************************/
    /**********************************************************************************************/
    var userInputId = "";
    userInputId = _Join.getCookie("userInputId");
    console.log(userInputId);
    $("#user").val(userInputId);
    console.log($("#user").val());
    if ($("#user").val() != "") {
        $("#idsave").prop("checked", true);
    }
    $("#loginSection")
        .on("change", "#idsave", function () {
            if ($("#idsave").is(":checked")) {
                var userInputId = $("#user").val();
                _Join.setCookie("userInputId", userInputId, 7);
            } else {
                _Join.deleteCookie("userInputId");
            }
            console.log($("#idsave").is(":checked"));
        })
        .on("keyup", "#user", function () {
            console.log("아이디값 변경");
            if ($("#idsave").is(":checked")) {
                var userInputId = $("#user").val();
                _Join.setCookie("userInputId", userInputId, 7);
            }
        });
})();