<bady>
    <div id="findPassword">
        <div id="findPassLogoContainer">
            <div class="s_title_logo">
                <img src="https://www.gghotel.co.kr/img/kr/mypage/title_icon_1.gif" alt="엔톡 비밀번호 초기화">
            </div>
        </div>
        <div id="passContent">
            <form action="/api/oauth/find_pass" method="POST" id="passwordRequest">
                <div class="titleText">
                    <h3>비밀번호 초기화</h3>
                </div>
                <div class="firstInputContainer">
                    <div class="idInput">
                        <label>
                            <span>아이디</span>
                            <input type="text" autocomplete="off" name="id" id="id" placeholder="아이디를 입력해 주세요">
                        </label>
                    </div>
                    <div class="phoneInput">
                        <label>
                            <span>휴대전화번호</span>
                            <input type="text" autocomplete="off" name="phone" placeholder="01X로 시작되는 전화번호" maxlength="13">
                            <input type="button" value="인증요청">
                        </label>
                    </div>
                    <div class="p_info_text">
                        <p>
                            비밀번호를 초기화 하기 위해 귀하가 가입한 휴대전화번호를 인증합니다.<br>
                            - 를 제외한 휴대전화번호를 입력하여 선택해주세요.
                        </p>
                    </div>
                    <div class="certificationNumber nonDisplay">
                        <div class="confrim_number">
                            <label>
                                <span>인증번호</span>
                                <input type="text" autocomplete="off" name="certification" placeholder="인증번호 6자리 입력" maxlength="6">
                            </label>
                            <input type="hidden" name="phone">
                            <div class="time_contanier">
                                <div class="timer_text">남은 인증 시간 : </div>
                                <div class="time" id="time"></div>
                            </div>
                            <div class="n_info_text">
                                <p>
                                    귀하의 휴대전화번호로 인증번호가 발송되었습니다.<br>
                                    <br>
                                    휴대전화번호로 인증번호가 전송되지 않은 경우 아래의 재전송<br>
                                    버튼을 눌러서 인증번호를 다시 요청해 주세요.
                                </p>
                            </div>
                        </div>
                        <div class="re_number">
                            <label>
                                <input type="button" value="재 전 송">
                            </label>
                        </div>
                        <div id="submit_request">
                            <label>
                                <input type="button" value="인증확인">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="secondInputConatainer nonDisplay">
                    <div id="passwordInput" class="passwordInput">
                        <div class="s_pwContainer">
                            <label class="s_pw" for="password">
                                <span>비밀번호</span>
                                <input type="password" placeholder="비밀번호" id="password" name="password" autocomplete="new-password" minlength="8" maxlength="12">
                                <p>8자 이상 12자 이하만 사용 가능합니다.</p>
                            </label>
                            <label class="s_pw_confirm" for="pw_confirm_reslut">
                                <span>비밀번호 확인</span>
                                <input type="password" placeholder="비밀번호 확인" id="pw_confirm_result" name="password_confirm" autocomplete="new-password" minlength="8" maxlength="12">
                            </label>
                        </div>
                    </div>
                    <div id="submit_value">
                        <label>
                            <input type="submit" value="비밀번호 초기화">
                        </label>
                    </div>
                </div>
            </form>
        </div>
    </div>
