/* global io */

var _chat = null,
    _ChatAjax = null,
    loginUserInfo = null,
    selectUserNickname = null,
    myNickname = null;
(function () {
    _ChatAjax = (function () {
        return {
            user_info: function () {
                $.ajax({
                    timeout: 3000,
                    type: "POST",
                    url: "/api/oauth/logindata",
                    dataType: "json",
                    success: function (data) {
                        console.log(data);
                        var user_info = data.message;
                        console.log(user_info);
                        console.log(user_info.nickname);
                        myNickname = user_info.nickname;
                        $('#myNick').val(myNickname);
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    }
                });
            }
        };
    })();
    (function () {
        _ChatAjax.user_info();
    })();
})(); // 로그인 사용자 정보 
(function () {
    _chat = (function () {
        return {
            infoReceive: function (res) {
                // TODO : ZLIB 압축해제
                var json_data = JSON.parse(res);
                console.log(json_data);
                console.log(json_data.cmd);
                switch (json_data.cmd) {
                    case 100: // 최초 접속시 (REGISTER 이후) 사용자에게 제공되는 메세지
                        break;
                    case 200: // 채팅 메세지
                        _chat.message(json_data);
                        break;
                    case 201: // 채팅 타이핑 중을 알림
                        break;
                    case 202: // 채팅 타이핑 종료를 알림
                        break;
                    case 203: // 채팅 방이 생성되었음
                        break;
                    case 204: // 사용자가 채팅방에서 나갔음
                        break;
                    case 205: // 사용자가 채팅방내 대화메세지를 읽었음
                        break;
                    case 206: // 채팅방 화면을 퇴장함
                        break;
                    case 207: // 채팅방 화면을 입장함
                        break;
                    case 208: // 채팅대화신고
                        break;
                    case 209: // 채팅방 히스토리 요청
                        break;
                    case 210: // 참여중인 채팅방 리스트
                        break;
                    case 211: // 채팅방 ID 찾기
                        var target = json_data.data.target,
                            roomResult = json_data.data.result;
                        var _server = 'wss://ntalk.me:8443/Lounge';
                        var _socket = io(_server, {
                            transports: ['websocket']
                        })
                        if (roomResult == '') {
                            _chat.sendEmit(_socket, _chat.submit_request(212, {
                                nickName: myNickname,
                                oppNickName: target
                            }));
                        } else {
                            _chat.sendEmit(_socket, _chat.submit_request(207, {
                                roomId: roomResult,
                                nickName: myNickname,
                                oppNickName: target
                            }));
                        }

                        $('#userNick').val(target);
                        console.log($('#userNick').val(target));
                        break;
                    case 212: // (Web 전용 커멘드)웹 에서 채팅 ROOM_ID 가 없이 Socket이 추가 접속되었음을 서버로 알림
                        // (Guest 상태의 Redis 접속정보에서 User 로 Upgrade, /READY Socket 상태로 대기)
                        // Mobile 에서는 단일 소켓으로 모두 처리 가능하나 웹에서는 창단위로 Socket이 생성되므로
                        // Socket 접속 정보의 Upgrade 과정이 필요함
                        break;
                    case 300: // 사용자 리스트
                        loginUserInfo = json_data.data;
                        console.log(loginUserInfo);
                        var html = '',
                            object = loginUserInfo;
                        for (var variable in object) {
                            if (object.hasOwnProperty(variable)) {
                                var data = object[variable],
                                    gameCount = parseInt(variable) + 1,
                                    listCount = $('#userList').find('.listContent').children().length,
                                    userNickname = data.nickname,
                                    number = data.idx;

                                if (listCount == 0) {
                                    gameCount = gameCount;
                                } else {
                                    number = parseInt($('#userList').find('.listContent').children().last().attr('class').replace(/[^0-9]/g, ""));
                                    gameCount = number + gameCount;
                                }
                                console.log(data);
                                html += '<li class="number ' + gameCount + '">';
                                html += '<div id="userInfo">';
                                html += '<div class="userImg">';
                                html += '<img src="https://image.flaticon.com/icons/png/512/44/44562.png">';
                                html += '</div>';
                                html += '<div class="number nonDisplay">' + number + '</div>';
                                html += '<div class="userId" title="아이디">' + userNickname + '</div>';
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
                                html += '</li>';
                            }
                        }
                        $('#userList').find('.listContent').append(html);
                        $('#userInfo')
                        break;
                    case 400: // 쪽지 메세지
                        break;
                    case 500: // 타임라인 등록 알림 데이터
                        break;
                    case 601: // 사용자 접속 알림
                        // alert("로그인");
                        break;
                    case 602: // 사용자 접속 해제 알림
                        //alert("사용자가 로그아웃 했습니다.");
                        break;
                    case 603: // 사용자 정보가 변경되었음
                        break;
                    case 1000: // 다른장비에서 사용자가 접속하였음
                        break;
                    default:
                }
            },
            sendEmit: function (socket, jsonObject) {
                var message = JSON.stringify(jsonObject, null, 4);
                console.log("=>[EMIT]" + message)
                // TODO : Zlib 압축
                socket.emit('SUBMIT', message);
            },
            submit_request: function (cmd_val, data_val) {
                var object_val = {
                    cmd: cmd_val,
                    data: data_val
                }
                return object_val;
            },
            message: function (res) {
                //메세지처리 함


                var data = res.data.data,
                    roomId = res.data.roomId,
                    content = data.text,
                    insertRoomId = $('#roomId').val(),
                    content_length = $('#messages').length,
                    msg_box_last = $('#messages').children().last().text(),
                    divisionNick = $('#myNick').val(),
                    recieveNick = res.data.from,
                    send_at = res.data.send_at,
                    recieveSendAt = send_at.substr(11, 8);
                console.log(res);
                console.log(data);
                console.log(content);
                console.log(roomId);
                console.log(msg_box_last);
                if (insertRoomId != roomId) {
                    $('#roomId').val(roomId);
                }
                if (divisionNick == recieveNick) {
                    if (content_length == 0 || msg_box_last != content) {
                        $('#messages').append('<li class="me"><div class="content">' + content + '</div></li>');
                        $('#input').val('');
                        console.log($('#roomId').val());
                    }
                } else {
                    var html = '';
                    html = '<li class="partner">';
                    html += '<div id="partnerContainer">';
                    html += '<div class="partnerImg"><img src="https://image.flaticon.com/icons/png/512/44/44562.png"></div>';
                    html += '<div id="partnercontent">';
                    html += '<div class="partnerNick">' + recieveNick + '</div>';
                    html += '<div class="partnerContent">' + content + '</div>';
                    html += '<div class="sendTime">' + recieveSendAt + '</div>';
                    html += '</div></div>';
                    html += '</li>';
                    //$('#messages').prepend('<li class="partner">' + content + '</li>');
                    $('#messages').append(html);
                    $('#input').val('');
                }
                $('#messages').scrollTop($('#messages').prop('scrollHeight'));
            }
        };
    })();
})();
$(function () {
    var _server = 'wss://ntalk.me:8443/Lounge';
    var _socket = io(_server, {
        transports: ['websocket']
    }).on('connect', function () {
        console.log('connect');
        console.log(_socket.connected);
        _socket.emit('hello', 'world2');
    }).on('RECEIVE', function (res) { // 사용자 파이프
        _chat.infoReceive(res);
    }).on('REGISTER', function (response) {
        console.log(response);
    }).on('disconnect', function () {
        console.log('you have been disconnected');
    }).on('reconnect', function () {
        console.log('you have been reconnected');
    }).on('reconnect_error', function () {
        console.log('attempt to reconnect has failed');
    });
    _chat.sendEmit(_socket, _chat.submit_request(300, {
        gender: "all"
    }));
    $(document).on('click', '#popupProfile div .profileEvent div a', function () {
        _chat.sendEmit(_socket, _chat.submit_request(300, {
            gender: "all"
        }));
        console.log(loginUserInfo);
        var cmd_val = 211,
            _this = $(this),
            selectUserId = _this.parents('.profileEvent').siblings('.profileInfo').children('.profileId').text(),
            //loginUserInfo_length = loginUserInfo.length,
            selectUserImg = _this.parents('.profileEvent').siblings('.profileInfo').children('.profileImg').children('img').attr('src');
        console.log(selectUserId);
        console.log(myNickname);
        console.log(selectUserImg);
        /*for (var i = 0; i < loginUserInfo.length; i++) {
            var userInfo = loginUserInfo.data;
            if (userInfo[i].id == selectUserId) {
                selectUserNickname = userInfo[i].nickname;
            }
        }*/
        console.log(selectUserNickname);
        _chat.sendEmit(_socket, _chat.submit_request(cmd_val, {
            user1: myNickname,
            user2: selectUserId,
            profile: selectUserImg
        }));
    });
    $('#chatContainer').on('click', '#btn', function () {
        //alert("dfdfd");

        var coment = $('#chatContainer #input').val(),
            /*targetNick = $('#userNick').val(),*/
            targetNick = $(opener.document).find('#userNick').val(),
            date = new Date(),
            year = date.getFullYear(),
            month = date.getMonth(),
            day = date.getDate(),
            hours = date.getHours(),
            minutes = date.getMinutes(),
            seconds = date.getSeconds(),
            currentTime = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds,
            Encryption_time = $.md5(currentTime);
        console.log(date);
        console.log(year);
        console.log(currentTime);
        console.log(Encryption_time);
        console.log($('#userNick').val());
        console.log(targetNick);

        console.log(coment);
        // _socket.emit('SUBMIT', $('#input').val());
        _chat.sendEmit(_socket, _chat.submit_request(200, {
            from: myNickname,
            to: targetNick,
            roomId: "",
            requestId: Encryption_time,
            data: {
                type: "text",
                text: coment,
                image: "",
                optional: ""
            }
        }));
    });
});

function sendEmit(socket, jsonObject) {
    var message = JSON.stringify(jsonObject, null, 4);
    console.log("=>[EMIT]" + message)
    // TODO : Zlib 압축
    socket.emit('SUBMIT', message);
}