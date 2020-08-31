<body>
    <div id="sms_confirm">
        <div class="sms_content">
            <div class="sms_title">
                <span>휴대폰 인증</span>
            </div>
            <div class="p_info_text">
                <p>
                    앤톡 회원가입을 위해서<br>
                    휴대폰 인증은 필수입니다
                </p>
            </div>
            <div class="first_request">
                <div class="phone_number">
                    <label>
                        <input type="text" autocomplete="off" id="phone" name="phone" placeholder="- 없이 번호를 입력해주세요" maxlength="13">
                        <div class="time_contanier nonDisplay">
                            <div class="timer_text"></div>
                            <div class="time" id="time"></div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="second_request">
                <!--form action="/api/oauth/sms_confirm" method="post" class="sms_form" id="sms_form"-->
                    <div class="confirm_number">
                        <label for="certification">
                            <input type="text" autocomplete="off" id="certification" name="certification" placeholder="인증번호를 입력해주세요" maxlength="6">
                        </label>
                        <input type="hidden" name="phone">
                        
                        <!-- <div class="n_info_text">
                            <p>
                                귀하의 휴대전화번호로 인증번호가 발송되었습니다.<br>
                                <br>
                                휴대전화번호로 인증번호가 전송되지 않은 경우 아래의 재전송<br>
                                버튼을 눌러서 인증번호를 다시 요청해 주세요.
                            </p>
                        </div> -->
                    </div>
                    <div class="request_input">
                        <input type="button" value="인증요청">
                    </div>
                    <div class="re_number nonDisplay">
                        <input type="button" value="인증번호 재전송">
                    </div>
                    <div class="send_number">
                        <input type="button" value="인증확인">
                    </div>
                <!--/form-->
            </div>
        </div>
    </div>
