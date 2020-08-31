var _Join = null,
    _Time = null,
    _Main = null,
    idOverlap = null,
    nickOverlap = null,
    //json_data = null,
    DOCUMENT = $(document),
    SMS = $('#sms_confirm'),
    first_request = SMS.find('.first_request'),
    second_request = SMS.find('.second_request'),
    REGISTER = $('#s_register'),
    first_select = REGISTER.find('#s_Additional_information #first'),
    form_submit = REGISTER.find('#member_register'),
    FIND = $('#findId'),
    find_content = FIND.find('#findIdContent'),
    FIND_P = $('#findPassword'),
    pass_content = FIND_P.find('#passContent'),
    TIMELIST = $('#timeLineContainer'),
    TIMEPRO = $('#popupProfile');


(function () {
    _Join = (function () {
        return {
            phoneNumberConfirm: function (phone, input_number, this_val, a, b, c, d, e, f) {
                $.ajax({
                    type: "GET",
                    url: "/api/oauth/sms_request/",
                    data: phone,
                    success: function (data) {
                        console.log(data);
                        alert("인증번호가 전송 되었습니다.");
                        //인증요청 유효시간
                        var fiveMinutes = 180 * 1,
                            display = $('#time');

                        if (this_val == "인증요청") {
                            var timer = fiveMinutes,
                                minutes, seconds;
                            var refreshIntervalId = setInterval(function () {
                                minutes = parseInt(timer / 60, 10)
                                seconds = parseInt(timer % 60, 10);

                                minutes = minutes < 10 ? "0" + minutes : minutes;
                                seconds = seconds < 10 ? "0" + seconds : seconds;

                                display.text(minutes + ":" + seconds);
                                if (--timer < 0) {
                                    alert('인증요청 시간이 초과되었습니다.');
                                    clearInterval(refreshIntervalId);
                                }
                            }, 1000);
                        }
                        console.log($(location).attr('href'));
                        console.log($(location).attr('href') == 'https://ntalk.me/auth/sms');
                        if ($(location).attr('href') == 'https://ntalk.me/auth/sms') {
                            a.removeClass('nonDisplay');
                            b.removeClass('nonDisplay');
                            c.removeClass('nonDisplay');
                            d.addClass('nonDisplay');
                            e.addClass('nonDisplay');
                            f.val(input_number);
                        } else if ($(location).attr('href') == 'https://ntalk.me/auth/find_id') {
                            a.removeClass('nonDisplay');
                            b.addClass('nonDisplay');
                            c.val(input_number);
                        } else if ($(location).attr('href') == 'https://ntalk.me/auth/find_pass') {
                            a.removeClass('nonDisplay');
                            b.attr('name', 'use_name');
                            c.val(input_number);
                        }
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        if (request.status == 403) {
                            alert("1일 인증요청 횟수 초과입니다.")
                        } else if (request.status == 400) {
                            alert("전화번호 가입횟수 제한이 초과 되었습니다.");
                        } else if (request.status == 500) {
                            alert("관리자에게 문의해 주세요")
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
                        var data_val = data.data;
                        alert("인증이 완료 되었습니다.");
                        if (url == '/api/oauth/sms_confirm' && $(location).attr('href') == 'https://ntalk.me/auth/sms') {
                            location.href = '/auth/register?phone=' + request_value.phone;
                        } else if (url == '/api/oauth/find_id') {
                            FIND.find('#findIdFooter').removeClass('nonDisplay');
                            find_content.find('.certificationNumber').addClass('nonDisplay');
                            for (var i = 0; i < 3; i++) {
                                FIND.find('#findIdFooter .responseContainer div input[name="response' + (i + 1) + '"]').val(data_val[i]);
                                if (FIND.find('#findIdFooter .responseContainer div input[name="response' + (i + 1) + '"]').val() == '') {
                                    FIND.find('#findIdFooter .responseContainer div input[name="response' + (i + 1) + '"]').addClass('nonDisplay');
                                }
                            }
                        } else if (url == '/api/oauth/sms_confirm' && $(location).attr('href') == 'https://ntalk.me/auth/find_pass') {
                            pass_content.find('.firstInputContainer .certificationNumber .confrim_number .time_contanier').addClass('nonDisplay');
                            pass_content.find('.firstInputContainer .certificationNumber .confrim_number .n_info_text').addClass('nonDisplay');
                            pass_content.find('.firstInputContainer .certificationNumber .re_number').addClass('nonDisplay');
                            pass_content.find('#submit_request').addClass('nonDisplay');
                            pass_content.find('.secondInputConatainer').removeClass('nonDisplay');
                        }
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        if (request.status == 404) {
                            alert("인증 번호를 확인해 주세요.")
                        } else if (request.status == 500) {
                            alert("인증시간이 초과 되었습니다.")
                        } else if (request.status == 400) {
                            alert("전화번호 가입횟수 제한이 초과 되었습니다.");
                        }
                    }
                });
            },
            locationHTML: function (locationData) {
                var html = '',
                    locationData_langth = locationData.length;

                html += '<div class="first_location">';
                html += '<select id="first" name="location1" title="1차 지역선택">';
                html += '<option value="">시/도 선택</option>';
                for (var i = 0; i < locationData_langth; i++) {
                    html += '<option value=' + locationData[i].name + '>' + locationData[i].name + '</option>';
                }
                html += '</select>';
                html += '</div>';
                html += '<div class="second_location">';
                html += '<select id="second" name="location2" title="2차 지역선책">';
                html += '<option value="" class="noDel">구/군 선택</option>';
                html += '</select>';
                html += '</div>';

                return html;
            },
            locationSecond: function (locationData) {
                var select_value = REGISTER.find('#s_Additional_information #first option:selected').val(),
                    select_index = REGISTER.find('#s_Additional_information #first option:selected').index() - 1,
                    second_select = REGISTER.find('#second'),
                    secondData_length = locationData[select_index].childs.length;

                second_select.prop('disabled', false);
                REGISTER.find('#second').children().not('.noDel').remove();
                for (var i = 0; i < secondData_length; i++) {
                    var html = '';

                    html += '<option value=' + locationData[select_index].childs[i] + '>' + locationData[select_index].childs[i] + '</option>';

                    REGISTER.find('#second').append(html);
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
                        alert('사용 가능한 ' + name + ' 입니다.');
                        if (data.code == 200 && data.message == '사용 가능한 아이디 입니다.') {
                            idOverlap = "check";
                        } else if (data.code == 200 && data.message == '사용 가능한 닉네임 입니다.') {
                            nickOverlap = "check";
                        }
                        console.log(nickOverlap);
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        if (request.status == 409) {
                            alert('사용중인 ' + name + ' 입니다.')
                        } else if (request.status == 400) {
                            alert("형식에 맞게 입력해 주세요.")
                        }
                    }
                });
            },
            getParameterValue: function (val) {
                val = val.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
                var regex = new RegExp("[\\?&]" + val + "=([^&#]*)"),
                    result = regex.exec(location.search);
                return result === null ? '' : decodeURIComponent(result[1].replace(/\+/g, " "));
            },
            checkPassword: function (password, id) {
                var password_adr = REGISTER.find('#password'),
                    password_length = password.length;
                console.log(password_length);
                if (password_length < 8 || password_length > 14) {
                    alert('8자리 이상 14자리 이하로 입력해 주세요.');
                    password_adr.val('').focus();
                    return false;
                }
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
                        $text.val($text.val() + '-');
                    }
                    if ($text.val().length === 8) {
                        $text.val($text.val() + '-');
                    }
                }
                return (key == 8 || key == 9 || key == 46 || (key >= 48 && key <= 57) || (key >= 96 && key <= 105));
            },
            location_json: function () {
                $.ajax({
                    timeout: 3000,
                    type: "GET",
                    url: "/json/administrative_region.json",
                    dataType: "json"
                }).done(function (data) {
                    var locationData = data.data;

                    REGISTER.find('#s_Additional_information .s_area').append(_Join.locationHTML(locationData));

                    REGISTER.find('#first').change(function () { //1차 지역 선택시

                        REGISTER.find('#first option:selected').attr('selected', 'selected');
                        if (REGISTER.find('#first option:selected').text() == "시/도 선택") {
                            REGISTER.find('#s_Additional_information #second .noDel').prop('selected', 'selected');
                            REGISTER.find('#second').children().not('.noDel').remove();
                        } else {
                            _Join.locationSecond(locationData);
                        }
                        var phone = _Join.getParameterValue('phone'); // 주소창 핸드폰 번호 input에 추가
                        REGISTER.find('.s_joinContainer label input[type=hidden]').val(phone);
                    });
                    REGISTER.find('#second').change(function () { // 2차 지역 선택시
                        REGISTER.find('#second option:selected').attr('selected', 'selected');
                    });
                });
            }
        };
    })(); //회원가입 관련 함수
    (function () {
        _Join.location_json();
    })(); // 지역 선택 json 처리 후 append
    (function () {
        form_submit.submit(function (event) {
            var form_data = form_submit.serialize(),
                password_first = REGISTER.find('.s_pwContainer #password').val(),
                password_second = REGISTER.find('.s_pwContainer #pw_confirm_result').val(),
                id_val = REGISTER.find('.s_idContainer #id').val(),
                radio_check = false,
                radio_adr = REGISTER.find('.s_genderContainer label input[name=gender]'),
                radio_length = REGISTER.find('.s_genderContainer label input[name=gender]').length;
            for (var i = 0; i < radio_length; i++) {
                if (radio_adr[i].checked == true) {
                    radio_check = true;
                }
            }
            if (password_first == '' || password_second == '' || id_val == '' || radio_check == false) {
                alert("필수 입력란을 입력해 주세요.");
                event.preventDefault();
                return
            } else if (password_first !== password_second) {
                alert("비밀번호를 확인해 주세요.");
                event.preventDefault();
                return
            } else if (idOverlap == null) {
                alert("아이디 중복검사를 해주세요.");
                event.preventDefault();
                return
            } else if (nickOverlap == null) {
                alert("닉네임 중복검사를 해주세요.");
                event.preventDefault();
                return
            }
        }); // 회원가입 가입요청
        pass_content.find('#passwordRequest').submit(function (event) {
            var form_data = pass_content.find('#passwordRequest').serialize(),
                password_first = pass_content.find('#password').val(),
                password_second = pass_content.find('#pw_confirm_result').val();

            if (password_first !== password_second) {
                alert("비밀번호를 확인해 주세요");
                event.preventDefault();
                return
            }
        }); //비밀번호 초기화 요청
        REGISTER.on('click', '#s_essentialContainer .s_idContainer label input[type=button]', function () { //아이디 중복확인
            var id_val = REGISTER.find('#s_essentialContainer .s_idContainer label input').val(),
                division_val = REGISTER.find('#s_essentialContainer .s_idContainer label input[type=hidden]').val(),
                confirm_val = {
                    id: id_val,
                    division: division_val
                },
                id_name = "아이디";

            _Join.overlap_ajax(confirm_val, id_name);
        }).on('click', '.s_nickContainer label input[type=button]', function () {
            var nick_val = REGISTER.find('.s_nickContainer label input[type=text]').val(),
                division_val = REGISTER.find('.s_nickContainer label input[type=hidden]').val(),
                confirm_val = {
                    id: nick_val,
                    division: division_val
                },
                nick_name = "닉네임";

            _Join.overlap_ajax(confirm_val, nick_name);
        }); //닉네임 중복 검사
        first_request.on('click', '.phone_number .request_input input', function () {
            var input_number = SMS.find('.phone_number label input').val().replace(/-/gi, ""),
                phone = {
                    phone: input_number
                },
                this_val = $(this).val(),
                val_1 = SMS.find('.confirm_number'),
                val_2 = SMS.find('.re_number'),
                val_3 = SMS.find('.send_number'),
                val_4 = SMS.find('.phone_number'),
                val_5 = SMS.find('.p_info_text'),
                val_6 = SMS.find('.confirm_number input[type=hidden]');

            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6);
        }); // 전화인증 번호 요청
        second_request.on('click', '.re_number label input', function () { //전화인증 번호 재전송
            var input_number = SMS.find('.second_request .confirm_number input[type=hidden]').val(),
                phone = {
                    phone: input_number
                },
                this_val = $(this).val(),
                val_1 = SMS.find('.confirm_number'),
                val_2 = SMS.find('.re_number'),
                val_3 = SMS.find('.send_number'),
                val_4 = SMS.find('.phone_number'),
                val_5 = SMS.find('.p_info_text'),
                val_6 = SMS.find('.confirm_number input[type=hidden]');

            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6);
        }).on('click', '.send_number label input', function () {
            var request_number = second_request.find('#certification').val(),
                phone_number = second_request.find('.confirm_number input[type=hidden]').val(),
                request_value = {
                    phone: phone_number,
                    certification: request_number
                },
                url = '/api/oauth/sms_confirm';

            _Join.confirmRequest(request_value, url);
        }); // 인증확인 요청
        REGISTER.find('#password').change(function () {
            var password_val = REGISTER.find('#password').val(),
                id_val = REGISTER.find('#id').val();

            _Join.checkPassword(password_val, id_val);
        }); // 회원가입 비밀번호 유효성 검사
        pass_content.find('#password').change(function () {
            var password_val = pass_content.find('#password').val(),
                id_val = pass_content.find('#id').val();

            _Join.checkPassword(password_val, id_val);
        }); //아이디 찾기 비밀번호 유효성 검사
    })(); // 회원가입 이벤트, 비밀번호 유효성 검사
    (function () {
        //*************************** 핸드폰번호 입력 자동 하이픈 ****************************//
        first_request.find('.phone_number label input').keyup(function (event) {
            var this_text = $(this);
            _Join.live_hyphen(this_text);
        }); //휴대전화인증 하이픈 자동입력
        find_content.find('.phoneCertification div label input').keyup(function (event) {
            var this_text = $(this);
            _Join.live_hyphen(this_text);
        }); //아이디 찾기 핸드폰 번호 하이픈
        pass_content.find('.firstInputContainer .phoneInput label input[type=text]').keyup(function (event) {
            var this_text = $(this);
            _Join.live_hyphen(this_text);
        }); //비번 찾기 하이픈
    })(); // 핸드폰 번호 입력 실시간 하이픈 입력 이벤트
    (function () {
        //************************** 아이디 찾기 ******************************************//
        find_content.find('.phoneCertification .request_input').on('click', 'input', function () {
            var input_number = find_content.find('.phoneCertification div label input').val().replace(/-/gi, ""),
                phone = {
                    phone: input_number
                },
                this_val = $(this).val(),
                val_1 = find_content.find('.certificationNumber'),
                val_2 = find_content.find('.phoneCertification'),
                val_3 = find_content.find('.certificationNumber .confrim_number input[type=hidden]'),
                val_4 = '',
                val_5 = '',
                val_6 = '';

            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6);
        }); //아이디 인증번호 요청
        find_content.find('.certificationNumber').on('click', '.re_number label input', function () { // 인증번호 재요청
            var input_number = find_content.find('.certificationNumber .confrim_number input[type=hidden]').val(),
                phone = {
                    phone: input_number
                },
                this_val = $(this).val(),
                val_1 = find_content.find('.certificationNumber'),
                val_2 = find_content.find('.phoneCertification'),
                val_3 = find_content.find('.certificationNumber .confrim_number input[type=hidden]'),
                val_4 = '',
                val_5 = '',
                val_6 = '';

            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6);
        }).on('click', '#submit_value label input', function () {
            var request_number = find_content.find('.certificationNumber .confrim_number label input').val(),
                phone_number = find_content.find('.certificationNumber .confrim_number input[type=hidden]').val(),
                request_value = {
                    phone: phone_number,
                    certification: request_number
                },
                url = '/api/oauth/find_id';

            _Join.confirmRequest(request_value, url);
        }); // 인증 확인 요청
    })(); // 아이디 찾기 이벤트
    (function () {
        //************************** 비밀번호 초기화 *************************//
        pass_content.on('click', '.firstInputContainer .phoneInput label input[type=button]', function () { //비번 핸드폰 인증 요청
            var input_number = pass_content.find('.firstInputContainer .phoneInput label input[type=text]').val().replace(/-/gi, ""),
                phone = {
                    phone: input_number
                },
                this_val = $(this).val(),
                val_1 = FIND_P.find('#passwordRequest .firstInputContainer .certificationNumber'),
                val_2 = pass_content.find('.firstInputContainer .phoneInput label input[type=text]'),
                val_3 = pass_content.find('.firstInputContainer .certificationNumber .confrim_number input[type=hidden]'),
                val_4 = '',
                val_5 = '',
                val_6 = '';

            pass_content.find('.firstInputContainer .phoneInput label input[type=button]').addClass('nonDisplay');
            pass_content.find('.firstInputContainer .p_info_text').addClass('nonDisplay');
            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6);
        }).on('click', '#submit_request label input', function () { //비번 핸드폰 인증 확인
            var request_number = pass_content.find('.firstInputContainer .certificationNumber .confrim_number label input[type=text]').val(),
                phone_number = pass_content.find('.firstInputContainer .phoneInput input[type=text]').val().replace(/-/gi, ""),
                request_value = {
                    phone: phone_number,
                    certification: request_number
                },
                url = '/api/oauth/sms_confirm';

            _Join.confirmRequest(request_value, url);
        }).on('click', '.firstInputContainer .certificationNumber .re_number label input', function () {
            var input_number = pass_content.find('.firstInputContainer .phoneInput label input[type=text]').val().replace(/-/gi, ""),
                phone = {
                    phone: input_number
                },
                this_val = $(this).val(),
                val_1 = FIND_P.find('#passwordRequest .firstInputContainer .certificationNumber'),
                val_2 = pass_content.find('.firstInputContainer .phoneInput label input[type=text]'),
                val_3 = pass_content.find('.firstInputContainer .certificationNumber .confrim_number input[type=hidden]'),
                val_4 = '',
                val_5 = '',
                val_6 = '';

            _Join.phoneNumberConfirm(phone, input_number, this_val, val_1, val_2, val_3, val_4, val_5, val_6);
        }); //인증번호 재전송
    })(); //비밀번호 초기화 이벤트
})(); //회원가입, 문자인증, 로그인, 아이디/패스워드 찾기
(function () {
    _Time = (function () {
        return {
            timeLineList: function (_this_text, position_top, position_left) {
                $.ajax({
                    timeout: 3000,
                    type: "POST",
                    url: "/Api/timeline/timeline_lists?page=" + _this_text,
                    dataType: "json",
                    success: function (data) {
                        console.log(_this_text)
                        console.log(data);
                        var dataContent = data.data,
                            currentPage = dataContent.currentPage,
                            nextPage = dataContent.nextPage,
                            lastPage = dataContent.lastPage,
                            dataList = dataContent.post,
                            dataListLength = dataList.length,
                            dataMessage = data.message,
                            lodingH = position_top, //크기 구해서 중간에 나나택내기
                            lodingW = position_left;
                        console.log(lodingH);
                        console.log(lodingW);
                        console.log($(document).scrollTop());
                        TIMELIST.find('.bubblingG').css({
                            'left': position_left,
                            'top': position_top
                        });
                        TIMELIST.find('.bubblingG').show();

                        _Time.list_html(dataContent, currentPage, nextPage, lastPage, dataList, _this_text);

                        $(window).data('ajaxready', true);
                        console.log(dataMessage);
                        if (dataMessage == '마지막 페이지 입니다.') {
                            TIMELIST.find('#plusView').addClass('nonDisplay');
                        } else {
                            TIMELIST.find('#plusView').removeClass('nonDisplay');
                        }
                        setTimeout(function () {
                            TIMELIST.find('.bubblingG').hide();
                        }, 1000);
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    }
                });
            },
            list_html: function (dataContent, currentPage, nextPage, lastPage, dataList, _this_text) {
                var html = '',
                    object = dataList;
                for (var variable in object) {
                    if (object.hasOwnProperty(variable)) {
                        console.log(lastPage);
                        var data = object[variable],
                            gameCount = parseInt(variable) + 1,
                            listCount = TIMELIST.find('.timeLineContent').children().length,
                            userNickname = data.nickname,
                            userComment = data.comment,
                            number = data.t_idx;

                        if (listCount == 0) {
                            gameCount = gameCount;
                        } else {
                            var number = parseInt(TIMELIST.find('.timeLineContent').children().last().attr('class').replace(/[^0-9]/g, ""));
                            gameCount = number + gameCount;
                        }

                        html += '<li class="number ' + gameCount + '">';
                        html += '<div id="userInfo">';
                        html += '<div class="userImg">';
                        html += '<img src="https://image.flaticon.com/icons/png/512/44/44562.png">';
                        html += '</div>';
                        html += '<div class="number nonDisplay">' + number + '</div>';
                        html += '<div class="userId" title="아이디">' + userNickname + '</div>';
                        html += '<input type="hidden" id="userNick" name="userNick">'
                        html += '<div class="userReport" title="신고하기">';
                        html += '<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSCE8kuphCnvH31Q2x8VzLP9gaIVbzVusPg7IRZTdSez5ahgTFB">';
                        html += '</div>';
                        html += '<div class="userDelete" title="삭제하기">';
                        html += '<img src="https://pic.90sjimg.com/design/00/56/26/96/591c60a7605ff.png">';
                        html += '</div>';
                        html += '<div class="userModify" title="수정하기">';
                        html += '<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7pIF0diqoY-PwAj5J0munMuN330uXaEs-nswSuZ4KRkSMfQiZ">';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="userComment">' + userComment + '</div>';
                        html += '<div class="textModify nonDisplay">';
                        html += '<textarea class="noresize" cols="50" autofocus required wrap="hard"></textarea>'
                        html += '<input type="button" value="수정글 등록">';
                        html += '</div>';
                        html += '</li>';
                    }
                }
                TIMELIST.find('.timeLineContent').append(html);
            },
            timeLine_ajax: function (url, request_value, _this) {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: request_value,
                    success: function (data) {
                        console.log(data);
                        //location.href = '/timeline';
                        var _this_text = 1,
                            LIST = TIMELIST.find('.timeLineContent');
                        location_true = location.href == 'https://ntalk.me/timeline';
                        console.log(location_true);
                        if (location_true == true) {
                            LIST.find('li').remove();
                            _Time.timeLineList(_this_text); // 타임라인 리스트, 자동 스크롤 첫페이지 입력 이벤트
                        }
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        if (request.status == 406) {
                            alert("아이디가 일치하지 않습니다.")
                        } else if (request.status == 404) {
                            alert("본인이 작성한 타임라인이 아닙니다.")
                        } else if (request.status == 400) {
                            alert("게시글이 입력되지 않았습니다.");
                        }
                        if (url == '/api/Timeline/timeline_edit') {
                            _this.parent('.textModify').addClass('nonDisplay');
                        }
                    }
                });
            },
            profile_html: function (imgUrl, idUrl) {
                var html = '';

                html += '<div id="popupProfile">';
                html += '<div class="profilePopupContainer">';
                html += '<div class="profileInfo">';
                html += '<div class="profileTitle">';
                html += '<div calss="profileClose">';
                html += '<img src="https://image.flaticon.com/icons/svg/61/61155.svg">';
                html += '</div>';
                html += '</div>';
                html += '<div class="profileImg">';
                html += '<img src="' + imgUrl + '">';
                html += '</div>';
                html += '<div class="profileId">';
                html += '<p>' + idUrl + '</p>';
                html += '</div>';
                html += '</div>';
                html += '<div class="profileEvent">';
                html += '<div class="talk">';
                html += '<a href="javascript:;" id="talking"><img src="https://image.flaticon.com/icons/svg/1878/1878874.svg"></a>';
                //html += '<a href="javascript:;" id="talking"><p>대화걸기</p></a>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                TIMELIST.before(html);
            },
            report_html: function () {
                var html = '';

                html += '<div id="popupReport">';
                html += '<div class="reportPopupContainer">';
                html += '<div class="reportTitle">';
                html += '<p>신고하기</p>';
                html += '</div>';
                html += '<div class="reportText">';
                html += '<p>';
                html += '해당 콘텐츠의 사유를 선택하여 주세요<br>';
                html += '심사후 해당 콘텐츠에 대한 조치를 취하겠습니다.';
                html += '</p>';
                html += '</div>';
                html += '<div class="reportButtonContainer">';
                html += '<form action="" method="POST" id="report">';
                html += '<div class="reportCheckContainer">';
                html += '<div class="reportButton">';
                html += '<input type="radio" id="report_1" name="report" value=""><span>홍보 / 광고글</span></div>';
                html += '<div class="reportButton">';
                html += '<input type="radio" id="report2" name="report" value=""><span>음란성 문구 사용 / 성매매 관련 내용</span></div>';
                html += '<div class="reportButton">';
                html += '<input type="radio" id="report_3" name="report" value=""><span>특정인에 대한 모욕 및 비방행위</span></div>';
                html += '<div class="reportButton">';
                html += '<input type="radio" id="report_4" name="report" value=""><span>금전요구 / 사행성 조장행위를 포함</span></div>';
                html += '<div class="reportButton">'
                html += '<input type="radio" id="report_5" name="report" value=""><span>스팸성 도배 콘텐츠</span></div>';
                html += '</div>';
                html += '<div class="buttonContainer">';
                html += '<input type="submit" class="btn btn-danger" name="report_value" value="신고하기">';
                html += '<input type="button" class="btn btn-danger" name="close" value="취소">';
                html += '</div>';
                html += '</form>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                TIMELIST.before(html);
            },
            chat_html: function (time) {
                var win = window.open("https://ntalk.me/main/chat/", "대화방", "location=no, scrollbars=yes, resizable=no, top=500, left=500, width=350px, height=600px");
            }
        }
    })(); //타임라인 관련 함수
    (function () {
        var _this_text = 1,
            location_true = location.href == 'https://ntalk.me/timeline';

        if (location_true == true) {
            _Time.timeLineList(_this_text); // 타임라인 리스트, 자동 스크롤 첫페이지 입력 이벤트
        }
        console.log($('#menuTap .writeTimeLine input'));
        /*    var date = new Date(),
                hours = String(date.getHours()),
                minutes = String(date.getMinutes()),
                seconds = String(date.getSeconds());
                time = hours+minutes+seconds;
        console.log(time);*/
        (function () { // 타임라인 자동스크롤 이벤트
            if (location_true == true) {
                //$(window).data('ajaxready', true).scroll(function(){
                $(window).data('ajaxready', true);
                TIMELIST.find('#plusView').on('click', 'div', function () {
                    console.log($(window).data('ajaxready'));
                    if ($(window).data('ajaxready') == false) return;

                    var _this = $(this),
                        select_offset = _this.offset(),
                        position_top = (select_offset.top) - 500,
                        position_left = (select_offset.left) + 220;
                    /*var scrollT = $(this).scrollTop(),
                        scrollH = $(this).height(),
                        contentH = TIMELIST.height(),
                        height_value = scrollT + scrollH;
                    console.log(height_value);
                    console.log(contentH);
                    if(height_value >= contentH){*/
                    _this_text++;
                    $(window).data('ajaxready', false);
                    console.log($(window).data('ajaxready'));
                    _Time.timeLineList(_this_text, position_top, position_left); // 타임라인 리스트, 자동 스크롤
                    //}
                });
            }
            TIMELIST.find('.timeLineContent').on('click', '.userModify', function () { //타임라인 수정버튼 클릭 이벤트
                var _this = $(this),
                    present_value = _this.parents('#userInfo').siblings('.userComment').text(),
                    select_number = _this.parents('li').attr('class').replace(/[^0-9]/g, "");

                _this.parents('#userInfo').siblings('.textModify').removeClass('nonDisplay');
                TIMELIST.find('.timeLineContent').children().not('li.number.' + select_number + '').children('.textModify').addClass('nonDisplay');
                _this.parents('#userInfo').siblings('.textModify').children('textarea').val(present_value);
            }).on('click', '.textModify input[type=button]', function () { // 타임라인 수정글 등록 이벤트
                var _this = $(this),
                    number = _this.parents('.textModify').siblings('#userInfo').children('.number').text(),
                    content = _this.siblings('textarea').val(),
                    request_value = {
                        t_idx: number,
                        comment: content
                    },
                    url = '/api/Timeline/timeline_edit';

                console.log(number);
                console.log(content);
                console.log(request_value);
                _Time.timeLine_ajax(url, request_value, _this);
            }).on('click', '.userDelete', function () { // 타임라인 글 삭제 이벤트
                var _this = $(this),
                    number = _this.siblings('.number').text(),
                    number2 = _this.parents('li').attr('class').replace(/[^0-9]/g, ""),
                    request_value = {
                        content_id: number
                    },
                    url = '/api/Timeline/timeline_delete';

                console.log(number2);
                console.log(request_value);
                _Time.timeLine_ajax(url, request_value, _this);
            }).on('click', '#userInfo .userImg', function () { // 타임라인 프로필이미지 클릭 이벤트
                var _this = $(this),
                    imgUrl = _this.find('img').attr('src'),
                    idUrl = _this.siblings('.userId').text(),
                    select_offset = _this.offset(),
                    position_top = select_offset.top,
                    position_left = (select_offset.left) + 60,
                    popupProfile_length = $('#popupProfile').length,
                    popupReport_length = $('#popupReport').length;

                console.log(imgUrl);
                console.log(popupProfile_length);
                if (popupProfile_length == 0 && popupReport_length == 0) {
                    _Time.profile_html(imgUrl, idUrl);
                    var popupProfile = $('#popupProfile');
                    popupProfile.css({
                        'top': position_top,
                        'left': position_left
                    });
                } else if (popupProfile_length == 0 && popupReport_length == 1) {
                    var popupReport = $('#popupReport');

                    popupReport.remove();
                    _Time.profile_html(imgUrl, idUrl);
                    var popupProfile = $('#popupProfile');
                    popupProfile.css({
                        'top': position_top,
                        'left': position_left
                    });
                } else if (popupProfile_length == 1 && popupReport_length == 0) {
                    var popupProfile = $('#popupProfile');

                    popupProfile.remove();
                    _Time.profile_html(imgUrl, idUrl);
                    var popupProfile = $('#popupProfile');
                    popupProfile.css({
                        'top': position_top,
                        'left': position_left
                    });
                }
            }).on('click', '#userInfo .userReport', function () { // 타임라인 신고버튼 클릭 이벤트
                var _this = $(this),
                    select_offset = _this.offset(),
                    position_top = select_offset.top,
                    position_left = (select_offset.left) - 400,
                    popupProfile_length = $('#popupProfile').length,
                    popupReport_length = $('#popupReport').length;

                if (popupReport_length == 0 && popupProfile_length == 0) {
                    _Time.report_html();
                    var popupReport = $('#popupReport');
                    popupReport.css({
                        'top': position_top,
                        'left': position_left
                    });
                } else if (popupReport_length == 0 && popupProfile_length == 1) {
                    var popupProfile = $('#popupProfile');

                    popupProfile.remove();
                    _Time.report_html();
                    var popupReport = $('#popupReport');
                    popupReport.css({
                        'top': position_top,
                        'left': position_left
                    });
                } else if (popupReport_length == 1 && popupProfile_length == 0) {
                    var popupReport = $('#popupReport');

                    popupReport.remove();
                    _Time.report_html();
                    var popupReport = $('#popupReport');
                    popupReport.css({
                        'top': position_top,
                        'left': position_left
                    });
                }
            });
            DOCUMENT.on('click', '#popupProfile .profileInfo .profileTitle div', function () { //프로필 팝업 닫기 번큰 이벤트
                var _this = $(this);

                _this.parents('#popupProfile').remove();
            }).on('click', '#report .buttonContainer input[type=button]', function () { //신고하기 팝업 취소 이벤트
                var _this = $(this);

                _this.parents('#popupReport').remove();
            }).on('click', '#popupProfile div .profileEvent div a', function () { //대화걸기
                //var win = window.open("", "_blank", "toolbar=no,scrollbars=yes,resizable=no,top=500,left=500,width=400,height=400")
                var url = 'https://ntalk.me/main/chat/',
                    name = '대화방',
                    options = 'location=no, scrollbars=yes, resizable=no, top=500, left=500, width=350px, height=600px',
                    openDialog = function (url, name, options, closeCallback) {
                        var win = window.open(url, name, options),
                            interval = window.setInterval(function () {
                                try {
                                    if (win == null || win.closed) {
                                        window.clearInterval(interval);
                                        closeCallback(win);
                                    }
                                } catch (e) {

                                }
                            }, 1000);
                        return win;

                    };

                $('#popupProfile').remove();
                openDialog(url, name, options, function (win) {
                    location.reload();
                });
            }).on('click', '#menuTap .writeTimeLine input[type=button]', function () {
                var _this = $(this),
                    textarea = $('#menuTap .writeTimeLine textarea'),
                    textarea_val = textarea.val(),
                    request_value = {
                        comment: textarea_val
                    },
                    url = '/api/Timeline/timeline_insert';

                console.log(request_value);
                _Time.timeLine_ajax(url, request_value, _this);
                textarea.val('');
            });
        })();
    })(); // 타임라인 글등록 / 수정 / 삭제 / 프로필 / 신고하기 대화하기이벤트
})(); // 타임라인
(function () {
    _Main = (function () {
        return {
            user_list: function () {

            }
        };
    })();
    (function () {
        console.log("tj;tt");
        //_Main.user_info();

    })();
})();