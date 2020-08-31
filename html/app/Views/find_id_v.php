<body>
    <div id="findId">
        <div id="findIdLogoContainer">
            <div class="s_title_logo">
                <img src="https://www.dreamapt.co.kr/mod/images/membership/t_find_id.gif" alt="엔톡 아아디 찾기">
            </div>
        </div>
        <div id="findIdContent">
            <div class="titleText">
                <h3>아이디 찾기</h3>
            </div>
            <div class="phoneCertification">
                <div>
                    <label>
                        <span>휴대전화번호</span>
                        <input type="text" autocomplete="off" name="phone" placeholder="01X로 시작되는 전화번호" maxlength="13">
                    </label>
                </div>
                <div class="p_info_text">
                    <p>
                        아이디를 찾기위해 귀하가 가입한 휴대전화번호를 인증합니다.<br>
                        - 을 제외한 휴대전화 번호를 입력하여 선택해주세요
                    </p>
                </div>
                <div class="request_input">
                    <input type="button" value="인증요청">
                </div>
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
                <div id="submit_value">
                    <label>
                        <input type="button" value="인증확인">
                    </label>
                </div>
            </div>
        </div>
        <div id="findIdFooter" class="findIdFooter nonDisplay">
            <div class="responseContainer">
                <div class="response">
                    <input type="text" name="response1">
                    <input type="text" name="response2">
                    <input type="text" name="response3">
                </div>
                <div class="goMain">
                    <a href="http://ntalk.me/auth">LOGIN</a>
                </div>
            </div>
        </div>
    </div>
    



<!-- <div id="findIdBack">
    <div id="findIdLayer">
        <div id="idHeader">
            <div class="findIdTitle">
                <span>아이디 찾기</span>
            </div>
            <div class="closeBtn">
                <span>닫기</span>
            </div>
        </div>
        <div id="idcontent">
            <div class="requestContent">
                <div class="phoneNum">
                    <div class="phoneTitle">
                        <span>휴대폰 번호</span>
                    </div>
                    <div class="phoneContent">
                        <label>
                            <input type="text" id="idPhone" autocomplete="off" name="phone" placeholder="- 없이 번호를 입력해주세요" maxlength="16" minlength="8">
                        </label>
                    </div>
                </div>
                <div class="confirmNum">
                    <div class="confirmTitle">
                        <span>인증번호</span>
                    </div>
                    <div class="confirmContent">
                        <label>
                            <input type="text" id="idconfirm" autocomplete="off" name="phone" placeholder="인증번호를 입력해 주세요" maxlength="16" minlength="8">
                            <div class="time_contanier">
                                <div class="timer_text">남은시간 </div>
                                <div class="time" id="time"></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="resultContent nonDisplay">
                <div class="resultId">
                    <div class="oneId">
                        <span class="snsImg"></span>
                        <span class="findUserId"></span>
                    </div>
                    <div class="twoId">
                        <span class="snsImg"></span>
                        <span class="findUserId"></span>
                    </div>
                    <div class="threeId">
                        <span class="snsImg"></span>
                        <span class="findUserId"></span>
                    </div>
                </div>
                <div class="resultText">
                    <span>이 아이디로 로그인을 하시겠습니까?</span>
                    <span>비밀번호가 기억나지 않으시면 재설정 해주세요</span>
                </div>
            </div>
        </div>
        <div id="idBtn">
            <div class="requestContainer">
                <div class="requestBtn">인증요청</div>
                <div class="requestReBtn nonDisplay">인증번호 재전송</div>
            </div>
            <div class="subBtn">아이디 찾기</div>
        </div>
    </div>
</div> -->

<!-- <div id="findPwBack">
    <div id="findPwLayer">
        <div id="pwHeader">
            <div class="findpwTitle">
                <span>비밀번호 재설정</span>
            </div>
            <div class="closeBtn">
                <span>닫기</span>
            </div>
        </div>
        <div id="pwcontent">
            <div class="requestContent">
                <div class="userId">
                    <div class="idTitle">
                        <span>아이디</span>
                    </div>
                    <div class="idContent">
                        <label>
                            <input type="text" id="pw_id" autocomplete="off" name="id" placeholder="아이디를 입력하세요">
                        </label>
                    </div>
                </div>
                <div class="phoneNum">
                    <div class="phoneTitle">
                        <span>휴대폰 번호</span>
                    </div>
                    <div class="phoneContent">
                        <label>
                            <input type="text" id="idPhone" autocomplete="off" name="phone" placeholder="- 없이 번호를 입력해주세요" maxlength="16" minlength="8">
                        </label>
                    </div>
                </div>
                <div class="confirmNum">
                    <div class="confirmTitle">
                        <span>인증번호</span>
                    </div>
                    <div class="confirmContent">
                        <label>
                            <input type="text" id="pwconfirm" autocomplete="off" name="certification" placeholder="인증번호를 입력해 주세요" maxlength="6">
                            <div class="time_contanier">
                                <div class="timer_text">남은시간 </div>
                                <div class="time" id="time"></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="resultContent nonDisplay">
                <div class="newPwContainer">
                    <div class="newPwTitle">
                        <span>새 비밀번호</span>
                    </div>
                    <div class="newPwContent">
                        <label>
                            <input type="text" id="newPw" autocomplete="off" name="newPw" placeholder="변경할 비밀번호를 입력하세요">
                        </label>
                    </div>
                </div>
                <div class="newPwConfirmContainer">
                    <div class="newPwConfirmTitle">
                        <span>비밀번호 확인</span>
                    </div>
                    <div class="newPwConfirmContent">
                        <label>
                            <input type="text" id="newPwConfirm" autocomplete="off" name="newPwConfirm" placeholder="변경할 비밀번호를 다시 한번 입력하세요">
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div id="idBtn">
            <div class="requestContainer">
                <div class="requestBtn">인증요청</div>
                <div class="requestReBtn nonDisplay">인증번호 재전송</div>
            </div>
            <div class="subBtn">아이디 찾기</div>
            <div class="sub_reset nonDisplay">재설정 하기</div>
        </div>
    </div>
