// 채팅 커멘드 정의 (JSON { cmd : xxx })
var WELCOME = 100; // 최초 접속시 (REGISTER 이후) 사용자에게 제공되는 메세지
let CHAT_PUBLISH = 200; // 채팅 메세지
let CHAT_ROOM_TYPING_START = 201; // 채팅 타이핑 중을 알림
let CHAT_ROOM_TYPING_END = 202; // 채팅 타이핑 종료를 알림
let CHAT_ROOM_CREATED = 203; // 채팅 방이 생성되었음
let CHAT_ROOM_LEAVED = 204; // 사용자가 채팅방에서 나갔음
let CHAT_ROOM_READED = 205; // 사용자가 채팅방내 대화메세지를 읽었음
let CHAT_ROOM_EXIT = 206; // 채팅방 화면을 퇴장함
let CHAT_ROOM_JOIN = 207; // 채팅방 화면을 입장함
let CHAT_HISTORY = 209; // 채팅방 히스토리 요청
let CHAT_LIST = 210; // 참여중인 채팅방 리스트
let CHAT_FIND_ROOM = 211; // 채팅방 ID 찾기
// (Web 전용 커멘드)웹 에서 채팅 ROOM_ID 가 없이 Socket이 추가 접속되었음을 서버로 알림
// (Guest 상태의 Redis 접속정보에서 User 로 Upgrade, /READY Socket 상태로 대기)
// Mobile 에서는 단일 소켓으로 모두 처리 가능하나 웹에서는 창단위로 Socket이 생성되므로
// Socket 접속 정보의 Upgrade 과정이 필요함
let CHAT_READY = 212;
let USERLIST = 300; // 사용자 리스트
let CHANGED_PROFILE = 401; // 채팅 내 사용자 프로필 이미지 경로 요청
let CHANGED_NICKNAME = 402; // 닉네임이 변경되었음
let TIMELINE_REGISTERED = 500; // 타임라인 등록 알림 데이터
let USER_JOIN = 601; // 사용자 접속 알림
let USER_LEAVE = 602; // 사용자 접속 해제 알림
let GET_PROFILE = 700; // 해당 사용자 닉네임에 대한 프로필 이미지 경로를 요청 (Optional)
let ANOTHER_CONNECTED = 1000; // 다른장비에서 사용자가 접속하였음

var _Io = null,
    _Fx = null,
    _Fn = null,
    _Talk = null,
    loginUserInfo = null,
    selectUserNickname = null,
    myNickname = null,
    login_nick = null,
    DOCUMENT = $(document),
    TIMELIST = $("#timeLineContainer"),
    _socketChat = null,
    _serverChat = null,
    _socket = null,
    _server = null,
    timer = null,
    typing = false,
    pop = null,
    locationData = null,
    timeline_data = null,
    userlist_gender = null,
    idOverlap = null,
    nickOverlap = null,
    male_user = null,
    all_user = [
        []
    ],
    date = new Date(),
    year = date.getFullYear(),
    month1 = new String(date.getMonth() + 1),
    month = month1 >= 10 ? month1 : '0' + month1,
    day1 = new String(date.getDate()),
    day = day1 >= 10 ? day1 : '0' + day1,
    hours = date.getHours(),
    minutes = date.getMinutes(),
    seconds = date.getSeconds(),
    currentTime = year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds,
    currentday = year + "-" + month + "-" + day,
    Encryption_time = null,
    order_plus = 0;

