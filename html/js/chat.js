var _chat = null;

(function(){
    _chat = (function(){
        return {
            receive : function(res) {
            //    res.type === '001' // 채팅대화
            //    res.type === '100' // 접속알림
            //    res.type === '101' // 채팅초대
                var cmd = res.cmd;
                console.log(cmd);
                switch(cmd) {
                    case '100':  // 최초 접속시 (REGISTER 이후) 사용자에게 제공되는 메세지
                        console.log()
                        break;
                    case '200' :  // 채팅 메세지
                        _chat.message();
                        break;
                    case '201' :  // 채팅 타이핑 중을 알림

                        break;
                    case '202' : // 채팅 타이핑 종료를 알림

                        break;
                    case '203' :  // 채팅 방이 생성되었음

                        break;
                    case '204' : // 사용자가 채팅방에서 나갔음

                        break;
                    case '205' : // 사용자가 채팅방내 대화메세지를 읽었음

                        break;
                    case '206' : // 채팅방 화면을 퇴장함

                        break;
                    case '207' : // 채팅방 화면을 입장함

                        break;
                    case '208' :  // 채팅대화신고

                        break;
                    case '209' : // 채팅방 히스토리 요청

                        break;
                    case '210' : // 참여중인 채팅방 리스트

                        break;
                    case '211' :  // 채팅방 ID 찾기

                        break;
                    case '212' : // (Web 전용 커멘드)웹 에서 채팅 ROOM_ID 가 없이 Socket이 추가 접속되었음을 서버로 알림
                                // (Guest 상태의 Redis 접속정보에서 User 로 Upgrade, /READY Socket 상태로 대기)
                                // Mobile 에서는 단일 소켓으로 모두 처리 가능하나 웹에서는 창단위로 Socket이 생성되므로
                                // Socket 접속 정보의 Upgrade 과정이 필요함

                        break;
                    case '300' : // 사용자 리스트

                        break;
                    case '400' :  // 쪽지 메세지

                        break;
                    case '500' : // 타임라인 등록 알림 데이터

                        break;
                    case '601' :  // 사용자 접속 알림

                        break;
                    case '602' :  // 사용자 접속 해제 알림

                        break;
                    case '603' : // 사용자 정보가 변경되었음

                        break;
                    case '1000' : // 다른장비에서 사용자가 접속하였음

                        break;
                    default :

                }
            },
            message : function(res) {
                //메세지처리 함
                alert("굿");
            }

        }
    })();
})();

$(function(){
    var _server = 'wss://ntalk.me:8443/Lounge';
    var _socket = io(_server, {
            transports: ['websocket']
        }).on('connect', function() {
            console.log('connect');
            console.log(_socket.connected);
            _socket.emit ( 'hello' , 'world2' );
        }).on('receive', function(res) { // 사용자 파이프
            console.log(res);
            _chat.receive(res);
        }).on('SUBMIT', function(msg) {
            /*var msg = $('#input').val();
            $('#messages').append($('<li>').text(msg));*/
        }).emit('REGISTER', function(response) {
            console.log(response);
        }).on('disconnect', function() {
            console.log('you have been disconnected');
        }).on('reconnect', function() {
            console.log('you have been reconnected');
        }).on('reconnect_error', function() {
            console.log('attempt to reconnect has failed');
        });

    $('#chatContainer').on('click', '#btn', function() {
        _socket.emit('SUBMIT', $('#input').val());
    })

});