</div> -->

    <div id="phChangeBack">
        <div id="phChangeLayer">
            <div id="phChangeHeader">
                <div class="headerTitle">
                    <span>휴대폰 번호 변경</span>
                </div>
                <div class="closeBtn">
                    <span></span>
                </div>
            </div>
            <div id="beforePhone">
                <div class="beforeTitle">
                    <span>기존 휴대폰 번호</span>
                </div>
                <div class="beforeContent">
                    <label>
                        <input type="text" id="beforePh" name="phone">
                    </label>
                </div>
            </div>
            <div id="afterPhone">
                <div class="afterTitle">
                    <span>신규 휴대폰 번호</span>
                </div>
                <div class="afterContent">
                    <label>
                        <input type="text" id="afterPh" name="phone">
                        <div id="overlap_nick">중복확인</div>
                    </label>
                </div>
            </div>
            <div id="memberBtn">
                <div class="phChangeBtn">수정하기</div>
            </div>
        </div>
    </div>
            <div id="memberAge">
                <div class="ageTitle">
                    <span>나이</span>
                </div>
                <div class="ageContent">
                    <select>
                    </select>
                </div>
            </div>
            <div id="memberPhoto">
                <div class="photoTitle">
                    <span>사진등록</span>
                </div>
                <div class="photoContent">
                    <div class="imgUp">
                        <div class="closeBtn">
                            <span></span>
                        </div>
                        <div class="plusImg nonDisplay"></div>
                        <input type="file" id="memberImg" class="memberImg" name="img[]">
                        <input type="hidden" value=''>
                        <div id="preview">
                            <div style="display: inline-flex; width: 110px; height: 110px;">
                                <img src="" width="110" height="110">
                            </div>
                        </div>
                        <div class="infoText">
                            <span>
                                선정적이거나 사회적으로 이슈가되는</br>
                                이미지는 등록을 금지합니다.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div id="memberLocal">
                <div class="localTitle">
                    <span>지역</span>
                </div>
            </div>
            <div id="memberBtn">
                <div class="memberInfoEdit">수정하기</div>
            </div>
        </div>
    </div>





    <div id="footerContainer">
        <div class="footerMenu">
            <div>
                <span>회사소개</span> |
            </div>
            <div>
                <span>이용약관</span> |
            </div>
            <div>
                <span>개인정보취급방침</span> |
            </div>
            <div>
                <span>청소년보호정책</span> |
            </div>
            <div>
                <span>광고문의</span> |
            </div>
            <div>
                <span>고객센터</span> |
            </div>
        </div>
        <div class="footerContent">
            <div class="footerImg">
                <span></span>
            </div>
            <div class="footerinfo">
                <div class="companyTitle">
                    <span>하온컴퍼티</span>
                </div>
                <div class="companyInfo">
                    <div class="owner">대표이사 : 이원하</div>
                    <div class="adr">주소 : 서울특별시 강남구 논현동 28-11 2층</div>
                    <div class="licenseNumber">사업자등록번호 : 603-81-50424</div>
                    <div class="licenseResult"><a href="#">사업자정보확인</a></div>
                </div>
                <div clss="companyInfo2">
                    <div class="reportNumber">통신판매업신고번호 : 제 2018-서울서초-1946호</div>
                    <div class="pipOfficer">개인정보보호 책임자 : 오재영</div>
                    <div class="email_adr">E-mail : ntalk@naver.com</div>
                </div>
                <div class="copyWrite">Copyright (c) Ntalk. All Rights Reserved</div>
            </div>
            <div class="footerCSC">
                <div class="social">
                    <span class="facebook"></span>
                    <span class="twitter"></span>
                    <span class="instagram"></span>
                </div>
                <div class="serviceInfo">
                    고객행복센터<span>1522-8276</span>
                </div>
                <div class="operationTime">
                    <span>평일 09:00 ~ 18:00</span>
                    <span>점심시간 12:00 ~ 13:30</span>
                </div>
                <div class="infoText">
                    주말 및 공휴일은 <span>1:1 상담</span>을 이용해 주세요.
                </div>
            </div>
        </div>
    </div>