(function () {
    _Fn = (function () {
        //공통 함수
        return {
            isEmpty: function (value) {
                if (value == "" || value == null || value == undefined || (value != null && typeof value == "object" && !Object.keys(value).length)) {
                    return true;
                } else {
                    return false;
                }
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
            dateDiff: function (join, curr) {
                var arrDate1 = join.split("-");
                var getDate1 = new Date(parseInt(arrDate1[0]), parseInt(arrDate1[1]) - 1, parseInt(arrDate1[2]));
                var arrDate2 = curr.split("-");
                var getDate2 = new Date(parseInt(arrDate2[0]), parseInt(arrDate2[1]) - 1, parseInt(arrDate2[2]));

                var getDiffTime = getDate1.getTime() - getDate2.getTime();

                return Math.floor(getDiffTime / (1000 * 60 * 60 * 24));
            },
            ajax_post: function (url, data) {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data
                });
            }
        };
    })();
    _Fx = (function () {
        // 이벤트 함수
        return {
            timeLineList: function (_this_text, position_top, position_left, data_value) {
                var url_adr = "?page=" + _this_text,
                    adr = "";
                if (_this_text != null) {
                    adr = "/Api/timeline/timeline_lists" + url_adr;
                } else {
                    adr = "/Api/timeline/timeline_lists";
                }
                console.log(data_value);
                if (data_value == undefined) {
                    data_value = {
                        location1: null,
                        location2: null,
                        minAge: null,
                        maxAge: null,
                        gender: null
                    };
                }
                $.ajax({
                    timeout: 3000,
                    type: "POST",
                    url: adr,
                    dataType: "json",
                    data: {
                        location1: data_value.location1,
                        location2: data_value.location2,
                        minAge: data_value.minAge,
                        maxAge: data_value.maxAge,
                        gender: data_value.gender
                    },
                    success: function (data) {
                        console.log(_this_text);
                        console.log(data);
                        var dataContent = data.data,
                            currentPage = dataContent.currentPage,
                            nextPage = dataContent.nextPage,
                            lastPage = dataContent.lastPage,
                            dataList = dataContent.post,
                            dataMessage = data.message,
                            lodingH = position_top, //크기 구해서 중간에 나나택내기
                            lodingW = position_left;
                        console.log(lodingH);
                        console.log(lodingW);
                        console.log($(document).scrollTop());
                        TIMELIST.find(".bubblingG").css({
                            left: position_left,
                            top: position_top
                        });
                        TIMELIST.find(".bubblingG").show();

                        _Fx.list_html(dataContent, currentPage, nextPage, lastPage, dataList, _this_text);

                        $(window).data("ajaxready", true);
                        console.log(dataMessage);
                        if (dataMessage == "마지막 페이지 입니다.") {
                            TIMELIST.find("#plusView").addClass("nonDisplay");
                        } else {
                            TIMELIST.find("#plusView").removeClass("nonDisplay");
                        }
                        setTimeout(function () {
                            TIMELIST.find(".bubblingG").hide();
                        }, 1000);
                        _Fx.autoHeight();
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    }
                });
            },
            list_html: function (dataContent, currentPage, nextPage, lastPage, dataList, _this_text) {
                var html = "",
                    object = dataList;
                for (var variable in object) {
                    if (object.hasOwnProperty(variable)) {
                        console.log(lastPage);
                        var data = object[variable],
                            gameCount = parseInt(variable) + 1,
                            listCount = TIMELIST.find(".timeLineContent").children().length,
                            userNickname = data.nickname,
                            userComment = data.comment,
                            userTitle = data.title,
                            number = null,
                            hash = data.hash,
                            userUid = data.uid,
                            userUid2 = userUid.replace(/\@/g, ""),
                            userUid3 = userUid2.replace(/\./g, ""),
                            location1 = data.location,
                            location2 = data.location2,
                            flocation1 = data.flocation,
                            flocation2 = data.flocation2,
                            userAge = "(" + data.age + ")",
                            userFgender = data.fgender,
                            userGender = data.gender,
                            userMaxAge = data.maxAge,
                            userMinAge = data.minAge,
                            createAt = data.create_at,
                            userFiles = data.files,
                            timeago_createAt = "#" + $.timeago(createAt),
                            joinDay = data.user_create_at.substring(0, 10),
                            joinAt = _Fn.dateDiff(currentday, joinDay),
                            viewNumber = data.view_count;

                        console.log(location1);
                        console.log(joinDay);
                        console.log(currentday);
                        console.log(joinAt);
                        console.log(userUid2);
                        console.log(userUid3);
                        console.log($.timeago(createAt));
                        console.log(data);
                        console.log(hash);
                        console.log(userAge);
                        if (userComment.indexOf("\n") != -1) {
                            userComment = userComment.replace(/(?:\r\n|\r|\n)/g, "<br>");
                        }

                        if (location1 == null || location1 == "" || location1 == undefined) {
                            location1 = "";
                        } else {
                            location1 = "#" + data.location;
                        }

                        if (location2 == null || location2 == "" || location2 == undefined) {
                            location2 = "";
                        } else {
                            location2 = "#" + data.location2;
                        }

                        if (flocation1 == null || flocation1 == "" || flocation1 == undefined) {
                            flocation1 = "";
                        } else {
                            flocation1 = "#" + data.flocation;
                        }

                        if (flocation2 == null || flocation2 == "" || flocation2 == undefined) {
                            flocation2 = "";
                        } else {
                            flocation2 = "#" + data.flocation2;
                        }

                        if (userGender == "male") {
                            userGender = "남";
                        } else {
                            userGender = "여";
                        }

                        if (listCount == 0) {
                            gameCount = gameCount;
                        } else {
                            number = parseInt(
                                TIMELIST.find(".timeLineContent")
                                .children()
                                .last()
                                .attr("class")
                                .replace(/[^0-9]/g, "")
                            );
                            gameCount = number + gameCount;
                        }

                        if (userAge == "(-1)") {
                            userAge = "";
                        }

                        html += '<li class="number ' + gameCount + '">';
                        html += '<div class="main_info">';
                        html += '<div id="userInfo">';
                        html += '<div class="connectUser nonDisplay"><p>ON</p></div>';
                        if (joinAt >= 10) {
                            html += '<div class="newUser nonDisplay"><p>NEW</p></div>';
                        } else {
                            html += '<div class="newUser"><p>NEW</p></div>';
                        }
                        html += '<div class="hotUser nonDisplay"><p>HOT</p></div>';
                        if (userGender == "여") {
                            html += '<div class="userId woman" title="닉네임">' + userNickname + " <span class='woman'>" + userGender + "</span><span class='woman'>" + userAge + "</span></div>";
                        } else {
                            html += '<div class="userId man" title="닉네임">' + userNickname + " <span class='man'>" + userGender + "</span><span class='man'>" + userAge + "</span></div>";
                        }
                        html += "</div>";
                        html += '<div class="contentContianer">';
                        html += '<div class="contentTitle">';
                        html += "<span>" + userTitle + "</span>";
                        html += "</div>";
                        html += '<div class="commentContent">';
                        html += "<span>" + userComment + "</span>";
                        html += "</div>";
                        html += "</div>";
                        html += '<div class="locationInfo">';
                        if (location1 == "") {
                            html += '<div class="firstLocation nonDisplay">' + location1 + "</div>";
                        } else {
                            html += '<div class="firstLocation">' + location1 + "</div>";
                        }
                        if (location2 == "") {
                            html += '<div class="secondLocation nonDisplay">' + location2 + "</div>";
                        } else {
                            html += '<div class="secondLocation">' + location2 + "</div>";
                        }
                        html += '<div class="afterCreate">' + timeago_createAt + "</div>";
                        html += "</div>";
                        html += "</div>";
                        html += '<div class="ImgContainer">';
                        if (userFiles != null || userFiles != undefined || userFiles != "") {
                            for (var i = 0; i < userFiles.length; i++) {
                                var one = "one",
                                    two = "two",
                                    three = "three",
                                    four = "four",
                                    five = "five",
                                    imgNum = [one, two, three, four, five];
                                html += '<img class="' + imgNum[i] + 'Img" src="' + userFiles[i].thumb + '">';
                            }
                        }
                        html += "</div>";
                        html += '<div id="iconContainer">';
                        html += '<div class="view">';
                        html += "<span></span>";
                        html += '<div class="viewNumber nonDisplay">' + viewNumber + "</div>";
                        html += "</div>";
                        html += '<div class="bookmark"><span></span></div>';
                        html += '<div class="talking"><span></span></div>';
                        html += "</div>";
                        html += '<input type="hidden" name="hash" id="hash" value="' + hash + '">';
                        html += '<input type="hidden" name="nick2" id="userNickname" value="' + userNickname + '">';
                        html += '<input type="hidden" name="uid" id="' + userUid3 + '" value = "' + userUid3 + '">';
                        html += '<input type="hidden" name="fgender" id="fgender" value="' + userFgender + '">';
                        html += '<input type="hidden" name="maxAge" id="maxAge" value="' + userMaxAge + '">';
                        html += '<input type="hidden" name="minAge" id="minAge" value="' + userMinAge + '">';
                        html += '<input type="hidden" name="flocation" id="flocation" value="' + flocation1 + '">';
                        html += '<input type="hidden" name="flocation2" id="flocation2" value="' + flocation2 + '">';
                        html += "</li>";
                    }
                }
                TIMELIST.find(".timeLineContent").append(html);
            },
            receiveTimelie_del: function (res) {
                var data = res.data,
                    nick = data.nickname;
                /* userUid = data.uid,
                    userUid2 = userUid.replace(/\@/g, ""),
                    userUid3 = userUid2.replace(/\./g, ""); */
                console.log(nick);

                var preTimeline = $("#userNickname").val();
                console.log(preTimeline);
                if (preTimeline == nick) {
                    $("#userNickname")
                        .parents("li")
                        .remove();
                }
                _Fx.autoHeight();
            },
            receiveTimeLine: function (res) {
                var html = "",
                    data = res.data,
                    gameCount = 1,
                    userNickname = data.nickname,
                    userGender = data.gender,
                    userAge = data.age,
                    userTitle = data.title,
                    userComment = data.comment,
                    location1 = data.location,
                    location2 = data.location2,
                    flocation1 = "#" + data.flocation,
                    flocation2 = "#" + data.flocation2,
                    hash = data.hash,
                    userUid = data.uid,
                    userUid2 = userUid.replace(/\@/g, ""),
                    userUid3 = userUid2.replace(/\./g, ""),
                    userFgender = data.fgender,
                    userMaxAge = data.maxAge,
                    userMinAge = data.minAge,
                    createAt = data.create_at,
                    userFiles = data.files,
                    timeago_createAt = "#" + $.timeago(createAt),
                    _joinDay = data.user_create_at,
                    joinDay = _joinDay.substring(0, 10),
                    joinAt = _Fn.dateDiff(currentday, joinDay),
                    viewNumber = "12";
                if (userComment.indexOf("\n") != -1) {
                    userComment = userComment.replace(/(?:\r\n|\r|\n)/g, "<br>");
                }
                if (location1 != null || location1 == undefined || location1 == "") {
                    location1 = "#" + location1;
                }
                if (location2 != null || location2 == undefined || location2 == "") {
                    location2 = "#" + location2;
                }
                if (userGender == "female") {
                    userGender = "여";
                } else {
                    userGender = "남";
                }
                console.log(userUid2);
                console.log(userUid3);
                console.log(data);
                console.log(hash);
                html += '<li class="number ' + gameCount + '">';
                html += '<div class="main_info">';
                html += '<div id="userInfo">';
                html += '<div class="connectUser"><p>ON</p></div>';
                if (joinAt >= 10) {
                    html += '<div class="newUser nonDisplay"><p>NEW</p></div>';
                } else {
                    html += '<div class="newUser"><p>NEW</p></div>';
                }
                html += '<div class="hotUser nonDisplay"><p>HOT</p></div>';
                if (userGender == "여") {
                    html += '<div class="userId woman" title="닉네임">' + userNickname + " <span>" + userGender + "</span><span>(" + userAge + ")</span></div>";
                } else {
                    html += '<div class="userId man" title="닉네임">' + userNickname + " <span>" + userGender + "</span><span>(" + userAge + ")</span></div>";
                }
                html += "</div>";
                html += '<div class="contentContianer">';
                html += '<div class="contentTitle">';
                html += "<span>" + userTitle + "</span>";
                html += "</div>";
                html += '<div class="commentContent">';
                html += "<span>" + userComment + "</span>";
                html += "</div>";
                html += "</div>";
                html += '<div class="locationInfo">';
                html += '<div class="firstLocation">' + location1 + "</div>";
                html += '<div class="secondLocation">' + location2 + "</div>";
                html += '<div class="afterCreate">' + timeago_createAt + "</div>";
                html += "</div>";
                html += "</div>";
                html += '<div class="ImgContainer">';
                if (userFiles != null || userFiles != undefined || userFiles != "") {
                    for (var i = 0; i < userFiles.length; i++) {
                        var one = "one",
                            two = "two",
                            three = "three",
                            four = "four",
                            five = "five",
                            imgNum = [one, two, three, four, five];
                        html += '<img class="' + imgNum[i] + 'Img" src="' + userFiles[i].thumb + '">';
                    }
                }
                html += "</div>";
                html += '<div id="iconContainer">';
                html += '<div class="view"><span></span><div class="viewNumber nonDisplay">' + viewNumber + "</div></div>";
                html += '<div class="bookmark"><span></span></div>';
                html += '<div class="talking"><span></span></div>';
                html += "</div>";
                html += '<input type="hidden" name="hash" id="hash" value="' + hash + '">';
                html += '<input type="hidden" name="nick2" id="userNickname" value="' + userNickname + '">';
                html += '<input type="hidden" name="uid" id="' + userUid3 + '" value="' + userUid3 + '">';
                html += '<input type="hidden" name="fgender" id="fgender" value="' + userFgender + '">';
                html += '<input type="hidden" name="maxAge" id="maxAge" value="' + userMaxAge + '">';
                html += '<input type="hidden" name="minAge" id="minAge" value="' + userMinAge + '">';
                html += '<input type="hidden" name="flocation" id="flocation" value="' + flocation1 + '">';
                html += '<input type="hidden" name="flocation2" id="flocation2" value="' + flocation2 + '">';
                html += "</li>";

                var preTimeline = $("#" + userUid3).val();
                console.log(preTimeline);
                if (preTimeline == userUid3) {
                    $("#" + userUid3)
                        .parents("li")
                        .remove();
                }

                TIMELIST.find(".timeLineContent").prepend(html);

                for (var i = 0; i < TIMELIST.find(".timeLineContent").children("li").length; i++) {
                    TIMELIST.find(".timeLineContent")
                        .children("li")
                        .eq(i)
                        .attr("class", "number " + (i + 1));
                }
                _Fx.autoHeight();
            },
            receiveFilter: function (res) {
                console.log(res);
                var data = res.data,
                    result = null,
                    filterLocation = $("#first")
                    .find("option:selected")
                    .val(),
                    filterSecondLocation = $("#second")
                    .find("option:selected")
                    .val(),
                    filterMinAge = $("#timeLineContainer")
                    .find(".tab .timeFilter .beforeAge select option:selected")
                    .val(),
                    filterMaxAge = $("#timeLineContainer")
                    .find(".tab .timeFilter .afterAge select option:selected")
                    .val(),
                    filterGender = $("#timeLineContainer")
                    .find(".tab .timeFilter .gender select option:selected")
                    .val(),
                    resLocation = data.location,
                    resSecondLocation = data.location2,
                    resGender = data.gender,
                    resAge = data.age,
                    matchedLocation1 = false,
                    matchedLocation2 = false,
                    matchedGender = false,
                    matchedAge = false;
                if (filterLocation == resLocation || _Fn.isEmpty(filterLocation) || _Fn.isEmpty(resLocation)) {
                    matchedLocation1 = filterLocation == resLocation;
                    console.log("지역 1 일치");
                    if (filterSecondLocation == resSecondLocation || _Fn.isEmpty(filterSecondLocation) || _Fn.isEmpty(resSecondLocation)) {
                        matchedLocation2 = filterSecondLocation == resSecondLocation;
                        console.log("지역 2 일치");
                        if (filterGender == resGender || _Fn.isEmpty(filterGender) || resGender == "all") {
                            matchedGender = filterGender == resGender;
                            console.log("성별 일치");
                            if ((resAge >= filterMinAge && resAge <= filterMaxAge) || _Fn.isEmpty(resAge)) {
                                matchedAge = resAge != "" && resAge >= filterMinAge && resAge <= filterMaxAge;

                                result = JSON.parse(JSON.stringify(res));
                                result.data.matched = (matchedLocation1 || matchedLocation2) && matchedGender && matchedAge;
                                console.log("나이 일치");
                            }
                        }
                    }
                }

                console.log(result);
                return result;
            },
            selectOptionHtml: function (minAdr, maxAdr, minAge, maxAge) {
                var htmlA = "",
                    htmlB = "";
                console.log(minAge);
                console.log(maxAge);
                for (var i = 19; i < 61; i++) {
                    if (minAge != i) {
                        htmlA += '<option value="' + i + '">' + i + "</option>";
                    } else {
                        htmlA += '<option value="' + i + '" selected="selected">' + i + "</option>";
                    }
                }
                minAdr.append(htmlA);
                for (var n = 19; n < 61; n++) {
                    if (maxAge != n) {
                        htmlB += '<option value="' + n + '">' + n + "</option>";
                    } else {
                        htmlB += '<option value="' + n + '" selected="selected">' + n + "</option>";
                    }
                }
                maxAdr.append(htmlB);
                DOCUMENT.find("#timeLineAdd > div.ageTag > div.beforeAge > select").change(function () {
                    $("#timeLineAdd > div.ageTag > div.beforeAge > select")
                        .find("option:selected")
                        .attr("selected", "selected");
                    $("#timeLineAdd > div.ageTag > div.beforeAge > select")
                        .find("option:selected")
                        .siblings()
                        .attr("selected", false);
                });
                DOCUMENT.find("#timeLineAdd > div.ageTag > div.afterAge > select").change(function () {
                    var beforeAge = $("#timeLineAdd > div.ageTag > div.beforeAge > select")
                        .find("option:selected")
                        .val(),
                        afterAge = $("#timeLineAdd > div.ageTag > div.afterAge > select")
                        .find("option:selected")
                        .val();
                    if (beforeAge >= afterAge) {
                        alert("연령을 확인해 주세요.");
                        $("#timeLineAdd > div.ageTag > div.afterAge > select")
                            .find("option")
                            .attr("selected", false);
                        $("#timeLineAdd > div.ageTag > div.afterAge > select")
                            .find("option")
                            .first()
                            .attr("selected", "selected");
                    } else {
                        $("#timeLineAdd > div.ageTag > div.afterAge > select")
                            .find("option:selected")
                            .attr("selected", "selected");
                        $("#timeLineAdd > div.ageTag > div.afterAge > select")
                            .find("option:selected")
                            .siblings()
                            .attr("selected", false);
                    }
                });
            },
            timelineLocation_json: function (adr, append_adr, text, text2, location1, location2) {
                $.ajax({
                    timeout: 3000,
                    type: "GET",
                    url: "/json/administrative_region.json",
                    dataType: "json"
                }).done(function (data) {
                    locationData = data.data;
                    console.log(append_adr);
                    append_adr.append(_Fx.timelineLocationHTML(locationData, text, text2, location1));
                    console.log(locationData);
                    console.log("여기까지");
                    console.log(location1);
                    console.log(location2);
                    if (location1 == "전체지역") {
                        location1 = "";
                    }
                    if (location1 == null || location1 == undefined || location1 == "") {
                        adr.on("change", "#first", function () {
                            console.log("아아아아아");
                            adr.find("#first option:selected").attr("selected", "selected");
                            adr.find("#first option:selected")
                                .siblings()
                                .attr("selected", false);
                            if (adr.find("#first option:selected").text() == text) {
                                adr.find("#second .noDel").prop("selected", "selected");
                                adr.find("#second")
                                    .children()
                                    .not(".noDel")
                                    .remove();
                            } else {
                                _Fx.timelineLocationSecond(locationData, adr, location1, location2);
                            }
                        }).on("change", "#second", function () {
                            adr.find("#second option:selected").attr("selected", "selected");
                            adr.find("#second option:selected")
                                .siblings()
                                .attr("selected", false);
                        });
                    } else {
                        _Fx.timelineLocationSecond(locationData, adr, location1, location2);
                    }
                });
            },
            timelineLocationHTML: function (locationData, text, text2, location1) {
                var html = "",
                    locationData_langth = locationData.length;
                if (location1 == null || location1 == undefined || location1 == "") {
                    html += '<div class="firstLocation">';
                    html += '<select id="first" name="location1" title="1차 지역선택">';
                    html += '<option value="" selected="selected">' + text + "</option>";
                    for (var i = 0; i < locationData_langth; i++) {
                        html += "<option value=" + locationData[i].name + ">" + locationData[i].name + "</option>";
                    }
                    html += "</select>";
                    html += "</div>";
                    html += '<div class="secondLocation">';
                    html += '<select id="second" name="location2" title="2차 지역선책">';
                    html += '<option value="" selected="selected" class="noDel">' + text2 + "</option>";
                    html += "</select>";
                    html += "</div>";
                } else {
                    html += '<div class="firstLocation">';
                    html += '<select id="first" name="location1" title="1차 지역선택">';
                    html += '<option value="">' + text + "</option>";
                    for (var n = 0; n < locationData_langth; n++) {
                        if (location1 != locationData[n].name) {
                            html += '<option value="' + locationData[n].name + '">' + locationData[n].name + "</option>";
                        } else {
                            html += '<option value="' + locationData[n].name + '" selected="selected">' + locationData[n].name + "</option>";
                        }
                    }
                    html += "</select>";
                    html += "</div>";
                    html += '<div class="secondLocation">';
                    html += '<select id="second" name="location2" title="2차 지역선책">';
                    html += '<option value="" class="noDel">' + text2 + "</option>";
                    html += "</select>";
                    html += "</div>";
                }

                return html;
            },
            timelineLocationSecond: function (locationData, adr, location1, location2) {
                console.log(locationData);
                var select_value = adr.find("#first option:selected").val(),
                    select_index = adr.find("#first option:selected").index() - 1,
                    second_select = adr.find("#second"),
                    secondData_length = locationData[select_index].childs.length;
                if (location1 == null || location1 == undefined || location1 == "") {
                    second_select.prop("disabled", false);
                    adr.find("#second")
                        .children()
                        .not(".noDel")
                        .remove();
                    for (var i = 0; i < secondData_length; i++) {
                        var html = "";

                        html += "<option value=" + locationData[select_index].childs[i] + ">" + locationData[select_index].childs[i] + "</option>";

                        adr.find("#second").append(html);
                    }
                } else {
                    for (var i = 0; i < secondData_length; i++) {
                        var html = "";
                        if (location2 == locationData[select_index].childs[i]) {
                            html += '<option value="' + locationData[select_index].childs[i] + '" selected="selected">' + locationData[select_index].childs[i] + "</option>";
                        } else {
                            html += '<option value="' + locationData[select_index].childs[i] + '">' + locationData[select_index].childs[i] + "</option>";
                        }

                        adr.find("#second").append(html);
                    }
                }
            },
            timelineWrite_ajax: function (url, _this) {
                $.ajax({
                    type: "POST",
                    url: url,
                    success: function (res) {
                        console.log(res);
                        var data = res.data;

                        timeline_data = data;
                        if (location.href != "https://ntalk.me/auth/mypage") {
                            if (data == null) {
                                var text = "등록하기",
                                    url = "/api/Timeline/timeline_insert";
                                $("#wirtePopupContainer").remove();
                                _Fx.timeline_write_edit_html(text, url);
                                $("#writepopup").css({
                                    width: $(document).width(),
                                    height: $(document).height()
                                });
                                $("#timeWrite").css({
                                    top: "448px",
                                    left: "28%"
                                });
                                var timeline_adr = $("#writepopup"),
                                    timeline_append_adr = timeline_adr.find("#timeLineAdd > div.locationTag"),
                                    timeline_text = "지역",
                                    timeline_text2 = "상세지역";
                                _Fx.timelineLocation_json(timeline_adr, timeline_append_adr, timeline_text, timeline_text2);
                                var minAdr = $("#timeLineAdd").find(".ageTag .beforeAge select"),
                                    maxAdr = $("#timeLineAdd").find(".ageTag .afterAge select");
                                _Fx.selectOptionHtml(minAdr, maxAdr);
                            } else {
                                var position = _this.offset(),
                                    positionT = position.top - 50,
                                    positionL = position.left + 195;
                                _Fx.write_popup();
                                $("#wirtePopupContainer").css({
                                    top: positionT,
                                    left: positionL
                                });
                            }
                        } else {
                            var position = _this.offset(),
                                positionT = position.top - 50,
                                positionL = position.left + 195;
                            _Fx.mypage_write_popup();
                            $("#wirtePopupContainer").css({
                                top: positionT,
                                left: positionL
                            });
                        }

                        return timeline_data;
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    }
                });
            },
            timeline_del_ajax: function (url, ajax_data) {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: ajax_data,
                    success: function (res) {
                        console.log(res);

                        $("#wirtePopupContainer").remove();
                        $("#mytimeline > div.myTime").addClass("nonDisplay");
                        $("#mytimeline > div.nonTime").removeClass("nonDisplay");
                    }
                });
            },
            timeline_write_edit_html: function (text, url, files) {
                var html = "";

                html += '<div id="writepopup">';
                html += '<div id="timeWrite">';
                html += '<div id="titleContainer">';
                html += '<div class="writeTitle"><span>타임라인</span>' + text + "</div>";
                html += '<div class="closeBtn">';
                html += "<span></span>";
                html += "</div>";
                html += "</div>";
                html += '<div id="contentContainer">';
                html += '<form id="timeLineAdd" method="post" action="' + url + '" enctype="multipart/form-data">';
                html += '<div id="contentTitle">';
                html += '<div class="titleText">제목</div>';
                html += '<input type="text" id="conTitle" name="title" autocomplete="off" maxlength="30">';
                html += "</div>";
                html += '<div class="genderTag">';
                html += '<div class="genderTitle">성별</div>';
                html += '<div class="all" value="all">모두</div>';
                html += '<div class="female" value="female">여자</div>';
                html += '<div class="male" value="male">남자</div>';
                html += '<input type="hidden" name="gender">';
                html += "</div>";
                html += '<div class="ageTag">';
                html += '<div class="ageTitle">연령</div>';
                html += '<div class="beforeAge">';
                html += '<select name="minAge">';
                html += '<option value="">선택</option>';
                html += "</select>";
                html += "</div> ~ ";
                html += '<div class="afterAge">';
                html += '<select name="maxAge">';
                html += '<option value="">선택</option>';
                html += "</select>";
                html += "</div>";
                html += "</div>";
                html += '<div class="locationTag">';
                html += '<div class="locationTitle">지역선택</div>';
                html += "</div>";
                html += '<div class="imgTag">';
                html += '<div class="imgTitle">사진등록</div>';
                if (files != null || files != undefined || files == "") {
                    for (var i = 0; i < files.length; i++) {
                        html += '<div class="imgUp ' + (i + 1) + '">';
                        html += '<div class="closeBtn">';
                        html += "<span></span>";
                        html += "</div>";
                        html += '<div class="shadow"></div>';
                        html += '<div class="plusImg nonDisplay"></div>';
                        html += '<input type="file" id="file_img" class="upImg ' + (i + 1) + '" name="img[]">';
                        html += '<input type="hidden" value="' + files[i].f_idx + '">';
                        html += '<div id="preview">';
                        html += '<div style="display: inline-flex; width: 73px;">';
                        html += '<img src="' + files[i].thumb + '" title="' + files[i].thumb + '" width=73 height=73 />';
                        html += "</div></div>";
                        html += "</div>";
                    }
                    html += '<div class="imgUp ' + (files.length + 1) + '">';
                    html += '<div class="closeBtn nonDisplay">';
                    html += "<span></span>";
                    html += "</div>";
                    html += '<div class="shadow nonDisplay"></div>';
                    html += '<div class="plusImg"></div>';
                    html += '<input type="file" id="file_img" class="upImg ' + (files.length + 1) + '" name="img[]">';
                    html += '<div id="preview"></div>';
                    html += "</div>";
                } else {
                    html += '<div class="imgUp 1">';
                    html += '<div class="closeBtn nonDisplay">';
                    html += "<span></span>";
                    html += "</div>";
                    html += '<div class="shadow nonDisplay"></div>';
                    html += '<div class="plusImg"></div>';
                    html += '<input type="file" id="file_img" class="upImg 1" name="img[]">';
                    html += '<div id="preview"></div>';
                    html += "</div>";
                }
                html += "</div>";
                html += '<div id="iconTag">';
                html += '<div class="iconTitle">아이콘 스타일</div>';
                html += '<div class="btnContainer">';
                html += "<div>";
                html += '<input type="radio" id="afternoon" value="낮에만" name="afternoon">';
                html += '<label for="afternoon"></label>';
                html += "<div></div>";
                html += "</div>";
                html += "<div>";
                html += '<input type="radio" id="weekend" value="주말만" name="weekend">';
                html += '<label for="weekend"></label>';
                html += "<div></div>";
                html += "</div>";
                html += "<div>";
                html += '<input type="radio" id="night" value="밤에만" name="night">';
                html += '<label for="night"></label>';
                html += "<div></div>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
                html += '<div class="contentTag">';
                html += '<div class="contentTitle">내용</div>';
                html += '<div class="text_wrap">';
                html += '<textarea type="text" id="time_input" name="comment" class="msg" style="resize: none;" autofocus autocomplete="off" srollbar="yes" wrap="hard"></textarea>';
                html += "</div>";
                html += "</div>";
                html += '<div id="submitContainer">';
                html += '<input type="submit" id="sub" value="등록하기">';
                html += "</div>";
                html += "</form>";
                html += "</div>";
                html += "</div>";
                html += "</div>";

                $("#mainContent").prepend(html);
            },
            checkExtension: function (fileName, fileSize) {
                var regex = new RegExp("(.*?).(exe|sh|zip|alz|gif)$");
                var maxSize = 5242880; //5MB 계산법: 용량 * 1024 * 1024

                if (fileSize >= maxSize) {
                    alert("파일 사이즈 초과");
                    $("#file_img1").val(""); //파일 초기화
                    return false;
                }
                /* var ext = $('#file_img').val().split(".").pop().toLowerCase();

                if ($.inArray(ext, ["jpg", "jpeg", "png", "bmp"]) == -1) {
                    alert("jpg, jpeg, png, bmp 파일만 업로드 해주세요.");
                    $("input[id=file_img]").val("");
                    return;
                } */
                if (regex.test(fileName)) {
                    alert("업로드 불가능한 파일이 있습니다. jpg, jpeg, png, bmp 파일만 업로드 해주세요 ");
                    $("#file_img1").val(""); //파일 초기화
                    return false;
                }
                return true;
            },
            preview: function (arr) {
                arr.forEach(function (f) {
                    //파일명이 길면 파일명...으로 처리
                    var fileName = f.name;
                    var img_length = $("#timeLineAdd > div.imgTag").children(".imgUp").length;
                    if (fileName.length > 10) {
                        fileName = fileName.substring(0, 7) + "...";
                    }

                    //div에 이미지 추가
                    var str = '<div style="display: inline-flex; width: 73px;">';

                    //이미지 파일 미리보기
                    if (f.type.match("image.*")) {
                        var reader = new FileReader(); //파일을 읽기 위한 FileReader객체 생성
                        reader.onload = function (e) {
                            //파일 읽어들이기를 성공했을때 호출되는 이벤트 핸들러
                            //str += '<button type="button" class="delBtn" value="'+f.name+'" style="background: red">x</button><br>';
                            //str += '<div class="colseBtn"><span></span></div>';
                            str += '<img src="' + e.target.result + '" title="' + f.name + '" width=73 height=73 />';
                            str += "</div>";
                            $(str).appendTo("#contentContainer .imgTag .imgUp." + img_length + " #preview");
                        };

                        reader.readAsDataURL(f);
                        console.log(img_length);
                        if (img_length < 5) {
                            var html = "";
                            html += '<div class="imgUp ' + (img_length + 1) + '">';
                            html += '<div class="closeBtn nonDisplay">';
                            html += '<div class="shadow"></div>';
                            html += "<span></span>";
                            html += "</div>";
                            html += '<div class="plusImg"></div>';
                            html += '<input type="file" id="file_img" class="upImg ' + (img_length + 1) + '" name="img[]">';
                            html += '<div id="preview"></div>';
                            html += "</div>";

                            $("#contentContainer")
                                .find(".imgTag")
                                .append(html);
                        }
                    } else {
                        str += '<img src="/resources/img/fileImg.png" title="' + f.name + '" width=73 height=73 />';
                        $(str).appendTo("#preview");
                    }
                    $("#timeLineAdd .imgTag .imgUp." + img_length + " .plusImg").addClass("nonDisplay");
                    $("#timeLineAdd .imgTag .imgUp." + img_length + " .closeBtn").removeClass("nonDisplay");
                    $("#timeLineAdd .imgTag .imgUp." + img_length + " .shadow").removeClass("nonDisplay");
                });
            },
            write_popup: function () {
                var html = "";
                html += '<div id="wirtePopupContainer">';
                html += '<div class="wpopTitle">';
                html += "<div>타임라인 글쓰기</div>";
                html += '<div class="closeBtn"><span></span></div>';
                html += "</div>";
                html += '<div class="wpopContent">';
                html += "<div>기존의 타임라인을 불러와서 수정 하시거나 처음부터 새로 쓰실수 있습니다.</div>";
                html += "</div>";
                html += '<div class="wpopBtn">';
                html += '<div class="wpopLoad">수정하기</div>';
                html += '<div class="wpopNew">새로쓰기</div>';
                html += "</div>";
                html += "</div>";

                $("#mainContent").prepend(html);
            },
            mypage_write_popup: function () {
                var html = "";
                html += '<div id="wirtePopupContainer">';
                html += '<div class="wpopTitle">';
                html += "<div>내 타임라인</div>";
                html += '<div class="closeBtn"><span></span></div>';
                html += "</div>";
                html += '<div class="wpopContent">';
                html += "<div>기존의 타임라인이 존재합니다. 수정하거나 삭제하시겠습니까?</div>";
                html += "</div>";
                html += '<div class="wpopBtn">';
                html += '<div class="wpopLoad">수정하기</div>';
                html += '<div class="wpopDel">삭제하기</div>';
                html += "</div>";
                html += "</div>";

                $("#mainContent").prepend(html);
            },

            profile_html: function (imgUrl, idUrl, adr) {
                var html = "";

                html += '<div id="popupProfile">';
                html += '<div class="profilePopupContainer">';
                html += '<div class="profileInfo">';
                html += '<div class="profileTitle">';
                html += '<div calss="profileClose">';
                html += '<img src="https://image.flaticon.com/icons/svg/61/61155.svg">';
                html += "</div>";
                html += "</div>";
                html += '<div class="profileImg">';
                html += '<img src="' + imgUrl + '">';
                html += "</div>";
                html += '<div class="profileId">';
                html += "<p>" + idUrl + "</p>";
                html += "</div>";
                html += "</div>";
                html += '<div class="profileEvent">';
                html += '<div class="talk">';
                html += '<a href="javascript:;" id="talking"><img src="https://image.flaticon.com/icons/svg/1878/1878874.svg"></a>';
                html += "</div>";
                html += "</div>";
                html += "</div>";
                html += "</div>";

                adr.before(html);
            },
            report_html: function () {
                var html = "";

                html += '<div id="popupReport">';
                html += '<div class="reportPopupContainer">';
                html += '<div class="reportTitle">';
                html += "<p>신고하기</p>";
                html += "</div>";
                html += '<div class="reportText">';
                html += "<p>";
                html += "해당 콘텐츠의 사유를 선택하여 주세요<br>";
                html += "심사후 해당 콘텐츠에 대한 조치를 취하겠습니다.";
                html += "</p>";
                html += "</div>";
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
                html += '<div class="reportButton">';
                html += '<input type="radio" id="report_5" name="report" value=""><span>스팸성 도배 콘텐츠</span></div>';
                html += "</div>";
                html += '<div class="buttonContainer">';
                html += '<input type="submit" class="btn btn-danger" name="report_value" value="신고하기">';
                html += '<input type="button" class="btn btn-danger" name="close" value="취소">';
                html += "</div>";
                html += "</form>";
                html += "</div>";
                html += "</div>";
                html += "</div>";

                TIMELIST.before(html);
            },
            profilePopup: function (_this, _node) {
                var imgUrl = _this.find("img").attr("src"),
                    idUrl = _this.siblings(".userId").text(),
                    select_offset = _this.offset(),
                    position_top = select_offset.top,
                    position_left = select_offset.left + 60,
                    popupProfile_length = $("#popupProfile").length,
                    popupReport_length = $("#popupReport").length,
                    popupProfile = $("#popupProfile"),
                    TIMELIST = _node;

                console.log(imgUrl);
                console.log(popupProfile_length);
                if (popupProfile_length == 0 && popupReport_length == 0) {
                    _Fx.profile_html(imgUrl, idUrl, TIMELIST);
                    var popupProfile = $("#popupProfile");
                    popupProfile.css({
                        top: position_top,
                        left: position_left
                    });
                } else if (popupProfile_length == 0 && popupReport_length == 1) {
                    var popupReport = $("#popupReport");

                    popupReport.remove();
                    _Fx.profile_html(imgUrl, idUrl, TIMELIST);
                    var popupProfile = $("#popupProfile");
                    popupProfile.css({
                        top: position_top,
                        left: position_left
                    });
                } else if (popupProfile_length == 1 && popupReport_length == 0) {
                    var popupProfile = $("#popupProfile");

                    popupProfile.remove();
                    _Fx.profile_html(imgUrl, idUrl, TIMELIST);
                    var popupProfile = $("#popupProfile");
                    popupProfile.css({
                        top: position_top,
                        left: position_left
                    });
                }
            },

            realUserList: function (gender_data) {
                var pinkN = $(".genderSortation .pink"),
                    blueN = $(".genderSortation .blue"),
                    user_list = $("#realList").find("ul"),
                    list_length = $("#realList")
                    .find("ul")
                    .children().length,
                    _receive = gender_data;
                console.log(_receive);
                if (_receive == "male") {
                    blueN.addClass("pick");
                    pinkN.removeClass("pick");
                } else {
                    blueN.removeClass("pick");
                    pinkN.addClass("pick");
                }
                _Io.sendEmit(
                    _socket,
                    _Io.submit_request(300, {
                        gender: gender_data
                    })
                );
                if (list_length != 0) {
                    user_list.children().remove();
                }
            },
            mypageInfo: function (url) {
                var mNick = $("#navibar > div.mainLoginContainer > div.logoutPanel > div.userInfo > span.nick").text(),
                    send_data = {
                        nickname: _Fn.getCookie("login_nick")
                    };
                console.log(url);
                console.log(send_data);
                $.ajax({
                    type: "POST",
                    url: "/api/oauth/getUserInfo",
                    data: send_data,
                    success: function (res) {
                        console.log(res);
                        var data = res.data,
                            login_id = data.uid,
                            login_type = data.type,
                            dif_id = data.anotherid,
                            title = data.title,
                            psw_change = data.updated_pass,
                            comment = data.comment,
                            imgs = data.files,
                            profileImg = data.thumb,
                            emptyImg = "//ntalk.me/img/people_base_back.svg",
                            phone = data.phone,
                            gender = data.gender,
                            age = data.age,
                            nick = data.nickname,
                            location = data.location,
                            location2 = data.location2,
                            content_id = data.t_idx;
                        console.log($.isEmptyObject(imgs));
                        /* if ($.isEmptyObject(imgs) == false) {
                            imgs = data.files[0].thumb;
                        } */
                        if (location != null || location != undefined || location != "") {
                            location = "#" + location;
                        }
                        if (location2 != null || location2 != undefined || location2 != "") {
                            location2 = "#" + location2;
                        }
                        console.log(comment);
                        console.log($.isEmptyObject(comment));
                        console.log(typeof comment);
                        if (comment != null) {
                            if (comment.indexOf("\n") != -1) {
                                comment = comment.replace(/(?:\r\n|\r|\n)/g, "<br>");
                            }
                        }
                        console.log(comment);
                        /*******마이페이지 타임라인 내용 넣기 *******/
                        if ($.isEmptyObject(comment) == false) {
                            $("#mytimeline > div.myTime > input[type=hidden]").val(content_id);
                            $("#mytimeline > div.myTime > div.myTimeContent > div.commentContainer > div.mycommentTitle").text(title);
                            $("#mytimeline > div.myTime > div.myTimeContent > div.commentContainer > div.myComment span").append(comment);
                            for (var i = 0; i < imgs.length; i++) {
                                var html = "";
                                if (imgs[i].division == "2") {
                                    html += '<img src="' + imgs[i].thumb + '" class="img_' + (i + 1) + '">';
                                }
                                /* $("#mytimeline > div.myTime > div.myTimeContent > div.myProfileImg > img").prop("src", imgs); */
                                $("#mytimeline > div.myTime > div.myTimeContent > div.myProfileImg").append(html);
                            }
                            /* $("#preview").prop("src", profileImg);
                            $("#myInfoContent > div.myNickname").text(nickname);
                            $("#myInfoContent > div.myinfo > span.myAge").text(age);
                            $("#myInfoContent > div.myinfo > span.mylocation").text(location);
                            $("#myInfoContent > div.myinfo > span.mylocation2").text(location2); */
                        } else {
                            console.log($("#mytimeline > div.myTime"));
                            $("#mytimeline > div.myTime").addClass("nonDisplay");
                            $("#mytimeline > div.nonTime").removeClass("nonDisplay");
                            /* $("#preview").prop("src", profileImg);
                            $("#myInfoContent > div.myNickname").text(nickname);
                            $("#myInfoContent > div.myinfo > span.myAge").text(age);
                            $("#myInfoContent > div.myinfo > span.mylocation").text(location);
                            $("#myInfoContent > div.myinfo > span.mylocation2").text(location2); */
                        }
                        /***** 로그인된 계정 ******/
                        if (login_type == "email" || login_type == "ntalk") {
                            $("#myPageContent .accountContent .loginAccount .accountImg").addClass("appORweb");
                        } else if (login_type == "google") {
                            $("#myPageContent .accountContent .loginAccount .accountImg").addClass("google");
                        } else if (login_type == "kakao") {
                            $("#myPageContent .accountContent .loginAccount .accountImg").addClass("kakao");
                        } else if (login_type == "naver") {
                            $("#myPageContent .accountContent .loginAccount .accountImg").addClass("naver");
                        } else if (login_type == "facebook") {
                            $("#myPageContent .accountContent .loginAccount .accountImg").addClass("facebook");
                        }

                        $("#myPageContent > div.accountContent > div.loginAccount > span:nth-child(2)").text(login_id);

                        /*****다른 계정******/
                        for (var n = 0; n < dif_id.length; n++) {
                            var str = "";

                            str += "<div>";
                            if (dif_id[n].type == "email" || dif_id[n].type == "ntalk") {
                                str += '<span class="anotherImg appORweb"></span>';
                            } else if (dif_id[n].type == "google") {
                                str += '<span class="anotherImg google"></span>';
                            } else if (dif_id[n].type == "kakao") {
                                str += '<span class="anotherImg kakao"></span>';
                            } else if (dif_id[n].type == "naver") {
                                str += '<span class="anotherImg naver"></span>';
                            } else if (dif_id[n].type == "facebook") {
                                str += '<span class="anotherImg facebook"></span>';
                            }

                            str += "<span>" + dif_id[n].id + "</span>";
                            str += "</div>";

                            $("#myPageContent .difAccountContent .difAccountId").append(str);
                        }

                        /******비밀번호 ******/
                        var psw_date = psw_change.substr(5, 5),
                            psw_replace = psw_date.replace(/\-/g, "월"),
                            psw_result = "최종 변경일: " + psw_replace + "일",
                            login_class = $("#myPageContent .accountContent .loginAccount .accountImg").prop("class");

                        if (login_class == "accountImg appORweb") {
                            $("#myPageContent .pswContent .psw .pswChangeDate").text(psw_result);
                        } else {
                            $("#myPageContent .pswContent .psw .pswChangeDate").text("");
                        }

                        /*****프로필 이미지 *****/
                        if (profileImg == "" || profileImg == null || profileImg == undefined) {
                            $("#profileImg #preview").prop("src", emptyImg);
                        } else {
                            $("#profileImg #preview").prop("src", profileImg);
                        }

                        /****** 핸드폰 번호 *******/
                        var Hyphen_phone = phone.replace(/(\d{3})(\d{4})(\d{4})/, "$1-$2-$3");
                        $("#myPageContent > div.phoneContent > div.phone > span").text(Hyphen_phone);

                        /******* 닉네임 *********/
                        $("#myPageContent > div.nickContent > div.myNickname > span").text(nick);

                        /******* 성별 *********/
                        if (gender == "male") {
                            gender = "남성";
                        } else if (gender == "female") {
                            gender = "여성";
                        } else {
                            gender = "";
                        }
                        $("#myPageContent > div.genderContent > div.gender > span").text(gender);

                        /******* 나이 *********/
                        if (age == -1) {
                            $("#myPageContent > div.ageContent > div.age > span").text("미입력");
                        } else {
                            $("#myPageContent > div.ageContent > div.age > span").text(age + "세");
                        }

                        /******* 지역 *********/
                        var info_location = data.location,
                            info_location2 = data.location2;
                        if (_Fn.isEmpty(info_location) == true) {
                            $("#myPageContent > div.locationContent > div.location > span.mylocation").text("전체지역");
                            $("#myPageContent > div.locationContent > div.location > span.mylocation2").text("");
                        } else if (_Fn.isEmpty(info_location) == false && _Fn.isEmpty(info_location2) == true) {
                            $("#myPageContent > div.locationContent > div.location > span.mylocation").text(info_location + "시");
                            $("#myPageContent > div.locationContent > div.location > span.mylocation2").text("전체지역");
                        } else {
                            $("#myPageContent > div.locationContent > div.location > span.mylocation").text(info_location + "시");
                            $("#myPageContent > div.locationContent > div.location > span.mylocation2").text(info_location2 + "구");
                        }

                        /******* 내사진 *********/
                        var html1 = "";
                        if (imgs.length == 0) {
                            html1 += '<div class="imgUp 1">';
                            html1 += '<div class="closeBtn nonDisplay">';
                            html1 += "<span></span>";
                            html1 += "</div>";
                            html1 += '<div class="plusImg"></div>';
                            html1 += '<form id="form_photo">';
                            html1 += '<input type="file" id="file_img_1" class="upImg" name="img[]">';
                            html1 += "</form>";
                            html1 += '<div id="preview">';
                            /*  html1 += '<div style="display: inline-flex; width: 134px; height: 166px;">';
                             html1 += '<img src="" title="" width=134 height=166 />'; */
                            html1 += "</div></div>";
                            //html1 += "</div>";
                        } else if (imgs.length > 0) {
                            var class_number = 1;
                            for (var s = 0; s < imgs.length; s++) {
                                if (imgs[s].division == 0) {
                                    html1 += '<div class="imgUp ' + class_number++ + '">';
                                    html1 += '<div class="closeBtn">';
                                    html1 += "<span></span>";
                                    html1 += "</div>";
                                    html1 += '<div class="plusImg nonDisplay"></div>';
                                    //html1 += '<input type="file" id="file_img_' + (s + 1) + '" class="upImg ' + (s + 1) + '" name="img[]">';
                                    html1 += '<input type="hidden" value="' + imgs[s].f_idx + '">';
                                    html1 += '<div id="preview">';
                                    html1 += '<div style="display: inline-flex; width: 134px; height: 166px;">';
                                    html1 += '<img src="' + imgs[s].thumb + '" title="' + imgs[s].thumb + '" width=134 height=166 />';
                                    html1 += "</div></div>";
                                    html1 += "</div>";
                                }
                            }
                            html1 += '<div class="imgUp ' + class_number++ + '">';
                            html1 += '<div class="closeBtn nonDisplay">';
                            html1 += "<span></span>";
                            html1 += "</div>";
                            html1 += '<div class="plusImg"></div>';
                            html1 += '<form id="form_photo">';
                            html1 += '<input type="file" id="file_img_' + class_number++ + '" class="upImg ' + class_number++ + '" name="img[]">';
                            html1 += "</form>";
                            html1 += '<div id="preview">';
                            html1 += "</div></div>";
                            html1 += "</div>";
                        }
                        $("#photoContent").append(html1);
                    },
                    error: function (e) {
                        console.log(e);
                    }
                });
            },
            mypage_psw_html: function () {
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
                html += '<div class="resultContent">';
                html += '<div class="newPwContainer">';
                html += '<div class="newPwTitle">';
                html += "<span>신규 비밀번호</span>";
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
                html += '<div class="cancellBtn">취소</div>';
                html += '<div class="mypageSubBtn">재설정하기</div>';
                html += "</div>";
                html += "</div>";
                html += "</div>";

                $("#mainContent").before(html);
            },
            mypage_ph_change_html: function () {
                var html = "";

                html += '<div id="phChangeBack">';
                html += '<div id="phChangeLayer">';
                html += '<div id="phChangeHeader">';
                html += '<div class="headerTitle">';
                html += "<span>휴대폰 번호 변경</span>";
                html += "</div>";
                html += '<div class="closeBtn">';
                html += "<span></span>";
                html += "</div>";
                html += "</div>";
                html += '<div id="beforePhone">';
                html += '<div class="beforeTitle">';
                html += "<span>기존 휴대폰 번호</span>";
                html += "</div>";
                html += '<div class="beforeContent">';
                html += "<label>";
                html += '<input type="text" id="beforePh" name="phone">';
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += '<div id="afterPhone">';
                html += '<div class="afterTitle">';
                html += "<span>신규 휴대폰 번호</span>";
                html += "</div>";
                html += '<div class="afterContent">';
                html += "<label>";
                html += '<input type="text" id="afterPh" name="phone">';
                html += '<div id="overlap_ph" class="nonDisplay">중복확인</div>';
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += '<div id="phoneBtn">';
                html += '<div class="phChangeBtn">수정하기</div>';
                html += "</div>";
                html += "</div>";
                html += "</div>";

                $("#mainContent").prepend(html);
            },
            mypage_member_edit_html: function () {
                var html = "",
                    emptyImg = "https://ntalk.me/img/people_base_back.svg",
                    img = $("#profileImg #preview").prop("src");
                console.log(img);

                html += '<div id="memberInfoBack">';
                html += '<div id="memberInfoLayer">';
                html += '<div id="memberInfoHeader">';
                html += '<div class="headerTitle">';
                html += "<span>회원정보수정</span>";
                html += "</div>";
                html += '<div class="closeBtn">';
                html += "<span></span>";
                html += "</div>";
                html += "</div>";
                html += '<div id="memberNick">';
                html += '<div class="nickTitle">';
                html += "<span>닉네임</span>";
                html += "</div>";
                html += '<div class="nickContent">';
                html += "<label>";
                html += '<input type="text" id="nick" name="nickname">';
                html += '<div id="overlap_nick">중복확인</div>';
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += '<div id="memberGender">';
                html += '<div class="genderTitle">';
                html += "<span>성별</span>";
                html += "</div>";
                html += '<div class="genderContent">';
                html += '<label class="gender" for="male">남자';
                html += '<input type="button" id="male" class="male nonDisplay" name="male" value="male">';
                html += "</label>";
                html += '<label class="gender" for="female">여자';
                html += '<input type="button" id="female" class="female nonDisplay" name="female" value="female">';
                html += "</label>";
                html += "</div>";
                html += "</div>";
                html += '<div id="memberAge">';
                html += '<div class="ageTitle">';
                html += "<span>나이</span>";
                html += "</div>";
                html += '<div class="ageContent">';
                html += "<select>";
                html += "</select>";
                html += "</div>";
                html += "</div>";
                html += '<div id="memberPhoto">';
                html += '<div class="photoTitle">';
                html += "<span>사진등록</span>";
                html += "</div>";
                html += '<div class="photoContent">';
                if (img != emptyImg) {
                    html += '<div class="imgUp">';
                    html += '<div class="closeBtn">';
                    html += "<span></span>";
                    html += "</div>";
                    html += '<div class="plusImg nonDisplay"></div>';
                    html += '<input type="hidden" value="">';
                    html += '<div id="preview">';
                    html += '<div style="display: inline-flex; width: 110px; height: 110px;">';
                    html += '<img src="" width="110" height="110">';
                    html += "</div>";
                    html += "</div>";
                    html += "</div>";
                } else {
                    html += '<div class="imgUp">';
                    html += '<div class="closeBtn nonDisplay">';
                    html += "<span></span>";
                    html += "</div>";
                    html += '<div class="plusImg"></div>';
                    html += '<form id="form_photo">';
                    html += '<label for="memberImg">';
                    html += '<input type="file" id="memberImg" class="upImg" name="img[]">';

                    html += '<div id="preview">';
                    html += '<img src="" width="110" height="110">';
                    html += "</div>";
                    html += "</label>";
                    html += "</form>";
                    html += "</div>";
                }
                html += '<div class="infoText">';
                html += "<span>";
                html += "선정적이거나 사회적으로 이슈가되는</br>";
                html += "이미지는 등록을 금지합니다.";
                html += "</span>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
                html += '<div id="memberLocal">';
                html += '<div class="localTitle">';
                html += "<span>지역</span>";
                html += "</div>";
                html += "</div>";
                html += '<div id="memberBtn">';
                html += '<div class="memberInfoEdit">수정하기</div>';
                html += "</div>";
                html += "</div>";
                html += "</div>";

                $("#mainContent").prepend(html);
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
            user_on: function (res) {
                //실시간 유저 접속시 601번 receive 타임라인에 on 및 실시간 접속 회원 추가
                var data = res.data,
                    html = "",
                    hash = data.hash,
                    timeline_adr = $("#timeLineContainer > ul li");
                console.log(hash);
                for (var i = 0; i < timeline_adr.length; i++) {
                    console.log(
                        timeline_adr
                        .eq(i)
                        .find("#hash")
                        .val()
                    );
                    if (
                        timeline_adr
                        .eq(i)
                        .find("#hash")
                        .val() == hash
                    ) {
                        console.log(
                            timeline_adr
                            .eq(i)
                            .find("#hash")
                            .siblings(".main_info")
                            .find("#userInfo .connectUser")
                        );
                        timeline_adr
                            .eq(i)
                            .find("#hash")
                            .siblings(".main_info")
                            .find("#userInfo .connectUser")
                            .removeClass("nonDisplay");
                    }
                }
                console.log(data);
                var image = null,
                    gameCount = 1,
                    listCount = $("#realList")
                    .find("ul")
                    .children().length,
                    userNickname = data.nickname,
                    location1 = data.location,
                    location2 = data.location2,
                    userAge = data.age,
                    number = data.idx,
                    gender = data.gender,
                    profileImg = data.profile_thum,
                    _joinDay = "" + data.user_create_at,
                    joinDay = _joinDay.substring(0, 10),
                    joinAt = _Fn.dateDiff(currentday, joinDay),
                    _hash = data.hash;
                console.log(location1);
                console.log(location2);
                console.log(_hash);
                if ((profileImg == null || profileImg == undefined || profileImg == "") && gender == "male") {
                    image = "//ntalk.me/img/man_profile.png";
                } else if ((profileImg == null || profileImg == undefined || profileImg == "") && gender == "female") {
                    image = "//ntalk.me/img/girl_profile.png";
                } else {
                    image = profileImg;
                }
                if (location1 == null || location1 == undefined || location1 == "") {
                    location1;
                } else {
                    location1 = "#" + location1;
                }
                if (location2 == null || location2 == undefined || location2 == "") {
                    location2;
                } else {
                    location2 = "#" + location2;
                }
                if (userAge == -1) {
                    userAge = "";
                } else {
                    userAge = "(" + userAge + ")";
                }
                console.log(listCount);
                if (listCount == 0) {
                    gameCount = gameCount;
                } else {
                    number = parseInt(
                        $("#realList")
                        .find("ul")
                        .children()
                        .last()
                        .attr("class")
                        .replace(/[^0-9]/g, "")
                    );
                    gameCount = number + gameCount;
                }
                html += '<li class="number ' + gameCount + '">';
                html += '<div id="userInfo">';
                html += '<div class="userImg">';
                html += '<img src="' + image + '">';
                html += "</div>";
                html += '<div class="number nonDisplay">' + number + "</div>";
                html += '<div class="infoContainer">';
                html += '<div class="userId" title="아이디">' + userNickname + "</div>";
                if (gender == "female") {
                    html += '<div class="userAge woman" title="나이">' + userAge + "</div>";
                } else {
                    html += '<div class="userAge man" title="나이">' + userAge + "</div>";
                }
                html += '<div class="connectUser"><p>ON</p></div>';
                if (joinAt >= 10) {
                    html += '<div class="newUser nonDisplay"><p>NEW</p></div>';
                } else {
                    html += '<div class="newUser"><p>NEW</p></div>';
                }
                html += '<div class="hotUser"><p>HOT</p></div>';
                html += "</div>";
                html += '<div class="locationInfo">';
                html += '<div class="firstLocation">' + location1 + "</div>";
                html += '<div class="secondLocation">' + location2 + "</div>";
                html += "</div>";
                html += '<input type="hidden" id="userNick" name="userNick">';
                html += '<input type="hidden" id="hash" name="hash" value="' + _hash + '">';
                html += "</div>";
                html += "</li>";
                var select_p = $("#realTimeListContainer > div > div.genderSortation .pink").attr("class"),
                    select_b = $("#realTimeListContainer > div > div.genderSortation .blue").attr("class");
                console.log(select_p);
                console.log(select_b);
                if (select_p == "pink pick" && gender == "female") {
                    $("#realList ul").append(html);
                } else if (select_b == "blue pick" && gender == "male") {
                    $("#realList ul").append(html);
                }
            },
            user_del: function (res) {
                var data = res.data,
                    real_adr = $("#realList ul li");
                console.log(data);
                for (var i = 0; i < real_adr.length; i++) {
                    if (
                        real_adr
                        .eq(i)
                        .find("#hash")
                        .val() == data
                    ) {
                        real_adr
                            .eq(i)
                            .find("#hash")
                            .parents("li")
                            .remove();
                    }
                }
                var timeline_adr = $("#timeLineContainer > ul li");
                for (var n = 0; n < timeline_adr.length; n++) {
                    if (
                        timeline_adr
                        .eq(n)
                        .find("#hash")
                        .val() == data
                    ) {
                        timeline_adr
                            .eq(n)
                            .find("#hash")
                            .siblings(".main_info")
                            .find("#userInfo .connectUser")
                            .addClass("nonDisplay");
                    }
                }
            },
            autoHeight: function () {
                setTimeout(function () {
                    var bodyHeight = $("#timeLineContainer").height() + 80;
                    var parentIframe = $("#cside");

                    console.log(bodyHeight);
                    console.log(typeof bodyHeight);
                    console.log(parentIframe.height());
                    if (bodyHeight > parentIframe.height()) {
                        parentIframe.height(bodyHeight);
                        $("#cside").height(bodyHeight);
                    } else {
                        if (parentIframe.length > 0) {
                            parentIframe.height(bodyHeight);
                            $("#cside").height(bodyHeight);
                        }
                    }
                }, 1500);
            }
        };
    })(); //타임라인 관련 함수

    _Io = (function () {
        //통신 함수
        return {
            connectChat: function () {
                console.log("Initialize Chat... (" + location.href + ")");
                l_c = "chat talking";
                _serverChat = "wss://ntalk.me:8443/Chat";
                _socketChat = io(_serverChat, {
                    transports: ["websocket"],
                    forceNew: true
                });

                _socketChat
                    .on("connect", function () {
                        console.log("chat room connect.. (delivered roomId : " + window.roomId + ")");
                        $("#roomId").val(window.roomId);
                        if (window.roomId == "") {
                            _Io.sendEmit(
                                _socketChat,
                                _Io.submit_request(212, {
                                    nickName: myNickname,
                                    oppNickName: window.target
                                })
                            );
                            $("#messages").append(_Talk.chatNotice());
                        } else {
                            _Io.sendEmit(
                                _socketChat,
                                _Io.submit_request(207, {
                                    roomId: window.roomId,
                                    nickName: myNickname,
                                    oppNickName: window.target
                                })
                            );
                            _Io.sendEmit(
                                _socketChat,
                                _Io.submit_request(209, {
                                    roomId: window.roomId,
                                    start: 0,
                                    limit: 50
                                })
                            );
                        }
                    })
                    .on("disconnect", function () {
                        // TODO : 접속이 끊기면 모든 채팅 전송 관련 UI를 비활성화 처리 (채팅방 완전 나가기 포함)
                    })
                    .on("RECEIVE", function (res) {
                        _Io.chatReceive(res);
                        console.log("chat talking RECEIVE ..............");
                        console.log(res);
                    });
            },
            connectLounge: function () {
                //var _server, _socket;
                console.log("Connecting Lounge...");
                _server = "wss://ntalk.me:8443/Lounge";
                _socket = io(_server, {
                    transports: ["websocket"],
                    reconnection: true,
                    reconnectionAttempts: Infinity,
                    reconectionDelay: 1000,
                    reconnectionDelayMax: 3000,
                    timeout: 5000,
                    autoConnect: false
                });

                _socket.connect();

                _socket
                    .on("connect", function () {
                        console.log("connect");
                        console.log(_socket.connected);
                        _socket.emit("hello", "world2");
                        /* _Io.sendEmit(
                            _socket,
                            _Io.submit_request(300, {
                                gender: "female"
                            })
                        ); */
                        _Io.sendEmit(
                            _socket,
                            _Io.submit_request(300, {
                                gender: "all"
                            })
                        );
                    })
                    .on("RECEIVE", function (res) {
                        // 사용자 파이프
                        _Io.loungeReceive(res);
                        console.log("Lounge RECEIVE ..............");
                    })
                    .on("REGISTER", function (response) {
                        console.log(response);
                    })
                    .on("disconnect", function () {
                        console.log("you have been disconnected");
                    })
                    .on("reconnect", function () {
                        console.log("you have been reconnected");
                    })
                    .on("reconnect_error", function () {
                        console.log("attempt to reconnect has failed");
                    })
                    .on("error", function (error) {
                        console.log("socket error occured : " + error);
                    });
            },
            chatReceive: function (res) {
                // TODO : ZLIB 압축해제
                var json_data = JSON.parse(res);
                console.log(JSON.stringify(json_data, null, 4));
                switch (json_data.cmd) {
                    case 100: // 최초 접속시 (REGISTER 이후) 사용자에게 제공되는 메세지
                        break;
                    case 200: // 채팅 메세지
                        _Talk.message(json_data);
                        break;
                    case 201: // 채팅 타이핑 중을 알림
                        console.log(json_data);
                        _Talk.typing(json_data);
                        break;
                    case 202: // 채팅 타이핑 종료를 알림
                        console.log(json_data);
                        _Talk.typing(json_data);
                        break;
                    case 203: // 채팅 방이 생성되었음
                        console.log(json_data);
                        _Talk.roomCreated(json_data, myNickname);
                        break;
                    case 204: // 사용자가 채팅방에서 나갔음
                        console.log(json_data);
                        break;
                    case 205: // 사용자가 채팅방내 대화메세지를 읽었음
                        $("#meContainer .read").addClass("nonDisplay");
                        console.log(_socketChat);
                        break;
                    case 206: // 채팅방 화면을 퇴장함
                        console.log(json_data);
                        break;
                    case 207: // 채팅방 화면을 입장함
                        console.log(json_data);
                        break;
                    case 208: // 채팅대화신고
                        console.log(json_data);
                        break;
                    case 209: // 채팅방 히스토리 요청
                        console.log(json_data);
                        _Talk.history(json_data, myNickname);
                        break;
                    case 210: // 참여중인 채팅방 리스트
                        console.log(json_data);
                        break;
                    case 211: // 채팅방 ID 찾기
                        break;
                    case 212: // (Web 전용 커멘드)웹 에서 채팅 ROOM_ID 가 없이 Socket이 추가 접속되었음을 서버로 알림
                        // (Guest 상태의 Redis 접속정보에서 User 로 Upgrade, /READY Socket 상태로 대기)
                        // Mobile 에서는 단일 소켓으로 모두 처리 가능하나 웹에서는 창단위로 Socket이 생성되므로
                        // Socket 접속 정보의 Upgrade 과정이 필요함
                        break;
                    case 300: // 사용자 리스트
                        _Talk.userList(json_data);
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
            loungeReceive: function (res) {
                // TODO : ZLIB 압축해제
                var json_data = JSON.parse(res);
                console.log(JSON.stringify(json_data, null, 4));
                switch (json_data.cmd) {
                    case 100: // 최초 접속시 (REGISTER 이후) 사용자에게 제공되는 메세지
                        break;
                    case 200: // 채팅 메세지
                        break;
                    case 201: // 채팅 타이핑 중을 알림
                        console.log(json_data);
                        break;
                    case 202: // 채팅 타이핑 종료를 알림
                        console.log(json_data);
                        break;
                    case 203: // 채팅 방이 생성되었음
                        console.log(json_data);

                        break;
                    case 204: // 사용자가 채팅방에서 나갔음
                        console.log(json_data);
                        break;
                    case 205: // 사용자가 채팅방내 대화메세지를 읽었음
                        console.log(json_data);
                        break;
                    case 206: // 채팅방 화면을 퇴장함
                        console.log(json_data);
                        break;
                    case 207: // 채팅방 화면을 입장함
                        console.log(json_data);
                        break;
                    case 208: // 채팅대화신고
                        console.log(json_data);
                        break;
                    case 209: // 채팅방 히스토리 요청
                        console.log(json_data);

                        break;
                    case 210: // 참여중인 채팅방 리스트
                        console.log(json_data);
                        break;
                    case 211: // 채팅방 ID 찾기
                        _Talk.findRoomId(myNickname, json_data);
                        break;
                    case 212: // (Web 전용 커멘드)웹 에서 채팅 ROOM_ID 가 없이 Socket이 추가 접속되었음을 서버로 알림
                        // (Guest 상태의 Redis 접속정보에서 User 로 Upgrade, /READY Socket 상태로 대기)
                        // Mobile 에서는 단일 소켓으로 모두 처리 가능하나 웹에서는 창단위로 Socket이 생성되므로
                        // Socket 접속 정보의 Upgrade 과정이 필요함
                        break;
                    case 300: // 사용자 리스트
                        /* var gender = "female";
                        _Talk.user_list_json(json_data, gender);
                        console.log(_Talk.user_list_json(json_data, gender)); */
                        _Talk.userList(json_data, userlist_gender);
                        break;
                    case 400: // 회원 정보 변경 알림
                        break;
                    case 500: // 타임라인 본인 또는 다른 사용자 등록 된 json 데이터
                        var data_nick = json_data.data.nickname,
                            validation_data = _Fx.receiveFilter(json_data);
                        console.log(data_nick);
                        console.log(validation_data);
                        console.log(myNickname);
                        if (data_nick != myNickname) {
                            _Fx.receiveTimeLine(validation_data);
                        } else {
                            return false;
                        }

                        break;
                    case 501: // 타임라인 본인 또는 다른 사용자 등록 알림
                        console.log(json_data);
                        var data_nick1 = json_data.data.nickname,
                            validation_data1 = _Fx.receiveFilter(json_data);
                        console.log(data_nick1);
                        console.log(validation_data1);
                        console.log(myNickname);
                        if (data_nick1 != myNickname) {
                            _Fx.receiveTimeLine(validation_data1);
                        } else {
                            return false;
                        }
                        break;
                    case 502: // 터임라인 삭제 알림
                        console.log(json_data);
                        _Fx.receiveTimelie_del(json_data);
                        break;
                    case 601: // 사용자 접속 알림
                        // alert("로그인");
                        console.log(json_data);
                        _Fx.user_on(json_data);
                        break;
                    case 602: // 사용자 접속 해제 알림
                        //alert("사용자가 로그아웃 했습니다.");
                        _Fx.user_del(json_data);
                        console.log(json_data);
                        break;
                    case 603: // 사용자 정보가 변경되었음
                        break;
                    case 1000: // 다른장비에서 사용자가 접속하였음
                        if (
                            $(location)
                            .attr("href")
                            .indexOf("nick") != -1
                        ) {
                            _socket.disconnect();
                            var con = confirm("다른기기에서 사용자가 접속 하여 로그아웃 되었습니다.\n처음화면으로 이동합니다.");
                            window.close();
                            location.href = "https://ntalk.me";
                        }
                        break;
                    default:
                }
            },
            sendEmit: function (socket, jsonObject) {
                var message = JSON.stringify(jsonObject, null, 4);
                console.log("=>[EMIT]" + message);
                // TODO : Zlib 압축
                socket.emit("SUBMIT", message);
            },
            submit_request: function (cmd_val, data_val) {
                var object_val = {
                    cmd: cmd_val,
                    data: data_val
                };
                return object_val;
            },
            user_info: function () {
                $.ajax({
                    timeout: 3000,
                    type: "POST",
                    url: "/api/oauth/logindata",
                    dataType: "json",
                    success: function (data) {
                        console.log(data);
                        var user_info = data.message,
                            html = "",
                            user_img = user_info.thumb,
                            user_gender = user_info.gender,
                            user_local = user_info.location,
                            user_local2 = user_info.location2,
                            user_age = user_info.age,
                            user_id = user_info.id;

                        if (user_img == null && user_gender == "male") {
                            user_img = "//ntalk.me/img/man_profile.png";
                        } else if (user_img == null && user_gender == "female") {
                            user_img = "//ntalk.me/img/girl_profile.png";
                        }
                        myNickname = user_info.nickname;
                        _Fn.setCookie("login_nick", myNickname, 1);
                        $("#myNick").val(myNickname);
                        html += '<div class="profilImg">';
                        html += '<img src="' + user_img + '"></img>';
                        html += "</div>";
                        html += '<div class="content">';
                        html += "<span>" + myNickname + "님,</span>타임라인에 글쓰고 메시지를 받아보세요.";
                        html += "</div>";
                        $("#writeProfile .profileContainer .profileComment").remove();
                        $("#writeProfile .profileContainer").append(html);
                        $("#navibar .mainLoginContainer .loginPanel").addClass("nonDisplay");
                        $("#navibar .mainLoginContainer .logoutPanel").removeClass("nonDisplay");
                        $("#navibar .mainLoginContainer .logoutPanel .userProfileImg img").attr("src", user_img);
                        $("#navibar .mainLoginContainer .logoutPanel .userInfo .nick").text(myNickname);

                        $("#hidden_id").val(user_id);
                        if (user_gender == "male") {
                            $("#navibar .mainLoginContainer .logoutPanel .userInfo .age").addClass("man");
                            //클래스로 색깔주기 (css에서 색상 지정 해줘야함)
                        } else if (user_gender == "female") {
                            $("#navibar .mainLoginContainer .logoutPanel .userInfo .age").addClass("woman");
                        }
                        if (user_local == null || user_local == undefined || user_local == "") {
                            $("#navibar .mainLoginContainer .logoutPanel .userLocation .local1").addClass("nonDisplay");
                        } else {
                            /*  user_local = "#" + user_info.location; */
                            user_local = "#" + user_local;
                            $("#navibar .mainLoginContainer .logoutPanel .userLocation .local1").text(user_local);
                        }
                        if (user_local2 == null || user_local2 == undefined || user_local2 == "") {
                            $("#navibar .mainLoginContainer .logoutPanel .userLocation .local2").addClass("nonDisplay");
                        } else {
                            user_local2 = "#" + user_local2;
                            $("#navibar .mainLoginContainer .logoutPanel .userLocation .local2").text(user_local2);
                        }
                        if (user_age == -1) {
                            user_age = "";
                        } else {
                            $("#navibar .mainLoginContainer .logoutPanel .userInfo .age").text("(" + user_age + ")");
                        }
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    }
                });
            },
            getUserInfo: function () {
                // 타임라인 클릭시 팝업메인페이지 바디에서 호출
                var nick = window.selectNick,
                    location_nick = decodeURIComponent($(location).attr("href")),
                    nickName = location_nick.substring(location_nick.lastIndexOf("=") + 1);
                console.log(decodeURIComponent($(location).attr("href")));
                console.log(myNickname);
                var sessionUId = sessionStorage.getItem("nickname");
                console.log(sessionUId);
                console.log(nickName);
                console.log(nick);
                $.ajax({
                    type: "POST",
                    url: "/api/oauth/getUserInfo",
                    data: {
                        nickname: nickName
                    },
                    dataType: "json",
                    success: function (res) {
                        console.log(res);
                        var data = res.data,
                            pop_uid = data.uid,
                            pop_nickname = data.nickname,
                            pop_location = data.location,
                            pop_location2 = data.location2,
                            pop_gender = data.gender,
                            pop_age = data.age,
                            pop_t_idx = data.t_idx,
                            pop_title = data.title,
                            pop_comment = data.comment,
                            pop_minAge = data.minAge,
                            pop_maxAge = data.maxAge,
                            pop_fgender = data.fgender,
                            pop_flocation = data.flocation,
                            pop_flocation2 = data.flocation2,
                            pop_file_url_real = data.file_url_real,
                            pop_file_url_thumb = data.file_url_thumb,
                            pop_f_tempname = data.f_tempname,
                            pop_user_create_at = data.user_create_at,
                            pop_count = data.count,
                            pop_real = data.real,
                            pop_thumb = data.thumb,
                            pop_files = data.files;
                        $("#loginNick").val(myNickname);
                        $("#uid").val(pop_uid);
                        $("#nickname").val(pop_nickname);
                        $("#location").val(pop_location);
                        $("#location2").val(pop_location2);
                        $("#gender").val(pop_gender);
                        $("#age").val(pop_age);
                        $("#t_idx").val(pop_t_idx);
                        $("#title").val(pop_title);
                        $("#comment").val(pop_comment);
                        $("#minAge").val(pop_minAge);
                        $("#maxAge").val(pop_maxAge);
                        $("#fgender").val(pop_fgender);
                        $("#flocation").val(pop_flocation);
                        $("#flocation2").val(pop_flocation2);
                        $("#file_url_real").val(pop_file_url_real);
                        $("#file_url_thumb").val(pop_file_url_thumb);
                        $("#f_tempname").val(pop_f_tempname);
                        $("#user_create_at").val(pop_user_create_at);
                        $("#count").val(pop_count);
                        $("#real").val(pop_real);
                        $("#thumb").val(pop_thumb);
                        console.log(pop_files.length);

                        if (pop_comment.indexOf("\n") != -1) {
                            pop_comment = pop_comment.replace(/(?:\r\n|\r|\n)/g, "<br>");
                            console.log(pop_comment);
                        }
                        for (var i = 0; i < pop_files.length; i++) {
                            var html = "";
                            html += '<div calss="nonDisplay" id="files' + i + ">";
                            html += '<input type="hidden" name="files_division" id="files_division" value="' + pop_files[i].division + " > ";
                            html += '<input type="hidden" name="files_real" id="files_real" value="' + pop_files[i].real + " > ";
                            html += '<input type="hidden" name="files_thumb" id="files_thumb" value="' + pop_files[i].thumb + " > ";
                            html += "</div>";
                            console.log(html);
                            console.log($("#profileInfo"));
                            $("#profileInfo").append(html);
                        }
                        console.log($("#files1"));
                        //$('#btnContainer').after(html);
                        $("#profilePopup #profileInfo").append(html);

                        /********************메인페이지 데이터 입력  ***********************************/
                        $("#popImg > img").prop("src", pop_files[0].real);
                        $("#popImg > div > div > span:nth-child(2)").text(pop_files.length);
                        $("#popInfo > div.infoImg > img").prop("src", pop_thumb);
                        $("#popInfo > div.infoContainer > div.infoTitle > div.infoNick").text(pop_nickname);
                        $("#popInfo > div.infoContainer > div.infoTitle > div.infoAge").text(pop_age);
                        if (pop_location != null || pop_location != undefined || pop_location != "") {
                            pop_location = "#" + pop_location;
                        }
                        $("#popInfo > div.infoContainer > div.infoLocation > div.firstLocal").text(pop_location);
                        if (pop_location2 != null || pop_location2 != undefined || pop_location2 != "") {
                            pop_location2 = "#" + pop_location2;
                        }
                        $("#popInfo > div.infoContainer > div.infoLocation > div.secondLocal").text(pop_location2);

                        /********************* 타임라인 페이지 데디터 입력  ****************************************/
                        $("#poptitle > div.popSubject > span").text(pop_nickname);
                        var age_data = "",
                            age_data2 = "",
                            age_text = "",
                            age_text2 = "";
                        if (pop_minAge != "" && pop_maxAge == "") {
                            age_text = "세 이상";
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(1)").text(pop_minAge);
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(2)").text(age_text);
                        } else if (pop_minAge == "" && pop_maxAge != "") {
                            age_text = "세 이하";
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(1)").text(pop_maxAge);
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(2)").text(age_text);
                        } else if (pop_minAge != "" && pop_maxAge != "" && pop_minAge != "0" && pop_maxAge != "0") {
                            age_text == "세 이상";
                            age_text2 == "세 이하";
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(1)").text(pop_minAge + "세 이상");
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(2)").text(pop_maxAge + "세 이하");
                        } else if (pop_minAge == "0" && pop_maxAge == "0") {
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(1)").text("모든 연령");
                            $("#poptimeContent > div.popAge > div.ageContent > span:nth-child(2)").text("");
                        }
                        if (pop_flocation != "" && pop_flocation2 == "") {
                            $("#poptimeContent > div.popLocal > div.localContent > span.flocal").text(pop_flocation);
                            $("#poptimeContent > div.popLocal > div.localContent > span.slocal").text("전체지역");
                            $("#poptimeContent > div.popLocal > div.localContent > span.tlocal").text("");
                        } else if (pop_flocation != "" && pop_flocation2 != "") {
                            $("#poptimeContent > div.popLocal > div.localContent > span.flocal").text(pop_flocation);
                            $("#poptimeContent > div.popLocal > div.localContent > span.slocal").text(pop_flocation2);
                            $("#poptimeContent > div.popLocal > div.localContent > span.tlocal").text("");
                        } else if (pop_flocation == "" && pop_flocation2 == "") {
                            $("#poptimeContent > div.popLocal > div.localContent > span.flocal").text("전제지역");
                            $("#poptimeContent > div.popLocal > div.localContent > span.slocal").text("");
                            $("#poptimeContent > div.popLocal > div.localContent > span.tlocal").text("");
                        }
                        if (pop_fgender == "male") {
                            $("#poptimeContent > div.popGender > div.genderContent > span.selectGender").text("남자");
                            $("#poptimeContent > div.popGender > div.genderContent > span:nth-child(2)").text("");
                        } else if (pop_fgender == "female") {
                            $("#poptimeContent > div.popGender > div.genderContent > span.selectGender").text("여자");
                            $("#poptimeContent > div.popGender > div.genderContent > span:nth-child(2)").text("");
                        } else if (pop_fgender == "all") {
                            $("#poptimeContent > div.popGender > div.genderContent > span.selectGender").text("모두");
                            $("#poptimeContent > div.popGender > div.genderContent > span:nth-child(2)").text("");
                        }
                        $("#poptimeContent > div.popSub > div.subContent").text(pop_title);
                        $("#poptimeContent > div.popCon > div.conContent").append("<span>" + pop_comment + "</span>");
                        for (var n = 0; n < pop_files.length; n++) {
                            var html = "",
                                division = pop_files[n].division;
                            if (division == "2") {
                                html += '<img src="' + pop_files[n].real + '">';
                                //$('#poptimeContent > div.popImg > div.imgContent > img').prop('src', )
                                $("#poptimeContent > div.popImg > div.imgContent").append(html);
                            }
                        }
                        if (pop_nickname == myNickname) {
                            $("#poptitle > div.titleBtn").addClass("nonDisplay");
                            $("#btnContainer > div.chatBtn").addClass("nonDisplay");
                            $("#btnContainer > div.bookmarkBtn").addClass("nonDisplay");
                            $("#btnContainer > div.timelineBtn").css({
                                marginLeft: "12.5px",
                                marginRight: "9px",
                                marginTop: "10px"
                            });
                            $("#btnContainer > div.photoBtn").css({
                                marginRight: "12.5px",
                                marginLeft: "0px"
                            });
                        }
                    }
                });
            },
            best_list: function () {
                //오늘의 베스트 회원 리스트 (작업중)
                $.ajax({
                    type: "GET",
                    url: "/api/oauth/ranking",
                    dataType: "json",
                    success: function (res) {
                        console.log(res);
                        var html = "",
                            data = res.data.rank;

                        console.log(data);
                        for (var i = 0; i < data.length; i++) {
                            var img = data[i].profile,
                                nick = data[i].nickname,
                                gender = data[i].gender;
                            if (img == null) {
                                img = "https://ntalk.me/img/people_base_back.svg";
                            }
                            html += '<div class="bestUser ' + i + '">';
                            html += '<div class="bestInfo nonDisplay">';
                            if (gender == "male") {
                                html += '<div class="bestImg"><span class="gender_male"></span></div>';
                            } else if (gender == "female") {
                                html += '<div class="bestImg"><span class="gender_female"></span></div>';
                            }
                            html += '<div class="bestNick">' + nick + "</div>";
                            html += "</div>";
                            html += '<a href=""><img src="' + img + '"></a>';
                            html += "</div>";
                        }
                        $("#bestContent").append(html);
                        //bestSlider.reloadSlider();
                        var bestSlider = $(".slider").bxSlider({
                            minSlides: 9,
                            maxSlides: 9,
                            moveSlides: 1,
                            controls: true,
                            pager: false,
                            slideWidth: 130,
                            slideMargin: 10,
                            auto: true,
                            speed: 500,
                            autoHover: true,
                            responsive: true
                        });
                    }
                });
            }
        };
    })();

    _Talk = (function () {
        return {
            message: function (res, l_c) {
                // 채팅 메시지
                //메세지 전송 수신 함수
                var data = res.data.data,
                    roomId = res.data.roomId,
                    content = data.text,
                    insertRoomId = DOCUMENT.find("#roomId").val(),
                    content_length = DOCUMENT.find("#messages").length,
                    msg_box_last = DOCUMENT.find("#messages")
                    .children()
                    .last()
                    .children()
                    .children(".content")
                    .text(),
                    divisionNick = DOCUMENT.find("#myNick").val(),
                    recieveNick = res.data.from,
                    send_at = res.data.send_at,
                    recieveSendAt = send_at.substr(11, 5),
                    type = res.data.type,
                    leavedChatRoom = data.text,
                    imgType = data.type,
                    imgAdr = data.optional.thumb,
                    imgContent = '<img src="' + imgAdr + '">';

                console.log(imgAdr);
                console.log(res);
                console.log(content);
                console.log(roomId);
                console.log(msg_box_last);
                console.log(insertRoomId);
                console.log(divisionNick);
                console.log(recieveNick);
                console.log(recieveSendAt);
                console.log(window.opener);
                console.log(leavedChatRoom);
                console.log(type);
                if (insertRoomId != roomId) {
                    $("#roomId").val(roomId);
                }
                if (type != "system" || type == "undefined") {
                    if (imgType == "text") {
                        if (content.indexOf("\n") != -1) {
                            content = content.replace(/(?:\r\n|\r|\n)/g, "<br>");
                        }

                        if (divisionNick == recieveNick) {
                            if (content_length == 0 || msg_box_last != content) {
                                var html = "";
                                html += '<li class="me">';
                                html += '<div id="meContainer">';
                                html += '<div class="read">1</div>';
                                html += '<div class="content">' + content + "</div>";
                                html += "</div>";
                                html += '<div clss="sendTime">' + recieveSendAt + "</div>";
                                html += "</li>";
                                $("#messages").append(html);
                                $("#input").val("");
                            }
                        } else {
                            var html = "";
                            html += '<li class="partner">';
                            html += '<div id="partnerContainer">';
                            html += '<div class="partnerImg"><img src="https://image.flaticon.com/icons/png/512/44/44562.png"></div>';
                            html += '<div id="partnercontent">';
                            html += '<div class="partnerNick">' + recieveNick + "</div>";
                            html += '<div class="partnerContent">' + content + "</div>";
                            html += '<div class="sendTime">' + recieveSendAt + "</div>";
                            html += "</div></div>";
                            html += "</li>";
                            $("#messages").append(html);
                            $("#input").val("");
                        }
                        $("#messages").scrollTop($("#messages").prop("scrollHeight"));
                        console.log($("#input").is(":focus"));
                        if ($("#input").is(":focus") == true) {
                            _Io.sendEmit(
                                _socketChat,
                                _Io.submit_request(205, {
                                    roomId: roomId,
                                    nickName: divisionNick,
                                    target: window.target
                                })
                            );
                        } else {
                            $("#input").focus(function () {
                                //채팅 입력창 포커스 시 대화 읽음 처리
                                _Io.sendEmit(
                                    _socketChat,
                                    _Io.submit_request(205, {
                                        roomId: roomId,
                                        nickName: divisionNick,
                                        target: window.target
                                    })
                                );
                            });
                        }
                    } else if (imgType == "image") {
                        console.log("이미지 파일");
                        if (divisionNick == recieveNick) {
                            var imgHtml = "";
                            imgHtml += '<li class="me">';
                            imgHtml += '<div id="meContainer">';
                            imgHtml += '<div class="read">1</div>';
                            imgHtml += '<div class="content">' + imgContent + "</div>";
                            imgHtml += "</div>";
                            imgHtml += '<div clss="sendTime">' + recieveSendAt + "</div>";
                            imgHtml += "</li>";
                            $("#messages").append(imgHtml);
                            $("#input").val("");
                        } else {
                            var imgHtml = "";
                            imgHtml += '<li class="partner">';
                            imgHtml += '<div id="partnerContainer">';
                            imgHtml += '<div class="partnerImg"><img src="https://image.flaticon.com/icons/png/512/44/44562.png"></div>';
                            imgHtml += '<div id="partnercontent">';
                            imgHtml += '<div class="partnerNick">' + recieveNick + "</div>";
                            imgHtml += '<div class="partnerContent">' + imgContent + "</div>";
                            imgHtml += '<div class="sendTime">' + recieveSendAt + "</div>";
                            imgHtml += "</div></div>";
                            imgHtml += "</li>";
                            $("#messages").append(imgHtml);
                            $("#input").val("");
                        }
                        $("#messages").scrollTop($("#messages").prop("scrollHeight"));
                        console.log($("#input").is(":focus"));
                        if ($("#input").is(":focus") == true) {
                            _Io.sendEmit(
                                _socketChat,
                                _Io.submit_request(205, {
                                    roomId: roomId,
                                    nickName: divisionNick,
                                    target: window.target
                                })
                            );
                        } else {
                            $("#input").focus(function () {
                                //채팅 입력창 포커스 시 대화 읽음 처리
                                _Io.sendEmit(
                                    _socketChat,
                                    _Io.submit_request(205, {
                                        roomId: roomId,
                                        nickName: divisionNick,
                                        target: window.target
                                    })
                                );
                            });
                        }
                    }
                } else {
                    var html = "";
                    html += '<li class="leavedRoom">상대방이 대화방을 나갔습니다.</li>';
                    $("#messages").append(html);
                    $("#input").val("");
                }
            },
            findRoomId: function (myNickname, json_data) {
                // 채팅방 존재여부
                var target = json_data.data.target,
                    roomResult = json_data.data.result;

                pop = window.open(
                    "https://ntalk.me/main/chat",
                    "_blank",
                    "location=no, scrollbars=no, resizable=no, top=500, left=500, width=450px, height=600px, status=no, menubar=no, toolbar=no, directories=no"
                );

                pop.roomId = roomResult;
                pop.target = target;

                $("#userNick").val(target);
                console.log($("#userNick").val(target));
            },
            roomCreated: function (json_data, myNickname) {
                //채팅방이 개설 되었음을 서버에 알림
                var data = json_data.data,
                    createdId = data.roomId;
                $("#roomId").val(createdId);
                window.roomId = createdId;
                _Io.sendEmit(
                    _socketChat,
                    _Io.submit_request(207, {
                        roomId: window.roomId,
                        nickName: myNickname,
                        oppNickName: window.target
                    })
                );
            },
            userList: function (json_data, gen) {
                //실시간 접속 회원 (필크앤,블루앤) 클릭시 리스트
                loginUserInfo = json_data.data;
                console.log(loginUserInfo);
                if (gen == null) {
                    gen = "all";
                    var html = "",
                        object = loginUserInfo.filter(function (gender) {
                            return gender.gender == "female";
                        });
                } else {
                    var html = "",
                        object = loginUserInfo.filter(function (gender) {
                            return gender.gender == gen;
                        });
                }
                console.log(object);
                if (gen == "male" || gen == "female") {
                    for (var variable in object) {
                        if (object.hasOwnProperty(variable)) {
                            var data = object[variable],
                                image = data.profile_thum,
                                gameCount = parseInt(variable) + 1,
                                listCount = $("#realList")
                                .find("ul")
                                .children().length,
                                userNickname = data.nickname,
                                location1 = data.location,
                                location2 = data.location2,
                                userAge = data.age,
                                number = data.idx,
                                hash = data.hash,
                                gender = data.gender,
                                emptyImg = "//ntalk.me/img/people_base_back.svg",
                                profileImg = data.profile_thum,
                                _joinDay = "" + data.user_create_at,
                                joinDay = _joinDay.substring(0, 10),
                                joinAt = _Fn.dateDiff(currentday, joinDay);
                            console.log(location1);
                            console.log(location2);
                            console.log(profileImg);
                            if ((profileImg == null || profileImg == undefined || profileImg == "") && gender == "male") {
                                image = "//ntalk.me/img/man_profile.png";
                            } else if ((profileImg == null || profileImg == undefined || profileImg == "") && gender == "female") {
                                image = "//ntalk.me/img/girl_profile.png";
                            } else {
                                image = profileImg;
                            }

                            if (location1 == null || location1 == undefined || location1 == "") {
                                location1;
                            } else {
                                location1 = "#" + location1;
                            }

                            if (location2 == null || location2 == undefined || location2 == "") {
                                location2;
                            } else {
                                location2 = "#" + location2;
                            }

                            console.log(listCount);
                            if (userAge == -1) {
                                userAge = "";
                            } else {
                                userAge = "(" + userAge + ")";
                            }

                            if (listCount == 0) {
                                gameCount = gameCount;
                            } else {
                                number = parseInt(
                                    $("#realList")
                                    .find("ul")
                                    .children()
                                    .last()
                                    .attr("class")
                                    .replace(/[^0-9]/g, "")
                                );
                                gameCount = number + gameCount;
                            }
                            console.log(data);
                            html += '<li class="number ' + gameCount + '">';
                            html += '<div id="userInfo">';
                            html += '<div class="userImg">';
                            html += '<img src="' + image + '">';
                            html += "</div>";
                            html += '<div class="number nonDisplay">' + number + "</div>";
                            html += '<div class="infoContainer">';
                            html += '<div class="userId" title="아이디">' + userNickname + "</div>";
                            if (gender == "female") {
                                html += '<div class="userAge woman" title="나이">' + userAge + "</div>";
                            } else {
                                html += '<div class="userAge man" title="나이">' + userAge + "</div>";
                            }
                            html += '<div class="connectUser"><p>ON</p></div>';
                            if (joinAt >= 10) {
                                html += '<div class="newUser nonDisplay"><p>NEW</p></div>';
                            } else {
                                html += '<div class="newUser"><p>NEW</p></div>';
                            }
                            html += '<div class="hotUser"><p>HOT</p></div>';
                            html += "</div>";
                            html += '<div class="locationInfo">';
                            html += '<div class="firstLocation">' + location1 + "</div>";
                            html += '<div class="secondLocation">' + location2 + "</div>";
                            html += "</div>";
                            html += '<input type="hidden" id="userNick" name="userNick">';
                            html += '<input type="hidden" id="hash" name="hash" value="' + hash + '">';
                            html += "</div>";
                            html += "</li>";
                        }
                    }
                    $("#realList")
                        .find("ul")
                        .append(html);
                } else {
                    for (var variable in object) {
                        if (object.hasOwnProperty(variable)) {
                            var data = object[variable],
                                image = null,
                                gameCount = parseInt(variable) + 1,
                                listCount = $("#realList")
                                .find("ul")
                                .children().length,
                                userNickname = data.nickname,
                                location1 = data.location,
                                location2 = data.location2,
                                userAge = data.age,
                                number = data.idx,
                                hash = data.hash,
                                gender = data.gender,
                                emptyImg = "//ntalk.me/img/people_base_back.svg",
                                profileImg = data.profile_thum,
                                _joinDay = "" + data.user_create_at,
                                joinDay = _joinDay.substring(0, 10),
                                joinAt = _Fn.dateDiff(currentday, joinDay);
                            console.log(data);
                            console.log(joinDay);
                            console.log(location1);
                            console.log(location2);
                            console.log(profileImg);
                            if ((profileImg == null || profileImg == undefined || profileImg == "") && gender == "male") {
                                image = "//ntalk.me/img/man_profile.png";
                            } else if ((profileImg == null || profileImg == undefined || profileImg == "") && gender == "female") {
                                image = "//ntalk.me/img/girl_profile.png";
                            } else {
                                image = profileImg;
                            }

                            if (location1 == null || location1 == undefined || location1 == "") {
                                location1;
                            } else {
                                location1 = "#" + location1;
                            }

                            if (location2 == null || location2 == undefined || location2 == "") {
                                location2;
                            } else {
                                location2 = "#" + location2;
                            }

                            console.log(listCount);
                            if (userAge == -1) {
                                userAge = "";
                            } else {
                                userAge = "(" + userAge + ")";
                            }

                            if (listCount == 0) {
                                gameCount = gameCount;
                            } else {
                                number = parseInt(
                                    $("#realList")
                                    .find("ul")
                                    .children()
                                    .last()
                                    .attr("class")
                                    .replace(/[^0-9]/g, "")
                                );
                                gameCount = number + gameCount;
                            }
                            console.log(data);
                            html += '<li class="number ' + gameCount + '">';
                            html += '<div id="userInfo">';
                            html += '<div class="userImg">';
                            html += '<img src="' + image + '">';
                            html += "</div>";
                            html += '<div class="number nonDisplay">' + number + "</div>";
                            html += '<div class="infoContainer">';
                            html += '<div class="userId" title="아이디">' + userNickname + "</div>";
                            if (gender == "female") {
                                html += '<div class="userAge woman" title="나이">' + userAge + "</div>";
                            } else {
                                html += '<div class="userAge man" title="나이">' + userAge + "</div>";
                            }
                            html += '<div class="connectUser"><p>ON</p></div>';
                            if (joinAt >= 10) {
                                html += '<div class="newUser nonDisplay"><p>NEW</p></div>';
                            } else {
                                html += '<div class="newUser"><p>NEW</p></div>';
                            }
                            html += '<div class="hotUser"><p>HOT</p></div>';
                            html += "</div>";
                            html += '<div class="locationInfo">';
                            html += '<div class="firstLocation">' + location1 + "</div>";
                            html += '<div class="secondLocation">' + location2 + "</div>";
                            html += "</div>";
                            html += '<input type="hidden" id="userNick" name="userNick" value="' + userNickname + '">';
                            html += '<input type="hidden" id="hash" name="hash" value="' + hash + '">';
                            html += "</div>";
                            html += "</li>";
                        }
                    }
                    $("#realList")
                        .find("ul")
                        .append(html);
                    console.log(loginUserInfo);
                    setTimeout(function () {
                        var content_length = $("#timeLineContainer ul li").length;
                        for (var n = 0; n < content_length; n++) {
                            var contentNick = $("#timeLineContainer ul li")
                                .eq(n)
                                .find("#userNickname");
                            console.log($("#timeLineContainer ul li").eq(n));
                            console.log(contentNick);
                            console.log(contentNick.val());
                            //console.log(nick);
                            console.log($("#timeLineContainer > ul").children());
                            for (var s = 0; s < loginUserInfo.length; s++) {
                                console.log(loginUserInfo[s].nickname);
                                if (contentNick.val() == loginUserInfo[s].nickname) {
                                    console.log(
                                        contentNick
                                        .siblings(".main_info")
                                        .children("#userInfo")
                                        .children(".connectUser")
                                    );
                                    console.log(contentNick.val());
                                    console.log(loginUserInfo[s].nickname);
                                    contentNick
                                        .siblings(".main_info")
                                        .children("#userInfo")
                                        .children(".connectUser")
                                        .removeClass("nonDisplay");
                                }
                            }
                        }
                    }, 2000);
                }
            },
            history: function (json_data, myNickname) {
                //채팅내역 불러오기 함수
                console.log(json_data);
                var data = json_data.data,
                    data_result = data.data,
                    roomId = data.roomId,
                    data_length = data_result.length,
                    valueHtml = [],
                    myNick = myNickname;

                console.log(data_result);
                console.log(roomId);
                console.log(data_length);
                console.log(myNick);
                $("#roomId").val(roomId);
                for (var i = 0; i < data_length; i++) {
                    var html = "",
                        //realHtml = [],
                        dataNick = data_result[i].from,
                        receiveSendAt = data_result[i].send_at.substr(11, 5),
                        content = data_result[i].data.text,
                        confirmResult = data_result[i].confirm,
                        confirm_at = data_result[i].confirm_at,
                        type = data_result[i].data.type,
                        order = data_result[i].order,
                        imgAdr = data_result[i].data.image,
                        imgContent = '<img src="' + imgAdr + '">';

                    console.log(content);
                    console.log(receiveSendAt);
                    console.log(dataNick);
                    console.log(myNick == dataNick);
                    console.log(order);
                    console.log(type);
                    console.log(imgAdr);
                    if (type == "text") {
                        console.log(content);
                        if (content.indexOf("\n") != -1) {
                            content = content.replace(/(?:\r\n|\r|\n)/g, "<br>");
                            console.log(content);
                        }

                        if (myNick == dataNick) {
                            html += '<li class="me">';
                            html += '<input type="hidden" id="order_val" value="' + order + '">';
                            html += '<div id="meContainer">';
                            html += '<div class="read">1</div>';
                            html += '<div class="content">' + content + "</div>";
                            html += "</div>";
                            html += '<div clss="sendTime">' + receiveSendAt + "</div>";
                            html += "</li>";
                            valueHtml.push(html);
                            // $('#messages').append(html);
                            // if (confirmResult == true) {
                            //     $('#meContainer .read').addClass('nonDisplay');
                            // }
                        } else {
                            html += '<li class="partner">';
                            html += '<input type="hidden" id="order_val" value="' + order + '">';
                            html += '<div id="partnerContainer">';
                            html += '<div class="partnerImg"><img src="https://image.flaticon.com/icons/png/512/44/44562.png"></div>';
                            html += '<div id="partnercontent">';
                            html += '<div class="partnerNick">' + dataNick + "</div>";
                            html += '<div class="partnerContent">' + content + "</div>";
                            html += '<div class="sendTime">' + receiveSendAt + "</div>";
                            html += "</div>";
                            html += "</div>";
                            html += "</li>";
                            valueHtml.push(html);
                            // $('#messages').append(html);
                            // if (confirm_at == null) {
                            //     _Io.sendEmit(_socketChat, _Io.submit_request(205, {
                            //         roomId: roomId,
                            //         nickName: myNick,
                            //         target: window.target
                            //     }));
                            // }
                        }
                    } else if (type == "image") {
                        if (myNick == dataNick) {
                            html += '<li class="me">';
                            html += '<input type="hidden" id="order_val" value="' + order + '">';
                            html += '<div id="meContainer">';
                            html += '<div class="read">1</div>';
                            html += '<div class="content">' + imgContent + "</div>";
                            html += "</div>";
                            html += '<div clss="sendTime">' + receiveSendAt + "</div>";
                            html += "</li>";
                            valueHtml.push(html);
                            // $('#messages').append(html);
                            // if (confirmResult == true) {
                            //     $('#meContainer .read').addClass('nonDisplay');
                            // }
                        } else {
                            html += '<li class="partner">';
                            html += '<input type="hidden" id="order_val" value="' + order + '">';
                            html += '<div id="partnerContainer">';
                            html += '<div class="partnerImg"><img src="https://image.flaticon.com/icons/png/512/44/44562.png"></div>';
                            html += '<div id="partnercontent">';
                            html += '<div class="partnerNick">' + dataNick + "</div>";
                            html += '<div class="partnerContent">' + imgContent + "</div>";
                            html += '<div class="sendTime">' + receiveSendAt + "</div>";
                            html += "</div>";
                            html += "</div>";
                            html += "</li>";
                            valueHtml.push(html);
                            // $('#messages').append(html);
                            // if (confirm_at == null) {
                            //     _Io.sendEmit(_socketChat, _Io.submit_request(205, {
                            //         roomId: roomId,
                            //         nickName: myNick,
                            //         target: window.target
                            //     }));
                            // }
                        }
                    }
                }
                var msg_length = $("#messages").children().length;
                $("#messages").prepend(valueHtml);
                if (confirmResult == true) {
                    $("#meContainer .read").addClass("nonDisplay");
                } else if (confirm_at == null) {
                    _Io.sendEmit(
                        _socketChat,
                        _Io.submit_request(205, {
                            roomId: roomId,
                            nickName: myNick,
                            target: window.target
                        })
                    );
                }
                console.log(msg_length);
                $("#input").val("");
                if (msg_length < 1) {
                    $("#messages").append(_Talk.chatNotice());
                }
                var _this = $(this),
                    scrollT = _this.scrollTop(),
                    lastChildren = $("#messages")
                    .children()
                    .last()
                    .attr("class");
                console.log(lastChildren);
                if (lastChildren == "ntc" && msg_length < 50) {
                    console.log("ntc 후 스크롤 자동계산");
                    $("#messages").scrollTop($("#messages").prop("scrollHeight"));
                }
            },
            chatNotice: function () {
                var notice = "";
                notice += '<li class="ntc">';
                notice += '<div class="ntcContent">';
                notice += "<span>[건전한 대화를 위한 안내]<br>";
                notice += "채팅방을 통한 의약품 불법판매, 동반자살 모의, 음란행위, 성매매, 청소년이하 연령에<br>";
                notice += "유해한 대화내용이 발견 될 경우 서비스 이용정지 조치를 취하므로 채팅간 반드시<br>";
                notice += "유의하시기 바랍니다.</span>";
                notice += "</div>";
                notice += "</li>";
                return notice;
            },
            typing: function (json_data) {
                //채팅 입력중
                var myNick = $("#myNick").val(),
                    cmd = json_data.cmd,
                    dataMyNick = json_data.data.nickName;
                if (myNick != dataMyNick && cmd == 201) {
                    $(".message_content")
                        .find(".typing")
                        .removeClass("nonDisplay");
                } else {
                    $(".message_content")
                        .find(".typing")
                        .addClass("nonDisplay");
                }
            },
            resize: function (url) {
                //채팅방 이미지 클릭 시 확대보기
                //이미지 클릭시 상세보기 이미지 사이즈 계산후 새창 오픈
                var img1, imgWidth, imgHeight, winOption, imgWin;
                img1 = new Image();
                img1.src = url;
                console.log(img1);
                //if (img1.width != 0 && img1.height != 0) {
                imgWidth = img1.width + 23;
                imgHeight = img1.height;
                winOption = "width=" + imgWidth + ", height=" + imgHeight + ", scroolbars = yes";
                imgWin = window.open("", "", winOption);
                imgWin.document.write("<html><head><title>이미지 상세보기</title></head>");
                imgWin.document.write("<body topmargin=0 leftmargin=0>");
                imgWin.document.write("<img src=" + url + " onclick='self.close()' style='cursor:pointer;'>");
                //imgWin.document.close();
                //}
                // var img, img_width, win_width, img_height, win, openWindow;
                // img = new Image();
                // img.src = url;
                // img_width = img.width;
                // win_width = img_width + 25;
                // img_height = img.height;
                // win = img_height + 30;
                // openWindow = window.open("", "_blank", "width=" + win_width + ", height=" + win + ", menubars=no, scrollbars=auto");
                // console.log(win_width);
                // console.log(win);
                // openWindow.document.write("<style>body{margin:0px;}</style><img src='" + url + " 'width='" + win_width + " 'height=' " + win + " 'onclick='self.close()' style='cursor:pointer'>");
                //openWindow.document.close();
            }
        };
    })();

    /**************************************** 소켓 라운지 연결  *****************************************/
    _Io.connectLounge(); //소켓 라운지 연결
    _Io.user_info(); // 윈도우창 오픈 후 로그인 사용자 데이터 리턴
    _Io.best_list(); // 오늘의 베스트 회원 리스트
    //$(window).load(function () {
    /* var bestSlider = $(".slider").slick({
        infinite: true,
        slidesToShow: 9,
        slidesToScroll: 1
    }); */
    /* $("#bestContainer")
        .find(".bx-wrapper .bx-controls")
        .on("click", ".bx-controls-direction .bx-prev", function () {
            console.log("bx_slider  이전 클릭");
            bestSlider.goToPrevSlide();
            return false;
        })
        .on("click", ".bx-controls-direction .bx-next", function () {
            console.log("bx_slider  다음 클릭");
            bestSlider.goToNextSlide();
            return false;
        }); */
    //});



    /**************************** // 타임라인 글등록 / 수정 / 삭제 / 프로필 / 신고하기 대화하기이벤트***************/
    var _this_text = 1,
        location_true = location.href == "https://ntalk.me/timeline",
        location_true2 = location.href == "https://ntalk.me/",
        location_true3 = location.href == "https://ntalk.me/main";
    console.log(location_true2);
    console.log(location.href);
    if (location_true == true || location_true2 == true || location_true3 == true) {
        _Fx.timeLineList(_this_text); // 타임라인 리스트, 자동 스크롤 첫페이지 입력 이벤트
    }

    /******************************* 타임라인 자동스크롤 이벤트 ******************************************/

    if (location_true == true || location_true2 == true) {
        $(window).data("ajaxready", true);
        TIMELIST.find("#plusView").on("click", "div", function () {
            console.log($(window).data("ajaxready"));
            if ($(window).data("ajaxready") == false) return;
            var _this = $(this),
                select_offset = _this.offset(),
                position_top = select_offset.top - 500,
                position_left = select_offset.left + 220;
            _this_text++;
            $(window).data("ajaxready", false);
            _Fx.timeLineList(_this_text, position_top, position_left); // 타임라인 리스트, 자동 스크롤
        });
    }
    /*****************************타임라인 필터 지역등 옵션값 추가  ********************************************/

    var timeline_adr = $("#timeLineContainer"),
        timeline_append_adr = timeline_adr.find(".tab .timeFilter .locationContainer"),
        timeline_text = "지역",
        timeline_text2 = "상세지역";
    _Fx.timelineLocation_json(timeline_adr, timeline_append_adr, timeline_text, timeline_text2);
    var minAdr = $("#timeLineContainer").find(".tab .timeFilter .beforeAge select"),
        maxAdr = $("#timeLineContainer").find(".tab .timeFilter .afterAge select");
    _Fx.selectOptionHtml(minAdr, maxAdr);

    /**************************************** 타임라인 이벤트 ***********************************************/

    DOCUMENT.on("click", "#popupProfile div .profileEvent div a", function () {
            //다시 해야도ㅓㅣㅁ
            //대화걸기

            $("#popupProfile").remove();
            console.log(loginUserInfo);
            var _this = $(this),
                selectUserId = _this
                .parents(".profileEvent")
                .siblings(".profileInfo")
                .children(".profileId")
                .text(),
                selectUserImg = _this
                .parents(".profileEvent")
                .siblings(".profileInfo")
                .children(".profileImg")
                .children("img")
                .attr("src");
            console.log(selectUserId);
            console.log(myNickname);
            console.log(selectUserImg);
            console.log(selectUserNickname);
            console.log(location.href);
            if (myNickname == selectUserId) {
                alert("대화상대를 확인해 주세요.");
                return false;
            } else {
                _Io.sendEmit(
                    _socket,
                    _Io.submit_request(211, {
                        user1: myNickname,
                        user2: selectUserId,
                        profile: selectUserImg
                    })
                );
            }
        })
        .on("mouseenter", "#timeLineContainer ul li .ImgContainer", function () {
            //타임라인 이미지 애니메이션
            console.log("mouseenter");
            console.log($(this));
            console.log($(this).find(".twoImg"));
            $(this)
                .find(".twoImg")
                .animate({
                        left: "86px"
                    },
                    300
                );
            $(this)
                .find(".threeImg")
                .animate({
                        left: "21px"
                    },
                    300
                );
            $(this)
                .find(".fourImg")
                .animate({
                        left: "-44px"
                    },
                    300
                );
            $(this)
                .find(".fiveImg")
                .animate({
                        left: "-109px"
                    },
                    300
                );
        })
        .on("mouseleave", "#timeLineContainer ul li .ImgContainer", function () {
            //타임라인 이미지 애니메이션
            console.log("mouseleave");
            $(this)
                .find(".twoImg")
                .animate({
                        left: "138px"
                    },
                    300
                );
            $(this)
                .find(".threeImg")
                .animate({
                        left: "125px"
                    },
                    300
                );
            $(this)
                .find(".fourImg")
                .animate({
                        left: "112px"
                    },
                    300
                );
            console.log($(this).find(".fiveImg"));
            $(this)
                .find(".fiveImg")
                .animate({
                        left: "99px"
                    },
                    300
                );
        })
        .on("click", "#timeLineContainer ul li .main_info .contentContianer", function () {
            //타임라인 본문 클릭 (프로필 팝업 오픈 이벤트)
            var select_nick = $(this)
                .parents(".main_info")
                .siblings("#userNickname")
                .val(),
                profilePop = window.open(
                    "https://ntalk.me/pop/timeline?nick=" + select_nick,
                    "::설레이는 시간, 앤톡::",
                    "location=no, scrollbars=no, resizable=no, top=500, left=500, width=860px, height=640px, status=no, menubar=no, toolbar=no, directories=no"
                );
            console.log(decodeURIComponent(select_nick));
            console.log(myNickname);
            //opener.document.title = "::설레이는 시간, 앤톡::";
        })
        .on("click", "#writeTime", function () {
            // 타임라인 등록 (글쓰기) 클릭
            var url = "/api/Timeline/timeline_presence",
                _this = $(this);

            _Fx.timelineWrite_ajax(url, _this);
        })
        .on("click", "#wirtePopupContainer > div.wpopTitle > div.closeBtn", function () {
            //새로쓰기 수정 여부 닫기 버튼
            $(this)
                .parents("#wirtePopupContainer")
                .remove();
        });

    TIMELIST.find(".tab")
        .on("click", ".timeFilter .timeSearch .searchBtn", function () {
            //타임라인 필터 검색버튼
            var _this = $(this),
                fLocation = TIMELIST.find("#first option:selected").val(),
                sLocation = TIMELIST.find("#second option:selected").val(),
                minAge = TIMELIST.find(".beforeAge select option:selected").val(),
                maxAge = TIMELIST.find(".afterAge select option:selected").val(),
                gen = TIMELIST.find(".gender select option:selected").val(),
                select_offset = _this.offset(),
                position_top = select_offset.top - 500,
                position_left = select_offset.left + 220,
                data_value = null,
                _this_text = null;
            data_value = {
                location1: fLocation,
                location2: sLocation,
                minAge: minAge,
                maxAge: maxAge,
                gender: gen
            };
            $("#timeLineContainer > ul")
                .children()
                .remove();
            _Fx.timeLineList(_this_text, position_top, position_left, data_value);
        })
        .on("click", ".timeTitle .plus_view", function () {
            //타임라인 필터 초기화
            var flocation_adr = TIMELIST.find("#first")
                .children()
                .first(),
                slocation_adr = TIMELIST.find("#second")
                .children()
                .first(),
                minAge_adr = TIMELIST.find(".timeFilter .beforeAge select")
                .children()
                .first(),
                maxAge_adr = TIMELIST.find(".timeFilter .afterAge select")
                .children()
                .first(),
                gen_adr = TIMELIST.find(".timeFilter .gender select")
                .children()
                .first();
            flocation_adr.prop("selected", true);
            slocation_adr.prop("selected", true);
            minAge_adr.prop("selected", true);
            maxAge_adr.prop("selected", true);
            gen_adr.prop("selected", true);

            $("#timeLineContainer > ul")
                .children()
                .remove();
            _Fx.timeLineList(_this_text);
        })
        .on("change", ".timeFilter div select", function () {
            //타임라인 필터 선택시 selected값 주기
            $(this)
                .find("option:selected")
                .attr("selected", "selected");
            $(this)
                .find("option:selected")
                .siblings()
                .attr("selected", false);
        });

    /************************************* 타임라인 글쓰기 페이지 이벤트 *******************************/
    var location_result = location.href == "https://ntalk.me/timeline/write",
        location_result2 = location.href == "https://ntalk.me/timeline/edit";
    if (location_result == true || location_result2 == true) {
        var timewirte_adr = $("#timeWrite #contentContainer"),
            timewrite_append_adr = timewirte_adr.find(".locationTag"),
            timewrite_text = "전체지역",
            timewrite_text2 = "전체지역";
        _Fx.timelineLocation_json(timewirte_adr, timewrite_append_adr, timewrite_text, timewrite_text2);
        var write_minAdr = $("#timeWrite #contentContainer").find(".ageTag .beforeAge select"),
            write_maxAdr = $("#timeWrite #contentContainer").find(".ageTag .afterAge select");
        _Fx.selectOptionHtml(write_minAdr, write_maxAdr);
    }
    DOCUMENT.find("#mainContent")
        .on("click", "#titleContainer .closeBtn", function () {
            // 타임라인 등록창 삭제
            $("#writepopup").remove();
        })
        .on("click", "#timeLineAdd .genderTag div", function () {
            //타임라인 등록창 성별 선택
            var click_val = null;
            $(this)
                .siblings()
                .removeClass("genderPick");
            $(this).addClass("genderPick");
            if ($(this).text() == "모두") {
                click_val = "all";
            } else if ($(this).text() == "여자") {
                click_val = "female";
            } else if ($(this).text() == "남자") {
                click_val = "male";
            }
            console.log($(this));
            console.log($(this).val());
            console.log(click_val);
            $('#timeLineAdd .genderTag input[type="hidden"]').val(click_val);
            console.log($('#timeLineAdd .genderTag input[type="hidden"]').val());
        })
        .on("change", "#file_img", function (e) {
            //타임라인 사진 추가
            //$("#preview").empty();

            var files = e.target.files;
            var arr = Array.prototype.slice.call(files);

            //업로드 가능 파일인지 체크
            for (var i = 0; i < files.length; i++) {
                if (!_Fx.checkExtension(files[i].name, files[i].size)) {
                    return false;
                }
            }

            _Fx.preview(arr);
        })
        .on("click", "#timeLineAdd .imgTag .imgUp .closeBtn", function () {
            //타임라인 등록 (이미지 삭제버튼)
            var hidden_val = $(this).siblings('input[type="hidden"]').length,
                hidden_adr = $(this).siblings('input[type="hidden"]');
            console.log($(this).siblings('input[type="hidden"]').length);
            if (hidden_val == 0) {
                $(this)
                    .parent(".imgUp")
                    .remove();
            } else {
                var html = "";
                html += '<input type="hidden" name="remove[]" value="' + hidden_adr.val() + '">';
                $("#submitContainer").append(html);
                $(this)
                    .parent(".imgUp")
                    .remove();
            }
            var imgLength = $("#timeLineAdd .imgTag .imgUp").length;
            console.log(imgLength);
            for (var i = 0; i < imgLength; i++) {
                $("#timeLineAdd .imgTag")
                    .children()
                    .eq(i + 1)
                    .removeClass()
                    .addClass("imgUp " + (i + 1));
            }
        })
        .on("click", "#wirtePopupContainer .wpopBtn .wpopNew", function () {
            //팝업 새로쓰기 클릭

            var text = "등록하기",
                url = "/api/Timeline/timeline_insert";
            $("#wirtePopupContainer").remove();
            _Fx.timeline_write_edit_html(text, url);
            $("#writepopup").css({
                width: $(document).width(),
                height: $(document).height()
            });
            $("#timeWrite").css({
                top: "448px",
                left: "28%"
            });
            console.log($("#mainContent").length);
            var timeline_adr = $("#writepopup"),
                timeline_append_adr = timeline_adr.find("#timeLineAdd > div.locationTag"),
                timeline_text = "지역",
                timeline_text2 = "상세지역";
            _Fx.timelineLocation_json(timeline_adr, timeline_append_adr, timeline_text, timeline_text2);
            var minAdr = $("#timeLineAdd").find(".ageTag .beforeAge select"),
                maxAdr = $("#timeLineAdd").find(".ageTag .afterAge select");
            _Fx.selectOptionHtml(minAdr, maxAdr);
        })
        .on("click", "#wirtePopupContainer .wpopBtn .wpopLoad", function () {
            // 타임라인 수정 클릭

            console.log(timeline_data);
            var data = timeline_data,
                title = data.title,
                fGender = data.fgender,
                flocation1 = data.flocation,
                flocation2 = data.flocation2,
                minAge = data.minAge,
                maxAge = data.maxAge,
                comment = data.comment,
                files = data.files;
            var text = "수정하기",
                url = "/api/Timeline/timeline_edit";
            $("#wirtePopupContainer").remove();
            _Fx.timeline_write_edit_html(text, url, files);
            $("#writepopup").css({
                width: $(document).width(),
                height: $(document).height()
            });
            $("#timeWrite").css({
                top: "448px",
                left: "28%"
            });
            var timeline_adr = $("#writepopup"),
                timeline_append_adr = timeline_adr.find("#timeLineAdd > div.locationTag"),
                timeline_text = "지역",
                timeline_text2 = "상세지역";
            _Fx.timelineLocation_json(timeline_adr, timeline_append_adr, timeline_text, timeline_text2, flocation1, flocation2);
            var minAdr = $("#timeLineAdd").find(".ageTag .beforeAge select"),
                maxAdr = $("#timeLineAdd").find(".ageTag .afterAge select");
            _Fx.selectOptionHtml(minAdr, maxAdr, minAge, maxAge);

            console.log(files);
            $("#contentTitle")
                .find("#conTitle")
                .val(title);
            if (fGender == "male") {
                $("#timeLineAdd")
                    .find(".genderTag .male")
                    .addClass("genderPick");
                $("#timeLineAdd")
                    .find('.genderTag input[type="hidden"]')
                    .val(fGender);
            } else if (fGender == "female") {
                $("#timeLineAdd")
                    .find(".genderTag .female")
                    .addClass("genderPick");
                $("#timeLineAdd")
                    .find('.genderTag input[type="hidden"]')
                    .val(fGender);
            } else if (fGender == "all") {
                $("#timeLineAdd")
                    .find(".genderTag .all")
                    .addClass("genderPick");
                $("#timeLineAdd")
                    .find('.genderTag input[type="hidden"]')
                    .val(fGender);
            }
            $("#time_input").val(comment);
        });
    DOCUMENT.on("change", "#writepopup #first", function () {
            // 타임라인 수정 1차 지역 재선택시 이벤트
            var adr = $("#writepopup"),
                text = "지역";
            adr.find("#first option:selected").attr("selected", "selected");
            adr.find("#first option:selected")
                .siblings()
                .attr("selected", false);
            if (adr.find("#first option:selected").text() == text) {
                adr.find("#second .noDel").prop("selected", "selected");
                adr.find("#second")
                    .children()
                    .not(".noDel")
                    .remove();
            } else {
                _Fx.timelineLocationSecond(locationData, adr);
            }
        })
        .on("change", "#writepopup #second", function () {
            // 타임라인 수정 2차 지역 재선택시 이벤트
            var adr = $("#writepopup");
            adr.find("#second option:selected").attr("selected", "selected");
            adr.find("#second option:selected")
                .siblings()
                .attr("selected", false);
        })
        .on("keyup", "#time_input", function () {
            console.log($(this).val().length);
            if ($(this).val().length > 1000) {
                alert("내용은 1000자 까지 가능 합니다.");
                $(this).val(
                    $(this)
                    .val()
                    .substring(0, 1000)
                );
            }
        })
        .on("mouseenter", "#iconContainer .view", function () {
            //타임라인 툴팁 (조회수)
            var _this = $(this),
                viewNumber = _this.children(".viewNumber").text(),
                this_offset = _this.offset(),
                this_top = this_offset.top - 33,
                this_left = this_offset.left - 15;
            console.log(this_offset);
            console.log(this_top);
            console.log(this_left);
            _this.children("span").addClass("vw");
            $("body").append('<div id="tooltip"><div id="tooltipContent">조회수 : ' + viewNumber + "</div></div>");
            $("#tooltip").css({
                top: this_top,
                left: this_left,
                position: "absolute"
            });
        })
        .on("mouseleave", "#iconContainer .view", function () {
            // 툴팁 (조회수) 삭제
            $(this)
                .children("span")
                .removeClass("vw");
            $("#tooltip").remove();
        })
        .on("mouseenter", "#iconContainer .bookmark", function () {
            // 타임라인 툴팁 (즐겨찾기)
            var _this = $(this),
                this_offset = _this.offset(),
                this_top = this_offset.top - 33,
                this_left = this_offset.left - 11;
            $(this)
                .children()
                .addClass("bo");
            $("body").append('<div id="tooltip"><div id="tooltipContent">즐겨찾기</div></div>');
            $("#tooltip").css({
                top: this_top,
                left: this_left,
                position: "absolute"
            });
        })
        .on("mouseleave", "#iconContainer .bookmark", function () {
            // 툴팁 (즐겨찾기) 삭제
            $(this)
                .children()
                .removeClass("bo");
            $("#tooltip").remove();
        })
        .on("mouseenter", "#iconContainer .talking", function () {
            // 타임라인 툴팁 (대화하기)
            var _this = $(this),
                this_offset = _this.offset(),
                this_top = this_offset.top - 33,
                this_left = this_offset.left - 11;
            $(this)
                .children()
                .addClass("ta");
            $("body").append('<div id="tooltip"><div id="tooltipContent">대화하기</div></div>');
            $("#tooltip").css({
                top: this_top,
                left: this_left,
                position: "absolute"
            });
        })
        .on("mouseleave", "#iconContainer .talking", function () {
            // 툴팁 (대화하기) 삭제
            $(this)
                .children()
                .removeClass("ta");
            $("#tooltip").remove();
        });
    /*   DOCUMENT.find("#writepopup").on("change", ".#file_img", function () {
          //타임라인 사진파일 첨부 이벤트
          alert("사진첨부 유효성 검사");
          var img_length = $("#contentContainer").find(".imgTag").children().length,
              ext = $(this).val().split(".").pop().toLowerCase();

          if ($.inArray(ext, ["gif", "jpg", "jpeg", "png", "bmp"]) == -1) {
              alert("gif, jpg, jpeg, png, bmp 파일만 업로드 해주세요.");
              $("input[id=file_img]").val("");
              return;
          }

          var fileSize = this.files[0].size;
          var maxSize = 5 * 1024 * 1024;
          if (fileSize > maxSize) {
              alert("파일용량을 초과하였습니다.");
              $(this).val("");
              return;
          } else {
              if (img_length < 5) {
                  var html = "";
                  html += '<input type="file" id="file_img" name="img[]">';

                  $("#contentContainer")
                      .find(".imgTag")
                      .append(html);
              }
          }
      }); */
    /*  DOCUMENT.find("#file_img1").change(function (e) {
         $('#preview').empty();

         var files = e.target.files;
         var arr = Array.prototype.slice.call(files);

         //업로드 가능 파일인지 체크
         for (var i = 0; i < files.length; i++) {
             if (!_Fx.checkExtension(files[i].name, files[i].size)) {
                 return false;
             }
         }

         _Fx.preview(arr);
     }); */

    /*********************************** 메인페이지 이벤트 ********************************************/

    $("#aside")
        .on("click", "#userInfo .userImg", function () {
            //메인 페이지 프로필 클릭 팝업 생성
            var _this = $(this),
                USERLIST = $("#userList");

            _Fx.profilePopup(_this, USERLIST);
        })
        .on("click", "#realTimeListContainer .genderSortation .blue", function () {
            //유저 실시간 접속(블루앤 클릭)
            var gender_data = "male";
            userlist_gender = "male";
            _Fx.realUserList(gender_data);
        })
        .on("click", "#realTimeListContainer .genderSortation .pink", function () {
            //유저 실시간 접속(핑크앤 클릭)
            var gender_data = "female";
            userlist_gender = "female";
            _Fx.realUserList(gender_data);
        });

    $("#mainContent")
        .on("mouseenter mouseleave", "#navibar div", function () {
            $(this)
                .children("a")
                .toggleClass("timecolor");
            $(this).toggleClass("timecolor");
        })
        .on("mouseenter mouseleave", "#bestContent div", function () {
            $(this)
                .children(".bestInfo")
                .toggleClass("nonDisplay");
        });
    $("#naviContainer").on("click", "#navibar > div.mainLoginContainer > div.logoutPanel > div.logout", function () {
        /* _server = "wss://ntalk.me:8443/Lounge";
        _socket = io(_server, {
            transports: ["websocket"]
        });

        _socket.on("disconnect", function() {
            console.log("you have been disconnected");
        });
        $.ajax({
            type: "POST",
            url: "/api/oauth/logout",
            success: function(res) {
                console.log(res);
            }
        }); */
        //s_socket.disconnect();
        _socket.on("disconnect", function () {
            console.log("you have been disconnected");
        });
    });

    /********************************** 마이페이지 이벤트  ***********************************************/
    /*********** 마이페이지 핸드폰 입력 자동 하이픈 **********/
    DOCUMENT.on("keydown", "#afterPh", function (event) {
        var this_text = $(this);
        _Fn.live_hyphen(this_text);
    }); //아이디 찾기 핸드폰 번호 하이픈
    var location_t = location.href == "https://ntalk.me/auth/mypage",
        url = "/api/oauth/getUserInfo";
    console.log(location_t);
    $(window).load(function () {
        // 마이페이지 메인정보 입력
        if (location_t == true) {
            _Fx.mypageInfo(url);
        }
    });
    $(window).resize(function () {
        // 마이페이지 프로필변경 레이어 팝업 리사이즈 이벤트
        console.log($(document).width());
        $("#memberInfoBack").css({
            width: $(window).width(),
            height: $(window).height()
        });
        var do_width = $(window).width(),
            layer_width = $("#memberInfoLayer").width(),
            real_width = (do_width - layer_width) / 2;
        console.log(real_width);
        $("#memberInfoLayer").css("left", real_width);
    });
    $(window).resize(function () {
        // 마이페이지 핸드폰 휴대폰 번호 변경 레이어 팝업 리사이즈 이벤트
        console.log($(document).width());
        $("#phChangeBack").css({
            width: $(window).width(),
            height: $(window).height()
        });
        var do_width = $(window).width(),
            layer_width = $("#phChangeLayer").width(),
            real_width = (do_width - layer_width) / 2;
        console.log(real_width);
        $("#phChangeLayer").css("left", real_width);
    });

    /* $("#myPage").on("click", "#mytimeline .myTimeContent", function () {
        var url = "/api/Timeline/timeline_presence",
            _this = $(this);

        _Fx.timelineWrite_ajax(url, _this);
    }); */
    $("#mainContent")
        .on("click", "#mytimeline > div.myTime > div.myTimeContent > div.myTimeDelBtn", function () {
            //마이페이지 타임라인 삭제키
            var result = $("#mytimeline > div > input[type = hidden]").val(),
                url = "/api/Timeline/timeline_delete",
                ajax_data = {
                    content_id: result
                };
            console.log(result);
            _Fx.timeline_del_ajax(url, ajax_data);
        })
        .on("click", "#mytimeline > div.myTime > div.myTimeContent > div.myTimeEditBtn", function () {
            // 마이페이지 타임라인 수정 클릭
            var url = "/api/Timeline/timeline_presence",
                _this = $(this);
            $.ajax({
                type: "POST",
                url: url,
                success: function (res) {
                    console.log(res);
                    var data = res.data,
                        timeline_data = data;
                    console.log(timeline_data);
                    var data = timeline_data,
                        title = data.title,
                        fGender = data.fgender,
                        flocation1 = data.flocation,
                        flocation2 = data.flocation2,
                        minAge = data.minAge,
                        maxAge = data.maxAge,
                        comment = data.comment,
                        files = data.files;
                    var text = "수정하기",
                        url = "/api/Timeline/timeline_edit";
                    $("#wirtePopupContainer").remove();
                    _Fx.timeline_write_edit_html(text, url, files);
                    $("#writepopup").css({
                        width: $(document).width(),
                        height: $(document).height()
                    });
                    $("#timeWrite").css({
                        top: "448px",
                        left: "28%"
                    });
                    var timeline_adr = $("#writepopup"),
                        timeline_append_adr = timeline_adr.find("#timeLineAdd > div.locationTag"),
                        timeline_text = "지역",
                        timeline_text2 = "상세지역";
                    _Fx.timelineLocation_json(timeline_adr, timeline_append_adr, timeline_text, timeline_text2, flocation1, flocation2);
                    var minAdr = $("#timeLineAdd").find(".ageTag .beforeAge select"),
                        maxAdr = $("#timeLineAdd").find(".ageTag .afterAge select");
                    _Fx.selectOptionHtml(minAdr, maxAdr, minAge, maxAge);

                    console.log(files);
                    $("#contentTitle")
                        .find("#conTitle")
                        .val(title);
                    if (fGender == "male") {
                        $("#timeLineAdd")
                            .find(".genderTag .male")
                            .addClass("genderPick");
                        $("#timeLineAdd")
                            .find('.genderTag input[type="hidden"]')
                            .val(fGender);
                    } else if (fGender == "female") {
                        $("#timeLineAdd")
                            .find(".genderTag .female")
                            .addClass("genderPick");
                        $("#timeLineAdd")
                            .find('.genderTag input[type="hidden"]')
                            .val(fGender);
                    } else if (fGender == "all") {
                        $("#timeLineAdd")
                            .find(".genderTag .all")
                            .addClass("genderPick");
                        $("#timeLineAdd")
                            .find('.genderTag input[type="hidden"]')
                            .val(fGender);
                    }
                    $("#time_input").val(comment);
                }
            });
        })
        .on("click", "#mytimeline > div.nonTime", function () {
            //마이페이지 타임라인 등록
            /*  var url = "/api/Timeline/timeline_presence",
                _this = $(this);

            _Fx.timelineWrite_ajax(url, _this); */
            var text = "등록하기",
                url = "/api/Timeline/timeline_insert";
            $("#wirtePopupContainer").remove();
            _Fx.timeline_write_edit_html(text, url);
            $("#writepopup").css({
                width: $(document).width(),
                height: $(document).height()
            });
            $("#timeWrite").css({
                top: "448px",
                left: "28%"
            });
            console.log($("#mainContent").length);
            var timeline_adr = $("#writepopup"),
                timeline_append_adr = timeline_adr.find("#timeLineAdd > div.locationTag"),
                timeline_text = "지역",
                timeline_text2 = "상세지역";
            _Fx.timelineLocation_json(timeline_adr, timeline_append_adr, timeline_text, timeline_text2);
            var minAdr = $("#timeLineAdd").find(".ageTag .beforeAge select"),
                maxAdr = $("#timeLineAdd").find(".ageTag .afterAge select");
            _Fx.selectOptionHtml(minAdr, maxAdr);
        })
        .on("click", "#myPageContent > div.pswContent > div.pswChange", function () {
            // 마이페이지 비밀번호 재설정 클릭 이벤트
            var login_account = $("#myPageContent .accountContent .loginAccount .accountImg").prop("class");
            console.log(login_account);
            if (login_account == "accountImg appORweb") {
                _Fx.mypage_psw_html();
                $("#findPwLayer").css("height", "270px");
                var do_width = $(window).width(),
                    layer_width = $("#findPwLayer").width(),
                    real_width = (do_width - layer_width) / 2,
                    do_height = $(window).height(),
                    layer_height = $("#findPwLayer").height(),
                    real_height = (do_height - layer_height) / 2;

                $("#findPwLayer").css({
                    left: real_width,
                    top: real_height
                });
                $("#findPwBack").css({
                    width: $(window).width(),
                    height: $("#mainContent").height()
                });
            } else {
                alert("SNS 계정은 해당 사이트에서 비밀번호를 변경하셔야 합니다.");
            }
        })
        .on("change", '#photoContent .imgUp input[type="file"]', function (e) {
            // 마이페이지 내사진 파일 올리기 이벤트
            var files = e.target.files;
            var arr = Array.prototype.slice.call(files);

            //업로드 가능 파일인지 체크
            for (var i = 0; i < files.length; i++) {
                if (!_Fx.checkExtension(files[i].name, files[i].size)) {
                    return false;
                }
            }
            //console.log(arr);
            var form = $("#form_photo")[0];
            console.log(form);
            var formData = new FormData(form);
            //_Fx.mypage_preview(arr);
            console.log(form);
            console.log(formData);
            formData.append("division", "0");
            console.log(formData);
            $.ajax({
                url: "/api/oauth/img_upload",
                enctype: "multipart/form-data",
                processData: false,
                contentType: false,
                data: formData,
                type: "POST",
                cache: false,
                timeout: 600000,
                success: function (res) {
                    console.log(res);
                    //location.href = '/auth/mypage';
                    var data = res.data,
                        files = data.files,
                        f_idx = files[0].f_idx,
                        img_thumb = files[0].thumb,
                        html = "",
                        img_length = $("#photoContent").children().length;

                    html += '<div class="imgUp 1">';
                    html += '<div class="closeBtn">';
                    html += "<span></span>";
                    html += "</div>";
                    html += '<div class="plusImg nonDisplay"></div>';
                    //html += '<input type="file" id="file_img_' + (s + 1) + '" class="upImg ' + (s + 1) + '" name="img[]">';
                    html += '<input type="hidden" value="' + f_idx + '">';
                    html += '<div id="preview">';
                    html += '<div style="display: inline-flex; width: 134px; height: 166px;">';
                    html += '<img src="' + img_thumb + '" title="' + img_thumb + '" width=134 height=166 />';
                    html += "</div></div>";
                    html += "</div>";

                    $("#photoContent").prepend(html);
                    for (var n = 0; n < img_length; n++) {
                        $("#photoContent")
                            .children()
                            .eq(n + 1)
                            .prop("class", "imgUp " + (n + 2) + "");
                    }
                },
                error: function (request, status, error) {
                    console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                }
            });
        })
        .on("click", "#photoContent .imgUp .closeBtn", function () {
            // 마이페이지 내사진 삭제버튼 클릭 이벤트
            var select_img = $(this).parents(".imgUp"),
                select_idx = $(this)
                .siblings('input[type="hidden"]')
                .val(),
                arr = new Array();

            arr[0] = select_idx;
            console.log(arr);
            console.log(select_img);
            console.log($(this));
            console.log(select_idx);
            $.ajax({
                type: "POST",
                url: "/api/oauth/img_delete",
                data: {
                    remove: arr,
                    division: 0
                },
                success: function (res) {
                    console.log(res);
                    select_img.remove();
                    var img_length = $("#photoContent").children().length;
                    for (var i = 0; i < img_length; i++) {
                        $("#photoContent")
                            .children()
                            .eq(i + 1)
                            .prop("class", "imgUp " + (i + 2) + "");
                    }
                },
                error: function (request, status, error) {
                    console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                }
            });
        });

    DOCUMENT.on("click", "#idBtn .requestContainer .cancellBtn", function () {
            // 마이페이지 비밀번호 재설정 취소 버튼 클릭
            $("#findPwBack").remove();
        })
        .on("click", "#memberInfoHeader .closeBtn", function () {
            // 마이페이지 프로필변경 취소버튼 클릭 이벤트
            $("#memberInfoBack").remove();
        })
        .on("click", "#phChangeHeader .closeBtn span", function () {
            // 마이페이지 휴대폰 변경 팝업 닫기 클릭 이벤트
            $("#phChangeBack").remove();
        })
        .on("click", "#phoneBtn div", function () {
            var newPhone = $("#afterPh")
                .val()
                .replace(/\-/g, "");
            console.log(newPhone);
            $.ajax({
                type: "POST",
                url: "/api/oauth/phone_edit",
                data: {
                    phone: newPhone
                },
                success: function (res) {
                    console.log(res);
                    var Hyphen_phone = newPhone.replace(/(\d{3})(\d{4})(\d{4})/, "$1-$2-$3");
                    $("#myPageContent .phoneContent .phone span").text(Hyphen_phone);
                    $("#phChangeBack").remove();
                },
                error: function (request, status, error) {
                    console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    var error_message = JSON.parse(request.responseText);
                    if (request.status == 406) {
                        alert(error_message.message);
                    } else if (request.status == 500) {
                        console.log(error_message.message);
                        alert("사전에 정의되어 있지 않은 오류가 발생하였습니다.");
                    }
                }
            });
        })
        .on("click", "#idBtn > div.mypageSubBtn", function () {
            // 마이페이지 비밀번호 재설정 재설정 완료 이벤트
            var newPw = $("#newPw").val(),
                newPwConfirm = $("#newPwConfirm").val();

            if (newPw != newPwConfirm) {
                alert("비밀번호가 일치하지 않습니다.");
                $("#newPwConfirm")
                    .val("")
                    .focus();
            } else {
                $.ajax({
                    type: "POST",
                    url: "/api/oauth/mypage_pass",
                    data: {
                        password: newPw,
                        password_confirm: newPwConfirm
                    },
                    success: function (res) {
                        console.log(res);
                        var date = new Date(),
                            month = ("0" + (date.getMonth() + 1)).slice(-2),
                            day = ("0" + date.getDate()).slice(-2);
                        console.log(month);
                        console.log(day);
                        $("#myPageContent .pswContent .psw .pswChangeDate").text("최종 변경일: " + month + "월" + day + "일");
                        DOCUMENT.find("#findPwBack").remove();
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        var error_message = JSON.parse(request.responseText);
                        if (request.status == 406) {
                            alert(error_message.message);
                        } else if (request.status == 500) {
                            console.log(error_message.message);
                            alert("사전에 정의되어 있지 않은 오류가 발생하였습니다.");
                        }
                    }
                });
            }
        })
        .on("click", "#memberGender .genderContent label", function () {
            // 마이페이지 성별 선택 이벤트
            $(this).addClass("select_gen");
            $(this)
                .siblings()
                .removeClass("select_gen");
        })
        .on("change", "#memberAge .ageContent select", function () {
            //마이페이지 나이 선택 이벤트
            $("#memberAge .ageContent select option:selected").attr("selected", true);
            $("#memberAge .ageContent select option:selected")
                .siblings()
                .attr("selected", false);
        })
        .on("click", "#myPageContent > div.profileContent > label", function () {
            //마이페이지 회원정보수정 클릭 이벤트 ( 프로필 수정 팝업)
            var age_html = "",
                nick = $("#myPageContent .nickContent .myNickname span").text(),
                gender = $("#myPageContent .genderContent .gender span").text(),
                age = $("#myPageContent .ageContent .age span")
                .text()
                .replace(/\세/g, ""),
                img = $("#profileImg #preview").prop("src"),
                local = $("#myPageContent .locationContent .location .mylocation")
                .text()
                .replace(/\시/g, ""),
                local2 = $("#myPageContent .locationContent .location .mylocation2")
                .text()
                .replace(/\구/g, "");

            console.log(nick);
            console.log(gender);
            console.log(age);
            console.log(img);
            console.log(local);
            console.log(local2);
            _Fx.mypage_member_edit_html();
            var do_width = $(window).width(),
                layer_width = $("#memberInfoLayer").width(),
                real_width = (do_width - layer_width) / 2,
                do_height = $(window).height(),
                layer_height = $("#memberInfoLayer").height(),
                real_height = (do_height - layer_height) / 2;

            $("#memberInfoLayer").css({
                left: real_width,
                top: real_height
            });
            $("#memberInfoBack").css({
                width: $(window).width(),
                height: $("#mainContent").height()
            });
            $("#nick").val(nick);
            if (gender == "남성") {
                $("#memberGender #male")
                    .parent("label")
                    .addClass("select_gen");
            } else if (gender == "여성") {
                $("#memberGender #female")
                    .parent("label")
                    .addClass("select_gen");
            }
            for (var i = 19; i < 61; i++) {
                if (age != i) {
                    age_html += '<option value="' + i + '">' + i + "</option>";
                } else {
                    age_html += '<option value="' + i + '" selected="selected">' + i + "</option>";
                }
            }
            $("#memberAge .ageContent select").append(age_html);
            $("#memberPhoto #preview img").prop("src", img);

            var timeline_adr = $("#memberInfoBack"),
                timeline_append_adr = timeline_adr.find("#memberLocal"),
                timeline_text = "지역",
                timeline_text2 = "상세지역";
            _Fx.timelineLocation_json(timeline_adr, timeline_append_adr, timeline_text, timeline_text2, local, local2);
        })
        .on("click", "#profileImg > label > span", function () {
            $("#myPageContent > div.profileContent > label").trigger("click");
        })
        .on("change", "#memberInfoBack #memberInfoLayer #memberLocal #first", function () {
            // 마이페이지 프로필변경 회원정보수정  1차 지역 재선택시 이벤트
            console.log("회원정보수정에서 지역 선택 1차");
            var adr = $("#memberInfoBack"),
                text = "지역";
            adr.find("#first option:selected").attr("selected", "selected");
            adr.find("#first option:selected")
                .siblings()
                .attr("selected", false);
            if (adr.find("#first option:selected").text() == text) {
                adr.find("#second .noDel").prop("selected", "selected");
                adr.find("#second")
                    .children()
                    .not(".noDel")
                    .remove();
            } else {
                _Fx.timelineLocationSecond(locationData, adr);
            }
        })
        .on("change", "#memberInfoBack #memberInfoLayer #memberLocal #second", function () {
            // 마이페이지 프로필변경 회원정보수정  2차 지역 재선택시 이벤트
            var adr = $("#memberInfoBack");
            adr.find("#second option:selected").attr("selected", "selected");
            adr.find("#second option:selected")
                .siblings()
                .attr("selected", false);
        })
        .on("click", "#memberPhoto .photoContent .imgUp .closeBtn span", function () {
            //마이페이지 회원정보수정 이미지 삭제 이벤트
            var select_img = $(this).parents(".imgUp"),
                select_idx = $(this)
                .parent()
                .siblings('input[type="hidden"]')
                .val(),
                arr = new Array();

            arr[0] = select_idx;
            console.log(arr);
            console.log(select_img);
            console.log($(this));
            console.log(select_idx);
            $.ajax({
                type: "POST",
                url: "/api/oauth/img_delete",
                data: {
                    remove: arr,
                    division: 1
                },
                success: function (res) {
                    console.log(res);
                    select_img.remove();
                    var html = "";

                    html += '<div class="imgUp">';
                    html += '<div class="closeBtn nonDisplay">';
                    html += "<span></span>";
                    html += "</div>";
                    html += '<div class="plusImg"></div>';
                    html += '<form id="form_photo">';
                    html += '<label for="memberImg">';
                    html += '<input type="file" id="memberImg" class="upImg" name="img[]">';
                    html += '<div id="preview">';
                    html += '<img src="//ntalk.me/img/people_base_back.svg" width="110" height="110">';
                    html += "</div>";
                    html += "</label>";
                    html += "</form>";
                    html += "</div>";
                    $("#memberPhoto > div.photoContent").prepend(html);
                },
                error: function (request, status, error) {
                    console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                }
            });
        })
        .on("change", "#memberImg", function (e) {
            // 마이페이지 회원정보수정 프로필 사진 변경 이벤트
            var files = e.target.files;

            //업로드 가능 파일인지 체크
            for (var i = 0; i < files.length; i++) {
                if (!_Fx.checkExtension(files[i].name, files[i].size)) {
                    return false;
                }
            }
            //console.log(arr);
            var form = $("#form_photo")[0];
            console.log(form);
            var formData = new FormData(form);
            //_Fx.mypage_preview(arr);
            console.log(form);
            console.log(formData);
            formData.append("division", "1");
            console.log(formData);
            $.ajax({
                url: "/api/oauth/img_upload",
                enctype: "multipart/form-data",
                processData: false,
                contentType: false,
                data: formData,
                type: "POST",
                cache: false,
                timeout: 600000,
                success: function (res) {
                    console.log(res);
                    $("#memberPhoto > div.photoContent .imgUp").remove();
                    //location.href = '/auth/mypage';
                    var data = res.data,
                        img_thumb = data.profile_thum,
                        html = "";

                    html += '<div class="imgUp">';
                    html += '<div class="closeBtn">';
                    html += "<span></span>";
                    html += "</div>";
                    html += '<div class="plusImg nonDisplay"></div>';
                    //html += '<input type="file" id="file_img_' + (s + 1) + '" class="upImg ' + (s + 1) + '" name="img[]">';
                    html += '<div id="preview">';
                    html += '<div style="display: inline-flex; width: 110px; height: 110px;">';
                    html += '<img src="' + img_thumb + '" title="' + img_thumb + '" width=110 height=110 />';
                    html += "</div></div>";
                    html += "</div>";

                    $("#memberPhoto > div.photoContent").prepend(html);
                },
                error: function (request, status, error) {
                    console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                }
            });
        })
        .on("click", "#overlap_nick", function () {
            //마이페이지 회원정보수정 닉네임 중복 검사
            var nick_val = $("#nick").val(),
                division_nick = "닉네임",
                confirm_val = {
                    id: nick_val,
                    division: "nickname"
                };

            _Fx.overlap_ajax(confirm_val, division_nick);
        })
        .on("click", "#memberBtn > div", function () {
            // 마이페이지 회원정보수정 수정하기 클릭 이벤트
            var nick_val = $("#nick").val(),
                gender_val = $("#memberGender .genderContent .gender.select_gen input").val(),
                age_val = $("#memberAge .ageContent select option:selected").val(),
                local_val = $("#memberLocal #first option:selected").val(),
                local2_val = $("#memberLocal #second option:selected").val(),
                login_nick = $("#navibar .mainLoginContainer .logoutPanel .userInfo .nick").text();

            console.log(nick_val);
            console.log(gender_val);
            console.log(age_val);
            console.log(local_val);
            console.log(local2_val);
            console.log(login_nick);
            console.log(nickOverlap);
            if (nickOverlap == null && nick_val != login_nick) {
                alert("닉네임 중복 검사를 해주세요.");
            } else {
                $.ajax({
                    type: "POST",
                    url: "/api/oauth/itemEditSubmit",
                    data: {
                        nickname: nick_val,
                        gender: gender_val,
                        age: parseInt(age_val),
                        location: local_val,
                        location2: local2_val
                    },
                    success: function (res) {
                        console.log(res);
                        location.reload();
                    },
                    error: function (request, status, error) {
                        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    }
                });
            }
        })
        .on("click", "#myPageContent .phoneContent .phoneChange span", function () {
            var beforePh = $("#myPageContent .phoneContent .phone span")
                .text()
                .replace(/\-/g, "");
            _Fx.mypage_ph_change_html();
            var do_width = $(window).width(),
                layer_width = $("#phChangeLayer").width(),
                real_width = (do_width - layer_width) / 2,
                do_height = $(window).height(),
                layer_height = $("#phChangeLayer").height(),
                real_height = (do_height - layer_height) / 2;

            $("#phChangeLayer").css({
                left: real_width,
                top: real_height
            });
            $("#phChangeBack").css({
                width: $(window).width(),
                height: $("#mainContent").height()
            });
            $("#beforePh").val(beforePh);
        });

    /*********************************** 채팅 이벤트 ****************************************************/

    $("#chatContainer")
        .on("click", "#btn", function () {
            //메세지 전송버튼 클릭

            var coment = $("#chatContainer #input").val(),
                targetNick = $(opener.document)
                .find("#userNick")
                .val();
            console.log(coment);

            Encryption_time = $.md5(currentTime);
            console.log(date);
            console.log(year);
            console.log(currentTime);
            console.log(Encryption_time);
            console.log($("#userNick").val());
            console.log(targetNick);
            console.log(coment);
            console.log(_socketChat);
            _Io.sendEmit(
                _socketChat,
                _Io.submit_request(200, {
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
                })
            );
        })
        .on("click", ".file_up", function () {
            //이미지 파일 업로드 창 호출

            console.log("file update.............");
            var fileInput = $('input[name="file_up"]');
            fileInput.click();
        })
        .on("change", 'input[name="file_up"]', function () {
            // 이미지 파일 선택시 전송 이벤트

            var img = this.files[0],
                myNickname = $("#myNick").val(),
                target = window.target,
                roomNumber = window.roomId,
                Encryption_time = $.md5(currentTime),
                request = Encryption_time,
                frm = new FormData();
            console.log(request);
            frm.append("file", img);
            frm.append("from", myNickname);
            frm.append("to", target);
            frm.append("roomId", roomNumber);
            frm.append("requestId", request);

            console.log(frm);
            $.ajax({
                type: "POST",
                url: "https://ntalk.me:8443/chat/sendImage",
                processData: false,
                contentType: false,
                data: frm,
                async: true,
                success: function (data) {
                    console.log(data);
                },
                error: function (request, status, error) {
                    console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                }
            });
        })
        .on("click", "#exitbtn", function () {
            // 채팅방 나가기

            var myNick = $("#myNick").val(),
                _this = $(this),
                con = "",
                clickOffset = _this.offset(),
                topPosition = clickOffset.top - 330,
                leftPosition = clickOffset.left + 95;
            console.log(clickOffset);
            console.log(topPosition);
            console.log(leftPosition);
            con += '<div id="confirmContainer">';
            con += '<div class="conTitle"><span>엔톡</span></div>';
            con += '<div class="conContent">대화방을 나가시겠습니까?</div>';
            con += '<div id="conBtnContainer">';
            con += '<div class="conTrue">확인</div>';
            con += '<div class="conFalse">취소</div>';
            con += "</div>";
            con += "</div>";
            $("#chatContainer").before(con);
            var conpop = $("#confirmContainer"),
                back = $("body");
            conpop.css({
                top: topPosition,
                left: leftPosition
            });
            back.css("background-color", "rgb(235, 235, 228)");
            $("#input").attr("disabled", true);
            $("#btn").attr("disabled", true);

            DOCUMENT.on("click", "#conBtnContainer .conTrue", function () {
                _Io.sendEmit(
                    _socketChat,
                    _Io.submit_request(204, {
                        roomId: window.roomId,
                        nickName: myNick
                    })
                );
                window.close();
            });
            DOCUMENT.on("click", "#conBtnContainer .conFalse", function () {
                console.log(back);
                $("#confirmContainer").remove();
                back.css("background-color", "#fff");
                $("#input").attr("disabled", false);
                $("#btn").attr("disabled", false);
            });
        })
        .on("click", "#messages img", function () {
            // 채팅방 이미지 원본 사이즈 보기

            console.log("이미지 클릭");
            var _this = $(this),
                imgSrc = _this.attr("src");
            console.log(imgSrc);
            _Talk.resize(imgSrc);
        });

    /*  $(window).bind("beforeunload", function () {
        // 채팅창 닫기 (퇴장X) 이벤트

        _Io.sendEmit(
            _socketChat,
            _Io.submit_request(206, {
                roomId: window.roomId
            })
        );
    });
 */
    $("#input").on("propertychange change keyup paste textarea", function (event) {
        //채팅 타이핑 시작 & 종료

        var n = 0,
            nick = $("#myNick").val(),
            roomNumber = $("#roomId").val(),
            input_val = $("#input").val();
        console.log("타이핑 시작 : " + typing);
        console.log(roomNumber);
        console.log(window.roomId);
        console.log(event.keyCode);
        console.log(input_val);
        if (input_val != "") {
            if (typing == false) {
                _Io.sendEmit(
                    _socketChat,
                    _Io.submit_request(201, {
                        roomId: window.roomId,
                        nickName: nick,
                        target: window.target
                    })
                );
                typing = true;
                timer = setInterval(function () {
                    if (n <= 1) {
                        n++;
                        console.log(n);
                    } else {
                        clearInterval(timer);
                        _Io.sendEmit(
                            _socketChat,
                            _Io.submit_request(202, {
                                roomId: window.roomId,
                                nickName: nick,
                                target: window.target
                            })
                        );
                        typing = false;
                        console.log("타이핑 멈춤 : " + typing);
                    }
                }, 1000);
            } else {
                clearInterval(timer);
                timer = setInterval(function () {
                    if (n <= 1) {
                        n++;
                        console.log(n);
                    } else {
                        clearInterval(timer);
                        _Io.sendEmit(
                            _socketChat,
                            _Io.submit_request(202, {
                                roomId: window.roomId,
                                nickName: nick,
                                target: window.target
                            })
                        );
                        typing = false;
                        console.log("타이핑 멈춤 : " + typing);
                    }
                }, 1000);
                console.log("타이핑 재시작........ : " + typing);
            }
        } else {
            clearInterval(timer);
            typing = false;
            _Io.sendEmit(
                _socketChat,
                _Io.submit_request(202, {
                    roomId: window.roomId,
                    nickName: nick,
                    target: window.target
                })
            );
        }
    });
    $("#input").keypress(function (event) {
        // 채팅 엔터키로 입력 이벤트

        var nick = $("#myNick").val();
        if (event.keyCode === 13 && !event.shiftKey) {
            $("#btn").click();
            clearInterval(timer);
            console.log(timer);
            _Io.sendEmit(
                _socketChat,
                _Io.submit_request(202, {
                    roomId: window.roomId,
                    nickName: nick,
                    target: window.target
                })
            );
            typing = false;
            console.log("타이핑 엔터키로 끝냄 : " + typing);
            return false;
        }
    });

    $("#messages").scroll(function () {
        // 이전 채팅 내용 (히스토리) 스크롤 이벤트

        var _this = $(this),
            scrollT = _this.scrollTop(),
            limit_val = 50;

        if (scrollT < 30) {
            var firstOrder = $("#messages")
                .children()
                .first()
                .children("input")
                .val();
            console.log(firstOrder);
            if (firstOrder > 0) {
                order_plus++;
                _Io.sendEmit(
                    _socketChat,
                    _Io.submit_request(209, {
                        roomId: window.roomId,
                        start: limit_val * order_plus,
                        limit: limit_val
                    })
                );
            }
        }
    });

    /* function autoHeight() {
        // 채팅 입력창 높이 조절 (확인하고 삭제하던지 수정하던지 할것)
        var txt = $("#input");
        txt.css("height", "auto");
        var txtHeight = txt.prop("scrollHeight");
        txt.css("height", txtHeight);
    }
    autoHeight(); */
})();