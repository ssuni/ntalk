<body onload="javascript:_Io.connectChat()" onbeforeunload="javascript:_Io.sendEmit(
            _socketChat,
            _Io.submit_request(206, {
                roomId: window.roomId
            })
        );">
    <div id="chatContainer">
        <div class="message_content">
            <div class="msgbox" id="message">
                <ul id="messages"></ul>
            </div>
            <div class="typing nonDisplay">상대방이 대화내용을 입력하고 있습니다....</div>
            <div class="inputContainer">
                <div class="file_up">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcS3y0HgNp58PyLX6WMq2xLb4rTLK7ea-c52jDoWYrc1ssD0EoPl" style="width:25px;">
                    </div>
                    <form id="fileadd" method="post" action="/chat/sendImage" enctype="multipart/form-data"> 
                    <input type="file" id="file_input" name="file_up">
                    </form>
                
                <!--form action="http://ntalk.me/api/Timeline/curltest" name="chating" method="POST"-->
                    <textarea type="text" id="input" cols="41" rows="1" class="msg" style="resize: none;" autofocus autocomplete="off" wrap="hard"></textarea>
                    <button type="button" id="btn" class="send">전송</button>
                    <input type="hidden" id="roomId" name="roomId">
                    <input type="hidden" id="myNick" name="myNick">
                <!--/form-->
            </div>
        </div>
        <div id="roomExit">
            <button type="button" name="exit" id="exitbtn">나가기</button>
        </div>
    </div>
   